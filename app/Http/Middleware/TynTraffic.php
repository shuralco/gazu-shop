<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Облік відвідувань вітрини для сервісу «Тин» (POST {base}/v1/traffic).
 * Запис і надсилання робить бібліотека php/tyn.php (app/Support/Tyn/tyn-lib.php, без змін):
 * буфер-файл у storage/app/tyn, пачка не частіше ніж раз на 2 с, тайм-аут 1 с, усі збої мовчазні.
 * Усе після відповіді (terminate), станів між запитами в пам'яті немає (Octane).
 * Вимикач TYN_TRAFFIC (типово вимкнено); без ключа й секрету не робить нічого.
 */
class TynTraffic
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->attributes->set('tyn_started', microtime(true));

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        try {
            $cfg = config('tyn');
            if (!($cfg['traffic'] ?? false) || !($cfg['enabled'] ?? true) || empty($cfg['site_key']) || empty($cfg['secret'])) {
                return;
            }
            if (!$this->isStorefrontPage($request, $response)) {
                return;
            }

            require_once __DIR__ . '/../../Support/Tyn/tyn-lib.php';

            $server = $request->server->all();
            // Справжня адреса клієнта: через довірені проксі проєкту (trustProxies у bootstrap/app.php).
            $server['REMOTE_ADDR'] = (string) $request->ip();
            unset($server['HTTP_X_FORWARDED_FOR'], $server['HTTP_CF_CONNECTING_IP']);

            $dir = storage_path('app/tyn');
            \tyn_traffic_record($dir, $server, $response->getStatusCode(), $request->attributes->get('tyn_started'));
            \tyn_traffic_flush($dir, (string) ($cfg['base_url'] ?? 'https://c.tyn.com.ua'), (string) $cfg['site_key'], (string) $cfg['secret']);
        } catch (Throwable) {
        }
    }

    private function isStorefrontPage(Request $request, Response $response): bool
    {
        if (!$request->isMethod('GET') || $request->ajax() || $request->headers->has('X-Livewire')) {
            return false;
        }
        if ($request->is('admin', 'admin/*', 'livewire', 'livewire/*', 'filament/*', 'up', 'api/*', 'build/*', 'storage/*')) {
            return false;
        }

        return str_starts_with((string) $response->headers->get('Content-Type'), 'text/html');
    }
}
