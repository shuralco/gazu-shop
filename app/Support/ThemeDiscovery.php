<?php

namespace App\Support;

use Illuminate\Contracts\Foundation\Application;

/**
 * Boot-time hook for the active theme.
 *
 * Prepends the theme's views directory to the view finder so that:
 *   - view('gazu.layout') first checks themes/{active}/resources/views/gazu/layout.blade.php
 *   - and only falls back to resources/views/gazu/layout.blade.php if missing
 *
 * This means an installed theme can override ANY blade view by mirroring the
 * core path structure under themes/{name}/resources/views/ — without touching
 * core files.
 *
 * @see themes/README.md
 * @see App\Support\ThemeManager
 */
class ThemeDiscovery
{
    public static function bootActiveTheme(Application $app): void
    {
        // Views активної теми з урахуванням маніфест-ключа views_path (раніше
        // хардкод '/resources/views' ігнорував його). Для gazu — той самий
        // resources/views, тож поведінка незмінна.
        $viewsDir = ThemeManager::viewsPath();
        if ($viewsDir === null) {
            return;
        }

        $finder = $app->make('view')->getFinder();

        // Prepend so theme views win over core for paths that exist in both.
        if (method_exists($finder, 'prependLocation')) {
            $finder->prependLocation($viewsDir);
        } else {
            // Older Laravel — fallback to addLocation (less precedence-stable).
            $finder->addLocation($viewsDir);
        }
    }
}
