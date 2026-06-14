<?php

namespace App\Services\Shipping;

use App\Models\Order;
use App\Services\Shipping\Contracts\ShippingProviderInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Провайдер Нової Пошти
 */
class NovaPoshtaProvider implements ShippingProviderInterface
{
    protected string $apiKey;

    protected string $apiUrl;

    protected bool $sandbox;

    public function __construct()
    {
        // Отримуємо конфігурацію з бази даних (налаштування провайдера)
        $provider = \App\Models\ShippingProvider::where('code', 'novaposhta')->first();
        $config = $provider?->configuration ?? [];

        // Ключ/URL із тих самих джерел, що й NovaPoshtaApiService — інакше, коли
        // ключ заданий лише через services.nova_poshta (env NOVA_POSHTA_API_KEY),
        // цей провайдер лишався без ключа → getCities() порожній → пошук міста у
        // формі замовлення «не працював». ?: (не ??) щоб порожні рядки падали далі.
        $this->apiKey = ($config['api_key'] ?? null)
            ?: config('services.nova_poshta.api_key')
            ?: config('novaposhta.api_key', '');
        $this->apiUrl = config('services.nova_poshta.api_url')
            ?: config('novaposhta.api_url', 'https://api.novaposhta.ua/v2.0/json/');
        $this->sandbox = $config['sandbox'] ?? config('novaposhta.sandbox', false);
    }

    /**
     * Розрахувати вартість доставки
     */
    public function calculateShippingCost(Order $order, array $destination): float
    {
        try {
            $params = [
                'CitySender' => $this->getSenderCityRef(),
                'CityRecipient' => $destination['city_ref'] ?? '',
                'Weight' => $this->calculateOrderWeight($order),
                'ServiceType' => 'WarehouseWarehouse',
                'Cost' => $order->total,
                'CargoType' => \App\Models\DisplaySetting::get('np_default_cargo_type', 'Parcel'),
                'SeatsAmount' => '1',
            ];

            // Multi-parcel: sum weights, add OptionsSeat array per place
            $parcels = $destination['parcels'] ?? null;
            if (is_array($parcels) && count($parcels) > 0) {
                $totalWeight = 0;
                $optionsSeat = [];
                foreach ($parcels as $i => $p) {
                    $w = (float) ($p['weight'] ?? 0);
                    $l = (float) ($p['length'] ?? 0);
                    $width = (float) ($p['width'] ?? 0);
                    $h = (float) ($p['height'] ?? 0);
                    $vw = ($l > 0 && $width > 0 && $h > 0) ? round(($l * $width * $h) / 4000, 3) : 0;
                    $effective = max($w, $vw, 0.1);
                    $totalWeight += $effective;
                    $optionsSeat[] = [
                        'volumetricVolume' => $l && $width && $h ? round(($l * $width * $h) / 1_000_000, 4) : 0.001,
                        'volumetricWidth' => $width ?: 10,
                        'volumetricLength' => $l ?: 10,
                        'volumetricHeight' => $h ?: 10,
                        'weight' => $effective,
                    ];
                }
                $params['Weight'] = round($totalWeight, 2);
                $params['SeatsAmount'] = (string) count($parcels);
                $params['OptionsSeat'] = $optionsSeat;
            }

            $response = $this->makeApiCall('InternetDocument', 'getDocumentPrice', $params);

            if ($response['success'] && ! empty($response['data'])) {
                return (float) $response['data'][0]['Cost'];
            }

            return $this->offlineFallback($params['Weight'], $order->total);
        } catch (\Exception $e) {
            Log::error('Nova Poshta API Error: '.$e->getMessage());

            return $this->offlineFallback(
                $this->calculateOrderWeight($order),
                $order->total
            );
        }
    }

    /**
     * Offline tariff fallback when NP API is unreachable.
     * Uses DisplaySetting('np_offline_base_cost', 65) + per_kg * weight.
     * Optionally caps at threshold for free-shipping.
     */
    protected function offlineFallback(float $weight, float $orderTotal): float
    {
        $baseCost = (float) (\App\Models\DisplaySetting::get('np_offline_base_cost', 65) ?: 65);
        $perKg = (float) (\App\Models\DisplaySetting::get('np_offline_per_kg', 5) ?: 5);
        $cost = $baseCost + max(0, $weight - 1) * $perKg;

        Log::warning('Nova Poshta API failed, using offline tariff', [
            'weight' => $weight,
            'cost' => $cost,
        ]);

        return round($cost, 2);
    }

    /**
     * Отримати варіанти доставки
     */
    public function getDeliveryOptions(array $criteria): Collection
    {
        $options = collect([
            [
                'code' => 'warehouse_warehouse',
                'name' => 'Відділення - Відділення',
                'description' => 'Доставка до відділення Нової Пошти',
                'estimated_days' => 1,
            ],
            [
                'code' => 'warehouse_doors',
                'name' => 'Відділення - Адреса',
                'description' => 'Доставка кур\'єром до адреси',
                'estimated_days' => 2,
            ],
            [
                'code' => 'doors_doors',
                'name' => 'Адреса - Адреса',
                'description' => 'Забір та доставка кур\'єром',
                'estimated_days' => 2,
            ],
        ]);

        return $options;
    }

