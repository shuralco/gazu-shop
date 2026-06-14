<?php

namespace App\Support\Checkout;

use App\Models\DisplaySetting;

/**
 * Єдине джерело налаштувань кошика/оформлення замовлення (модуль
 * checkout_settings). Читає DisplaySetting з безпечними дефолтами; ці ж
 * дефолти показує Filament-сторінка налаштувань. Контролер і blade
 * звертаються ТІЛЬКИ сюди, щоб логіка не розповзалась.
 */
class CheckoutConfig
{
    /** Конфігурація контактних полів checkout за замовчуванням. */
    public const FIELD_DEFAULTS = [
        ['key' => 'last_name', 'label' => 'Прізвище', 'placeholder' => '', 'visible' => true, 'required' => false],
        ['key' => 'email', 'label' => 'Email', 'placeholder' => '', 'visible' => true, 'required' => false],
        ['key' => 'comment', 'label' => 'Коментар (необовʼязково)', 'placeholder' => 'Уточнення щодо доставки, монтажу тощо…', 'visible' => true, 'required' => false],
    ];

    /** Поля, якими дозволено керувати (first_name/phone — завжди обовʼязкові core). */
    public const MANAGEABLE_FIELDS = ['last_name', 'email', 'comment'];

    // ---- Кошик ------------------------------------------------------------

    public static function minOrderAmount(): float
    {
        return (float) DisplaySetting::get('checkout_min_order', 0);
    }

    public static function freeShippingThreshold(): float
    {
        return (float) DisplaySetting::get('checkout_free_shipping_threshold', 0);
    }

    public static function oneClickEnabled(): bool
    {
        return (bool) DisplaySetting::get('checkout_oneclick_enabled', true);
    }

    public static function promoEnabled(): bool
    {
        return (bool) DisplaySetting::get('checkout_promo_enabled', true);
    }

    public static function qtyMin(): int
    {
        return max(1, (int) DisplaySetting::get('checkout_qty_min', 1));
    }

    /** 0 = без обмеження. */
    public static function qtyMax(): int
    {
        return max(0, (int) DisplaySetting::get('checkout_qty_max', 0));
    }

    // ---- Поля -------------------------------------------------------------

    /**
     * Конфіг керованих полів, нормалізований (мердж збережених із дефолтами,
     * щоб нові ключі підхоплювались, а зайві ігнорувались).
     *
     * @return array<string, array{key:string,label:string,placeholder:string,visible:bool,required:bool}>
     */
    public static function fields(): array
    {
        $saved = DisplaySetting::get('checkout_fields', null);
        $savedByKey = [];
        if (is_array($saved)) {
            foreach ($saved as $row) {
                if (is_array($row) && ! empty($row['key'])) {
                    $savedByKey[$row['key']] = $row;
                }
            }
        }

        $out = [];
        foreach (self::FIELD_DEFAULTS as $def) {
            $row = $savedByKey[$def['key']] ?? [];
            $out[$def['key']] = [
                'key' => $def['key'],
                'label' => trim((string) ($row['label'] ?? '')) !== '' ? $row['label'] : $def['label'],
                'placeholder' => $row['placeholder'] ?? $def['placeholder'],
                'visible' => array_key_exists('visible', $row) ? (bool) $row['visible'] : $def['visible'],
                'required' => array_key_exists('required', $row) ? (bool) $row['required'] : $def['required'],
            ];
        }

        return $out;
    }

    public static function field(string $key): array
    {
        return self::fields()[$key] ?? ['key' => $key, 'label' => $key, 'placeholder' => '', 'visible' => true, 'required' => false];
    }

    /**
     * Кастомні поля, додані клієнтом.
     *
     * @return array<int, array{key:string,label:string,placeholder:string,required:bool}>
     */
    public static function customFields(): array
    {
        $rows = DisplaySetting::get('checkout_custom_fields', null);
        if (! is_array($rows)) {
            return [];
        }

        $out = [];
        foreach ($rows as $row) {
            if (! is_array($row) || empty($row['key'])) {
                continue;
            }
            $key = preg_replace('/[^a-z0-9_]/', '', \Illuminate\Support\Str::slug((string) $row['key'], '_'));
            if ($key === '') {
                continue;
            }
            $out[] = [
                'key' => $key,
                'label' => (string) ($row['label'] ?? $key),
                'placeholder' => (string) ($row['placeholder'] ?? ''),
                'required' => (bool) ($row['required'] ?? false),
            ];
        }

        return $out;
    }
}
