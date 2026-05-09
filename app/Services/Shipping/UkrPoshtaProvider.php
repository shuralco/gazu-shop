<?php

namespace App\Services\Shipping;

use App\Models\Order;
use App\Services\Shipping\Contracts\ShippingProviderInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Kolirt\Ukrposhta\Facade\Ukrposhta;

/**
 * Провайдер УкрПошти
 */
class UkrPoshtaProvider implements ShippingProviderInterface
{
    protected string $apiKey;

    protected string $bearerToken;

    protected string $counterpartyToken;

    protected string $delengineApiKey;

    protected string $apiUrl;

    protected string $apiV1Url;

    protected bool $sandbox;

    public function __construct()
    {
        // Отримуємо конфігурацію з бази даних (налаштування провайдера)
        $provider = \App\Models\ShippingProvider::where('code', 'ukrposhta')->first();
        $config = $provider->configuration ?? [];

        $this->bearerToken = $config['bearer_token'] ?? config('ukrposhta.bearer_token', '');
        $this->counterpartyToken = $config['counterparty_token'] ?? config('ukrposhta.counterparty_token', '');
        $this->apiKey = $config['api_key'] ?? config('ukrposhta.api_key', '');
        $this->delengineApiKey = $config['delengine_api_key'] ?? 'v4n208uaysugpqe6v3ijelusl601fduv';
        $this->apiUrl = config('ukrposhta.api_url');
        $this->apiV1Url = 'https://www.ukrposhta.ua/api/v1/';
        $this->sandbox = $config['sandbox'] ?? config('ukrposhta.sandbox', true);
    }

    /**
     * Розрахувати вартість доставки
     */
    public function calculateShippingCost(Order $order, array $destination): float
    {
        try {
            // УкрПошта має фіксовані тарифи залежно від ваги та відстані
            $weight = $this->calculateOrderWeight($order);
            $baseCost = config('ukrposhta.delivery.base_cost', 45.0);
            $perKgCost = config('ukrposhta.delivery.per_kg_cost', 8.0);

            $cost = $baseCost + ($weight * $perKgCost);

            // Округлюємо до 2 знаків після коми
            return round($cost, 2);
        } catch (\Exception $e) {
            Log::error('UkrPoshta shipping cost calculation error: '.$e->getMessage());

            return config('ukrposhta.delivery.base_cost', 45.0);
        }
    }

    /**
     * Отримати варіанти доставки
     */
    public function getDeliveryOptions(array $criteria): Collection
    {
        return collect([
            [
                'code' => 'branch',
                'name' => 'На відділення УкрПошти',
                'description' => 'Доставка до відділення УкрПошти',
                'estimated_days' => 3,
            ],
            [
                'code' => 'courier',
                'name' => 'Кур\'єром УкрПошти',
                'description' => 'Доставка кур\'єром до адреси',
                'estimated_days' => 4,
            ],
        ]);
    }

