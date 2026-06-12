<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Стаття довідки/wiki адмінки. Контент — Markdown (рендериться через
 * league/commonmark, що вже в Laravel). Редагується в адмінці (HelpArticleResource),
 * читається на сторінці «Інструкції» (HelpCenter). match_path звʼязує статтю з
 * розділом адмінки для контекстної кнопки «Довідка».
 */
class HelpArticle extends Model
{
    protected $fillable = [
        'slug', 'title', 'section', 'icon', 'content', 'match_path', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    /** Markdown → безпечний HTML (commonmark екранує сирий HTML за замовчуванням). */
    public function getContentHtmlAttribute(): string
    {
        return Str::markdown((string) $this->content, [
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ]);
    }
}
