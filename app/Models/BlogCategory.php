<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class BlogCategory extends Model
{
    use HasTranslations;

    public array $translatable = ['name', 'description', 'meta_title', 'meta_description'];

    protected $fillable = [
        'name', 'slug', 'description', 'meta_title', 'meta_description', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /** Blog posts (Page records) in this category. */
    public function posts(): HasMany
    {
        return $this->hasMany(Page::class, 'blog_category_id')->where('template', 'blog_post');
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}
