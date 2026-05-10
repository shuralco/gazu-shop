<?php

namespace App\Services\Geo;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Resolve client IP → coarse geo (lat/lng + city + country).
 *
 * Uses ip-api.com (free, ~45 req/min, no key). Per-IP cache for 24h
 * means real cost is one HTTP per unique visitor per day, never blocking
 * a request for >1 second.
 *
 * Returns null for private/loopback IPs and on any HTTP failure.
 */
class GeoLocator
{
    public function locate(string $ip): ?array
    {
        if ($this->isPrivate($ip)) {
            return null;
        }

        $cacheKey = "geo:ip:{$ip}";

        return Cache::remember($cacheKey, now()->addDay(), function () use ($ip) {
            try {
                $response = Http::timeout(1.5)
                    ->retry(1, 100)
                    ->get("http://ip-api.com/json/{$ip}", ['fields' => 'status,country,countryCode,city,lat,lon']);

                if (! $response->ok()) {
                    return null;
                }

                $data = $response->json();
                if (($data['status'] ?? null) !== 'success') {
                    return null;
                }

                return [
                    'ip' => $ip,
                    'country' => $data['country'] ?? null,
                    'country_code' => $data['countryCode'] ?? null,
                    'city' => $data['city'] ?? null,
                    'lat' => isset($data['lat']) ? (float) $data['lat'] : null,
                    'lng' => isset($data['lon']) ? (float) $data['lon'] : null,
                ];
            } catch (\Throwable $e) {
                report($e);
                return null;
            }
        });
    }

    private function isPrivate(string $ip): bool
    {
        if (! filter_var($ip, FILTER_VALIDATE_IP)) {
            return true;
        }
        return ! filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }
}
