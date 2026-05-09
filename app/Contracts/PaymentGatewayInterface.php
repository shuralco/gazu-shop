<?php

namespace App\Contracts;

use App\DTOs\PaymentResponse;
use App\DTOs\PaymentStatus;
use App\DTOs\RefundResponse;
use App\DTOs\WebhookResponse;
use App\Models\Order;
use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    /**
     * Створити платіж для замовлення
     */
    public function createPayment(Order $order, array $options = []): PaymentResponse;

    /**
     * Перевірити статус платежу
     */
    public function verifyPayment(string $paymentId): PaymentStatus;

    /**
     * Повернути кошти
     */
    public function refundPayment(string $paymentId, float $amount): RefundResponse;

    /**
     * Обробити webhook від платіжної системи
     */
    public function handleWebhook(Request $request): WebhookResponse;

    /**
     * Отримати підтримувані валюти
     */
    public function getSupportedCurrencies(): array;

    /**
     * Перевірити чи працює в тестовому режимі
     */
    public function isTestMode(): bool;

    /**
     * Отримати відображувану назву платіжної системи
     */
    public function getDisplayName(): string;

    /**
     * Отримати опис платіжної системи
     */
    public function getDescription(): string;

    /**
     * Розрахувати комісію
     */
    public function calculateFee(float $amount): float;

    /**
     * Отримати час обробки платежу
     */
    public function getProcessingTime(): string;

    /**
     * Отримати підтримувані функції
     */
    public function getSupportedFeatures(): array;

    /**
     * Мінімальна сума платежу
     */
    public function getMinAmount(): float;

    /**
     * Максимальна сума платежу
     */
    public function getMaxAmount(): float;

    /**
     * Перевірити чи підтримується замовлення
     */
    public function supportsOrder(Order $order): bool;
}
