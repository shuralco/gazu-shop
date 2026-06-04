<?php

namespace App\Filament\Concerns;

use App\Support\Access\AccessControl;
use App\Support\Access\NavPreferences;
use Illuminate\Database\Eloquent\Model;

/**
 * Gates a Filament Resource through the access-preset RBAC. Section key =
 * the resource FQCN. is_admin users bypass (AccessControl handles it).
 * Filament uses these to BOTH hide the nav item and block route/actions.
 */
trait GatedResource
{
    public static function canAccess(): bool
    {
        return static::moduleGateOpen() && AccessControl::can(static::class, 'view');
    }

    public static function canViewAny(): bool
    {
        return static::moduleGateOpen() && AccessControl::can(static::class, 'view');
    }

    /** Nav visibility: access + module gate + not personally hidden by the user. */
    public static function shouldRegisterNavigation(): bool
    {
        return static::moduleGateOpen()
            && AccessControl::can(static::class, 'view')
            && ! NavPreferences::isHidden(static::class);
    }

    /** Compose the module gate (RequiresModule) when present, else open. */
    protected static function moduleGateOpen(): bool
    {
        return method_exists(static::class, 'moduleEnabled') ? static::moduleEnabled() : true;
    }

    public static function canView(Model $record): bool
    {
        return AccessControl::can(static::class, 'view');
    }

    public static function canCreate(): bool
    {
        return AccessControl::can(static::class, 'create');
    }

    public static function canEdit(Model $record): bool
    {
        return AccessControl::can(static::class, 'update');
    }

    public static function canDelete(Model $record): bool
    {
        return AccessControl::can(static::class, 'delete');
    }

    public static function canDeleteAny(): bool
    {
        return AccessControl::can(static::class, 'delete');
    }
}
