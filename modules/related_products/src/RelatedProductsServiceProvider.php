<?php

namespace Modules\RelatedProducts;

use App\Console\Commands\AutoRelateAll;
use App\Support\Hooks;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/**
 * Bootstraps the related_products module.
 *
 *   register(): команди (поза auto-discovery).
 *
 *   boot(): підписки на core hook-points. Без хука сторінка товару нічого
 *   не знає про цей модуль — module просто реєструє listener і він
 *   викликається з blade через @hookAction.
 */
class RelatedProductsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($this->app->runningInConsole() && class_exists(AutoRelateAll::class)) {
            $this->commands([
                AutoRelateAll::class,
            ]);
        }
    }

    public function boot(): void
    {
        // Variants picker рендериться на сторінці товару одразу під
        // блоком compat-check. Core blade просто має `@hookAction('product.page.variants', $p)` —
        // модуль вирішує що рендерити (або нічого).
        Hooks::on('product.page.variants', function ($product) {
            // Lazy DB-aware enabled-check at render time. The provider is
            // registered via config-only isLikelyEnabled() (no DB at register
            // phase), so this listener stays alive even after a UI/DB-disable.
            // bootModuleResources() drops the 'related_products::' view
            // namespace for disabled modules → rendering below would throw
            // "No hint path defined for [related_products]". Bail silently
            // instead so the listener fully respects the DB-resolved state.
            if (! \App\Support\ModuleManager::for('related_products')->enabled()) return null;
            if (! $product || ! is_object($product)) return null;
            return view('related_products::variant-picker', ['p' => $product])->render();
        }, priority: 10, source: 'related_products');
    }
}
