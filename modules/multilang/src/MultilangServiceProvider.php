<?php

namespace Modules\Multilang;

use App\Support\Locales;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Bootstraps the multilang (premium) module.
 *
 * Провайдер реєструється лише коли модуль config/ENV-enabled (ModuleDiscovery).
 * Усередині boot() ще раз звіряємось із DB-станом через Locales::enabled(),
 * щоб UI-вимкнення (запис у таблицю modules) теж миттєво вимикало роут і
 * middleware — без жодних залишкових ефектів.
 */
class MultilangServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! Locales::enabled()) {
            return; // UI/DB-вимкнено — нічого не реєструємо.
        }

        // Застосувати обрану локаль до кожного web-запиту.
        $this->app->make(Router::class)
            ->pushMiddlewareToGroup('web', \Modules\Multilang\Http\Middleware\SetLocale::class);

        // Перемикач мови: /locale/uk, /locale/en → session + redirect back.
        Route::middleware('web')->group(function () {
            Route::get('/locale/{locale}', [\Modules\Multilang\Http\Controllers\LocaleController::class, 'switch'])
                ->where('locale', '[a-z]{2}')
                ->name('locale.switch');
        });
    }
}
