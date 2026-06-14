<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'api_endpoint',
        'is_active',
        'configuration',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'configuration' => 'array',
        'sort_order' => 'integer',
    ];

    /**
     * Методи доставки для цього провайдера
     */
    public function shippingMethods(): HasMany
    {
        return $this->hasMany(ShippingMethod::class, 'provider_id');
    }

    /**
     * Активні методи доставки
     */
    public function activeShippingMethods(): HasMany
    {
        return $this->shippingMethods()->where('is_active', true);
    }

    /**
     * Перевірити чи провайдер активний
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Отримати конфігурацію провайдера
     */
    public function getConfig(?string $key = null)
    {
        if ($key) {
            return $this->configuration[$key] ?? null;
        }

        return $this->configuration;
    }

    /**
     * Scope для активних провайдерів
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** Активні провайдери доставки у порядку sort_order — для checkout. */
    public function scopeActiveOrdered(Builder $query): Builder
    {
        $query->where('is_active', true);
        if (\Illuminate\Support\Facades\Schema::hasColumn('shipping_providers', 'sort_order')) {
            $query->orderBy('sort_order');
        }

        return $query->orderBy('id');
    }
}
