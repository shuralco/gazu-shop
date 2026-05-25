<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NpWarehouse extends Model
{
    protected $table = 'np_warehouses';

    protected $fillable = [
        'ref', 'site_key', 'number', 'description', 'short_address', 'phone',
        'city_ref', 'city_description', 'type_ref', 'type_description',
        'longitude', 'latitude',
        'total_max_weight', 'place_max_weight', 'max_dimensions',
        'sending_max_length', 'sending_max_width', 'sending_max_height',
        'receiving_max_length', 'receiving_max_width', 'receiving_max_height',
        'post_finance', 'bicycle_parking', 'payment_access', 'pos_terminal',
        'international_shipping', 'self_service_count',
        'reception_schedule', 'delivery_schedule', 'schedule',
        'warehouse_status', 'warehouse_status_date', 'category_of_warehouse',
        'district_code', 'region_city',
        'is_active',
    ];

    protected $casts = [
        'longitude' => 'decimal:7',
        'latitude' => 'decimal:7',
        'total_max_weight' => 'integer',
        'place_max_weight' => 'integer',
        'sending_max_length' => 'integer',
        'sending_max_width' => 'integer',
        'sending_max_height' => 'integer',
        'receiving_max_length' => 'integer',
        'receiving_max_width' => 'integer',
        'receiving_max_height' => 'integer',
        'post_finance' => 'boolean',
        'bicycle_parking' => 'boolean',
        'payment_access' => 'boolean',
        'pos_terminal' => 'boolean',
        'international_shipping' => 'boolean',
        'self_service_count' => 'integer',
        'reception_schedule' => 'array',
        'delivery_schedule' => 'array',
        'schedule' => 'array',
        'warehouse_status_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Місто
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(NpCity::class, 'city_ref', 'ref');
    }

    /**
     * Тільки активні
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Фільтр за містом
     */
    public function scopeForCity(Builder $query, string $cityRef): Builder
    {
        return $query->where('city_ref', $cityRef);
    }

    /**
     * Фільтр за типом (відділення, поштомат, вантажне)
     */
    public function scopeOfType(Builder $query, string $typeRef): Builder
    {
        return $query->where('type_ref', $typeRef);
    }

    /**
     * Пошук за описом
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term) {
            $q->where('description', 'like', "%{$term}%")
                ->orWhere('short_address', 'like', "%{$term}%")
                ->orWhere('site_key', 'like', "%{$term}%");
        });
    }

    /**
     * Чи може вмістити вантаж заданої ваги
     */
    public function canAcceptWeight(float $weight): bool
    {
        return $weight <= $this->total_max_weight;
    }

    public function canAcceptDimensions(float $length, float $width, float $height): bool
    {
        if (! $this->sending_max_length) {
            return true;
        }
        return $length <= $this->sending_max_length
            && $width <= $this->sending_max_width
            && $height <= $this->sending_max_height;
    }

    public function getCoordinates(): ?array
    {
        if ($this->longitude === null || $this->latitude === null) {
            return null;
        }
        return ['lat' => (float) $this->latitude, 'lng' => (float) $this->longitude];
    }
}
