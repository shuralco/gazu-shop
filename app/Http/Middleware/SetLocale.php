<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale') ?? session('locale', config('app.locale'));

        $available = config('app.available_locales', ['uk', 'en']);
        if (!in_array($locale, $available)) {
            $locale = config('app.locale', 'uk');
        }

        app()->setLocale($locale);
        session()->put('locale', $locale);

        // Forget the locale parameter so it doesn't get passed to Livewire components
        $request->route()->forgetParameter('locale');

        return $next($request);
    }
}
