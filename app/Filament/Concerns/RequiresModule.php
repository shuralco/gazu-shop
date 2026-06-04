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

    // canAccess()/canViewAny() are intentionally NOT defined here — the access
    // gate lives in GatedResource/GatedPage, which compose moduleEnabled() so a
    // disabled module is also denied. (Avoids a trait-method collision.)

    public static function moduleEnabled(): bool
    {
        $key = static::$moduleKey ?? null;
        if (! $key) {
            return true; // safety: untagged resource always accessible
        }

        return ModuleManager::for($key)->enabled();
    }
}
