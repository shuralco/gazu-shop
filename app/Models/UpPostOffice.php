<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UpPostOffice extends Model
{
    protected $table = 'up_post_offices';

    protected $fillable = [
        'po_id', 'city_id', 'postcode',
        'city_ua', 'district_ua', 'region_ua',
        'type_acronym', 'type_long',
        'address', 'lock_code', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'po_id' => 'integer',
            'city_id' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForCity(Builder $query, int $cityId): Builder
    {
        return $query->where('city_id', $cityId);
    }

    public function scopeOfType(Builder $query, string $acronym): Builder
    {
        return $query->where('type_acronym', $acronym);
    }
}
