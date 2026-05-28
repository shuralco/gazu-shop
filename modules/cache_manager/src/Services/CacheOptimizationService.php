<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class CacheOptimizationService
{
    public function optimizeForEnvironment(): void
    {
        $environment = $this->detectOptimalEnvironment();
        $strategy = config("cache-optimization.strategies.{$environment}", []);

        $this->applyCacheStrategy($strategy);
        $this->optimizeSpecificCaches();
    }

    private function detectOptimalEnvironment(): string
    {
        if (app()->environment('production')) {
            return 'production';
        }

        // Check if mobile/remote access
        if (request() && $this->isRemoteAccess()) {
            return 'mobile';
        }

        return 'development';
    }

    private function isRemoteAccess(): bool
    {
        $host = request()->getHost();

        return ! in_array($host, ['localhost', '127.0.0.1', '::1']);
    }

    private function applyCacheStrategy(array $strategy): void
    {
        if (empty($strategy)) {
            return;
        }

        // Apply cache driver optimization
        if (isset($strategy['default'])) {
            Config::set('cache.default', $strategy['default']);
        }

        // Apply production optimizations
        if ($strategy['config'] ?? false) {
            $this->cacheConfig();
        }

        if ($strategy['routes'] ?? false) {
            $this->cacheRoutes();
        }

        if ($strategy['events'] ?? false) {
            $this->cacheEvents();
        }

        // Aggressive caching for mobile
        if ($strategy['aggressive'] ?? false) {
            $this->enableAggressiveCaching();
        }
    }

    private function optimizeSpecificCaches(): void
    {
        // Cache frequently accessed settings
        $this->cacheDisplaySettings();

        // Cache navigation menus
        $this->cacheMegaMenuStructure();

        // Cache category hierarchy
        $this->cacheCategoryHierarchy();
    }

    private function cacheConfig(): void
    {
        if (! app()->configurationIsCached()) {
            Artisan::call('config:cache');
        }
    }

    private function cacheRoutes(): void
    {
        if (! app()->routesAreCached()) {
            Artisan::call('route:cache');
        }
    }

    private function cacheEvents(): void
    {
        Artisan::call('event:cache');
    }

    private function enableAggressiveCaching(): void
    {
        // Increase cache TTL for mobile users
        Config::set('session.lifetime', 240); // 4 hours
        Config::set('cache.ttl', config('cache-optimization.ttl.long_term'));
    }

    private function cacheDisplaySettings(): void
    {
        Cache::remember('display_settings_all', 3600, function () {
            return \App\Models\DisplaySetting::all()->pluck('value', 'key');
        });
    }

    private function cacheMegaMenuStructure(): void
    {
        Cache::remember('mega_menu_structure', 3600, function () {
            $structure = \App\Models\DisplaySetting::get('main_mega_menu_structure', []);

            return is_string($structure) ? json_decode($structure, true) : $structure;
        });
    }

    private function cacheCategoryHierarchy(): void
    {
        Cache::remember('category_hierarchy', 3600, function () {
            return \App\Models\Category::with('children')->whereNull('parent_id')->get();
        });
    }

    public function clearOptimizedCaches(): void
    {
        Cache::forget('display_settings_all');
        Cache::forget('mega_menu_structure');
        Cache::forget('category_hierarchy');

        Artisan::call('optimize:clear');
    }
}
