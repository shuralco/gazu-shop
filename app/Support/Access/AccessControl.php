<?php

namespace App\Support\Access;

use App\Models\AccessPreset;
use App\Models\User;
use Filament\Facades\Filament;

/**
 * Lightweight RBAC gate. A user's AccessPreset bundles per-section permissions
 * (view/create/update/delete). Section key = the Resource/Page FQCN.
 *
 * Rules:
 *  - users.is_admin === true  → super-admin, full access (bypasses presets).
 *  - otherwise → permission comes from the user's access preset; no preset OR
 *    no row for the section → DENY (mandatory-preset model).
 *
 * Per-request memoization keyed by user id (Octane-safe: flushed each request
 * by App\Listeners\Octane\FlushPerRequestSettingsState).
 */
class AccessControl
{
    /** @var array<int,array<string,array<string,bool>>> userId → section → abilities */
    private static array $maps = [];

    public static function can(string $section, string $ability = 'view', ?User $user = null): bool
    {
        $user ??= auth()->user();
        if (! $user) {
            return false;
        }
        if ($user->is_admin === true) {
            return true; // super-admin bypass
        }

        $key = class_basename($section);
        $perm = self::mapFor($user)[$key] ?? null;
        if ($perm === null) {
            return false; // mandatory preset: unknown section = denied
        }

        return (bool) ($perm[$ability] ?? false);
    }

    /** @return array<string,array<string,bool>> */
    private static function mapFor(User $user): array
    {
        if (array_key_exists($user->id, self::$maps)) {
            return self::$maps[$user->id];
        }

        $map = [];
        try {
            $presetId = $user->access_preset_id;
            if ($presetId) {
                $preset = AccessPreset::find($presetId);
                if ($preset) {
                    $map = $preset->permissionMap();
                }
            }
        } catch (\Throwable) {
            // Table not migrated yet / DB issue → empty map (non-admins denied).
        }

        return self::$maps[$user->id] = $map;
    }

    /** Octane / tests: drop the per-request cache. */
    public static function flush(): void
    {
        self::$maps = [];
    }

    /**
     * Registry of all gateable admin sections (Resources + Pages), grouped by
     * nav group — used by the AccessPreset editor UI. Resources expose all four
     * abilities; Pages expose only "view".
     *
     * @return array<int,array{section:string,label:string,group:string,kind:string,abilities:array<int,string>}>
     */
    public static function sections(): array
    {
        $out = [];

        $deny = ['ModuleSettings', 'ModuleMarketplace', 'ModuleDetail', 'IntegrationConfigPage', 'AccessPresetResource'];

        foreach (Filament::getResources() as $resource) {
            $key = class_basename($resource);
            if (in_array($key, $deny, true)) {
                continue; // is_admin-only sections — not preset-gateable
            }
            $out[] = [
                'section' => $key,
                'label' => self::safeLabel($resource, 'resource'),
                'group' => self::safeGroup($resource),
                'kind' => 'resource',
                'abilities' => ['view', 'create', 'update', 'delete'],
            ];
        }

        foreach (Filament::getPages() as $page) {
            $key = class_basename($page);
            if ($page === \App\Filament\Pages\Dashboard::class || in_array($key, $deny, true)) {
                continue;
            }
            $out[] = [
                'section' => $key,
                'label' => self::safeLabel($page, 'page'),
                'group' => self::safeGroup($page),
                'kind' => 'page',
                'abilities' => ['view'],
            ];
        }

        usort($out, fn ($a, $b) => [$a['group'], $a['label']] <=> [$b['group'], $b['label']]);

        return $out;
    }

    private static function safeLabel(string $class, string $kind): string
    {
        try {
            if ($kind === 'resource' && method_exists($class, 'getNavigationLabel')) {
                return (string) $class::getNavigationLabel();
            }
            if (method_exists($class, 'getNavigationLabel')) {
                return (string) $class::getNavigationLabel();
            }
        } catch (\Throwable) {
        }

        return class_basename($class);
    }

    private static function safeGroup(string $class): string
    {
        try {
            if (method_exists($class, 'getNavigationGroup')) {
                $g = $class::getNavigationGroup();
                if (is_string($g) && $g !== '') {
                    return $g;
                }
            }
        } catch (\Throwable) {
        }

        return 'Інше';
    }
}
