<?php

namespace App\Support\Modules;

use App\Support\ModuleDiscovery;
use App\Support\ModuleManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

/**
 * Health diagnostics for a single module. Returns array of check results.
 * Used on Detail page to show green/red indicators.
 *
 * Each check returns:
 *   { id: string, label: string, status: 'ok'|'warning'|'error', detail: ?string }
 */
class ModuleHealthCheck
{
    /**
     * @return list<array{id:string,label:string,status:string,detail:?string}>
     */
    public static function run(string $key): array
    {
        $manager = ModuleManager::for($key);
        if (! $manager->exists()) {
            return [['id' => 'exists', 'label' => 'Module exists', 'status' => 'error', 'detail' => 'Manifest not found']];
        }

        $manifest = ModuleDiscovery::manifests()[$key] ?? [];
        $results = [];

        // 1. Folder exists
        $modulePath = base_path("modules/{$key}");
        $folderExists = File::isDirectory($modulePath);
        $results[] = [
            'id' => 'folder',
            'label' => 'Папка модуля',
            'status' => $folderExists ? 'ok' : 'error',
            'detail' => $folderExists ? str_replace(base_path().'/', '', $modulePath) : 'modules/'.$key.' відсутня',
        ];

        // 2. Manifest valid
        $manifestPath = "{$modulePath}/module.json";
        $manifestValid = File::isFile($manifestPath)
            && is_array(json_decode((string) File::get($manifestPath), true) ?? null);
        $results[] = [
            'id' => 'manifest',
            'label' => 'module.json valid',
            'status' => $manifestValid ? 'ok' : 'error',
            'detail' => $manifestValid ? null : "Файл відсутній або невалідний JSON",
        ];

        // 3. Service providers exist
        $providers = $manifest['providers'] ?? [];
        if (! empty($providers)) {
            $missingProviders = array_filter($providers, fn ($p) => is_string($p) && ! class_exists($p));
            $results[] = [
                'id' => 'providers',
                'label' => 'Service providers ('.count($providers).')',
                'status' => empty($missingProviders) ? 'ok' : 'error',
                'detail' => empty($missingProviders) ? null : 'Відсутні класи: '.implode(', ', $missingProviders),
            ];
        }

        // 4. Filament resources class_exists
        $resources = $manifest['filament_resources'] ?? [];
        if (! empty($resources)) {
            $missing = array_filter($resources, fn ($c) => is_string($c) && ! class_exists($c));
            $results[] = [
                'id' => 'filament_resources',
                'label' => 'Filament Resources ('.count($resources).')',
                'status' => empty($missing) ? 'ok' : 'error',
                'detail' => empty($missing) ? null : 'Відсутні: '.implode(', ', $missing),
            ];
        }

        // 5. Filament pages class_exists
        $pages = $manifest['filament_pages'] ?? [];
        if (! empty($pages)) {
            $missing = array_filter($pages, fn ($c) => is_string($c) && ! class_exists($c));
            $results[] = [
                'id' => 'filament_pages',
                'label' => 'Filament Pages ('.count($pages).')',
                'status' => empty($missing) ? 'ok' : 'error',
                'detail' => empty($missing) ? null : 'Відсутні: '.implode(', ', $missing),
            ];
        }

        // 6. Migrations executed
        if (! empty($manifest['migrations_path'])) {
            $migDir = $modulePath.'/'.ltrim($manifest['migrations_path'], '/');
            if (File::isDirectory($migDir)) {
                $migFiles = collect(File::files($migDir))
                    ->map(fn ($f) => pathinfo($f->getFilename(), PATHINFO_FILENAME))
                    ->all();
                try {
                    $ran = DB::table('migrations')->whereIn('migration', $migFiles)->pluck('migration')->all();
                    $missing = array_diff($migFiles, $ran);
                    $results[] = [
                        'id' => 'migrations',
                        'label' => 'Migrations виконано ('.count($migFiles).')',
                        'status' => empty($missing) ? 'ok' : ($manager->enabled() ? 'warning' : 'ok'),
                        'detail' => empty($missing) ? null : 'Невиконані: '.implode(', ', array_slice($missing, 0, 3)),
                    ];
                } catch (\Throwable $e) {
                    $results[] = ['id' => 'migrations', 'label' => 'Migrations', 'status' => 'warning', 'detail' => 'DB error: '.substr($e->getMessage(), 0, 60)];
                }
            }
        }

        // 7. Routes registered (if module is enabled + has routes file)
        if ($manager->enabled() && ! empty($manifest['routes'])) {
            $routesPath = $modulePath.'/'.ltrim($manifest['routes'], '/');
            if (File::isFile($routesPath)) {
                $registeredCount = collect(Route::getRoutes())
                    ->filter(fn ($r) => str_contains((string) $r->uri(), "/{$key}/")
                        || str_contains((string) $r->getActionName(), "modules\\{$key}\\")
                        || str_contains((string) $r->getActionName(), "modules/{$key}/"))
                    ->count();
                $results[] = [
                    'id' => 'routes',
                    'label' => 'Routes зареєстровано',
                    'status' => $registeredCount > 0 ? 'ok' : 'warning',
                    'detail' => $registeredCount > 0 ? "$registeredCount routes" : 'Жодного route не знайдено',
                ];
            }
        }

        // 8. Version sync (installed_version vs manifest.version)
        $manifestVersion = $manifest['version'] ?? null;
        $installedVersion = \App\Models\Module::where('key', $key)->value('installed_version');
        if ($manifestVersion && $manager->enabled()) {
            if ($installedVersion === null) {
                $status = 'warning';
                $detail = "Не встановлено (manifest: {$manifestVersion}). Re-enable запустить install.";
            } elseif (version_compare($installedVersion, $manifestVersion, '<')) {
                $status = 'warning';
                $detail = "Доступне оновлення: {$installedVersion} → {$manifestVersion}";
            } else {
                $status = 'ok';
                $detail = "v{$installedVersion}";
            }
            $results[] = ['id' => 'version', 'label' => 'Версія', 'status' => $status, 'detail' => $detail];
        }

        // 9. Dependencies satisfied
        $requires = $manager->requires();
        if (! empty($requires)) {
            $unsatisfied = array_filter($requires, fn ($r) => ! ModuleManager::for($r)->enabled());
            $results[] = [
                'id' => 'dependencies',
                'label' => 'Залежності ('.count($requires).')',
                'status' => empty($unsatisfied) ? 'ok' : 'error',
                'detail' => empty($unsatisfied) ? null : 'Вимкнено: '.implode(', ', $unsatisfied),
            ];
        }

        return $results;
    }

    /**
     * Overall health: 'ok' if all checks OK, 'warning' if any warning, 'error' if any error.
     */
    public static function overall(string $key): string
    {
        $checks = self::run($key);
        foreach (['error', 'warning'] as $level) {
            foreach ($checks as $c) {
                if ($c['status'] === $level) {
                    return $level;
                }
            }
        }
        return 'ok';
    }
}
