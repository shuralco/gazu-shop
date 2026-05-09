<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class CacheHeaders
{
    /**
     * Apply Cache-Control headers based on route context.
     *
     * Static assets (JS/CSS/images): 1 year immutable (Vite hashes filenames).
     * Product/category pages: 1 hour, must-revalidate.
     * Cart/checkout: no caching.
     * Default pages: 10 minutes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Skip for non-GET/HEAD or Livewire AJAX requests
        if (! $request->isMethodCacheable() || $request->hasHeader('X-Livewire')) {
            return $response;
        }

        // Skip for binary file responses (they handle their own caching)
        if ($response instanceof BinaryFileResponse) {
            return $response;
        }

        // Skip for authenticated admin users (always fresh)
        if ($request->user()?->is_admin) {
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
            return $response;
        }

        $path = $request->path();

        // Static assets: long-term immutable cache (Vite uses hashed filenames)
        if ($this->isStaticAsset($path)) {
            $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
            return $response;
        }

        // Cart & checkout: never cache
        if ($this->isCartOrCheckout($path)) {
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
            return $response;
        }

        // User cabinet pages: private, no-cache
        if ($this->isUserArea($path)) {
            $response->headers->set('Cache-Control', 'private, no-cache, must-revalidate');
            return $response;
        }

        // Product and category pages: 1 hour, must-revalidate
        if ($this->isProductOrCategory($request)) {
            $response->headers->set('Cache-Control', 'public, max-age=3600, must-revalidate');
            return $response;
        }

        // Default: 10 minutes for other public pages
        $response->headers->set('Cache-Control', 'public, max-age=600, must-revalidate');

        return $response;
    }

    private function isStaticAsset(string $path): bool
    {
        return (bool) preg_match('/\.(js|css|png|jpg|jpeg|gif|webp|svg|ico|woff2?|ttf|eot)$/i', $path);
    }

    private function isCartOrCheckout(string $path): bool
    {
        return (bool) preg_match('/(cart|checkout|payment)/i', $path);
    }

    private function isUserArea(string $path): bool
    {
        return (bool) preg_match('/(cabinet|account|profile|orders)/i', $path);
    }

    private function isProductOrCategory(Request $request): bool
    {
        $routeName = $request->route()?->getName() ?? '';

        return in_array($routeName, ['product', 'category', 'home', 'brands', 'hits', 'new', 'specials']);
    }
}
