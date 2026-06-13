<?php

namespace App\Support\Cache;

use Illuminate\Http\Request;
use Spatie\ResponseCache\CacheProfiles\BaseCacheProfile;
use Symfony\Component\HttpFoundation\Response;

/**
 * Spatie ResponseCache profile. Caches storefront GET requests for гостей.
 *
 * Cache rules:
 *   - GET requests тільки
 *   - Successful responses (2xx) тільки
 *   - Excluded paths: /admin/*, /cart*, /checkout*, /account*, /login, /register, /logout,
 *     /api/*, /storage/*, /livewire/*
 *   - Bypass для авторизованих юзерів (мають свій cart/session-bound контент)
 *   - Cache lifetime: 1h (catalog/product), 24h (info/blog) — встановлено через
 *     route-specific middleware-вираз або per-controller setSeconds()
 */
class GazuCacheProfile extends BaseCacheProfile
{
    /**
     * Paths excluded from cache. Джерело — config('storefront.excluded_cache_prefixes')
     * (нова тема задає свої БЕЗ правки цього класу). Fallback = поточний GAZU.
     *
     * @var array<int,string>|null
     */
    private static ?array $excludedPathPrefixes = null;

    /** @return array<int,string> */
    private function excludedPrefixes(): array
    {
        if (self::$excludedPathPrefixes !== null) {
            return self::$excludedPathPrefixes;
        }

        $cfg = config('storefront.excluded_cache_prefixes');

        return self::$excludedPathPrefixes = (is_array($cfg) && $cfg !== []) ? array_values($cfg) : [
            'admin', 'cart', 'checkout', 'account', 'login', 'register', 'logout',
            'api', 'storage', 'livewire', 'sanctum', 'horizon', 'telescope', 'csrf-token',
            // User-specific — ніколи не кешувати.
            'wishlist', 'obrane', 'kabinet', 'zamovlennya', 'garazh',
        ];
    }

    public function shouldCacheRequest(Request $request): bool
    {
        if (! $request->isMethod('GET')) {
            return false;
        }

        // Anonymous only — авторизовані бачать персональні дані.
        if ($request->user()) {
            return false;
        }

        // Header-bypass для debug.
        if ($request->hasHeader('X-Cache-Bypass')) {
            return false;
        }

        $path = trim($request->path(), '/');
        foreach ($this->excludedPrefixes() as $prefix) {
            if ($path === $prefix || str_starts_with($path.'/', $prefix.'/')) {
                return false;
            }
        }

        return true;
    }

    public function shouldCacheResponse(Response $response): bool
    {
        return $response->isSuccessful();
    }

    public function useCacheNameSuffix(Request $request): string
    {
        // Different cache entries per: locale, query-string, AND Vite manifest hash.
        // Last part — silver bullet against CSS hash mismatch between deploys:
        // після `npm run build` manifest.json змінюється → cache keys auto-invalidate
        // → старі HTML з посиланнями на видалені assets більше не подаються.
        $manifestVersion = $this->viteManifestVersion();
        return implode(':', [
            app()->getLocale(),
            (string) $request->getQueryString(),
            $manifestVersion,
        ]);
    }

    private function viteManifestVersion(): string
    {
        static $cached = null;
        if ($cached !== null) return $cached;
        $manifest = public_path('build/manifest.json');
        if (! is_file($manifest)) return $cached = 'no-manifest';
        return $cached = substr(md5_file($manifest) ?: 'none', 0, 8);
    }
}
