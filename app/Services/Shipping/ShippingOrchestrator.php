<?php

namespace App\Services\Shipping;

use App\Models\Order;
use App\Models\Shipment;
use App\Models\ShippingMethod;
use App\Models\ShippingProvider;
use App\Services\Shipping\Contracts\ShippingProviderInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

/**
 * Оркестратор системи доставки
 * Управляє всіма провайдерами доставки та забезпечує єдиний інтерфейс
 */
class ShippingOrchestrator
{
    protected Collection $providers;

    protected array $providerInstances = [];

    public function __construct()
    {
        $this->providers = collect();
        $this->loadProviders();
    }

    /**
     * Отримати всі варіанти доставки для замовлення
     */
    public function getAllShippingOptions(Order $order, array $destination): Collection
    {
        $options = collect();

        foreach ($this->getActiveProviders() as $provider) {
            try {
                $providerInstance = $this->getProviderInstance($provider->code);

                if (! $providerInstance->isAvailable()) {
                    continue;
                }

                $cost = $providerInstance->calculateShippingCost($order, $destination);
                $deliveryOptions = $providerInstance->getDeliveryOptions($destination);

                foreach ($deliveryOptions as $option) {
                    $options->push([
                        'provider_code' => $provider->code,
                        'provider_name' => $provider->name,
                        'method_code' => $option['code'],
                        'method_name' => $option['name'],
                        'description' => $option['description'],
                        'cost' => $cost,
                        'estimated_days' => $option['estimated_days'] ?? null,
                        'provider_id' => $provider->id,
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning("Shipping provider {$provider->code} failed: ".$e->getMessage());

                continue;
            }
        }

        return $options->sortBy('cost');
    }

    /**
     * Отримати найкращий варіант доставки (найдешевший)
     */
    public function getBestRate(Order $order, array $destination): ?array
    {
        $options = $this->getAllShippingOptions($order, $destination);

        return $options->first();
    }

    /**
     * Отримати рекомендований варіант (баланс ціна/швидкість)
     */
    public function getRecommendedOption(Order $order, array $destination): ?array
    {
        $options = $this->getAllShippingOptions($order, $destination);

        if ($options->isEmpty()) {
            return null;
        }

        // Логіка вибору найкращого варіанту
        // Враховуємо ціну та час доставки
        return $options->sortBy(function ($option) {
            $costWeight = $option['cost'] * 0.7; // Вага ціни 70%
            $timeWeight = ($option['estimated_days'] ?? 3) * 10 * 0.3; // Вага часу 30%

            return $costWeight + $timeWeight;
        })->first();
    }

    /**
     * Створити відправлення
     */
    public function createShipment(Order $order, array $shippingData): Shipment
    {
        $providerCode = $shippingData['provider_code'];
        $methodCode = $shippingData['method_code'];

        $provider = $this->getProviderInstance($providerCode);
        if (! $provider) {
            throw new \Exception("Провайдер доставки '{$providerCode}' не знайдено");
        }

        try {
            // Створити відправлення через API провайдера
            $trackingNumber = $provider->createShipment($order, $shippingData);

            // Знайти метод доставки
            $shippingMethod = ShippingMethod::whereHas('provider', function ($q) use ($providerCode) {
                $q->where('code', $providerCode);
            })->where('method_code', $methodCode)->first();

            if (! $shippingMethod) {
                throw new \Exception('Метод доставки не знайдено');
            }

            // Створити запис в базі даних
            $shipment = Shipment::create([
                'order_id' => $order->id,
                'method_id' => $shippingMethod->id,
                'tracking_number' => $trackingNumber,
                'status' => Shipment::STATUS_CREATED,
                'sender_address' => $shippingData['sender_address'] ?? [],
                'recipient_address' => $shippingData['recipient_address'] ?? [],
                'weight' => $this->calculateOrderWeight($order),
                'declared_value' => $order->total,
                'shipping_cost' => $shippingData['cost'] ?? 0,
                'additional_data' => $shippingData,
            ]);

            return $shipment;
        } catch (\Exception $e) {
            Log::error('Failed to create shipment: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Відстежити всі посилки
     */
    public function trackAllPackages(): Collection
    {
        $shipments = Shipment::with('shippingMethod.provider')
            ->whereNotIn('status', [Shipment::STATUS_DELIVERED, Shipment::STATUS_FAILED])
            ->get();

        $results = collect();

        foreach ($shipments as $shipment) {
            try {
                $provider = $this->getProviderInstance($shipment->shippingMethod->provider->code);
                $trackingInfo = $provider->trackPackage($shipment->tracking_number);

                // Оновити статус якщо змінився
                if (isset($trackingInfo['status']) && $trackingInfo['status'] !== $shipment->status) {
                    $shipment->updateStatus($trackingInfo['status'], $trackingInfo);
                }

                $results->push([
                    'shipment_id' => $shipment->id,
                    'tracking_number' => $shipment->tracking_number,
                    'status' => $trackingInfo['status'] ?? 'unknown',
                    'provider' => $shipment->shippingMethod->provider->name,
                    'tracking_info' => $trackingInfo,
                ]);
            } catch (\Exception $e) {
                Log::error("Tracking failed for shipment {$shipment->id}: ".$e->getMessage());

                continue;
            }
        }

        return $results;
    }

    /**
     * Отримати міста від провайдера
     */
    public function getCities(string $providerCode, string $search = ''): Collection
    {
        $provider = $this->getProviderInstance($providerCode);
        if (! $provider) {
            return collect();
        }

        return $provider->getCities($search);
    }

    /**
     * Отримати відділення від провайдера
     */
    public function getWarehouses(string $providerCode, string $cityRef): Collection
    {
        $provider = $this->getProviderInstance($providerCode);
        if (! $provider) {
            return collect();
        }

        return $provider->getWarehouses($cityRef);
    }

    /**
     * Валідувати адресу через провайдера
     */
    public function validateAddress(string $providerCode, array $address): bool
    {
        $provider = $this->getProviderInstance($providerCode);
        if (! $provider) {
            return false;
        }

        return $provider->validateAddress($address);
    }

    /**
     * Отримати статистику системи доставки
     */
    public function getShippingMetrics(): array
    {
        $totalShipments = Shipment::count();
        $deliveredShipments = Shipment::where('status', Shipment::STATUS_DELIVERED)->count();
        $inTransitShipments = Shipment::inTransit()->count();
        $failedShipments = Shipment::where('status', Shipment::STATUS_FAILED)->count();

        $deliveryRate = $totalShipments > 0 ? ($deliveredShipments / $totalShipments) * 100 : 0;
        $failureRate = $totalShipments > 0 ? ($failedShipments / $totalShipments) * 100 : 0;

        return [
            'total_shipments' => $totalShipments,
            'delivered_shipments' => $deliveredShipments,
            'in_transit_shipments' => $inTransitShipments,
            'failed_shipments' => $failedShipments,
            'delivery_rate' => round($deliveryRate, 2),
            'failure_rate' => round($failureRate, 2),
            'active_providers' => $this->getActiveProviders()->count(),
        ];
    }

    /**
     * Отримати здоров'я провайдерів
     */
    public function getProviderHealth(): array
    {
        $health = [];

        foreach ($this->getActiveProviders() as $provider) {
            try {
                $providerInstance = $this->getProviderInstance($provider->code);
                $isAvailable = $providerInstance->isAvailable();

                $health[$provider->code] = [
                    'name' => $provider->name,
                    'status' => $isAvailable ? 'healthy' : 'unavailable',
                    'last_check' => now()->toISOString(),
                ];
            } catch (\Exception $e) {
                $health[$provider->code] = [
                    'name' => $provider->name,
                    'status' => 'error',
                    'error' => $e->getMessage(),
                    'last_check' => now()->toISOString(),
                ];
            }
        }

        return $health;
    }

    /**
     * Завантажити активні провайдери з бази даних
     */
    protected function loadProviders(): void
    {
        $this->providers = ShippingProvider::active()->get();
    }

    /**
     * Отримати активних провайдерів
     */
    protected function getActiveProviders(): Collection
    {
        return $this->providers->where('is_active', true);
    }

    /**
     * Отримати екземпляр провайдера
     */
    protected function getProviderInstance(string $providerCode): ?ShippingProviderInterface
    {
        if (isset($this->providerInstances[$providerCode])) {
            return $this->providerInstances[$providerCode];
        }

        $instance = null;

        switch ($providerCode) {
            case 'novaposhta':
                $instance = App::make(NovaPoshtaProvider::class);
                break;
            case 'ukrposhta':
                $instance = App::make(UkrPoshtaProvider::class);
                break;
                // Тут можна додати інших провайдерів
            default:
                return null;
        }

        $this->providerInstances[$providerCode] = $instance;

        return $instance;
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
        $weight += $itemsCount * 0.3; // 300г за товар

        return round($weight, 2);
    }
}
