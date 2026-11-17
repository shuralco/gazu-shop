<?php

declare(strict_types=1);

namespace App\Support\Tyn;

use Illuminate\Http\Request;
use Throwable;

/**
 * Серверні події Тин (POST {base}/v1/track): order, order_status, gate, identify (лише uid), catalog.
 * Станів між запитами немає (Octane): події живуть у пам'яті бібліотеки лише в межах запиту,
 * на початку запиту пам'ять очищається, а після відповіді (terminate) події йдуть у файловий буфер
 * storage/app/tyn, пачка надсилається з тайм-аутом 1 с не частіше ніж раз на 15 с. Усі збої мовчазні.
 * Контакти (email, телефон, ім'я) НЕ передаються: опція contacts не вмикається ніколи.
 * Вимикач TYN_TRACK (типово вимкнено).
 */
final class TynTrack
{
    public const COOKIE = 'tyn_cid';

    public static function enabled(): bool
    {
        $c = config('tyn');

        return (bool) ($c['track'] ?? false) && ($c['enabled'] ?? true) && !empty($c['site_key']) && !empty($c['secret']);
    }

    private static function lib(): void
    {
        require_once __DIR__ . '/tyn-lib.php';
    }

    /** Очищає події, що могли лишитися від попереднього запиту в цьому процесі. */
    public static function reset(): void
    {
        try {
            if (self::enabled()) {
                self::lib();
                \tyn_track_store('take');
            }
        } catch (Throwable) {
        }
    }

    /** Додає подію в пам'ять запиту (білий список полів робить бібліотека). */
    public static function add(string $type, array $fields = []): void
    {
        try {
            if (self::enabled()) {
                self::lib();
                \tyn_track_add($type, $fields);
            }
        } catch (Throwable) {
        }
    }

    /** Ідентифікатор браузера з сирого заголовка Cookie (32 hex) або ''. */
    public static function cid(Request $request): string
    {
        self::lib();

        return \tyn_cid_from_server($request->server->all());
    }

    /** Ref замовлення: перші 16 hex HMAC від номера замовлення (номер у відкритому вигляді не йде). */
    public static function orderRef(int|string $orderId): string
    {
        self::lib();

        return \tyn_order_ref((string) config('tyn.secret'), (string) $orderId);
    }

    /** Мінімальні одиниці валюти (1234,50 UAH дає 123450). */
    public static function minor(mixed $amount, string $cur = 'UAH'): int
    {
        self::lib();

        return (int) \tyn_track_minor($amount, $cur);
    }

    /**
     * Передає накопичені події після відповіді. $noNet: дія не від відвідувача (вебхук, адмінка): мережа, ua й
     * ідентифікатор браузера в пачку не йдуть.
     */
    public static function flush(Request $request, bool $noNet = false): void
    {
        try {
            if (!self::enabled()) {
                return;
            }
            self::lib();
            $cfg = config('tyn');
            $server = $request->server->all();
            $server['REMOTE_ADDR'] = (string) $request->ip();
            unset($server['HTTP_X_FORWARDED_FOR'], $server['HTTP_CF_CONNECTING_IP']);
            $cid = (string) $request->attributes->get('tyn_cid', '');
            if ($cid !== '' && \tyn_cid_from_server($server) === '') {
                $server['HTTP_COOKIE'] = trim((string) ($server['HTTP_COOKIE'] ?? '') . '; ' . self::COOKIE . '=' . $cid, '; ');
            }
            if (TynGateSupport::isPrivateIp((string) $request->ip())) {
                $noNet = true;
            }
            $uid = $request->user()?->getAuthIdentifier();
            $opts = [
                'key' => (string) $cfg['site_key'],
                'secret' => (string) $cfg['secret'],
                'base_url' => (string) ($cfg['base_url'] ?? 'https://c.tyn.com.ua'),
                'server' => $server,
                'no_net' => $noNet,
                'set_cid' => false,
                'uid' => $uid !== null ? (string) $uid : null,
                'buffer_dir' => storage_path('app/tyn'),
            ];
            \tyn_track_flush($opts);
            // Під Octane register_shutdown_function не спрацьовує після кожного запиту: розвантажуємо буфер явно.
            \tyn_track_pump($opts);
        } catch (Throwable) {
        }
    }
}
