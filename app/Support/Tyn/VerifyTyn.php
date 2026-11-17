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
    public function handle(Request $request, Closure $next, string $form = 'other'): Response
    {
        $cfg = config('tyn');
        if (!($cfg['enabled'] ?? true) || empty($cfg['site_key']) || empty($cfg['secret'])) {
            return $next($request);
        }

        $verifier = new TynVerifier($this->transport(...), fn (string $line) => Log::warning($line));
        $r = $verifier->check($cfg['secret'], $request->input('tyn_token'), [
            'base_url' => $cfg['base_url'] ?? null,
            'mode' => $cfg['mode'] ?? 'soft',
            'messages' => $cfg['messages'] ?? [],
            'timeout' => $cfg['timeout'] ?? 2,
        ]);

        if ($r['allow']) {
            return $next($request);
        }

        $errors = ['tyn_token' => [$r['message']]];
        if ($request->expectsJson()) {
            // ok:false: так фронт форм (Alpine fetch) показує d.message як звичайну помилку.
            return response()->json(['ok' => false, 'message' => $r['message'], 'errors' => $errors], 422);
        }
        return back()
            ->withErrors($errors)
            ->with('tyn_form', $form)
            ->withInput($request->except(['tyn_token', 'password', 'password_confirmation']));
    }

    /** @return array{status:int,body:string} */
    private function transport(string $url, array $fields, float $timeout): array
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
}
