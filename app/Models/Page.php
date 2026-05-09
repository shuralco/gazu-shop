<?php

namespace App\Models;

use App\Traits\TranslatableToArray;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Page extends Model
{
    use HasTranslations, TranslatableToArray, SoftDeletes;

    public array $translatable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'canonical_url',
        'is_indexable',
        'is_followable',
        'robots_custom',
        'template',
        'layout',
        'is_active',
        'show_in_menu',
        'show_in_footer',
        'menu_group',
        'sort_order',
        'icon',
        'og_image',
        'og_type',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_indexable' => 'boolean',
        'is_followable' => 'boolean',
        'show_in_menu' => 'boolean',
        'show_in_footer' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        // Auto-generate transliterated slugs per locale
        static::saving(function ($page) {
            if (!config('slugs.auto_transliterate', true)) {
                return;
            }

            $service = app(\App\Services\TransliterationService::class);
            $locales = config('slugs.locales', ['uk', 'en']);

            foreach ($locales as $locale) {
                $title = $page->getTranslation('title', $locale, false);
                if (!$title) {
                    continue;
                }

                $existingSlug = $page->getTranslation('slug', $locale, false);
                if ($existingSlug) {
                    continue;
                }

                $slug = $service->generateSlug($title, $locale);
                $slug = Str::limit($slug, config('slugs.max_length', 100), '');
                $page->setTranslation('slug', $locale, $slug);
            }
        });
    }

    /**
     * Get the locale-aware slug for the current or given locale.
     */
    public function getLocalizedSlug(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $slug = $this->getTranslation('slug', $locale, false);

        if ($slug) {
            return $slug;
        }

        foreach (config('slugs.locales', ['uk', 'en']) as $loc) {
            $slug = $this->getTranslation('slug', $loc, false);
            if ($slug) {
                return $slug;
            }
        }

        return $this->getAttributes()['slug'] ?? '';
    }

    /**
     * Find a page by its locale-specific slug.
     */
    public static function findBySlug(string $slug, ?string $locale = null): ?self
    {
        $locale = $locale ?? app()->getLocale();

        $page = static::where("slug->{$locale}", $slug)->first();

        if ($page) {
            return $page;
        }

        foreach (config('slugs.locales', ['uk', 'en']) as $loc) {
            if ($loc === $locale) {
                continue;
            }
            $page = static::where("slug->{$loc}", $slug)->first();
            if ($page) {
                return $page;
            }
        }

        // Legacy fallback
        return static::where('slug', $slug)->first();
    }

    /**
     * Get the robots directive based on is_indexable and is_followable flags.
     */
    public function getRobotsDirective(): string
    {
        if ($this->robots_custom) {
            return $this->robots_custom;
        }

        $index = $this->is_indexable ? 'index' : 'noindex';
        $follow = $this->is_followable ? 'follow' : 'nofollow';

        return "{$index}, {$follow}";
    }

    /**
     * Get the public URL for this page.
     */
    public function getUrl(?string $locale = null): string
    {
        return locale_url('page/' . $this->getLocalizedSlug($locale));
    }

    // --- Scopes ---

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFooter(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('show_in_footer', true)
            ->orderBy('sort_order');
    }

    public function scopeMenu(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('show_in_menu', true)
            ->orderBy('sort_order');
    }
}
