<?php

namespace App\Traits;

use App\Models\Category;
use App\Models\Product;
use App\Models\SeoMeta;
use App\Services\SeoMetaGenerator;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasSeoMeta
{
    public function seoMeta(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'seoable');
    }

    public function getSeoTitle(string $language = 'uk'): ?string
    {
        $seoMeta = $this->seoMeta()->where('language', $language)->first();

        if ($seoMeta && $seoMeta->meta_title) {
            return $seoMeta->meta_title;
        }

        return $this->generateDefaultSeoTitle($language);
    }

    public function getSeoDescription(string $language = 'uk'): ?string
    {
        $seoMeta = $this->seoMeta()->where('language', $language)->first();

        if ($seoMeta && $seoMeta->meta_description) {
            return $seoMeta->meta_description;
        }

        return $this->generateDefaultSeoDescription($language);
    }

    public function getSeoKeywords(string $language = 'uk'): ?string
    {
        $seoMeta = $this->seoMeta()->where('language', $language)->first();

        if ($seoMeta && $seoMeta->meta_keywords) {
            return is_array($seoMeta->meta_keywords)
                ? implode(', ', $seoMeta->meta_keywords)
                : $seoMeta->meta_keywords;
        }

        return $this->generateDefaultSeoKeywords($language);
    }

    public function getCanonicalUrl(): string
    {
        $seoMeta = $this->seoMeta()->first();

        if ($seoMeta && $seoMeta->canonical_url) {
            return $seoMeta->canonical_url;
        }

        return $this->generateDefaultCanonicalUrl();
    }

    public function getRobotsDirective(): string
    {
        $seoMeta = $this->seoMeta()->first();

        return $seoMeta?->robots ?? 'index,follow';
    }

    public function getOpenGraphData(string $language = 'uk'): array
    {
        $seoMeta = $this->seoMeta()->where('language', $language)->first();

        return [
            'og:title' => $seoMeta?->og_title ?? $this->getSeoTitle($language),
            'og:description' => $seoMeta?->og_description ?? $this->getSeoDescription($language),
            'og:image' => $seoMeta?->og_image,
            'og:type' => $seoMeta?->og_type ?? 'website',
            'og:url' => $this->getCanonicalUrl(),
        ];
    }

    public function getTwitterCardData(string $language = 'uk'): array
    {
        $seoMeta = $this->seoMeta()->where('language', $language)->first();

        return [
            'twitter:card' => $seoMeta?->twitter_card ?? 'summary_large_image',
            'twitter:title' => $seoMeta?->twitter_title ?? $this->getSeoTitle($language),
            'twitter:description' => $seoMeta?->twitter_description ?? $this->getSeoDescription($language),
            'twitter:image' => $seoMeta?->twitter_image,
            'twitter:site' => $seoMeta?->twitter_site,
        ];
    }

    public function getStructuredData(): ?array
    {
        $seoMeta = $this->seoMeta()->first();

        if ($seoMeta && $seoMeta->structured_data) {
            return is_array($seoMeta->structured_data)
                ? $seoMeta->structured_data
                : json_decode($seoMeta->structured_data, true);
        }

        return $this->generateDefaultStructuredData();
    }

    public function getFaqSchema(): ?array
    {
        $seoMeta = $this->seoMeta()->first();

        if ($seoMeta) {
            return $seoMeta->getFaqSchema();
        }

        return null;
    }

    public function shouldIncludeInSitemap(): bool
    {
        $seoMeta = $this->seoMeta()->first();

        return $seoMeta?->sitemap_include ?? true;
    }

    public function getSitemapPriority(): float
    {
        $seoMeta = $this->seoMeta()->first();

        return $seoMeta?->sitemap_priority ?? $this->getDefaultSitemapPriority();
    }

    public function getSitemapChangefreq(): string
    {
        $seoMeta = $this->seoMeta()->first();

        return $seoMeta?->sitemap_changefreq ?? $this->getDefaultSitemapChangefreq();
    }

    public function generateSeoMeta(string $language = 'uk'): SeoMeta
    {
        $generator = new SeoMetaGenerator;

        $seoData = match (static::class) {
            \App\Models\Category::class => $generator->generateForCategory($this, $language),
            \App\Models\Product::class => $generator->generateForProduct($this, $language),
            default => [],
        };

        return SeoMeta::updateOrCreate(
            [
                'seoable_type' => static::class,
                'seoable_id' => $this->id,
                'language' => $language,
            ],
            array_merge($seoData, [
                'robots' => 'index,follow',
                'sitemap_include' => true,
                'sitemap_priority' => $this->getDefaultSitemapPriority(),
                'sitemap_changefreq' => $this->getDefaultSitemapChangefreq(),
            ])
        );
    }

    protected function generateDefaultSeoTitle(string $language = 'uk'): string
    {
        $shopName = config('app.name', 'SimpleShop');

        return match (static::class) {
            \App\Models\Category::class => $language === 'uk'
                ? "Категорія {$this->title} | {$shopName}"
                : "{$this->title} Category | {$shopName}",
            \App\Models\Product::class => $language === 'uk'
                ? "Купити {$this->title} - {$this->price} грн | {$shopName}"
                : "Buy {$this->title} - {$this->price} UAH | {$shopName}",
            default => "{$this->title} | {$shopName}",
        };
    }

    protected function generateDefaultSeoDescription(string $language = 'uk'): string
    {
        return match (static::class) {
            \App\Models\Category::class => $language === 'uk'
                ? "Великий вибір товарів у категорії {$this->title}. Швидка доставка по Україні."
                : "Wide selection of products in {$this->title} category. Fast delivery across Ukraine.",
            \App\Models\Product::class => $language === 'uk'
                ? "Купити {$this->title} за найкращою ціною. {$this->short_description}"
                : "Buy {$this->title} at the best price. {$this->short_description}",
            default => $this->title,
        };
    }

    protected function generateDefaultSeoKeywords(string $language = 'uk'): string
    {
        $generator = new SeoMetaGenerator;

        return $generator->generateKeywords($this->title, [], $language);
    }

    protected function generateDefaultCanonicalUrl(): string
    {
        $locale = app()->getLocale();
        $slug = method_exists($this, 'getLocalizedSlug')
            ? $this->getLocalizedSlug($locale)
            : $this->slug;

        $baseUrl = rtrim(config('app.url'), '/');

        return "{$baseUrl}/{$locale}/{$slug}";
    }

    protected function generateDefaultStructuredData(): ?array
    {
        return match (static::class) {
            Product::class => [
                '@context' => 'https://schema.org/',
                '@type' => 'Product',
                'name' => $this->title,
                'description' => strip_tags($this->short_description ?? ''),
                'offers' => [
                    '@type' => 'Offer',
                    'price' => $this->price,
                    'priceCurrency' => 'UAH',
                    'availability' => 'https://schema.org/InStock',
                ],
            ],
            Category::class => [
                '@context' => 'https://schema.org/',
                '@type' => 'CollectionPage',
                'name' => $this->title,
                'mainEntity' => [
                    '@type' => 'ItemList',
                    'numberOfItems' => $this->products()->count(),
                ],
            ],
            default => null,
        };
    }

    protected function getDefaultSitemapPriority(): float
    {
        return match (static::class) {
            Product::class => 0.8,
            Category::class => 0.7,
            default => 0.5,
        };
    }

    protected function getDefaultSitemapChangefreq(): string
    {
        return match (static::class) {
            Product::class => 'daily',
            Category::class => 'weekly',
            default => 'monthly',
        };
    }
}
