<?php

namespace App\Services\Shipping;

use App\Models\NpArea;
use App\Models\NpCity;
use App\Models\NpWarehouse;
use App\Models\ShippingProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Synchronizes Nova Poshta reference data (areas / cities / warehouses)
 * from NP API into local DB tables for fast lookups.
 */
class NovaPoshtaReferenceSync
{
    private string $apiKey;
    private string $apiUrl;

    public function __construct()
    {
        $provider = ShippingProvider::where('code', 'novaposhta')->first();
        $cfg = $provider->configuration ?? [];
        $this->apiKey = $cfg['api_key'] ?? config('novaposhta.api_key');
        $this->apiUrl = config('novaposhta.api_url', 'https://api.novaposhta.ua/v2.0/json/');
    }

    public function syncAreas(): int
    {
        $resp = $this->call('Address', 'getAreas');
        if (! $resp['success']) {
            return 0;
        }

        $count = 0;
        foreach ($resp['data'] as $row) {
            NpArea::updateOrCreate(
                ['ref' => $row['Ref']],
                [
                    'description' => $row['Description'] ?? '',
                    'areas_center_ref' => $row['AreasCenter'] ?? null,
                    'last_synced_at' => now(),
                ]
            );
            $count++;
        }
        return $count;
    }

    public function syncCities(int $page = 1, int $limit = 500, ?\Closure $progress = null): int
    {
        $count = 0;
        do {
            $resp = $this->call('Address', 'getCities', [
                'Page' => (string) $page,
                'Limit' => (string) $limit,
            ]);

            if (! $resp['success'] || empty($resp['data'])) {
                break;
            }

            foreach ($resp['data'] as $row) {
                NpCity::updateOrCreate(
                    ['ref' => $row['Ref']],
                    [
                        'city_id' => $row['CityID'] ?? null,
                        'description' => $row['Description'] ?? '',
                        'description_ru' => $row['DescriptionRu'] ?? null,
                        'area_ref' => $row['Area'] ?? null,
                        'area_description' => $row['AreaDescription'] ?? null,
                        'settlement_type' => $row['SettlementType'] ?? null,
                        'settlement_type_description' => $row['SettlementTypeDescription'] ?? null,
                        'is_branch' => ! empty($row['IsBranch']),
                        'special_cash_check' => ! empty($row['SpecialCashCheck']),
                        'delivery_monday' => ! empty($row['Delivery1']),
                        'delivery_tuesday' => ! empty($row['Delivery2']),
                        'delivery_wednesday' => ! empty($row['Delivery3']),
                        'delivery_thursday' => ! empty($row['Delivery4']),
                        'delivery_friday' => ! empty($row['Delivery5']),
                        'delivery_saturday' => ! empty($row['Delivery6']),
                        'delivery_sunday' => ! empty($row['Delivery7']),
                        'last_synced_at' => now(),
                    ]
                );
                $count++;
            }

            if ($progress) {
                $progress($count);
            }

            $page++;
        } while (count($resp['data']) >= $limit);

        return $count;
    }

