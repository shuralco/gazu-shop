<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Довідник статусів складів (кастомізується адміном).
 * merchant_warehouses.status зберігає WarehouseStatus.key.
 */
class WarehouseStatus extends Model
{
    protected $fillable = [
        'key', 'label', 'color', 'icon', 'sort_order', 'is_default', 'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        $flush = fn () => Cache::forget('warehouse_statuses:map');
        static::saved($flush);
        static::deleted($flush);
    }

    public static function options(): array
    {
        return self::map('label');
    }

    public static function colors(): array
    {
        return self::map('color');
    }

    public static function icons(): array
    {
        return self::map('icon');
    }

    public static function defaultKey(): string
    {
        return (string) (self::cached()->firstWhere('is_default', true)->key
            ?? self::cached()->first()->key ?? 'active');
    }

    private static function map(string $field): array
    {
        return self::cached()->mapWithKeys(fn ($s) => [$s->key => $s->{$field}])->all();
    }

    private static function cached()
    {
        return Cache::remember('warehouse_statuses:map', 600, function () {
            if (! Schema::hasTable('warehouse_statuses')) {
                return collect();
            }
            return static::query()->where('is_active', true)->orderBy('sort_order')->orderBy('label')->get();
        });
    }
}
