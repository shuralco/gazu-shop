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
    // shouldRegisterNavigation()/canAccess()/canViewAny() are intentionally NOT
    // defined here — they live in GatedResource/GatedPage, which compose
    // moduleEnabled() (so a disabled module is denied + hidden). This avoids a
    // trait-method collision when both traits are used together.

    public static function moduleEnabled(): bool
    {
        $key = static::$moduleKey ?? null;
        if (! $key) {
            return true; // safety: untagged resource always accessible
        }

        return ModuleManager::for($key)->enabled();
    }
}
