<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UpCity extends Model
{
    protected $table = 'up_cities';

    public $incrementing = false;

    protected $fillable = [
        'id', 'region_id', 'district_id',
        'name_ua', 'name_en', 'district_ua', 'city_type_ua',
        'population', 'postcode',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'region_id' => 'integer',
            'district_id' => 'integer',
            'population' => 'integer',
        ];
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        $term = trim($term);
        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('name_ua', 'like', $term.'%')
                ->orWhere('name_en', 'like', $term.'%')
                ->orWhere('postcode', 'like', $term.'%');
        });
    }
}