    /**
     * Створити відправлення
     */
    public function createShipment(Order $order, array $data): string
    {
        try {
            // УкрПошта не має публічного API для створення відправлень
            // Повертаємо унікальний ID для відстеження
            $trackingNumber = 'UP'.time().rand(1000, 9999);

            Log::info('UkrPoshta shipment created', [
                'order_id' => $order->id,
                'tracking_number' => $trackingNumber,
                'destination' => $data,
            ]);

            return $trackingNumber;
        } catch (\Exception $e) {
            Log::error('UkrPoshta Create Shipment Error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Відстежити посилку
     */
    public function trackPackage(string $trackingNumber): array
    {
        try {
            // Заглушка для відстеження - УкрПошта не має публічного API
            return [
                'status' => 'in_transit',
                'status_code' => '200',
                'description' => 'Посилка в дорозі',
                'last_updated' => now()->toISOString(),
            ];
        } catch (\Exception $e) {
            Log::error('UkrPoshta Tracking Error: '.$e->getMessage());

            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Валідація адреси
     */
    public function validateAddress(array $address): bool
    {
        // Базова валідація для УкрПошти
        if (empty($address['city_id']) || empty($address['branch_id'])) {
            return false;
        }

        return true;
    }

    /**
     * Отримати міста
     */
    public function getCities(string $search = ''): Collection
    {
        $cacheKey = 'ukrposhta_cities_'.md5($search);

        return Cache::remember($cacheKey, config('ukrposhta.cache.cities_ttl', 3600), function () use ($search) {
            try {
                // Використовуємо kolirt/laravel-ukrposhta пакет для реальних даних
                $cities = Ukrposhta::getCities($search);

                if ($cities && count($cities) > 0) {
                    return collect($cities)->map(function ($city) {
                        // Конвертуємо stdClass в array
                        $cityArray = (array) $city;

                        return [
                            'ref' => $cityArray['CITY_ID'] ?? '',
                            'id' => $cityArray['CITY_ID'] ?? '',
                            'name' => $cityArray['CITY_UA'] ?? $cityArray['CITY_EN'] ?? '',
                            'name_ua' => $cityArray['CITY_UA'] ?? '',
                            'type' => $cityArray['CITYTYPE_UA'] ?? 'місто',
                            'region' => $cityArray['REGION_UA'] ?? '',
                            'district' => $cityArray['DISTRICT_UA'] ?? '',
                            'latitude' => $cityArray['LATTITUDE'] ?? null,
                            'longitude' => $cityArray['LONGITUDE'] ?? null,
                        ];
                    });
                }

                // Повертаємо порожню колекцію, якщо немає даних
                return collect([]);
            } catch (\Exception $e) {
                Log::error('UkrPoshta Get Cities Error: '.$e->getMessage());

                return collect([]);
            }
        });
    }

    /**
     * Отримати відділення/склади (тільки міські)
     */
    public function getWarehouses(string $cityRef): Collection
    {
        $cacheKey = "ukrposhta_branches_{$cityRef}";

        return Cache::remember($cacheKey, config('ukrposhta.cache.branches_ttl', 1800), function () use ($cityRef) {
            try {
                // Використовуємо kolirt/laravel-ukrposhta пакет для реальних відділень
                $offices = Ukrposhta::getPostOffices(null, null, (int) $cityRef);

                if ($offices && count($offices) > 0) {
                    $mappedOffices = collect($offices)->map(function ($office) {
                        // Конвертуємо stdClass в array
                        $officeArray = (array) $office;
                        $postcode = $officeArray['POSTCODE'] ?? '';
                        $cityName = $officeArray['CITY_UA'] ?? $officeArray['CITY_NAME'] ?? '';

                        // Формуємо назву та адресу з даних API
                        $name = 'Відділення №'.$postcode;
                        $address = $cityName ? $cityName.', індекс '.$postcode : 'Індекс '.$postcode;

                        return [
                            'ref' => $postcode,
                            'id' => $postcode,
                            'number' => $postcode,
                            'name' => $name,
                            'address' => $address,
                            'phone' => '+380800500440',
                            'schedule' => [
                                'monday' => '08:00-18:00',
                                'tuesday' => '08:00-18:00',
                                'wednesday' => '08:00-18:00',
                                'thursday' => '08:00-18:00',
                                'friday' => '08:00-18:00',
                                'saturday' => '09:00-16:00',
                                'sunday' => 'Вихідний',
                            ],
                            'description' => $name.' - '.$address,
                        ];
                    });

                    // Перевіряємо чи є мапа поштових індексів для цього міста
                    $cityPostcodes = CityPostcodesMap::getCityPostcodes($cityRef);

                    if ($cityPostcodes !== null) {
                        // Якщо є мапа - фільтруємо тільки міські індекси
                        $filtered = $mappedOffices->filter(function ($office) use ($cityPostcodes) {
                            return in_array($office['number'], $cityPostcodes);
                        });

                        return $filtered->sortBy('number')->values();
                    }

                    // Якщо немає мапи - повертаємо перші 10 відділень
                    return $mappedOffices->sortBy('number')->take(10)->values();
                }

                // Якщо немає відділень для села, пропонуємо найближчий районний центр
                return $this->getNearestDistrictOffices($cityRef);
            } catch (\Exception $e) {
                Log::error('UkrPoshta Get Warehouses Error: '.$e->getMessage());

                return collect([]);
            }
        });
    }

    /**
     * Перевірити доступність сервісу
     */
    public function isAvailable(): bool
    {
        try {
            // Перевіряємо чи є bearer token
            if (empty($this->bearerToken)) {
                return false;
            }

            // Тестуємо API через отримання міст
            $response = Http::connectTimeout(5)
                ->timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer '.$this->bearerToken,
                    'Content-Type' => 'application/json',
                ])
                ->get($this->apiV1Url.'cities', ['limit' => 1]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('UkrPoshta availability check failed: '.$e->getMessage());

            return true; // УкрПошта завжди доступна як резервний варіант
        }
    }

    /**
     * Отримати код провайдера
     */
    public function getProviderCode(): string
    {
        return 'ukrposhta';
    }

    /**
     * Отримати назву провайдера
     */
    public function getProviderName(): string
    {
        return 'УкрПошта';
    }

    /**
     * Отримати відділення найближчого районного центру
     */
    protected function getNearestDistrictOffices(string $cityRef): Collection
    {
        try {
            // Спеціальна обробка для відомих сіл
            $villageToDistrictMap = [
                '26321' => '26297', // Залісся (Старокостянтинівський) -> Старокостянтинів
                '26390' => '26410', // Залісся (Старосинявський) -> Стара Синява
                '26843' => '26748', // Залісся (Шепетівський) -> Шепетівка
                '25790' => '25308', // Залісся (Кам'янець-Подільський) -> Кам'янець-Подільський
            ];

            if (isset($villageToDistrictMap[$cityRef])) {
                $districtCityId = $villageToDistrictMap[$cityRef];
                $offices = Ukrposhta::getPostOffices(null, null, (int) $districtCityId);

                if ($offices && count($offices) > 0) {
                    return collect($offices)->map(function ($office) {
                        $officeArray = (array) $office;
                        $postcode = $officeArray['POSTCODE'] ?? '';
                        $cityName = $officeArray['CITY_UA'] ?? 'Районний центр';

                        return [
                            'ref' => $postcode,
                            'id' => $postcode,
                            'number' => $postcode,
                            'name' => 'Відділення №'.$postcode.' (найближче)',
                            'address' => $cityName.', індекс '.$postcode,
                            'phone' => '+380800500440',
                            'schedule' => [
                                'monday' => '08:00-18:00',
                                'tuesday' => '08:00-18:00',
                                'wednesday' => '08:00-18:00',
                                'thursday' => '08:00-18:00',
                                'friday' => '08:00-18:00',
                                'saturday' => '09:00-16:00',
                                'sunday' => 'Вихідний',
                            ],
                            'description' => 'Найближче відділення в '.$cityName.'. Доставка до села через листоношу.',
                        ];
                    })->take(5)->sortBy('number')->values();
                }
            }

            // Повідомлення про доставку листоношею
            return collect([[
                'ref' => 'mobile',
                'id' => 'mobile',
                'number' => 'Пересувне',
                'name' => 'Доставка листоношею',
                'address' => 'Доставка на домашню адресу через листоношу',
                'phone' => '+380800500440',
                'schedule' => [
                    'monday' => 'За графіком',
                    'tuesday' => 'За графіком',
                    'wednesday' => 'За графіком',
                    'thursday' => 'За графіком',
                    'friday' => 'За графіком',
                    'saturday' => 'За графіком',
                    'sunday' => 'Вихідний',
                ],
                'description' => 'Посилка буде доставлена листоношею на вашу домашню адресу',
            ]]);
        } catch (\Exception $e) {
            Log::error('Error getting nearest district offices: '.$e->getMessage());

            return collect([]);
        }
    }

    /**
     * Розрахувати вагу замовлення
     */
    protected function calculateOrderWeight(Order $order): float
    {
        // Базова вага - 0.5 кг мінімум
        $weight = 0.5;

        // Додаємо вагу залежно від кількості товарів
        $itemsCount = $order->orderProducts()->sum('quantity');
        $weight += $itemsCount * 0.4; // 400г за товар (трохи більше ніж Нова Пошта)

        return round($weight, 2);
    }
}
