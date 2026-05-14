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

        // Cart, checkout & auth: never cache. These pages embed a
        // session-bound CSRF token; a cached copy serves a stale token
        // and every POST fails with HTTP 419 "Page Expired".
        if ($this->isCartOrCheckout($path) || $this->isAuthPath($path)) {
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
            return $response;
        }

        // User cabinet pages: private, no-cache
        if ($this->isUserArea($path)) {
            $response->headers->set('Cache-Control', 'private, no-cache, must-revalidate');
            return $response;
        }

        // All other HTML pages (home, product, category, blog, info…):
        // revalidate every time — NEVER cache HTML with a TTL. Every page
        // embeds (a) a session-bound CSRF token and (b) Vite build-hashed
        // asset URLs. A TTL-cached copy survives a deploy and then points at
        // a pruned `*.css`/`*.js` hash → unstyled / broken page until the TTL
        // expires. `no-cache` still allows bfcache but forces revalidation;
        // the server-side `Cache::tags(['catalog'])` layer keeps it fast.
        $response->headers->set('Cache-Control', 'private, no-cache, must-revalidate');

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

    private function isAuthPath(string $path): bool
    {
        return (bool) preg_match('#^(auth|login|register|wishlist|garage)#i', $path);
    }

    private function isUserArea(string $path): bool
    {
        return (bool) preg_match('/(cabinet|account|profile|orders)/i', $path);
    }
}
