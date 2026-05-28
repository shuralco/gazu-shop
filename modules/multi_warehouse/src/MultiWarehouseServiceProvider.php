<?php

namespace Modules\MultiWarehouse;

use App\Console\Commands\CheckLowStock;
use Illuminate\Support\ServiceProvider;

/**
 * Bootstraps the multi_warehouse module — registers commands that live
 * outside app/Console/Commands and aren't auto-discovered by Laravel.
 *
 * Filament resources, routes, views, migrations — все wired by
 * ModuleDiscovery з module.json.
 */
class MultiWarehouseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($this->app->runningInConsole() && class_exists(CheckLowStock::class)) {
            $this->commands([
                CheckLowStock::class,
            ]);
        }
    }

    public function boot(): void
    {
        // ModuleDiscovery handles views/routes/migrations declared in module.json
    }
}
