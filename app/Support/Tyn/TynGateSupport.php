<?php

declare(strict_types=1);

namespace App\Support\Tyn;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Throwable;

/** Спільні дрібниці заслону (без стану): винятки, сторінка-заслін. */
final class TynGateSupport
{
    /** Шаблони шляхів, яким заслін не показується (разом з типовими шаблонами бібліотеки). */
    public const SKIP = [
        'webhooks/*', 'up', 'csrf-token', 'api/np/*', 'api/up/*', 'sitemap*', '*sitemap*', 'feed/*', 'feeds/*',
        'admin', 'admin/*', 'livewire', 'livewire/*', 'filament/*', 'tyn/pass', 'safe-mode',
        'cart', 'cart/*', 'checkout', 'checkout/*', 'zamovlennya/*', 'build/*', 'storage/*', 'css/*', 'js/*', 'fonts/*', 'img/*', 'favicon.ico', 'robots.txt',
    ];

    public static function route(Request $request): string
    {
        return strtolower(ltrim($request->path(), '/')) ?: '/';
    }

    /** @return list<string> */
    public static function skip(array $cfg): array
    {
        $extra = array_map(fn ($p) => strtolower(ltrim((string) $p, '/')), (array) ($cfg['gate_skip'] ?? []));

        return array_values(array_merge(self::SKIP, array_filter($extra, fn ($p) => $p !== '')));
    }

    /** Сторінка-заслін (200, no-store, noindex) або null, якщо зібрати не вдалося. */
    public static function page(Request $request, array $cfg, string $note): ?Response
    {
        require_once __DIR__ . '/tyn-lib.php';

        $return = \tyn_gate_safe_return($request->getRequestUri());
        $auto = $note === '';
        if ($note === '' && self::looksLikeLoop($request, $return)) {
            // Те саме посилання відкрито зі сторінки заслону, а позначки немає: cookie не працюють.
            $note = 'cookies';
            $auto = false;
        }
        $html = \tyn_gate_page([
            'site_key' => (string) ($cfg['site_key'] ?? ''),
            'base_url' => (string) ($cfg['base_url'] ?? ''),
            'action' => route('tyn.gate.pass', [], false),
            'return' => $return,
            'lang' => app()->getLocale(),
            'note' => $note,
            'auto' => $auto,
        ]);
        if ($html === '') {
            return null;
        }

        $request->attributes->set('tyn_gate_page', true);

        return new Response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Cache-Control' => 'no-store, private, max-age=0',
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);
    }

    private static function looksLikeLoop(Request $request, string $return): bool
    {
        $ref = parse_url((string) $request->headers->get('referer', ''));
        $cur = parse_url($return);
        if (!is_array($ref) || !is_array($cur) || !isset($ref['path'], $ref['host'], $cur['path'])) {
            return false;
        }

        return $ref['path'] === $cur['path']
            && ($ref['query'] ?? '') === ($cur['query'] ?? '')
            && strtolower($ref['host']) === strtolower($request->getHost());
    }

    /** Назва заголовка, яким прогрів кешу самого сайту (cache:warm, gazu:ensure-warm) позначає свої запити. */
    public const WARM_HEADER = 'X-Gazu-Warm';

    /** Підписаний ключ прогріву: HMAC від APP_KEY, тож зовнішній відвідувач його не підробить. */
    public static function warmToken(): string
    {
        return hash_hmac('sha256', 'gazu-warm-v1', (string) config('app.key'));
    }

    /** Чи це власний прогрів сайту (заголовок X-Gazu-Warm з правильним підписом). */
    public static function isWarm(Request $request): bool
    {
        $v = $request->headers->get(self::WARM_HEADER);

        return is_string($v) && $v !== '' && hash_equals(self::warmToken(), $v);
    }

    /** Приватна, loopback чи зарезервована адреса (10/8, 172.16/12, 192.168/16, 127/8, ::1, fc00::/7 тощо). */
    public static function isPrivateIp(string $ip): bool
    {
        return $ip === '' || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    }

    /** Максимальний розмір добового файлу «було б показано», байт. */
    public const OBSERVE_LOG_MAX = 1048576;

    /**
     * Режим observe: рядок «час UTC код-причини» у storage/app/tyn/gate-<UTC-дата>.log.
     * Без адрес, шляхів, запиту й User-Agent; файл не росте понад OBSERVE_LOG_MAX. Файли старші
     * за 14 діб прибираються при першому записі дня. Перший випадок дня йде ще й у журнал (warning).
     * Будь-який збій мовчазний.
     */
    public static function countWouldShow(string $reason): void
    {
        try {
            $reason = preg_replace('/[^a-z0-9_:]/', '', strtolower($reason)) ?: 'unknown';
            $dir = storage_path('app/tyn');
            if (!is_dir($dir) && !@mkdir($dir, 0700, true) && !is_dir($dir)) {
                return;
            }
            $file = $dir . '/gate-' . gmdate('Ymd') . '.log';
            $fh = @fopen($file, 'a');
            if ($fh === false) {
                return;
            }
            $first = false;
            try {
                if (!flock($fh, LOCK_EX)) {
                    return;
                }
                $size = (int) (fstat($fh)['size'] ?? 0);
                if ($size >= self::OBSERVE_LOG_MAX) {
                    return;
                }
                $first = $size === 0;
                fwrite($fh, gmdate('Y-m-d\TH:i:s\Z') . ' ' . $reason . "\n");
                fflush($fh);
                flock($fh, LOCK_UN);
            } finally {
                fclose($fh);
            }
            if ($first) {
                foreach (glob($dir . '/gate-*.log') ?: [] as $old) {
                    if (filemtime($old) < time() - 14 * 86400) {
                        @unlink($old);
                    }
                }
                Log::warning('tyn gate: would show (observe), лічильник у storage/app/tyn/gate-' . gmdate('Ymd') . '.log');
            }
        } catch (Throwable) {
        }
    }
}
