<?php

namespace App\Filament\Concerns;

use App\Support\ModuleManager;

/**
 * Filament Resource/Page trait — hides nav + blocks access when the
 * declared $moduleKey is disabled.
 *
 * Usage:
 *   class LoyaltyTransactionResource extends Resource
 *   {
 *       use \App\Filament\Concerns\RequiresModule;
 *       protected static string $moduleKey = 'loyalty';
 *       ...
 *   }
 */
trait RequiresModule
{
    public static function shouldRegisterNavigation(): bool
    {
        return static::moduleEnabled();
    }

    public static function canAccess(): bool
    {
        return static::moduleEnabled();
    }

    public static function canViewAny(): bool
    {
        return static::moduleEnabled();
    }

    protected static function moduleEnabled(): bool
    {
        $key = static::$moduleKey ?? null;
        if (! $key) {
            return true; // safety: untagged resource always accessible
        }

        return ModuleManager::for($key)->enabled();
    }
}
