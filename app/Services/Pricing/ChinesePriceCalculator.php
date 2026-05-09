<?php

namespace App\Services\Pricing;

use App\Models\DisplaySetting;

/**
 * Розрахунок роздрібної ціни з закупки у китайській валюті:
 *   retail = cost * fx_rate * (1 + markup% / 100)
 *
 * Курси беруться з DisplaySetting (admin може змінити):
 *   - fx_cny_uah   — курс юаня (default 4.0)
 *   - fx_usd_uah   — курс долара (default 41.0)
 *   - default_markup — стандартна націнка % (default 100)
 */
class ChinesePriceCalculator
{
    private const FALLBACK_FX = [
        'CNY' => 4.0,
        'USD' => 41.0,
        'EUR' => 44.0,
        'UAH' => 1.0,
    ];

    public function fxRate(string $currency): float
    {
        $currency = strtoupper($currency);
        if ($currency === 'UAH') return 1.0;

        if (class_exists(DisplaySetting::class)) {
            try {
                $key = 'fx_'.strtolower($currency).'_uah';
                $val = DisplaySetting::get($key);
                if ($val && is_numeric($val)) {
                    return (float) $val;
                }
            } catch (\Throwable) {}
        }

        return self::FALLBACK_FX[$currency] ?? 1.0;
    }

    public function defaultMarkup(): float
    {
        if (class_exists(DisplaySetting::class)) {
            try {
                $val = DisplaySetting::get('default_markup');
                if ($val !== null && is_numeric($val)) return (float) $val;
            } catch (\Throwable) {}
        }
        return 100.0;
    }

    public function calculate(?float $cost, string $currency = 'CNY', ?float $markupPercent = null): float
    {
        $cost = (float) $cost;
        if ($cost <= 0) return 0.0;

        $fx = $this->fxRate($currency);
        $markup = $markupPercent ?? $this->defaultMarkup();

        return round($cost * $fx * (1 + $markup / 100), 2);
    }

    /** @return array{retail: float, costInUah: float, fx: float, markup: float} */
    public function breakdown(?float $cost, string $currency = 'CNY', ?float $markupPercent = null): array
    {
        $cost = (float) $cost;
        $fx = $this->fxRate($currency);
        $markup = $markupPercent ?? $this->defaultMarkup();
        $costInUah = round($cost * $fx, 2);
        $retail = round($costInUah * (1 + $markup / 100), 2);

        return compact('retail', 'costInUah', 'fx', 'markup');
    }
}
