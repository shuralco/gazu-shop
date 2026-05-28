<?php

namespace App\Support\Modules;

use App\Models\Module;
use App\Support\Hooks;
use App\Support\ModuleDiscovery;
use App\Support\ModuleManager;
use App\Support\Modules\ModuleHealthCheck;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

/**
 * Orchestrates lifecycle calls for modules. Used by ModuleSettings page
 * and `module:enable / module:disable / module:purge` artisan commands.
 *
 * Flow on enable:
 *   1. Run module migrations (if migrations_path declared and not yet run)
 *   2. Resolve lifecycle handler from manifest "lifecycle" FQCN
 *   3. If installed_version is null → call install()
 *   4. If installed_version < manifest.version → call upgrade(from, to)
 *   5. Call boot() (always)
 *   6. Update modules.installed_version = manifest.version
 *
 * Flow on disable:
 *   - Call disable() if handler exists
 *   - DON'T drop tables (data preserved)
 *
 * Flow on purge (explicit `module:purge`):
 *   - Call uninstall() if handler exists
 *   - DON'T auto-drop tables — user must do via DB if desired
 */
class ModuleLifecycleRunner
{
    /**
     * @return array{actions: string[], errors: string[], from_version: ?string, to_version: ?string}
     */
    /**
     * Health-check gate перед enable. Повертає список критичних помилок
     * (порожній якщо все ОК — можна enable).
     *
     * @return list<string>
     */
    public static function preEnableCheck(string $key): array
    {
        $errors = [];
        $checks = ModuleHealthCheck::run($key);
        foreach ($checks as $check) {
            if (($check['status'] ?? null) === 'error') {
                $errors[] = ($check['label'] ?? 'check').': '.($check['detail'] ?? 'failed');
            }
        }
        return $errors;
    }

    public static function onEnable(string $key): array
    {
        $report = ['actions' => [], 'errors' => [], 'from_version' => null, 'to_version' => null];

        $manifest = ModuleDiscovery::manifests()[$key] ?? null;
        if (! $manifest) {
            $report['errors'][] = "Manifest not found for '{$key}'";
            Hooks::do('module.enable_failed', $key, $report);
            return $report;
        }

        // Pre-enable health gate — блокуємо якщо є критичні помилки.
        $healthErrors = self::preEnableCheck($key);
        if (! empty($healthErrors)) {
            $report['errors'] = array_merge(["Health-check заблокував enable:"], $healthErrors);
            Hooks::do('module.enable_failed', $key, $report);
            return $report;
        }

        Hooks::do('module.enabling', $key, $manifest);

        $dbRow = Module::where('key', $key)->first();
        $installedVersion = $dbRow?->installed_version;
        $manifestVersion = $manifest['version'] ?? '1.0.0';
        $report['from_version'] = $installedVersion;
        $report['to_version'] = $manifestVersion;

        // Step 1: Run migrations (if any)
        if (! empty($manifest['migrations_path'])) {
            $migPath = "modules/{$key}/".ltrim($manifest['migrations_path'], '/');
            if (is_dir(base_path($migPath))) {
                try {
                    Artisan::call('migrate', ['--path' => $migPath, '--force' => true]);
                    $output = trim(Artisan::output());
                    if (str_contains($output, 'DONE') || str_contains($output, 'Nothing to migrate')) {
                        $report['actions'][] = 'migrations: ' . (str_contains($output, 'DONE') ? 'ran' : 'up-to-date');
                    }
                } catch (\Throwable $e) {
                    $report['errors'][] = "Migration failed: ".$e->getMessage();
                    Log::error("Module {$key} migration failed", ['err' => $e->getMessage()]);
                }
            }
        }

        // Step 2-5: Lifecycle hooks
        $handler = self::resolveHandler($manifest);

        if ($handler) {
            if ($installedVersion === null) {
                // FIRST install
                self::safeCall($handler, 'install', [], $report);
            } elseif (version_compare($installedVersion, $manifestVersion, '<')) {
                // UPGRADE
                self::safeCall($handler, 'upgrade', [$installedVersion, $manifestVersion], $report);
            }
            self::safeCall($handler, 'boot', [], $report);
        }

        // Step 6: Update installed_version
        if ($dbRow) {
            $dbRow->update(['installed_version' => $manifestVersion]);
        } else {
            Module::create(['key' => $key, 'enabled' => true, 'installed_version' => $manifestVersion, 'enabled_at' => now()]);
        }

        // Step 7: Refresh composer autoload так щоб нові класи модуля
        // одразу резолвились (раніше треба було manually composer dump).
        self::refreshAutoload($report);

        ModuleManager::clearCache();

        // Fire ALWAYS — listeners (Telegram bot, email, audit) можуть
        // підписатись на 'module.enabled' / 'module.enable_failed'.
        if (! empty($report['errors'])) {
            Hooks::do('module.enable_failed', $key, $report);
        } else {
            Hooks::do('module.enabled', $key, $report);
        }

        return $report;
    }

