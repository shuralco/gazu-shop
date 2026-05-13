<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfoPage extends Model
{
    protected $fillable = [
        'slug', 'title', 'intro', 'content_html', 'sections',
        'meta_title', 'meta_description',
        'is_active', 'show_in_footer', 'show_in_topbar', 'sort_order',
    ];

    protected $casts = [
        'sections' => 'array',
        'is_active' => 'boolean',
        'show_in_footer' => 'boolean',
        'show_in_topbar' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive($q) { return $q->where('is_active', true); }
    public function scopeForFooter($q) { return $q->active()->where('show_in_footer', true)->orderBy('sort_order'); }
    public function scopeForTopbar($q) { return $q->active()->where('show_in_topbar', true)->orderBy('sort_order'); }
}
