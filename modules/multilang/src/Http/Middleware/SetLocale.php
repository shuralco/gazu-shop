<?php

namespace Modules\Multilang\Http\Middleware;

use App\Support\Locales;
use Closure;
use Illuminate\Http\Request;

/**
 * Застосовує обрану відвідувачем мову (session 'locale') до запиту.
 * No-op коли модуль вимкнено або мова не входить в активні — тоді
 * лишається дефолтна локаль застосунку.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        if (Locales::enabled()) {
            $loc = (string) $request->session()->get('locale', '');
            if ($loc !== '' && in_array($loc, Locales::active(), true)) {
                app()->setLocale($loc);
            }
        }

        return $next($request);
    }
}
