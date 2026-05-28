<?php

namespace App\Services\Shipping\Contracts;

use App\Models\Order;
use Illuminate\Support\Collection;

/**
 * Інтерфейс для провайдерів доставки
 */
interface ShippingProviderInterface
{
    /**
     * Розрахувати вартість доставки
     */
    public function calculateShippingCost(Order $order, array $destination): float;

    /**
     * Отримати варіанти доставки
     */
    public function getDeliveryOptions(array $criteria): Collection;

    /**
     * Створити відправлення
     */
    public function createShipment(Order $order, array $data): string;

    /**
     * Відстежити посилку
     */
    public function trackPackage(string $trackingNumber): array;

    /**
     * Валідація адреси
     */
    public function validateAddress(array $address): bool;

    /**
     * Отримати міста
     */
    public function getCities(string $search = ''): Collection;

    /**
     * Отримати відділення/склади
     */
    public function getWarehouses(string $cityRef): Collection;

    /**
     * Перевірити доступність сервісу
     */
    public function isAvailable(): bool;

    /**
     * Отримати код провайдера
     */
    public function getProviderCode(): string;

    /**
     * Отримати назву провайдера
     */
    public function getProviderName(): string;
}
