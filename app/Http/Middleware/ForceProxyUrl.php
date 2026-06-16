<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

/**
 * Під Octane/Swoole генератор URL кешує root від ПЕРШОГО запиту воркера
 * (health-check на localhost або інший домен з кількох FQDN). Через це
 * підписані URL (зокрема Livewire /livewire/upload-file) генеруються з
 * чужим хостом → підпис не сходиться при POST → 401 «failed to upload».
 *
 * Примусово фіксуємо root/scheme з APP_URL на КОЖЕН запит — і генерація, і
 * валідація підпису використовують той самий хост. Prepend глобально.
 */
class ForceProxyUrl
{
    public function handle(Request $request, Closure $next)
    {
        $appUrl = (string) config('app.url');
        if ($appUrl !== '') {
            URL::forceRootUrl($appUrl);

            if (str_starts_with($appUrl, 'https://')) {
                URL::forceScheme('https');

                // Проксі віддає застосунку X-Forwarded-Proto: http (TLS на краю),
                // тож $request сприймається як http → валідація підписаних URL
                // (Livewire upload) рахує підпис по http ≠ https генерації → 401.
                // Форсуємо https на самому запиті (prepend — до TrustProxies).
                $request->headers->set('X-Forwarded-Proto', 'https');
                $request->headers->set('X-Forwarded-Port', '443');
                $request->server->set('HTTPS', 'on');
                $request->server->set('SERVER_PORT', 443);
            }
        }

        return $next($request);
    }
}
