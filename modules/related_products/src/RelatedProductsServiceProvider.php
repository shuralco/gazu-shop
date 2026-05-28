<?php

namespace Modules\RelatedProducts;

use App\Console\Commands\AutoRelateAll;
use Illuminate\Support\ServiceProvider;

/**
 * Bootstraps the related_products module:
 *  - registers the products:auto-relate artisan command (lives in
 *    modules/related_products/src/Console/Commands/AutoRelateAll.php,
 *    namespace `App\Console\Commands` so the class fits the codebase
 *    convention, but it's not auto-discovered because it lives outside
 *    app/Console/Commands).
 *
 * Routes / migrations / views are wired by ModuleDiscovery from module.json.
 * Filament Resource is registered conditionally in
 * App\Filament\Resources\ProductResource::getRelations().
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
        // No-op — module.json already declares views/routes/migrations,
        // ModuleDiscovery handles those.
    }
}
