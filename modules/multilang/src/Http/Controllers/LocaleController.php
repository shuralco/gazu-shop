<?php

namespace Modules\Multilang\Http\Controllers;

use App\Support\Locales;
use Illuminate\Http\Request;

class LocaleController
{
    /** Зберегти обрану мову в сесії та повернутись назад. */
    public function switch(Request $request, string $locale)
    {
        if (Locales::enabled() && in_array($locale, Locales::active(), true)) {
            $request->session()->put('locale', $locale);
            app()->setLocale($locale);
        }

        return redirect(
            $request->headers->get('referer') ?: '/'
        );
    }
}
