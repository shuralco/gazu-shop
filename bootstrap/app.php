<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->append(\App\Http\Middleware\CacheHeaders::class);

        // Full-page HTML response cache (Redis). Caches storefront GET для гостей,
        // skip /admin /cart /checkout /api — див. GazuCacheProfile.
        // Auto-flush через model observers — див. AppServiceProvider::boot().
        $middleware->append(\Spatie\ResponseCache\Middlewares\CacheResponse::class);

        // Trust upstream proxies (Coolify, Traefik, Caddy, nginx) so
        // request()->ip() returns the real visitor IP for geo-detect,
        // rate-limiting, and audit logs. Reads TRUSTED_PROXIES env (csv
        // or "*" for all). Defaults to "*" — appropriate when the only
        // public entrypoint is the reverse proxy.
        $middleware->trustProxies(
            at: env('TRUSTED_PROXIES', '*'),
            headers: \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR
                | \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST
                | \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT
                | \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO
                | \Illuminate\Http\Request::HEADER_X_FORWARDED_AWS_ELB,
        );

        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'module' => \App\Http\Middleware\RequiresModule::class,
        ]);
        $middleware->redirectGuestsTo(fn () => route('gazu.auth'));
        $middleware->redirectUsersTo(fn () => route('gazu.home'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(function (\Illuminate\Http\Request $request, \Throwable $e) {
            return $request->is('api/*') || $request->expectsJson();
        });
    })->create();
