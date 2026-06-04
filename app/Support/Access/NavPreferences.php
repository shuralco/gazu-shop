<?php

namespace App\Support\Access;

use App\Models\User;

/**
 * Per-user personal navigation preferences. Currently: hidden items (the user
 * removed them from THEIR sidebar). Cosmetic only — does NOT affect access
 * (RBAC AccessControl is the access gate). Section key = class basename.
 *
 * Per-request memoized (Octane-safe: flushed by FlushPerRequestSettingsState).
 */
class NavPreferences
{
    /** @var array<int,array<int,string>> userId → hidden section keys */
    private static array $hidden = [];

    /** Is this Resource/Page hidden from the given (or current) user's sidebar? */
    public static function isHidden(string $section, ?User $user = null): bool
    {
        $user ??= auth()->user();
        if (! $user) {
            return false;
        }

        return in_array(class_basename($section), self::hiddenFor($user), true);
    }

    /** @return array<int,string> hidden section keys for the user */
    public static function hiddenFor(User $user): array
    {
        if (array_key_exists($user->id, self::$hidden)) {
            return self::$hidden[$user->id];
        }

        $prefs = (array) ($user->nav_preferences ?? []);
        $hidden = array_values(array_filter((array) ($prefs['hidden'] ?? []), 'is_string'));

        return self::$hidden[$user->id] = $hidden;
    }

    /** Persist the user's hidden list (section keys = basenames). */
    public static function setHidden(User $user, array $sectionKeys): void
    {
        $prefs = (array) ($user->nav_preferences ?? []);
        $prefs['hidden'] = array_values(array_unique(array_map('strval', $sectionKeys)));
        $user->nav_preferences = $prefs;
        $user->save();
        unset(self::$hidden[$user->id]);
    }

    public static function flush(): void
    {
        self::$hidden = [];
    }
}
