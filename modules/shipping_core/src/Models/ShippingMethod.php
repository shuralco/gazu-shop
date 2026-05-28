<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'provider_id',
        'method_code',
        'description',
        'base_cost',
        'per_kg_cost',
        'estimated_days',
        'max_weight',
        'additional_config',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'additional_config' => 'array',
        'base_cost' => 'decimal:2',
        'per_kg_cost' => 'decimal:2',
        'max_weight' => 'decimal:2',
    ];

    /**
     * Провайдер доставки
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(ShippingProvider::class);
    }

    /**
     * Тарифи для цього методу
     */
    public function shippingRates(): HasMany
    {
        return $this->hasMany(ShippingRate::class, 'method_id');
    }

    /**
     * Відправлення за цим методом
     */
    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'method_id');
    }

    /**
     * Обчислити вартість доставки
     */
    public function calculateCost(float $weight): float
    {
        return (float) ($this->base_cost + ($weight * $this->per_kg_cost));
    }

    /**
     * Перевірити чи метод підтримує вагу
     */
    public function supportsWeight(float $weight): bool
    {
        return $this->max_weight === null || $weight <= $this->max_weight;
    }

    /**
     * Scope для активних методів
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope для провайдера
     */
    public function scopeByProvider(Builder $query, string $providerCode): Builder
    {
        return $query->whereHas('provider', function ($q) use ($providerCode) {
            $q->where('code', $providerCode);
        });
    }
}
