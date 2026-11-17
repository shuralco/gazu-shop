<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Tyn\TynGateSupport;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Заслін Тин на весь сайт (TYN_GATE: off | observe | suspicious | all).
 * Рішення приймає tyn_gate_decide() бібліотеки (без мережі й бази); будь-який збій означає «звичайна сторінка».
 * Стоїть у групі web ДО CacheResponse, тож закешовані сторінки в обхід не йдуть.
 * Станів між запитами немає (Octane): усе береться з запиту й конфігурації.
 *
 * observe нікого не зупиняє: рахується рішення режиму suspicious, а «було б показано» лише
 * іде в добовий файл storage/app/tyn/gate-<дата>.log (див. TynGateSupport::countWouldShow).
 */
class TynGate
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $cfg = config('tyn');
            $mode = (string) ($cfg['gate'] ?? 'off');
            if (!in_array($mode, ['observe', 'suspicious', 'all'], true)
                || !($cfg['enabled'] ?? true) || empty($cfg['site_key']) || empty($cfg['secret'])) {
                return $next($request);
            }

            // Власний прогрів кешу сайту (підписаний заголовок) не оцінюється й не зупиняється ніколи.
            // За адресою тут не пропускаємо: X-Forwarded-For підробний, і приватна адреса стала б обхідною стежкою.
            if (TynGateSupport::isWarm($request)) {
                return $next($request);
            }

            require_once __DIR__ . '/../../Support/Tyn/tyn-lib.php';

            $decision = \tyn_gate_decide($request->server->all(), $request->cookies->all(), [
                // Octane працює під PHP_SAPI=cli, тож «не CLI» задаємо явно: це завжди веб-запит.
                'cli' => false,
                'mode' => $mode === 'observe' ? 'suspicious' : $mode,
                'secret' => (string) $cfg['secret'],
                'route' => TynGateSupport::route($request),
                'routes' => TynGateSupport::skip($cfg),
                'bots' => \tyn_gate_list(implode("\n", (array) ($cfg['gate_bots'] ?? []))),
                'logged_in' => $request->user() !== null,
                'bind' => (bool) ($cfg['gate_bind'] ?? false),
                'ip' => (string) $request->ip(),
            ]);
        } catch (Throwable) {
            return $next($request);
        }

        if (empty($decision['show'])) {
            return $next($request);
        }

        if ($mode === 'observe') {
            // Нікого не зупиняємо й нічого не показуємо клієнту (ні заголовків, ні розмітки):
            // лише рядок у добовому файлі: час UTC і код причини.
            TynGateSupport::countWouldShow((string) $decision['reason']);

            return $next($request);
        }

        try {
            $page = TynGateSupport::page($request, $cfg, '');
            if ($page !== null) {
                return $page;
            }
        } catch (Throwable) {
        }

        return $next($request);
    }
}
