<?php

namespace App\Services\Warehouse;

use App\Models\MerchantWarehouse;
use App\Services\Geo\GeoLocator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Resolve "closest" merchant warehouse to a request's IP.
 *
 * Strategy: GeoLocator(ip) → lat/lng → haversine to every active
 * warehouse with coords → nearest. Falls back to MerchantWarehouse::default()
 * when geo is unavailable or no warehouse has coords.
 *
 * Result is cached per session for the visit lifetime so repeat
 * lookups (product-page renders, ajax cart updates) stay free.
 */
class WarehouseLocator
{
    public function __construct(private GeoLocator $geo) {}

    /**
     * Closest active warehouse to the visitor; null if catalog has none.
     */
    public function closestForRequest(?Request $request = null): ?MerchantWarehouse
    {
        $request ??= request();
        $ip = $request->ip() ?: '';
        $sessionKey = 'closest_warehouse_id';

        if ($cached = $request->session()->get($sessionKey)) {
            $wh = MerchantWarehouse::query()->find($cached);
            if ($wh && $wh->is_active) {
                return $wh;
            }
        }

        $wh = $this->resolve($ip);
        if ($wh) {
            $request->session()->put($sessionKey, $wh->id);
        }

        return $wh;
    }

    private function resolve(string $ip): ?MerchantWarehouse
    {
        $geo = $ip ? $this->geo->locate($ip) : null;

        // Active warehouses with coords go through haversine. Anything else
        // is a fallback chain: city match → default → first active.
        $warehouses = MerchantWarehouse::query()->where('is_active', true)->get();

        if ($warehouses->isEmpty()) {
            return null;
        }

        if ($geo && $geo['lat'] !== null && $geo['lng'] !== null) {
            $withCoords = $warehouses->filter(
                fn (MerchantWarehouse $w) => $w->latitude !== null && $w->longitude !== null
            );
            if ($withCoords->isNotEmpty()) {
                return $withCoords->sortBy(
                    fn (MerchantWarehouse $w) => $this->haversine(
                        (float) $geo['lat'], (float) $geo['lng'],
                        (float) $w->latitude, (float) $w->longitude,
                    )
                )->first();
            }
        }

        // Soft city match — if user is in Kyiv and we have a Kyiv warehouse,
        // prefer it even without coords.
        if ($geo && ! empty($geo['city'])) {
            $cityNorm = mb_strtolower(trim($geo['city']));
            $byCity = $warehouses->first(
                fn (MerchantWarehouse $w) => $w->city && mb_strtolower(trim($w->city)) === $cityNorm
            );
            if ($byCity) {
                return $byCity;
            }
        }

        return MerchantWarehouse::default() ?: $warehouses->sortBy('sort_order')->first();
    }

    private function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $r = 6371.0; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        return $r * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
