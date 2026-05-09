<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ConfigureSessionFromSettings
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            if (function_exists('shopSetting')) {
                $sessionLifetime = shopSetting('session_lifetime', config('session.lifetime', 120));

                if ($sessionLifetime) {
                    config(['session.lifetime' => (int) $sessionLifetime]);
                }

                $sessionEncrypt = shopSetting('session_encrypt', config('session.encrypt', false));
                config(['session.encrypt' => (bool) $sessionEncrypt]);

                $sessionExpireOnClose = shopSetting('session_expire_on_close', config('session.expire_on_close', false));
                config(['session.expire_on_close' => (bool) $sessionExpireOnClose]);
            }
        } catch (\Exception $e) {
            // Fallback to default config if shopSetting fails
        }

        return $next($request);
    }
}
