<?php

declare(strict_types=1);

namespace App\Support\Tyn;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Middleware: перевірка поля tyn_token. Тип форми: ->middleware('tyn:callback').
 * Без ключа/секрету чи при TYN_ENABLED=false пропускає запит.
 */
class VerifyTyn
{
    /**
     * @param string $form Тип форми (login, register, callback, subscribe, contact, review, other).
     * @param string $mode 'observe': запит ніколи не відхиляється, рішення лише йде подією.
     */
    public function handle(Request $request, Closure $next, string $form = 'other', string $mode = ''): Response
    {
        $cfg = config('tyn');
        if (!($cfg['enabled'] ?? true) || empty($cfg['site_key']) || empty($cfg['secret'])) {
            return $next($request);
        }

        $token = $request->input('tyn_token');
        // Пастка: людина поле website_url не бачить; заповнене означає бота.
        $honeypot = is_string($request->input('website_url')) && trim($request->input('website_url')) !== '';
        $this->scheduleEvent($request, $cfg, $form, $token, $honeypot);

        if ($mode === 'observe') {
            return $next($request);
        }

        if ($honeypot) {
            return $this->reject($request, $form, $cfg['messages']['rejected'] ?? 'Перевірка застаріла, спробуйте ще раз');
        }

        $verifier = new TynVerifier($this->transport(...), fn (string $line) => Log::warning($line));
        $r = $verifier->check($cfg['secret'], $token, [
            'base_url' => $cfg['base_url'] ?? null,
            'mode' => $cfg['mode'] ?? 'soft',
            'messages' => $cfg['messages'] ?? [],
            'timeout' => $cfg['timeout'] ?? 2,
        ]);

        if ($r['allow']) {
            return $next($request);
        }

        return $this->reject($request, $form, $r['message']);
    }

    private function reject(Request $request, string $form, string $message): Response
    {
        $errors = ['tyn_token' => [$message]];
        if ($request->expectsJson()) {
            // ok:false: так фронт форм (Alpine fetch) показує d.message як звичайну помилку.
            return response()->json(['ok' => false, 'message' => $message, 'errors' => $errors], 422);
        }
        return back()
            ->withErrors($errors)
            ->with('tyn_form', $form)
            ->withInput($request->except(['tyn_token', 'website_url', 'password', 'password_confirmation']));
    }

    /** @return array{status:int,body:string} */
    public function transport(string $url, array $fields, float $timeout): array
    {
        try {
            $res = Http::asForm()->acceptJson()
                ->withoutRedirecting()
                ->connectTimeout(1)
                ->timeout($timeout)
                ->withUserAgent('tyn-laravel/1.0.0')
                ->post($url, $fields);
            return ['status' => $res->status(), 'body' => substr($res->body(), 0, TynVerifier::RESPONSE_MAX + 1)];
        } catch (Throwable) {
            return ['status' => 0, 'body' => ''];
        }
    }

    /**
     * Подія сервісу, терміновано після відповіді (terminate): form_submit, якщо був токен;
     * form_no_token з chain (хеш id сесії, не сам id), якщо токена не було. Без сесії подію не шлемо.
     * Вміст форми не передається (tyn_event бібліотеки пропускає лише token/chain/form/fill_s/honeypot).
     */
    private function scheduleEvent(Request $request, array $cfg, string $form, mixed $token, bool $honeypot = false): void
    {
        if (!($cfg['events'] ?? true)) {
            return;
        }
        $base = TynVerifier::normalizeBase($cfg['base_url'] ?? 'https://c.tyn.com.ua');
        $secret = (string) ($cfg['secret'] ?? '');
        if ($base === '' || $secret === '') {
            return;
        }
        $fields = ['form' => preg_match('/^[a-z0-9_]{1,32}$/', $form) ? $form : 'other'];
        if (TynVerifier::tokenOk($token) && $token !== 'unavailable') {
            $event = 'form_submit';
            $fields['token'] = $token;
        } else {
            $sessionId = $request->hasSession() ? (string) $request->session()->getId() : '';
            if ($sessionId === '') {
                return;
            }
            $event = 'form_no_token';
            $fields['chain'] = substr(sha1($sessionId . '|tyn|' . $secret), 0, 32);
        }
        if ($honeypot) {
            $event = 'honeypot';
            $fields['honeypot'] = 1;
        }
        $request->attributes->set('tyn_event', [$secret, $base, $event, $fields]);
    }

    /** Після відповіді: надсилає подію бібліотечним tyn_event (тайм-аут 1 с), якщо вона була заплановена. */
    public function terminate(Request $request, Response $response): void
    {
        try {
            $event = $request->attributes->get('tyn_event');
            if (!is_array($event)) {
                return;
            }
            require_once __DIR__ . '/tyn-lib.php';
            [$secret, $base, $name, $fields] = $event;
            \tyn_event($secret, $base, $name, $fields);
        } catch (Throwable) {
        }
    }
}
