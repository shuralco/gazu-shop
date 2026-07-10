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

    /**
     * Каскад: filters.filter_group_id / filter_products / category_filters —
     * FK RESTRICT → видалення групи з характеристиками падало (500). Чистимо
     * звʼязки й самі характеристики групи.
     */
    protected static function booted(): void
    {
        static::deleting(function (self $g) {
            \Illuminate\Support\Facades\DB::table('filter_products')->where('filter_group_id', $g->id)->delete();
            \Illuminate\Support\Facades\DB::table('category_filters')->where('filter_group_id', $g->id)->delete();
            \Illuminate\Support\Facades\DB::table('filters')->where('filter_group_id', $g->id)->delete();
        });

        static::saved(fn () => Filter::flushCatalogCache());
        static::deleted(fn () => Filter::flushCatalogCache());
    }

    public function filters(): HasMany
    {
        return $this->hasMany(Filter::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_filters');
    }
}
