<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CarModel extends Model
{
    protected $fillable = [
        'make_id', 'slug', 'name', 'body_type', 'years_range', 'sort_order', 'is_active',
        'meta_title', 'meta_description', 'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'make_id' => 'integer',
    ];

    public function make(): BelongsTo
    {
        return $this->belongsTo(CarMake::class, 'make_id');
    }

    public function engines(): HasMany
    {
        return $this->hasMany(CarEngine::class, 'model_id');
    }
}
