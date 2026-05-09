<?php

namespace App\Http\Middleware;

use App\Services\CacheOptimizationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class EnvironmentAutoConfig
{
    /**
     * Auto-configure environment based on access method
     */
    public function handle(Request $request, Closure $next)
    {
        $this->configureForRequest($request);

        // Skip auto-optimization for cart operations to improve performance
        if ($this->isCartOperation($request)) {
            return $next($request);
        }

        // Auto-optimize caches once per request (skip for cart operations)
        if (config('cache-optimization.auto_optimize', true)) {
            app(CacheOptimizationService::class)->optimizeForEnvironment();
        }

        return $next($request);
    }

    private function configureForRequest(Request $request): void
    {
        $host = $request->getHost();
        $userAgent = $request->userAgent();

        // Detect mobile access
        $isMobile = $this->isMobileDevice($userAgent);
        $isLocalIpAccess = $this->isLocalIpAccess($host);

        if ($isMobile || $isLocalIpAccess) {
            // Force production-like asset serving for mobile/remote access
            Config::set('app.asset_url', 'http://192.168.0.123:8003');
            Config::set('session.domain', '192.168.0.123');
        } else {
            // Local development
            Config::set('app.asset_url', null);
            Config::set('session.domain', null);
        }

        // Auto-configure debug mode based on environment
        if (app()->environment('production')) {
            Config::set('app.debug', false);
        } elseif ($isLocalIpAccess || $isMobile) {
            // Keep debug off for remote access even in local env
            Config::set('app.debug', false);
        }

        // Ensure CSRF works across environments
        Config::set('session.same_site', 'lax');
    }

    private function isMobileDevice(string $userAgent): bool
    {
        $mobilePatterns = [
            'Mobile', 'Android', 'iPhone', 'iPad', 'BlackBerry',
            'Windows Phone', 'Opera Mini', 'IEMobile',
        ];

        foreach ($mobilePatterns as $pattern) {
            if (stripos($userAgent, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    private function isLocalIpAccess(string $host): bool
    {
        return ! in_array($host, ['localhost', '127.0.0.1', '::1']);
    }

    private function isCartOperation(Request $request): bool
    {
        // Fast-path for cart operations to skip heavy middleware
        return $request->hasHeader('X-Livewire') &&
               (str_contains($request->getContent(), 'add2Cart') ||
                str_contains($request->getContent(), 'removeFromCart') ||
                str_contains($request->getContent(), 'updateItemQuantity') ||
                str_contains($request->getContent(), 'clearCart'));
    }
}
