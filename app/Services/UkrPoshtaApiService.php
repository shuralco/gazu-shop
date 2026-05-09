<?php

namespace App\Services;

use App\Models\DisplaySetting;
use App\Models\ShippingApiLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Kolirt\Ukrposhta\Facade\Ukrposhta;

/**
 * Wrapper around kolirt/laravel-ukrposhta facade that logs every Address Classifier
 * call to shipping_api_logs (provider='ukrposhta'). Mirrors NovaPoshtaApiService.
 *
 * The Address Classifier is public (no auth), so this service is read-only
 * (cities, districts, post offices). For TTN creation a separate ecom client is
 * required — currently disabled because UkrPoshta ecom endpoints return 404 with
 * the keys we have.
 */
class UkrPoshtaApiService
{
    public function getRegions(?string $regionName = null, string $lang = 'uk'): array
    {
        return $this->call('AddressClassifier', 'getRegions', compact('regionName', 'lang'),
            fn () => Ukrposhta::getRegions($regionName, $lang));
    }

    public function getDistricts(?string $districtName = null, ?int $regionId = null): array
    {
        return $this->call('AddressClassifier', 'getDistricts', compact('districtName', 'regionId'),
            fn () => Ukrposhta::getDistricts($districtName, $regionId));
    }

    public function getCities(?string $cityName = null, ?int $districtId = null, ?int $regionId = null): array
    {
        return $this->call('AddressClassifier', 'getCities', compact('cityName', 'districtId', 'regionId'),
            fn () => Ukrposhta::getCities($cityName, $districtId, $regionId));
    }

    public function getStreets(?string $streetName = null, ?int $cityId = null, ?int $districtId = null, ?int $regionId = null): array
    {
        return $this->call('AddressClassifier', 'getStreets', compact('streetName', 'cityId', 'districtId', 'regionId'),
            fn () => Ukrposhta::getStreets($streetName, $cityId, $districtId, $regionId));
    }

    public function getPostOffices(?int $cityId = null, ?string $zipCode = null): array
    {
        return $this->call('AddressClassifier', 'getPostOffices', compact('cityId', 'zipCode'),
            fn () => Ukrposhta::getPostOffices($zipCode, null, $cityId));
    }

    public function getCitiesByPostcode(string $zip, string $lang = 'uk'): array
    {
        return $this->call('AddressClassifier', 'getCitiesByPostcode', compact('zip', 'lang'),
            fn () => Ukrposhta::getCitiesByPostcode($zip, $lang));
    }

    /**
     * Public API ping — used by health check / diagnostics.
     */
    public function ping(): array
    {
        try {
            $regions = $this->getRegions();

            return [
                'success' => is_array($regions) && count($regions) > 0,
                'count' => is_array($regions) ? count($regions) : 0,
                'sample' => is_array($regions) ? array_slice($regions, 0, 3) : [],
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Universal call wrapper: runs $callable, logs duration + outcome.
     */
    private function call(string $model, string $method, array $properties, callable $callable): array
    {
        $startedAt = microtime(true);
        $errors = [];
        $response = null;
        $success = false;

        try {
            $response = $callable();
            $success = true;
        } catch (\Throwable $e) {
            $errors = [$e->getMessage()];
            Log::warning("UkrPoshta API call failed: {$model}.{$method}", ['error' => $e->getMessage()]);
        }

        $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
        $this->persistApiLog($model, $method, $properties, $response, $success, null, $errors, $durationMs);

        if (! $success) {
            return [];
        }

        // Convert stdClass -> array for consistency
        if (is_object($response)) {
            $response = json_decode(json_encode($response), true);
        }

        return is_array($response) ? $response : [];
    }

    private function persistApiLog(string $model, string $method, array $properties, mixed $response, bool $success, ?int $httpStatus, array $errors, int $durationMs): void
    {
        try {
            if (! Schema::hasTable('shipping_api_logs')) {
                return;
            }

            $debugOn = (bool) DisplaySetting::get('up_debug_mode', false);
            if ($success && ! $debugOn) {
                return;
            }

            $caller = '';
            foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 12) as $frame) {
                $cls = $frame['class'] ?? '';
                $fn = $frame['function'] ?? '';
                if ($cls === self::class || $fn === 'persistApiLog' || $fn === 'call') {
                    continue;
                }
                if ($cls && $fn) {
                    $caller = "{$cls}::{$fn}";
                    break;
                }
            }

            $responseArr = is_object($response) ? json_decode(json_encode($response), true) : $response;

            ShippingApiLog::create([
                'provider' => 'ukrposhta',
                'endpoint_model' => $model,
                'endpoint_method' => $method,
                'success' => $success,
                'http_status' => $httpStatus,
                'duration_ms' => $durationMs,
                'request_payload' => $properties,
                'response_payload' => is_array($responseArr) ? $responseArr : null,
                'errors' => $errors ?: null,
                'caller' => $caller ?: null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to persist UP API log: '.$e->getMessage());
        }
    }
}
