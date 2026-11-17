<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\Tyn\TynGateSupport;
use App\Support\Tyn\TynVerifier;
use App\Support\Tyn\VerifyTyn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * POST /tyn/pass: форма заслону. Токен перевіряється в м'якому режимі (будь-яка завершена спроба
 * пропускає); збій сервісу, unavailable, помилка налаштувань дають коротку позначку (5 хв), щоб не зациклити.
 * Немає токена або ok=false: заслін знову, з повідомленням. Повернення лише на відносну адресу цього сайту.
 */
class TynGatePassController extends Controller
{
    public function __invoke(Request $request): Response
    {
        require_once __DIR__ . '/../../Support/Tyn/tyn-lib.php';

        $return = \tyn_gate_safe_return(is_string($request->input('return')) ? $request->input('return') : '/');
        $cfg = config('tyn');
        $mode = (string) ($cfg['gate'] ?? 'off');
        if (!in_array($mode, ['suspicious', 'all'], true) || !($cfg['enabled'] ?? true)
            || empty($cfg['site_key']) || empty($cfg['secret'])) {
            return $this->back($return);
        }

        try {
            $token = $request->input('tyn_token');
            $verifier = new TynVerifier((new VerifyTyn())->transport(...), fn (string $line) => Log::warning($line));
            $r = $verifier->check((string) $cfg['secret'], is_string($token) ? $token : '', [
                'base_url' => $cfg['base_url'] ?? null,
                'mode' => 'soft',
                'timeout' => $cfg['timeout'] ?? 2,
            ]);
            if (!$r['allow']) {
                return TynGateSupport::page($request, $cfg, 'retry') ?? $this->back($return);
            }
            $hours = (int) ($cfg['gate_ttl_hours'] ?? 12);
            $ttl = $r['reason'] === 'ok' ? (($hours < 1 || $hours > 720) ? 12 : $hours) * 3600 : \TYN_GATE_SHORT_TTL;
        } catch (Throwable) {
            $ttl = \TYN_GATE_SHORT_TTL;
        }

        try {
            $net = !empty($cfg['gate_bind']) ? \tyn_gate_net((string) $request->ip()) : '';
            $value = \tyn_gate_mark_make((string) $cfg['secret'], $ttl, $net);
            $response = $this->back($return);
            $response->headers->setCookie(new Cookie('tyn_pass', $value, time() + $ttl, '/', null, $request->isSecure(), true, false, Cookie::SAMESITE_LAX));

            return $response;
        } catch (Throwable) {
            return $this->back($return);
        }
    }

    private function back(string $return): Response
    {
        return redirect()->to($return)->header('Cache-Control', 'no-store, private, max-age=0');
    }
}
