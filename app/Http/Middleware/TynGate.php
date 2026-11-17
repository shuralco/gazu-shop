<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Tyn\TynGateSupport;
use App\Support\Tyn\TynTrack;
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
            // Вхід в адмінку: окремий перемикач TYN_GATE_ADMIN=off|all, лише НЕзалогіненому GET-запиту (не Livewire/ajax).
            $admin = false;
            if (($cfg['gate_admin'] ?? 'off') === 'all' && self::isAdminEntry($request)) {
                $admin = true;
                $mode = 'all';
            }
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
                'routes' => $admin ? TynGateSupport::skipWithoutAdmin($cfg) : TynGateSupport::skip($cfg),
                'bots' => \tyn_gate_list(implode("\n", (array) ($cfg['gate_bots'] ?? []))),
                'logged_in' => $request->user() !== null || ($admin && self::anyGuardLoggedIn()),
                'bind' => (bool) ($cfg['gate_bind'] ?? false),
                'ip' => (string) $request->ip(),
                // Справжність названих ботів: PTR → пряма адреса підтверджує домен оператора (кеш 24 год, збій DNS = пропустити).
                'bot_dir' => storage_path('app/tyn'),
                'bot_ip' => (string) $request->ip(),
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
            TynTrack::add('gate', ['decision' => 'would_show', 'reason' => \tyn_gate_track_reason((string) $decision['reason'])]);

            return $next($request);
        }

        try {
            $page = TynGateSupport::page($request, $cfg, '');
            if ($page !== null) {
                TynTrack::add('gate', ['decision' => 'shown', 'reason' => \tyn_gate_track_reason((string) $decision['reason'])]);

                return $page;
            }
        } catch (Throwable) {
        }

        return $next($request);
    }

    /** Сторінки входу й усе до сесії адміністратора: GET, не ajax/Livewire, шлях admin або filament. */
    private static function isAdminEntry(Request $request): bool
    {
        return $request->isMethod('GET') && !$request->ajax() && !$request->headers->has('X-Livewire')
            && $request->is('admin', 'admin/*', 'filament/*') && !$request->is('admin/logout', 'admin/*/two-factor*', 'admin/*/challenge*');
    }

    private static function anyGuardLoggedIn(): bool
    {
        foreach (array_keys((array) config('auth.guards')) as $g) {
            if (auth()->guard($g)->check()) {
                return true;
            }
        }

        return false;
    }
}
