<?php

namespace App\Support\Modules;

use App\Models\Module;
use App\Support\ModuleDiscovery;
use App\Support\ModuleManager;
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
    public static function onEnable(string $key): array
    {
        $report = ['actions' => [], 'errors' => [], 'from_version' => null, 'to_version' => null];

        $manifest = ModuleDiscovery::manifests()[$key] ?? null;
        if (! $manifest) {
            $report['errors'][] = "Manifest not found for '{$key}'";

            return $report;
        }

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

        ModuleManager::clearCache();

        return $report;
    }

    /**
     * @return array{actions: string[], errors: string[]}
     */
    public static function onDisable(string $key): array
    {
        $report = ['actions' => [], 'errors' => []];
        $manifest = ModuleDiscovery::manifests()[$key] ?? null;
        if (! $manifest) {
            return $report;
        }
        $handler = self::resolveHandler($manifest);
        if ($handler) {
            self::safeCall($handler, 'disable', [], $report);
        }
        ModuleManager::clearCache();

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
        $handler = self::resolveHandler($manifest);
        if ($handler) {
            self::safeCall($handler, 'uninstall', [], $report);
        }
        Module::where('key', $key)->update(['installed_version' => null]);
        ModuleManager::clearCache();

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
