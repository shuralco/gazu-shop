<?php

namespace App\Listeners\Octane;

use App\Models\DisplaySetting;
use App\Support\ThemeManager;

/**
 * Octane RequestReceived listener — resets the in-process static caches that
 * hold settings/theme state so each request re-resolves fresh.
 *
 * On php-fpm every request is a fresh process, so these statics are never stale.
 * On Octane/Swoole a worker is long-lived: without this, a theme/setting changed
 * by one worker would stay stale in every other worker until restart (cached
 * HTML is already busted via ResponseCacheObserver, but the re-render would still
 * read stale statics). Resetting only the in-process caches is cheap — the next
 * read hits the shared cache, which set() invalidates on change.
 *
 * Registered in config/octane.php under listeners[RequestReceived].
 */
class FlushPerRequestSettingsState
{
    public function handle(mixed $event = null): void
    {
        DisplaySetting::resetRequestCache();
        ThemeManager::clearCache();
        \App\Support\Access\AccessControl::flush();
        \App\Support\Access\NavPreferences::flush();
    }
}
