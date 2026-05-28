<?php

namespace App\Console\Commands;

use App\Support\ThemeManager;
use Illuminate\Console\Command;

/**
 * Activate a different visual theme.
 *
 *   php artisan theme:set gazu
 *   php artisan theme:set cosmetics
 *
 * Persists choice in DisplaySetting (`active_theme`) so it survives across
 * deploys without touching .env. ThemeManager cache is cleared so the new
 * theme takes effect immediately for the next request / Octane worker.
 */
class ThemeSetCommand extends Command
{
    protected $signature = 'theme:set {name : Theme name (matches themes/{name}/theme.json)}';

    protected $description = 'Set the active storefront theme.';

    public function handle(): int
    {
        $name = (string) $this->argument('name');
        $themes = ThemeManager::themes();

        if (! isset($themes[$name])) {
            $this->error("Theme '{$name}' not found in themes/. Available: ".implode(', ', array_keys($themes)));

            return self::FAILURE;
        }

        ThemeManager::setActive($name);
        $this->info("✓ Active theme set to '{$name}' ({$themes[$name]['label']})");
        $this->line("  Path: {$themes[$name]['_path']}");
        $this->line("  Don't forget: npm run build  (rebuilds Vite assets)");

        return self::SUCCESS;
    }
}
