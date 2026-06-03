<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisplaySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'title',
        'description',
        'is_active',
        'sort_order',
        'main_mega_menu_structure',
        'horizontal_mega_menu_structure',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'seo_canonical_url',
        'seo_robots',
        'seo_og_title',
        'seo_og_description',
        'seo_og_image',
        'seo_twitter_title',
        'seo_twitter_description',
        'seo_twitter_image',
        'sitemap_include',
        'sitemap_priority',
        'sitemap_changefreq',
        'structured_data',
        'main_mega_menu_promo_image',
        'main_mega_menu_promo_description',
        'main_mega_menu_show_promo',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'array',
            'is_active' => 'boolean',
            'main_mega_menu_structure' => 'array',
            'horizontal_mega_menu_structure' => 'array',
            'sitemap_include' => 'boolean',
            'sitemap_priority' => 'float',
            'structured_data' => 'array',
        ];
    }

    /**
     * In-memory cache for display settings to avoid repeated DB queries
     * within the same request (eliminates N+1 in product card loops).
     */
    protected static ?array $settingsCache = null;

    public static function get(string $key, $default = null)
    {
        // Load all active settings once per request, cache in memory
        if (static::$settingsCache === null) {
            static::$settingsCache = cache()->remember('display_settings_all', 3600, function () {
                return static::where('is_active', true)
                    ->get()
                    ->keyBy('key')
                    ->toArray();
            });
        }

        $setting = static::$settingsCache[$key] ?? null;

        if (! $setting || ($setting['value'] ?? null) === null) {
            return $default;
        }

        $value = $setting['value'];

        return match ($setting['type'] ?? 'string') {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'number', 'float', 'decimal' => (float) $value,
            'json' => is_string($value) ? json_decode($value, true) : $value,
            'array' => is_string($value) ? json_decode($value, true) : (array) $value,
            default => $value,
        };
    }

    /**
     * Flush the in-memory and persistent cache for display settings.
     */
    public static function flushSettingsCache(): void
    {
        static::$settingsCache = null;
        cache()->forget('display_settings_all');
    }

    /**
     * Reset ONLY the in-process static cache (not the shared cache).
     *
     * On Octane/Swoole the static $settingsCache survives between requests in a
     * worker, so a setting changed by one worker stays stale in the others until
     * restart. An Octane RequestReceived listener calls this so every request
     * re-reads the SHARED cache (which set() invalidates), giving fresh values
     * without forgetting/rebuilding the shared cache each request.
     */
    public static function resetRequestCache(): void
    {
        static::$settingsCache = null;
    }

    public static function set(string $key, $value, ?string $title = null): void
    {
        // Infer the storage type from the value. WITHOUT this the `type` column
        // falls back to its DB default ('boolean'), so get() would cast a string
        // like a theme name via filter_var(..., BOOLEAN) → false (silent data loss).
        $type = match (true) {
            is_bool($value) => 'boolean',
            is_int($value) => 'integer',
            is_float($value) => 'number',
            is_array($value) => 'json',
            default => 'string',
        };

        $data = [
            'value' => is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value,
            'type' => $type,
            'title' => $title ?? ucfirst(str_replace(['_', '-'], ' ', $key)),
            // get() reads only is_active=true rows; a setting written via set()
            // must be active or it would be invisible to get() (silent miss).
            'is_active' => true,
        ];

        static::updateOrCreate(
            ['key' => $key],
            $data
        );

        // Always flush the global display settings cache
        static::flushSettingsCache();

        // Clear header cache when header settings change
        if (str_starts_with($key, 'header_') || str_starts_with($key, 'mega_menu_') || str_starts_with($key, 'horizontal_menu_')) {
            static::flushHeaderCache();
        }

        // Clear SEO cache when SEO settings change
        if (str_starts_with($key, 'seo_') || str_starts_with($key, 'sitemap_')) {
            static::flushSeoCache();
        }
    }

    public static function getHeaderSettings(): array
    {
        return cache()->remember('header_settings', 3600, function () {
            return static::whereIn('group', ['header_top_bar', 'header_social', 'header_main', 'mega_menu_content', 'horizontal_menu'])
                ->pluck('value', 'key')
                ->toArray();
        });
    }

    public static function getTopBarSettings(): array
    {
        return cache()->remember('header_top_bar_settings', 3600, function () {
            return static::where('group', 'header_top_bar')
                ->orderBy('sort_order')
                ->pluck('value', 'key')
                ->toArray();
        });
    }

    public static function getMegaMenuSettings(): array
    {
        return cache()->remember('mega_menu_settings', 3600, function () {
            return static::where('group', 'mega_menu_content')
                ->orderBy('sort_order')
                ->pluck('value', 'key')
                ->toArray();
        });
    }

    public static function flushHeaderCache(): void
    {
        cache()->forget('header_settings');
        cache()->forget('header_top_bar_settings');
        cache()->forget('mega_menu_settings');
        cache()->forget('horizontal_menu_settings');
    }

    public static function getSeoSettings(): array
    {
        return cache()->remember('seo_settings', 3600, function () {
            return static::where('group', 'seo')
                ->pluck('value', 'key')
                ->toArray();
        });
    }

    public static function getSeoMetaForPage(string $pageType): ?array
    {
        $cacheKey = "seo_meta_page_{$pageType}";

        return cache()->remember($cacheKey, 3600, function () use ($pageType) {
            $setting = static::where('key', "seo_page_{$pageType}")
                ->where('is_active', true)
                ->first();

            if (! $setting) {
                return null;
            }

            return [
                'title' => $setting->seo_title,
                'description' => $setting->seo_description,
                'keywords' => $setting->seo_keywords,
                'canonical_url' => $setting->seo_canonical_url,
                'robots' => $setting->seo_robots ?? 'index,follow',
                'og_title' => $setting->seo_og_title,
                'og_description' => $setting->seo_og_description,
                'og_image' => $setting->seo_og_image,
                'twitter_title' => $setting->seo_twitter_title,
                'twitter_description' => $setting->seo_twitter_description,
                'twitter_image' => $setting->seo_twitter_image,
                'structured_data' => $setting->structured_data,
            ];
        });
    }

    public static function getSitemapSettings(): array
    {
        return cache()->remember('sitemap_settings', 3600, function () {
            return static::where('group', 'sitemap')
                ->pluck('value', 'key')
                ->toArray();
        });
    }

    public static function flushSeoCache(): void
    {
        cache()->forget('seo_settings');
        cache()->flush(); // Clear all page-specific SEO cache
    }

    public function getDisplayValueAttribute(): string
    {
        return match ($this->type) {
            'boolean' => $this->value ? 'Увімкнено' : 'Вимкнено',
            'array' => implode(', ', $this->value ?? []),
            default => (string) $this->value
        };
    }
}
