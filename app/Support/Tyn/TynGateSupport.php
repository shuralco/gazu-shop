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
        'build/*', 'storage/*', 'css/*', 'js/*', 'fonts/*', 'img/*', 'favicon.ico', 'robots.txt',
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

        // Livewire сам дописує свої скрипти в будь-яку HTML-відповідь, якщо в цьому процесі (Octane) вже
        // рендерився компонент. Сторінка заслону проста: скидаємо прапорець (публічний static Livewire).
        if (class_exists(\Livewire\Features\SupportAutoInjectedAssets\SupportAutoInjectedAssets::class)) {
            \Livewire\Features\SupportAutoInjectedAssets\SupportAutoInjectedAssets::$hasRenderedAComponentThisRequest = false;
            \Livewire\Features\SupportAutoInjectedAssets\SupportAutoInjectedAssets::$forceAssetInjection = false;
        }

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

    /**
     * Добовий лічильник «було б показано» (режим observe): storage/app/tyn/gate-observe-<UTC-дата>.json
     * {"total": N, "reasons": {"suspicious:ua_script": n, ...}}. Без адрес, шляхів і user-agent.
     * У журнал (рівень warning, видно на проді) пишеться рядок на 1-му й кожному 100-му випадку за добу.
     * Файли старші за 14 діб прибираються при першому випадку дня. Будь-який збій мовчазний.
     */
    public static function countWouldShow(string $reason): void
    {
        try {
            $reason = preg_replace('/[^a-z0-9_:]/', '', strtolower($reason)) ?: 'unknown';
            $dir = storage_path('app/tyn');
            if (!is_dir($dir) && !@mkdir($dir, 0700, true) && !is_dir($dir)) {
                return;
            }
            $file = $dir . '/gate-observe-' . gmdate('Ymd') . '.json';
            $fh = @fopen($file, 'c+');
            if ($fh === false) {
                return;
            }
            $total = 0;
            try {
                if (!flock($fh, LOCK_EX)) {
                    return;
                }
                $data = json_decode((string) stream_get_contents($fh), true);
                $data = is_array($data) ? $data : [];
                $data['total'] = (int) ($data['total'] ?? 0) + 1;
                $data['reasons'] = is_array($data['reasons'] ?? null) ? $data['reasons'] : [];
                $data['reasons'][$reason] = (int) ($data['reasons'][$reason] ?? 0) + 1;
                $total = $data['total'];
                ftruncate($fh, 0);
                rewind($fh);
                fwrite($fh, json_encode($data));
                fflush($fh);
                flock($fh, LOCK_UN);
            } finally {
                fclose($fh);
            }
            if ($total === 1) {
                foreach (glob($dir . '/gate-observe-*.json') ?: [] as $old) {
                    if (filemtime($old) < time() - 14 * 86400) {
                        @unlink($old);
                    }
                }
            }
            if ($total === 1 || $total % 100 === 0) {
                Log::warning('tyn gate: would show today=' . $total);
            }
        } catch (Throwable) {
        }
    }
}
