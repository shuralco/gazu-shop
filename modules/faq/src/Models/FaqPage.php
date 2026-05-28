<?php

namespace App\Models;

use App\Traits\HasSeoMeta;
use App\Traits\TranslatableToArray;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class FaqPage extends Model
{
    use HasSeoMeta, HasTranslations, TranslatableToArray;

    public array $translatable = ['title', 'description'];

    protected $fillable = [
        'title',
        'slug',
        'description',
        'questions',
        'language',
        'is_active',
        'sort_order',
        'seo_title',
        'seo_description',
        'seo_keywords',
    ];

    protected function casts(): array
    {
        return [
            'questions' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function getStructuredData(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => collect($this->questions)->map(function ($faq) {
                return [
                    '@type' => 'Question',
                    'name' => $faq['question'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $faq['answer'],
                    ],
                ];
            })->toArray(),
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeByLanguage(Builder $query, string $language): Builder
    {
        return $query->where('language', $language);
    }
}
