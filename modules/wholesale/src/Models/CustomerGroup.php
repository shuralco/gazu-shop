<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerGroup extends Model
{
    /**
     * Стандартна група кешується в static на час запиту (App\\Support\\PricingGroup),
     * а від неї залежить роздрібна ціна кожного товару. Тому будь-яка зміна
     * групи одразу скидає кеш — інакше довгоживучий воркер (Octane, черга)
     * рахував би ціни за старою групою до перезапуску.
     */
    protected static function booted(): void
    {
        static::saved(fn () => \App\Support\PricingGroup::flush());
        static::deleted(fn () => \App\Support\PricingGroup::flush());
    }

    protected $fillable = [
        'name',
        'display_name',
        'discount_percentage',
        'markup_percentage',
        'min_order_amount',
        'payment_terms',
        'is_default',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'discount_percentage' => 'decimal:2',
        'markup_percentage' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function productPrices(): HasMany
    {
        return $this->hasMany(ProductGroupPrice::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
