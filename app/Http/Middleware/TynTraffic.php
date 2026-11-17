<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Order;
use App\Support\Tyn\TynGateSupport;
use App\Support\Tyn\TynTrack;
use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use Throwable;

/**
 * Облік відвідувань вітрини для сервісу «Тин» (POST {base}/v1/traffic).
 * Запис і надсилання робить бібліотека php/tyn.php (app/Support/Tyn/tyn-lib.php, без змін):
 * буфер-файл у storage/app/tyn, пачка не частіше ніж раз на 2 с, тайм-аут 1 с, усі збої мовчазні.
 * Усе після відповіді (terminate), станів між запитами в пам'яті немає (Octane).
 * Вимикач TYN_TRAFFIC (типово вимкнено); без ключа й секрету не робить нічого.
 * Рішення власника 2026-11-17: у запис іде повна публічна адреса (ip), сирий ua, постійний ідентифікатор браузера
 * (cookie tyn_cid, 128 біт, 2 роки, SameSite=Lax, Secure; ключ vc) і uid покупця (внутрішній id, не пошта й не телефон);
 * тип сторінки (pt) і події cart_add та order (сума в мінімальних одиницях). Рядок запиту, вміст полів і cookie не йдуть.
 * Тут же cookie tyn_cid ставиться першому візиту й очищується пам'ять серверних подій (TynTrack).
 */
class TynTraffic
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->attributes->set('tyn_started', microtime(true));
        TynTrack::reset();

        $new = false;
        $cid = '';
        try {
            if ($this->active() && !TynGateSupport::isWarm($request)) {
                $cid = TynTrack::cid($request);
                if ($cid === '') {
                    $cid = bin2hex(random_bytes(16));
                    $new = true;
                }
                $request->attributes->set('tyn_cid', $cid);
            }
        } catch (Throwable) {
            $new = false;
        }

        $response = $next($request);

        try {
            if ($new && $cid !== '' && $request->isMethod('GET') && !$request->ajax()
                && str_starts_with((string) $response->headers->get('Content-Type'), 'text/html')
                && !TynGateSupport::isPrivateIp((string) $request->ip())) {
                $response->headers->setCookie(new Cookie(TynTrack::COOKIE, $cid, time() + 63072000, '/', null, $request->isSecure(), false, false, Cookie::SAMESITE_LAX));
            }
        } catch (Throwable) {
        }

        return $response;
    }

    private function active(): bool
    {
        $cfg = config('tyn');

        return ($cfg['enabled'] ?? true) && !empty($cfg['site_key']) && !empty($cfg['secret'])
            && (($cfg['traffic'] ?? false) || ($cfg['track'] ?? false) || ($cfg['collector'] ?? false));
    }

    public function terminate(Request $request, Response $response): void
    {
        $this->traffic($request, $response);
        // Серверні події, накопичені за запит (order, order_status, gate, identify): вебхуки й адмінка не від відвідувача.
        TynTrack::flush($request, $request->is('webhooks/*', 'admin', 'admin/*', 'livewire/*', 'filament/*', 'api/*') && !$request->is('api/callback'));
    }

    private function traffic(Request $request, Response $response): void
    {
        try {
            $cfg = config('tyn');
            if (!($cfg['traffic'] ?? false) || !($cfg['enabled'] ?? true) || empty($cfg['site_key']) || empty($cfg['secret'])) {
                return;
            }
            $cartAdd = $request->isMethod('POST') && $request->routeIs('cart.add') && $response->getStatusCode() < 400;
            if (!$cartAdd && !$this->isStorefrontPage($request, $response)) {
                return;
            }
            // Прогрів кешу самого сайту й запити з приватних/loopback адрес (після довірених проксі) людьми не є.
            if (TynGateSupport::isWarm($request) || TynGateSupport::isPrivateIp((string) $request->ip())) {
                return;
            }

            require_once __DIR__ . '/../../Support/Tyn/tyn-lib.php';

            $server = $request->server->all();
            // Справжня адреса клієнта: через довірені проксі проєкту (trustProxies у bootstrap/app.php).
            $server['REMOTE_ADDR'] = (string) $request->ip();
            unset($server['HTTP_X_FORWARDED_FOR'], $server['HTTP_CF_CONNECTING_IP']);

            $opts = ['page_type' => $cartAdd ? 'cart' : $this->pageType($request)];
            $cid = (string) $request->attributes->get('tyn_cid', '');
            if ($cid !== '') {
                $opts['cid'] = $cid;
            }
            $uid = $request->user()?->getAuthIdentifier();
            if ($uid !== null) {
                $opts['uid'] = (string) $uid;
            }
            if ($cartAdd) {
                $opts['event'] = 'cart_add';
            } elseif ($request->routeIs('checkout.success')) {
                $this->orderEvent($request, $opts);
            }

            $dir = storage_path('app/tyn');
            \tyn_traffic_record($dir, $server, $response->getStatusCode(), $request->attributes->get('tyn_started'), $opts);
            \tyn_traffic_flush($dir, (string) ($cfg['base_url'] ?? 'https://c.tyn.com.ua'), (string) $cfg['site_key'], (string) $cfg['secret']);
        } catch (Throwable) {
        }
    }

    /** Подія order: сума в мінімальних одиницях, лише раз на замовлення (перезавантаження сторінки не дублює). */
    private function orderEvent(Request $request, array &$opts): void
    {
        $id = (int) $request->route('order');
        if ($id < 1 || !Cache::add('tyn:order-counted:' . $id, 1, 86400)) {
            return;
        }
        $order = Order::query()->select(['id', 'total'])->find($id);
        if ($order === null) {
            return;
        }
        $opts['event'] = 'order';
        $opts['amount'] = TynTrack::minor($order->total, 'UAH');
        $opts['currency'] = 'UAH';
    }

    /** Тип сторінки за іменем маршруту; товар і категорія позначаються в контролері атрибутом запиту. */
    private function pageType(Request $request): string
    {
        $t = $request->attributes->get('tyn_page_type');
        if (is_string($t) && $t !== '') {
            return $t;
        }
        $name = (string) $request->route()?->getName();
        // Закешована відповідь контролер не запускає: товар і категорія за формою slug (як у resolveSlug).
        if ($request->route()?->getActionMethod() === 'resolveSlug') {
            return preg_match('/-\\d+$/', (string) $request->route('slug')) ? 'product' : 'category';
        }

        return match (true) {
            $name === 'home' => 'home',
            $name === 'search' => 'search',
            str_starts_with($name, 'catalog') || str_starts_with($name, 'brand') => 'category',
            $name === 'cart' || str_starts_with($name, 'cart.') => 'cart',
            $name === 'checkout' => 'checkout',
            $name === 'checkout.success' => 'success',
            str_starts_with($name, 'account') || $name === 'order.payment' || $name === 'wishlist' => 'account',
            str_starts_with($name, 'blog') || str_starts_with($name, 'page') || str_starts_with($name, 'lp') => 'info',
            default => 'other',
        };
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