    public function syncWarehouses(?string $cityRef = null, int $page = 1, int $limit = 500, ?\Closure $progress = null): int
    {
        $count = 0;
        do {
            $props = [
                'Page' => (string) $page,
                'Limit' => (string) $limit,
            ];
            if ($cityRef) {
                $props['CityRef'] = $cityRef;
            }

            $resp = $this->call('AddressGeneral', 'getWarehouses', $props);

            if (! $resp['success'] || empty($resp['data'])) {
                break;
            }

            foreach ($resp['data'] as $row) {
                NpWarehouse::updateOrCreate(
                    ['ref' => $row['Ref']],
                    [
                        'site_key' => $row['SiteKey'] ?? null,
                        'number' => $row['Number'] ?? null,
                        'description' => $row['Description'] ?? '',
                        'short_address' => $row['ShortAddress'] ?? null,
                        'phone' => $row['Phone'] ?? null,
                        'city_ref' => $row['CityRef'] ?? '',
                        'city_description' => $row['CityDescription'] ?? null,
                        'type_ref' => $row['TypeOfWarehouse'] ?? null,
                        'type_description' => $row['CategoryOfWarehouse'] ?? null,
                        'longitude' => $row['Longitude'] ?? null,
                        'latitude' => $row['Latitude'] ?? null,
                        'total_max_weight' => $row['TotalMaxWeightAllowed'] ?? 30,
                        'place_max_weight' => $row['PlaceMaxWeightAllowed'] ?? 0,
                        'sending_max_length' => $row['SendingLimitationsOnDimensions']['Length'] ?? null,
                        'sending_max_width' => $row['SendingLimitationsOnDimensions']['Width'] ?? null,
                        'sending_max_height' => $row['SendingLimitationsOnDimensions']['Height'] ?? null,
                        'receiving_max_length' => $row['ReceivingLimitationsOnDimensions']['Length'] ?? null,
                        'receiving_max_width' => $row['ReceivingLimitationsOnDimensions']['Width'] ?? null,
                        'receiving_max_height' => $row['ReceivingLimitationsOnDimensions']['Height'] ?? null,
                        'post_finance' => ! empty($row['PostFinance']),
                        'bicycle_parking' => ! empty($row['BicycleParking']),
                        'payment_access' => ! empty($row['PaymentAccess']),
                        'pos_terminal' => ! empty($row['POSTerminal']),
                        'international_shipping' => ! empty($row['InternationalShipping']),
                        'self_service_count' => $row['SelfServiceWorkplacesCount'] ?? 0,
                        'reception_schedule' => $this->extractSchedule($row, 'Reception'),
                        'delivery_schedule' => $this->extractSchedule($row, 'Delivery'),
                        'schedule' => $this->extractSchedule($row, 'Schedule'),
                        'warehouse_status' => $row['WarehouseStatus'] ?? null,
                        'warehouse_status_date' => ! empty($row['WarehouseStatusDate']) ? $row['WarehouseStatusDate'] : null,
                        'category_of_warehouse' => $row['CategoryOfWarehouse'] ?? null,
                        'district_code' => $row['DistrictCode'] ?? null,
                        'region_city' => $row['RegionCity'] ?? null,
                        'is_active' => ($row['WarehouseStatus'] ?? '') === 'Working',
                    ]
                );
                $count++;
            }

            if ($progress) {
                $progress($count);
            }

            // For per-city sync, NP returns all warehouses in single response
            if ($cityRef) {
                break;
            }

            $page++;
        } while (count($resp['data']) >= $limit);

        return $count;
    }

    private function extractSchedule(array $row, string $prefix): ?array
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $result = [];
        foreach ($days as $day) {
            $key = "{$prefix}_{$day}";
            if (isset($row[$key])) {
                $result[strtolower($day)] = $row[$key];
            }
        }
        return $result ?: null;
    }

    private function call(string $model, string $method, array $properties = []): array
    {
        if (empty($this->apiKey)) {
            return ['success' => false, 'data' => [], 'errors' => ['API key not configured']];
        }

        try {
            $resp = Http::timeout(60)->retry(2, 1000)->post($this->apiUrl, [
                'apiKey' => $this->apiKey,
                'modelName' => $model,
                'calledMethod' => $method,
                'methodProperties' => empty($properties) ? (object) [] : $properties,
            ]);
            $body = $resp->json();
            if (! empty($body['errors'])) {
                Log::error("NP Sync API Error: ".implode(', ', $body['errors']));
            }
            return $body ?: ['success' => false, 'data' => [], 'errors' => ['Empty response']];
        } catch (\Throwable $e) {
            Log::error("NP Sync exception: {$e->getMessage()}");
            return ['success' => false, 'data' => [], 'errors' => [$e->getMessage()]];
        }
    }
}
