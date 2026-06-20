<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Довідник валют (кастомізується адміном). CurrencyService читає звідси.
 */
class Currency extends Model
{
    protected $fillable = [
        'code', 'name', 'symbol', 'rate', 'position', 'decimals',
        'is_base', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'rate' => 'float',
        'decimals' => 'integer',
        'is_base' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        $flush = fn () => Cache::forget('currencies:map');
        static::saved($flush);
        static::deleted($flush);
    }

    /**
     * Активні валюти у форматі config('currencies.available'):
     * [code => ['symbol','name','code','rate','position','decimals']].
     * null якщо таблиці/рядків немає (→ CurrencyService впаде на config).
     */
    public static function availableMap(): ?array
    {
        if (! Schema::hasTable('currencies')) {
            return null;
        }

        return Cache::remember('currencies:map', 600, function () {
            $rows = static::query()->where('is_active', true)->orderBy('sort_order')->orderBy('code')->get();
            if ($rows->isEmpty()) {
                return null;
            }

            return $rows->mapWithKeys(fn ($c) => [$c->code => [
                'symbol' => $c->symbol,
                'name' => $c->name,
                'code' => $c->code,
                'rate' => (float) $c->rate,
                'position' => $c->position ?: 'after',
                'decimals' => (int) $c->decimals,
            ]])->all();
        });
    }

    public static function baseCode(): ?string
    {
        $map = self::availableMap();
        if (! $map) {
            return null;
        }
        $base = static::query()->where('is_active', true)->where('is_base', true)->value('code');

        return $base ?: array_key_first($map);
    }

    /**
     * Опції для Filament-селектів валюти ціни: [code => "СИМВОЛ CODE — Назва"].
     * Джерело — довідник /admin/currencies (активні валюти). Fallback — UAH.
     */
    public static function selectOptions(): array
    {
        $map = self::availableMap();
        if (! $map) {
            return ['UAH' => '₴ UAH'];
        }
        $out = [];
        foreach ($map as $code => $c) {
            $out[$code] = trim(($c['symbol'] ?? '').' '.$code.' — '.($c['name'] ?? $code));
        }

        return $out;
    }

    /**
     * Конвертує суму з валюти $code у базову (грн) за курсами /admin/currencies.
     * Делегує CurrencyService (та сама семантика, що й перемикач валют на сайті).
     * $code порожній/базовий/невідомий → повертає суму без змін.
     */
    public static function toBase($amount, ?string $code): float
    {
        $amount = (float) $amount;
        $base = self::baseCode();
        $code = $code ? strtoupper($code) : null;
        if ($amount == 0.0 || ! $code || ! $base || $code === $base) {
            return round($amount, 2);
        }
        try {
            return app(\App\Services\Currency\CurrencyService::class)->convert($amount, $code, $base);
        } catch (\Throwable) {
            return round($amount, 2);
        }
    }
}
