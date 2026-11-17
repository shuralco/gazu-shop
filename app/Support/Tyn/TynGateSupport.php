<?php

declare(strict_types=1);

namespace App\Support\Tyn;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
}
