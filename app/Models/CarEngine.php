<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CarEngine extends Model
{
    protected $fillable = [
        'model_id', 'code', 'label', 'fuel_type', 'displacement', 'hp',
        'years_range', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'model_id' => 'integer',
        'hp' => 'integer',
        'displacement' => 'decimal:1',
    ];

    public function model(): BelongsTo
    {
        return $this->belongsTo(CarModel::class, 'model_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_compatibility', 'engine_id', 'product_id')
            ->withPivot('note')
            ->withTimestamps();
    }
}
