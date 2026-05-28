<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingZone extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'country_code',
        'regions',
        'postal_codes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'regions' => 'array',
        'postal_codes' => 'array',
    ];

    /**
     * Тарифи для цьої зони
     */
    public function shippingRates(): HasMany
    {
        return $this->hasMany(ShippingRate::class, 'zone_id');
    }

    /**
     * Перевірити чи адреса належить до цієї зони
     */
    public function containsAddress(array $address): bool
    {
        $region = $address['region'] ?? '';
        $postalCode = $address['postal_code'] ?? '';

        // Перевірка регіонів
        if (! empty($this->regions)) {
            $regionMatch = false;
            foreach ($this->regions as $zoneRegion) {
                if (stripos($region, $zoneRegion) !== false) {
                    $regionMatch = true;
                    break;
                }
            }
            if (! $regionMatch) {
                return false;
            }
        }

        // Перевірка поштових кодів
        if (! empty($this->postal_codes) && ! empty($postalCode)) {
            return in_array($postalCode, $this->postal_codes);
        }

        return true;
    }

    /**
     * Scope для активних зон
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope для країни
     */
    public function scopeByCountry(Builder $query, string $countryCode): Builder
    {
        return $query->where('country_code', $countryCode);
    }
}
