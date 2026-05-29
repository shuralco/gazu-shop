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

        // CSRF exclusions for server-to-server callbacks that carry no session/token.
        // Registered here (Laravel 12 style) so the FRAMEWORK ValidateCsrfToken
        // actually skips them. The old per-route withoutMiddleware(App\..\VerifyCsrfToken)
        // targeted a class not present in the resolved web stack and excluded nothing,
        // so payment webhooks were rejected with 419/302 before WebhookController ran.
        $middleware->validateCsrfTokens(except: [
            'webhooks/*',
            'orders/*/success',
            'mobile-test',
        ]);

        // Full-page HTML response cache (Redis). Caches storefront GET для гостей,
        // skip /admin /cart /checkout /api — див. GazuCacheProfile.
        // Auto-flush через model observers — див. AppServiceProvider::boot().
        // CRITICAL: appendToGroup('web') — НЕ global ->append() — щоб ResponseCache
        // спрацював ПІСЛЯ StartSession + VerifyCsrfToken. Інакше:
        //   - $request->user() = null (session не стартувала) → кешує auth users
        //   - CsrfTokenReplacer не знає поточного csrf_token() → 419 для всіх POST
        $middleware->appendToGroup('web', \Spatie\ResponseCache\Middlewares\CacheResponse::class);

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

        // Stale Livewire snapshots — client references a component that no
        // longer exists server-side (e.g. widget from a disabled module).
        // Return a Livewire-shaped payload that triggers full page reload
        // instead of bubbling up as 500.
        $exceptions->render(function (\Livewire\Exceptions\ComponentNotFoundException $e, \Illuminate\Http\Request $request) {
            if ($request->is('livewire/*') || $request->expectsJson() || $request->isMethod('POST')) {
                return response()->json([
                    'effects' => [
                        'redirect' => $request->headers->get('Referer') ?: url('/admin'),
                    ],
                    'serverMemo' => [],
                ], 200)->header('X-Livewire-Reload', 'stale-component');
            }
        });

        // Stale CSRF token (419 «Page Expired») — найчастіше: вкладка логіну/
        // адмінки була відкрита довго, сесія в Redis протермінувалась (TTL 120хв)
        // або ротувалась, токен у Livewire-snapshot застарів. Без обробки
        // юзер бачить мертвий 419 (тупик). Тут — graceful recovery.
        //
        // ВАЖЛИВО: Laravel у prepareException() конвертує TokenMismatchException
        // → HttpException(419) ДО render-callbacks. Тому ловимо саме 419-HttpException,
        // а не TokenMismatchException (той ніколи не доходить до callback).
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e, \Illuminate\Http\Request $request) {
            if ($e->getStatusCode() !== 419) {
                return null; // не CSRF — хай рендериться штатно
            }

            // Livewire-запит (Filament login теж сюди): reload-payload зі свіжим токеном.
            if ($request->is('livewire/*') || $request->hasHeader('X-Livewire')) {
                return response()->json([
                    'effects' => [
                        'redirect' => $request->headers->get('Referer') ?: url()->current(),
                    ],
                    'serverMemo' => [],
                ], 200)->header('X-Livewire-Reload', 'stale-csrf');
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Сесія застаріла, оновіть сторінку.'], 419);
            }

            // Звичайний POST форми — редирект назад (GET згенерує свіжий токен).
            // Fallback без Referer: /admin/login для адмінки, інакше головна.
            $fallback = str_starts_with(trim($request->path(), '/'), 'admin')
                ? url('/admin/login')
                : url('/');
            return redirect()
                ->to($request->headers->get('Referer') ?: $fallback)
                ->with('error', 'Сесія застаріла — спробуйте ще раз.');
        });
    })->create();
