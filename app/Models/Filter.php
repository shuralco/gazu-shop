<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Filter extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'title', 'value', 'filter_group_id', 'is_active', 'sort_order'];

    public $timestamps = false;

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Каскад: filter_products.filter_id = FK RESTRICT → видалення характеристики,
     * привʼязаної до товарів, падало (500). Чистимо звʼязки (brand_filters
     * каскадить на рівні БД).
     */
    protected static function booted(): void
    {
        static::deleting(function (self $f) {
            \Illuminate\Support\Facades\DB::table('filter_products')->where('filter_id', $f->id)->delete();
        });
    }

    public function filterGroup(): BelongsTo
    {
        return $this->belongsTo(FilterGroup::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'filter_products')
            ->using(\App\Models\Pivots\FilterProduct::class)
            ->withPivot('filter_group_id');
    }

    public function brands(): BelongsToMany
    {
        return $this->belongsToMany(Brand::class, 'brand_filters');
    }
}
