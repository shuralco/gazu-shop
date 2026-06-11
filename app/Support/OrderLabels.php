<?php

namespace App\Support;

/**
 * Єдине джерело людських назв для способів оплати/доставки/статусів замовлення.
 * Використовується на /checkout/success, у кабінеті (order-details) і будь-де,
 * щоб мапи не розходились і не показувалось сире «Manager_call»/«Cod».
 */
class OrderLabels
{
    public const PAYMENT = [
        'card' => 'Картою онлайн',
        'applepay' => 'Apple Pay / Google Pay',
        'cod' => 'Накладений платіж',
        'invoice' => 'Рахунок (гуртом)',
        'manager_call' => 'Уточнення з менеджером', // 1-клік замовлення
    ];

    public const SHIPPING = [
        'novaposhta' => 'Нова Пошта',
        'ukrposhta' => 'УкрПошта',
        'pickup' => 'Самовивіз з магазину',
        'manager_call' => 'Менеджер уточнить доставку', // 1-клік замовлення
    ];

    public const SHIPPING_TYPE = [
        'branch' => 'Відділення',
        'postomat' => 'Поштомат',
        'np_courier' => 'Курʼєр НП',
    ];

    public const PAYMENT_STATUS = [
        'paid' => 'Сплачено',
        'pending' => 'Очікує оплати',
        'processing' => 'Обробляється',
        'failed' => 'Помилка оплати',
        'refunded' => 'Повернено',
    ];

    public static function payment(?string $key): string
    {
        return self::PAYMENT[$key ?? ''] ?? ($key ? ucfirst($key) : '—');
    }

    public static function shipping(?string $key): string
    {
        return self::SHIPPING[$key ?? ''] ?? ($key ? ucfirst($key) : '—');
    }

    public static function shippingType(?string $key): ?string
    {
        return $key ? (self::SHIPPING_TYPE[$key] ?? null) : null;
    }

    public static function paymentStatus(?string $key): string
    {
        return self::PAYMENT_STATUS[$key ?? ''] ?? 'Очікує оплати';
    }
}
