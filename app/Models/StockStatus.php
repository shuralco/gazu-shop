<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Довідник статусів наявності товару (кастомізується адміном).
 * products.stock_status зберігає StockStatus.key.
 */
class StockStatus extends Model
{
    protected $fillable = [
        'key', 'label', 'color', 'icon', 'availability',
        'is_orderable', 'sort_order', 'is_default', 'is_active',
    ];

    protected $casts = [
        'is_orderable' => 'boolean',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        $flush = fn () => Cache::forget('stock_statuses:map');
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

    /** key => schema.org availability (InStock|OutOfStock|PreOrder|BackOrder). */
    public static function availabilities(): array
    {
        return static::map('availability');
    }

    /** key => bool (чи можна купувати). */
    public static function orderable(): array
    {
        return self::cached()->mapWithKeys(fn ($s) => [$s->key => (bool) $s->is_orderable])->all();
    }

    public static function defaultKey(): string
    {
        return (string) (self::cached()->firstWhere('is_default', true)->key
            ?? self::cached()->first()->key ?? 'in_stock');
    }

    /** Повний запис за ключем (із кешу). */
    public static function byKey(?string $key): ?self
    {
        if (! $key) {
            return null;
        }

        return self::cached()->firstWhere('key', $key);
    }

    private static function map(string $field): array
    {
        return self::cached()->mapWithKeys(fn ($s) => [$s->key => $s->{$field}])->all();
    }

    private static function cached()
    {
        return Cache::remember('stock_statuses:map', 600, function () {
            if (! Schema::hasTable('stock_statuses')) {
                return collect();
            }

            return static::query()->where('is_active', true)->orderBy('sort_order')->orderBy('label')->get();
        });
    }
}
