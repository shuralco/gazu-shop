<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Довідник статусів замовлень (кастомізується адміном).
 * orders.status зберігає OrderStatus.key.
 */
class OrderStatus extends Model
{
    protected $fillable = [
        'key', 'label', 'color', 'icon', 'sort_order', 'is_default', 'is_final', 'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_final' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        $flush = fn () => Cache::forget('order_statuses:map');
        static::saved($flush);
        static::deleted($flush);
    }

    /** key => label (активні, відсортовані) — для Select/Filter. */
    public static function options(): array
    {
        return static::map('label');
    }

    /** key => color — для бейджів. */
    public static function colors(): array
    {
        return static::map('color');
    }

    /** key => icon. */
    public static function icons(): array
    {
        return static::map('icon');
    }

    public static function defaultKey(): string
    {
        return (string) (self::cached()->firstWhere('is_default', true)->key
            ?? self::cached()->first()->key ?? 'pending');
    }

    private static function map(string $field): array
    {
        return self::cached()->mapWithKeys(fn ($s) => [$s->key => $s->{$field}])->all();
    }

    private static function cached()
    {
        return Cache::remember('order_statuses:map', 600, function () {
            if (! \Illuminate\Support\Facades\Schema::hasTable('order_statuses')) {
                return collect();
            }
            return static::query()->where('is_active', true)->orderBy('sort_order')->orderBy('label')->get();
        });
    }
}
