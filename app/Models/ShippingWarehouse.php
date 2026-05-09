<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ShippingWarehouse extends Model
{
    protected $fillable = [
        'external_id',
        'name',
        'short_address',
        'type',
        'city_ref',
        'city_name',
        'provider_code',
        'longitude',
        'latitude',
        'schedule',
        'additional_data',
        'is_active',
    ];

    protected $casts = [
        'longitude' => 'decimal:7',
        'latitude' => 'decimal:7',
        'schedule' => 'array',
        'additional_data' => 'array',
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeByProvider(Builder $query, string $provider): Builder
    {
        return $query->where('provider_code', $provider);
    }

    public function scopeByCity(Builder $query, string $cityRef): Builder
    {
        return $query->where('city_ref', $cityRef);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }
}
