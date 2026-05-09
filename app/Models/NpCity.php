<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NpCity extends Model
{
    protected $table = 'np_cities';

    protected $fillable = [
        'ref', 'city_id', 'description', 'description_ru',
        'area_ref', 'area_description', 'settlement_type', 'settlement_type_description',
        'is_branch', 'special_cash_check',
        'delivery_monday', 'delivery_tuesday', 'delivery_wednesday',
        'delivery_thursday', 'delivery_friday', 'delivery_saturday', 'delivery_sunday',
        'last_synced_at',
    ];

    protected $casts = [
        'is_branch' => 'boolean',
        'special_cash_check' => 'boolean',
        'delivery_monday' => 'boolean',
        'delivery_tuesday' => 'boolean',
        'delivery_wednesday' => 'boolean',
        'delivery_thursday' => 'boolean',
        'delivery_friday' => 'boolean',
        'delivery_saturday' => 'boolean',
        'delivery_sunday' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    /**
     * Область
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(NpArea::class, 'area_ref', 'ref');
    }

    /**
     * Відділення в цьому місті
     */
    public function warehouses(): HasMany
    {
        return $this->hasMany(NpWarehouse::class, 'city_ref', 'ref');
    }

    /**
     * Пошук за назвою
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where('description', 'like', "%{$term}%");
    }

    /**
     * Тільки міста (не села/селища)
     */
    public function scopeCitiesOnly(Builder $query): Builder
    {
        return $query->where('settlement_type', 'місто');
    }
}
