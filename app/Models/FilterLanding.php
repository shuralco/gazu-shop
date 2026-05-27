<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FilterLanding extends Model
{
    protected $fillable = [
        'slug', 'title', 'h1', 'meta_title', 'meta_description',
        'intro_html', 'outro_html',
        'category_id', 'brand_id', 'filter_ids',
        'is_active', 'show_applied_filters', 'sort_order', 'views_count',
    ];

    protected $casts = [
        'filter_ids' => 'array',
        'is_active' => 'bool',
        'show_applied_filters' => 'bool',
        'sort_order' => 'int',
        'views_count' => 'int',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Selected Filter records (lazy via filter_ids array).
     */
    public function filters()
    {
        return Filter::with('filterGroup')->whereIn('id', $this->filter_ids ?? [])->get();
    }

    /**
     * Build a product query that respects all filter constraints of this landing.
     */
    public function productsQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $q = Product::query()->where('is_active', true);
        if ($this->category_id) {
            $q->where('category_id', $this->category_id);
        }
        if ($this->brand_id) {
            $q->where('brand_id', $this->brand_id);
        }
        if (! empty($this->filter_ids)) {
            // Product must have ALL selected filters (AND across groups, OR within group is implicit)
            $q->whereHas('filters', fn ($f) => $f->whereIn('filters.id', $this->filter_ids), '>=', 1);
        }
        return $q;
    }
}