    /**
     * Best-effort composer dump-autoload. Critical для модулів що додали
     * нові класи — без цього вони не резолвляться через psr-4/classmap.
     */
    private static function refreshAutoload(array &$report): void
    {
        $composer = self::findComposerBinary();
        if (! $composer) {
            $report['actions'][] = 'autoload: skipped (composer not found)';
            return;
        }
        $cmd = $composer.' dump-autoload --no-interaction --no-scripts 2>&1';
        exec($cmd, $output, $exitCode);
        if ($exitCode === 0) {
            $report['actions'][] = 'autoload: refreshed';
            Artisan::call('view:clear');
            Artisan::call('filament:cache-components');
        } else {
            $report['actions'][] = 'autoload: failed';
            Log::warning('[ModuleLifecycle] composer dump-autoload failed', ['exit' => $exitCode, 'output' => $output]);
        }
    }

    private static function findComposerBinary(): ?string
    {
        foreach (['/usr/local/bin/composer', '/usr/bin/composer', 'composer'] as $candidate) {
            $check = $candidate === 'composer'
                ? trim(shell_exec('which composer 2>/dev/null') ?? '')
                : (is_executable($candidate) ? $candidate : '');
            if ($check && file_exists($check)) {
                return escapeshellarg($check);
            }
        }
        return null;
    }

    /**
     * @return array{actions: string[], errors: string[]}
     */
    public static function onDisable(string $key, bool $rollbackMigrations = false): array
    {
        $report = ['actions' => [], 'errors' => []];
        $manifest = ModuleDiscovery::manifests()[$key] ?? null;
        if (! $manifest) {
            return $report;
        }
        Hooks::do('module.disabling', $key, $manifest, $rollbackMigrations);

        $handler = self::resolveHandler($manifest);
        if ($handler) {
            self::safeCall($handler, 'disable', [], $report);
        }

        // Optional: rollback migrations — DROPS module tables.
        // Викликається коли admin вибирає "Disable з очисткою даних" у UI.
        if ($rollbackMigrations && ! empty($manifest['migrations_path'])) {
            $migPath = "modules/{$key}/".ltrim($manifest['migrations_path'], '/');
            if (is_dir(base_path($migPath))) {
                try {
                    Artisan::call('migrate:rollback', ['--path' => $migPath, '--force' => true]);
                    $report['actions'][] = 'migrations: rolled back (tables dropped)';
                    // Clear installed_version so next enable re-installs cleanly.
                    Module::where('key', $key)->update(['installed_version' => null]);
                } catch (\Throwable $e) {
                    $report['errors'][] = 'Rollback failed: '.$e->getMessage();
                    Log::error("Module {$key} rollback failed", ['err' => $e->getMessage()]);
                }
            }
        }

        ModuleManager::clearCache();

        Hooks::do('module.disabled', $key, $report);

        return $report;
    }

    /**
     * @return array{actions: string[], errors: string[]}
     */
    public static function onUninstall(string $key): array
    {
        $report = ['actions' => [], 'errors' => []];
        $manifest = ModuleDiscovery::manifests()[$key] ?? null;
        if (! $manifest) {
            return $report;
        }
        Hooks::do('module.uninstalling', $key, $manifest);

        $handler = self::resolveHandler($manifest);
        if ($handler) {
            self::safeCall($handler, 'uninstall', [], $report);
        }
        Module::where('key', $key)->update(['installed_version' => null]);
        ModuleManager::clearCache();

        Hooks::do('module.uninstalled', $key, $report);

        return $report;
    }

    private static function resolveHandler(array $manifest): ?ModuleLifecycle
    {
        $fqcn = $manifest['lifecycle'] ?? null;
        if (! $fqcn || ! is_string($fqcn)) {
            return null;
        }
        if (! class_exists($fqcn)) {
            return null;
        }
        $instance = app($fqcn);
        if (! $instance instanceof ModuleLifecycle) {
            return null;
        }

        return $instance;
    }

    private static function safeCall(ModuleLifecycle $handler, string $method, array $args, array &$report): void
    {
        if (! method_exists($handler, $method)) {
            return;
        }
        try {
            $handler->{$method}(...$args);
            $report['actions'][] = $method;
        } catch (\Throwable $e) {
            $report['errors'][] = "{$method}() failed: ".$e->getMessage();
            Log::error("Module lifecycle {$method} failed", ['err' => $e->getMessage()]);
        }
    }
}
