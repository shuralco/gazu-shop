<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FilterGroup extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'title', 'is_active', 'sort_order'];

    public $timestamps = false;

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function filters(): HasMany
    {
        return $this->hasMany(Filter::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_filters');
    }
}
