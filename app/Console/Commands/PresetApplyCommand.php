<?php

namespace App\Console\Commands;

use App\Models\DisplaySetting;
use App\Models\Module;
use App\Support\ModuleManager;
use App\Support\ThemeManager;
use Illuminate\Console\Command;

/**
 * Apply a preset — one command that sets the theme, toggles modules, and
 * writes display_settings for a chosen business type.
 *
 *   php artisan preset:apply auto-parts
 *   php artisan preset:apply cosmetics
 *   php artisan preset:apply general-shop
 *
 * Presets live under presets/{name}.php and return an array with keys:
 *   - theme              (string)
 *   - modules_on[]       (string[])
 *   - modules_off[]      (string[])
 *   - display_settings   (array<string,mixed>)
 *
 * Idempotent — re-applying the same preset is a no-op (toggles already-on
 * modules ON, already-off OFF). Use --dry-run to preview without writing.
 */
class PresetApplyCommand extends Command
{
    protected $signature = 'preset:apply {name : Preset name (matches presets/{name}.php)} {--dry-run : Print what would change without persisting}';

    protected $description = 'Apply a business-type preset (theme + module toggles + display settings).';

    public function handle(): int
    {
        $name = (string) $this->argument('name');
        $file = base_path("presets/{$name}.php");

        if (! is_file($file)) {
            $this->error("Preset '{$name}' not found at presets/{$name}.php");
            $this->line('Available presets:');
            foreach (glob(base_path('presets/*.php')) ?: [] as $p) {
                $this->line('  - '.basename($p, '.php'));
            }

            return self::FAILURE;
        }

        $preset = require $file;
        if (! is_array($preset)) {
            $this->error("Preset file did not return an array.");

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');

        $this->info(($dryRun ? '[DRY RUN] ' : '')."Applying preset: {$preset['label']}");
        $this->line($preset['description'] ?? '');
        $this->newLine();

        // Theme
        if (! empty($preset['theme'])) {
            $themes = ThemeManager::themes();
            $theme = (string) $preset['theme'];
            if (! isset($themes[$theme])) {
                $this->warn("Theme '{$theme}' not installed — skipping theme switch.");
            } else {
                $this->line("Theme  → {$theme} ({$themes[$theme]['label']})");
                if (! $dryRun) {
                    ThemeManager::setActive($theme);
                }
            }
        }

        // Modules — ON
        if (! empty($preset['modules_on']) && is_array($preset['modules_on'])) {
            foreach ($preset['modules_on'] as $key) {
                $this->line("Module ✓ {$key}");
                if (! $dryRun) {
                    Module::updateOrCreate(['key' => $key], ['enabled' => true, 'enabled_at' => now()]);
                }
            }
        }

        // Modules — OFF
        if (! empty($preset['modules_off']) && is_array($preset['modules_off'])) {
            foreach ($preset['modules_off'] as $key) {
                $this->line("Module ✗ {$key}");
                if (! $dryRun) {
                    Module::updateOrCreate(['key' => $key], ['enabled' => false, 'disabled_at' => now()]);
                }
            }
        }

        // Display settings
        if (! empty($preset['display_settings']) && is_array($preset['display_settings'])) {
            foreach ($preset['display_settings'] as $key => $value) {
                $this->line("Setting → {$key} = ".(is_scalar($value) ? var_export($value, true) : json_encode($value)));
                if (! $dryRun) {
                    DisplaySetting::set($key, $value);
                }
            }
        }

        if (! $dryRun) {
            ModuleManager::clearCache();
            ThemeManager::clearCache();
        }

        $this->newLine();
        $this->info($dryRun ? 'Dry run complete — nothing persisted.' : "✓ Preset '{$name}' applied.");
        if (! $dryRun) {
            $this->line('Suggested next steps:');
            $this->line('  npm run build           # rebuild assets if theme changed');
            $this->line('  php artisan optimize    # warm config + route caches');
        }

        return self::SUCCESS;
    }
}
