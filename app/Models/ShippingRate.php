<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'method_id',
        'zone_id',
        'weight_min',
        'weight_max',
        'base_cost',
        'per_kg_cost',
        'delivery_days',
    ];

    protected $casts = [
        'weight_min' => 'decimal:2',
        'weight_max' => 'decimal:2',
        'base_cost' => 'decimal:2',
        'per_kg_cost' => 'decimal:2',
    ];

    /**
     * Метод доставки
     */
    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class, 'method_id');
    }

    /**
     * Зона доставки
     */
    public function shippingZone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class, 'zone_id');
    }

    /**
     * Обчислити вартість для вказаної ваги
     */
    public function calculateCost(float $weight): float
    {
        $cost = (float) $this->base_cost;

        if ($weight > $this->weight_min) {
            $extraWeight = $weight - $this->weight_min;
            $cost += $extraWeight * (float) $this->per_kg_cost;
        }

        return $cost;
    }

    /**
     * Перевірити чи тариф підходить для ваги
     */
    public function supportsWeight(float $weight): bool
    {
        return $weight >= $this->weight_min &&
               ($this->weight_max === null || $weight <= $this->weight_max);
    }

    /**
     * Scope для вагового діапазону
     */
    public function scopeForWeight(Builder $query, float $weight): Builder
    {
        return $query->where('weight_min', '<=', $weight)
            ->where(function ($q) use ($weight) {
                $q->whereNull('weight_max')
                    ->orWhere('weight_max', '>=', $weight);
            });
    }

    /**
     * Scope для методу та зони
     */
    public function scopeForMethodAndZone(Builder $query, int $methodId, int $zoneId): Builder
    {
        return $query->where('method_id', $methodId)
            ->where('zone_id', $zoneId);
    }
}
