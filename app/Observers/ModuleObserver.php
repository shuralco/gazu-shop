<?php

namespace App\Observers;

use App\Models\Module;
use App\Support\ModuleManager;

/**
 * Invalidate ModuleManager caches when module state changes through Filament
 * UI or artisan commands. Without this, toggling a module in the admin
 * would not take effect until the cache TTL expires (1h).
 */
class ModuleObserver
{
    public function saved(Module $module): void
    {
        ModuleManager::clearCache();
    }

    public function deleted(Module $module): void
    {
        ModuleManager::clearCache();
    }
}