    /**
     * Створити відправлення
     */
    public function createShipment(Order $order, array $data): string
    {
        try {
            $response = $this->makeApiCall('InternetDocument', 'save', [
                'NewAddress' => '1',
                'PayerType' => 'Recipient',
                'PaymentMethod' => 'Cash',
                'CargoType' => \App\Models\DisplaySetting::get('np_default_cargo_type', 'Parcel'),
                'VolumeGeneral' => '0.004',
                'Weight' => $this->calculateOrderWeight($order),
                'ServiceType' => $data['service_type'] ?? 'WarehouseWarehouse',
                'SeatsAmount' => '1',
                'Description' => 'Товари з інтернет-магазину',
                'Cost' => $order->total,
                'CitySender' => $this->getSenderCityRef(),
                'Sender' => $this->getSenderRef(),
                'SenderAddress' => $this->getSenderWarehouseRef(),
                'ContactSender' => $this->getSenderContactRef(),
                'SendersPhone' => config('novaposhta.sender_phone'),
                'CityRecipient' => $data['city_ref'],
                'Recipient' => $data['recipient_name'],
                'RecipientAddress' => $data['warehouse_ref'],
                'ContactRecipient' => $data['recipient_name'],
                'RecipientsPhone' => $data['recipient_phone'],
            ]);

            if ($response['success'] && ! empty($response['data'])) {
                return $response['data'][0]['Ref'];
            }

            throw new \Exception('Failed to create shipment');
        } catch (\Exception $e) {
            Log::error('Nova Poshta Create Shipment Error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Відстежити посилку
     */
    public function trackPackage(string $trackingNumber): array
    {
        try {
            $response = $this->makeApiCall('TrackingDocument', 'getStatusDocuments', [
                'Documents' => [['DocumentNumber' => $trackingNumber]],
            ]);

            if ($response['success'] && ! empty($response['data'])) {
                $tracking = $response['data'][0];

                return [
                    'status' => $tracking['Status'] ?? 'unknown',
                    'status_code' => $tracking['StatusCode'] ?? '0',
                    'city' => $tracking['CityRecipient'] ?? '',
                    'warehouse' => $tracking['WarehouseRecipient'] ?? '',
                    'scheduled_delivery_date' => $tracking['ScheduledDeliveryDate'] ?? null,
                    'last_updated' => now()->toISOString(),
                ];
            }

            return ['status' => 'not_found'];
        } catch (\Exception $e) {
            Log::error('Nova Poshta Tracking Error: '.$e->getMessage());

            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Валідація адреси
     */
    public function validateAddress(array $address): bool
    {
        if (empty($address['city_ref']) || empty($address['warehouse_ref'])) {
            return false;
        }

        // Додаткова валідація через API якщо потрібно
        return true;
    }

    /**
     * Отримати міста
     */
    public function getCities(string $search = ''): Collection
    {
        $cacheKey = "novaposhta_cities_{$search}";

        return Cache::remember($cacheKey, 300, function () use ($search) {
            try {
                $params = ['Limit' => 50];

                // Only add FindByString if search is not empty
                if (! empty($search)) {
                    $params['FindByString'] = $search;
                }

                $response = $this->makeApiCall('Address', 'getCities', $params);

                if ($response['success'] && ! empty($response['data'])) {
                    return collect($response['data'])->map(function ($city) {
                        return [
                            'ref' => $city['Ref'],
                            'name' => $city['Description'],
                            'name_ua' => $city['DescriptionUa'] ?? $city['Description'],
                            'type' => $city['SettlementType'] ?? 'місто',
                        ];
                    });
                }

                return collect();
            } catch (\Exception $e) {
                Log::error('Nova Poshta Get Cities Error: '.$e->getMessage());

                return collect();
            }
        });
    }

    /**
     * Отримати місто за Ref
     */
    public function getCityByRef(string $ref): ?array
    {
        $cacheKey = "novaposhta_city_ref_{$ref}";

        return Cache::remember($cacheKey, 3600, function () use ($ref) {
            try {
                $response = $this->makeApiCall('Address', 'getCities', [
                    'Ref' => $ref,
                ]);

                if ($response['success'] && ! empty($response['data'])) {
                    $city = $response['data'][0] ?? null;
                    if ($city) {
                        return [
                            'ref' => $city['Ref'],
                            'name' => $city['Description'],
                            'name_ua' => $city['DescriptionUa'] ?? $city['Description'],
                            'type' => $city['SettlementType'] ?? 'місто',
                        ];
                    }
                }

                return null;
            } catch (\Exception $e) {
                Log::error('Nova Poshta Get City by Ref Error: '.$e->getMessage());

                return null;
            }
        });
    }

    /**
     * Отримати відділення/склади
     */
    public function getWarehouses(string $cityRef): Collection
    {
        $cacheKey = "novaposhta_warehouses_{$cityRef}";

        return Cache::remember($cacheKey, 600, function () use ($cityRef) {
            try {
                $response = $this->makeApiCall('AddressGeneral', 'getWarehouses', [
                    'CityRef' => $cityRef,
                    'Limit' => 500,
                ]);

                if ($response['success'] && ! empty($response['data'])) {
                    return collect($response['data'])->map(function ($warehouse) {
                        return [
                            'ref' => $warehouse['Ref'],
                            'number' => $warehouse['Number'],
                            'description' => $warehouse['Description'],
                            'address' => $warehouse['ShortAddress'] ?? $warehouse['Description'],
                            'phone' => $warehouse['Phone'] ?? '',
                            'schedule' => $warehouse['Schedule'] ?? [],
                            'CategoryOfWarehouse' => $warehouse['CategoryOfWarehouse'] ?? '',
                            'TypeOfWarehouse' => $warehouse['TypeOfWarehouse'] ?? '',
                        ];
                    });
                }

                return collect();
            } catch (\Exception $e) {
                Log::error('Nova Poshta Get Warehouses Error: '.$e->getMessage());

                return collect();
            }
        });
    }

    /**
     * Перевірити доступність сервісу
     */
    public function isAvailable(): bool
    {
        if (empty($this->apiKey)) {
            return false;
        }

        try {
            $response = $this->makeApiCall('Common', 'getTypesOfPayers');

            return isset($response['success']) && $response['success'] === true;
        } catch (\Exception $e) {
            Log::error('Nova Poshta availability check failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Отримати код провайдера
     */
    public function getProviderCode(): string
    {
        return 'novaposhta';
    }

    /**
     * Отримати назву провайдера
     */
    public function getProviderName(): string
    {
        return 'Нова Пошта';
    }

    /**
     * Виконати API запит
     */
    protected function makeApiCall(string $model, string $method, array $properties = []): array
    {
        $data = [
            'apiKey' => $this->apiKey,
            'modelName' => $model,
            'calledMethod' => $method,
            'methodProperties' => $properties,
        ];

        Log::info('Nova Poshta API Call', [
            'model' => $model,
            'method' => $method,
            'api_key' => substr($this->apiKey, 0, 8).'...',
            'properties' => $properties,
        ]);

        $response = Http::connectTimeout(5)
            ->timeout(30)
            ->retry(2, 1000)
            ->post($this->apiUrl, $data);

        if (! $response->successful()) {
            throw new \Exception('HTTP Error: '.$response->status());
        }

        $result = $response->json();

        if (! empty($result['errors'])) {
            throw new \Exception('API Error: '.implode(', ', $result['errors']));
        }

        return $result;
    }

    /**
     * Розрахувати ефективну вагу замовлення:
     * MAX(actualWeight, volumeWeight) для кожного товара × кількість.
     * Volume weight = L*W*H / 4000 (Nova Poshta tariff formula).
     */
    protected function calculateOrderWeight(Order $order): float
    {
        $totalActual = 0.0;
        $totalVolume = 0.0;

        foreach ($order->orderProducts as $orderProduct) {
            $product = $orderProduct->product;
            if (! $product) {
                $totalActual += 0.5 * $orderProduct->quantity;
                continue;
            }
            $actual = (float) ($product->weight ?? 0.5);
            $volume = $product->getVolumeWeight();
            $totalActual += $actual * $orderProduct->quantity;
            $totalVolume += $volume * $orderProduct->quantity;
        }

        $effective = max($totalActual, $totalVolume, 0.5);
        return round($effective, 2);
    }

    /**
     * Effective weight from a session cart (for checkout pre-calculation).
     */
    public function calculateCartWeight(array $cart): float
    {
        $totalActual = 0.0;
        $totalVolume = 0.0;
        foreach ($cart as $item) {
            $productId = $item['product_id'] ?? null;
            $qty = (int) ($item['quantity'] ?? 1);
            if (! $productId) {
                $totalActual += 0.5 * $qty;
                continue;
            }
            $product = \App\Models\Product::find($productId);
            if (! $product) {
                $totalActual += 0.5 * $qty;
                continue;
            }
            $totalActual += (float) ($product->weight ?? 0.5) * $qty;
            $totalVolume += $product->getVolumeWeight() * $qty;
        }
        return round(max($totalActual, $totalVolume, 0.5), 2);
    }

    /**
     * Отримати референс міста відправника
     */
    protected function getSenderCityRef(): string
    {
        return config('novaposhta.sender_city_ref', '8d5a980d-391c-11dd-90d9-001a92567626'); // Київ
    }

    /**
     * Отримати референс відправника
     */
    protected function getSenderRef(): string
    {
        return config('novaposhta.sender_ref', '');
    }

    /**
     * Отримати референс відділення відправника
     */
    protected function getSenderWarehouseRef(): string
    {
        return config('novaposhta.sender_warehouse_ref', '');
    }

    /**
     * Отримати референс контакту відправника
     */
    protected function getSenderContactRef(): string
    {
        return config('novaposhta.sender_contact_ref', '');
    }
}
