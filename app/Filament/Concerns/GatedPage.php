<?php

namespace App\Filament\Concerns;

use App\Support\Access\AccessControl;
use App\Support\Access\NavPreferences;

/**
 * Gates a Filament Page through the access-preset RBAC. Section key = the page
 * FQCN. is_admin users bypass. Replaces the old `canAccess(){ is_admin }` body.
 */
trait GatedPage
{
    public static function canAccess(): bool
    {
        $moduleOpen = method_exists(static::class, 'moduleEnabled') ? static::moduleEnabled() : true;

        return $moduleOpen && AccessControl::can(static::class, 'view');
    }

    public static function shouldRegisterNavigation(): bool
    {
        $moduleOpen = method_exists(static::class, 'moduleEnabled') ? static::moduleEnabled() : true;

        return $moduleOpen
            && AccessControl::can(static::class, 'view')
            && ! NavPreferences::isHidden(static::class);
    }
}
