<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SeoMeta extends Model
{
    protected $table = 'seo_meta';

    protected $fillable = [
        'seoable_type', 'seoable_id', 'page_type', 'url_slug',
        'meta_title', 'meta_description', 'meta_keywords', 'h1_title',
        'canonical_url', 'og_title', 'og_description', 'og_image', 'og_type',
        'twitter_title', 'twitter_description', 'twitter_image', 'twitter_card',
        'robots_index', 'robots_follow', 'robots_custom',
        'priority', 'changefreq', 'language', 'auto_generated', 'is_active',
        'sitemap_include',
        'structured_data', 'custom_meta', 'seo_text',
    ];

    protected function casts(): array
    {
        return [
            'robots_index' => 'boolean',
            'robots_follow' => 'boolean',
            'auto_generated' => 'boolean',
            'is_active' => 'boolean',
            'sitemap_include' => 'boolean',
            'priority' => 'decimal:1',
            'structured_data' => 'array',
            'custom_meta' => 'array',
        ];
    }

    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }

    // Автоматичне обрізання мета опису до 160 символів
    public function setMetaDescriptionAttribute($value): void
    {
        if ($value) {
            $maxLength = \App\Models\DisplaySetting::get('seo_description_max_length', 160);
            $this->attributes['meta_description'] = mb_substr(strip_tags($value), 0, $maxLength);
        }
    }

    // Автоматичне обрізання заголовку до 60 символів
    public function setMetaTitleAttribute($value): void
    {
        if ($value) {
            $maxLength = \App\Models\DisplaySetting::get('seo_title_max_length', 60);
            $this->attributes['meta_title'] = mb_substr($value, 0, $maxLength);
        }
    }

    // Отримання SEO даних для сторінки
    public static function getForPage(string $pageType, ?string $urlSlug = null): ?self
    {
        $query = static::where('page_type', $pageType)
            ->where('is_active', true);

        if ($urlSlug) {
            $query->where('url_slug', $urlSlug);
        }

        return $query->first();
    }

    // Отримання SEO даних для моделі
    public static function getForModel($model): ?self
    {
        return static::where('seoable_type', get_class($model))
            ->where('seoable_id', $model->id)
            ->where('is_active', true)
            ->first();
    }

    // Генерація robots директив
    public function getRobotsDirective(): string
    {
        $directives = [];

        if (! $this->robots_index) {
            $directives[] = 'noindex';
        }

        if (! $this->robots_follow) {
            $directives[] = 'nofollow';
        }

        if ($this->robots_custom) {
            $directives[] = $this->robots_custom;
        }

        return implode(', ', $directives);
    }

    // Перевірка чи сторінка включена в sitemap
    public function isIncludedInSitemap(): bool
    {
        return $this->is_active && $this->robots_index && $this->priority > 0;
    }

    // Отримання structured data як JSON-LD
    public function getStructuredDataJson(): ?string
    {
        if (! $this->structured_data) {
            return null;
        }

        return json_encode($this->structured_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    // Отримання FAQ Schema
    public function getFaqSchema(): ?array
    {
        if (! $this->structured_data) {
            return $this->generateDefaultFaqSchema();
        }

        $structuredData = is_array($this->structured_data)
            ? $this->structured_data
            : json_decode($this->structured_data, true);

        if (isset($structuredData['@type']) && $structuredData['@type'] === 'FAQPage') {
            return $structuredData;
        }

        return $this->generateDefaultFaqSchema();
    }

    // Генерація стандартного FAQ Schema
    private function generateDefaultFaqSchema(): ?array
    {
        if ($this->page_type === 'category' && $this->seoable) {
            $category = $this->seoable;

            return [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => [
                    [
                        '@type' => 'Question',
                        'name' => "Які товари представлені в категорії {$category->title}?",
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => "У категорії {$category->title} представлено ".$category->products()->count().' якісних товарів різних брендів за доступними цінами.',
                        ],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => "Чи є доставка товарів категорії {$category->title}?",
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => "Так, ми здійснюємо доставку всіх товарів категорії {$category->title} по всій Україні протягом 1-3 робочих днів.",
                        ],
                    ],
                ],
            ];
        }

        if ($this->page_type === 'product' && $this->seoable) {
            $product = $this->seoable;

            return [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => [
                    [
                        '@type' => 'Question',
                        'name' => "Чи є {$product->title} в наявності?",
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => "Так, {$product->title} є в наявності. Актуальну кількість можна уточнити у консультантів.",
                        ],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => "Скільки коштує доставка {$product->title}?",
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => "Доставка {$product->title} коштує від 50 грн по Україні, безкоштовна доставка при замовленні від 1000 грн.",
                        ],
                    ],
                ],
            ];
        }

        return null;
    }

    // Автогенерація canonical URL
    public function generateCanonicalUrl(): string
    {
        if ($this->canonical_url) {
            return $this->canonical_url;
        }

        return url($this->url_slug);
    }

    // Валідація довжини мета полів
    public function validateMetaFields(): array
    {
        $errors = [];

        if ($this->meta_title && mb_strlen($this->meta_title) > 60) {
            $errors['meta_title'] = 'Заголовок задовгий (понад 60 символів)';
        }

        if ($this->meta_description && mb_strlen($this->meta_description) > 160) {
            $errors['meta_description'] = 'Опис задовгий (понад 160 символів)';
        }

        if ($this->meta_title && mb_strlen($this->meta_title) < 30) {
            $errors['meta_title'] = 'Заголовок закороткий (менше 30 символів)';
        }

        return $errors;
    }

    // Скоупи для фільтрації
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForSitemap(Builder $query): Builder
    {
        return $query->active()
            ->where('robots_index', true)
            ->where('priority', '>', 0);
    }

    public function scopeByPageType(Builder $query, string $pageType): Builder
    {
        return $query->where('page_type', $pageType);
    }

    public function scopeByLanguage(Builder $query, string $language): Builder
    {
        return $query->where('language', $language);
    }

    // Кешування для продуктивності
    public static function getCachedMetaForPage(string $pageType, ?string $urlSlug = null): ?array
    {
        $cacheKey = "seo_meta_{$pageType}".($urlSlug ? "_{$urlSlug}" : '');

        return cache()->remember($cacheKey, 3600, function () use ($pageType, $urlSlug) {
            $seoMeta = static::getForPage($pageType, $urlSlug);

            return $seoMeta ? $seoMeta->toArray() : null;
        });
    }

    // Очищення кешу
    public static function flushCache(): void
    {
        cache()->flush(); // Можна зробити більш селективно
    }
}
