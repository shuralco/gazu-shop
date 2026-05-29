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
}
