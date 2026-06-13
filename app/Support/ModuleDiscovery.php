<?php

namespace App\Support;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Route;

/**
 * Boot-time scanner that walks `modules/*\/module.json`, validates each
 * manifest, and — for modules that ModuleManager says are enabled —
 * registers their ServiceProvider(s), views, routes, and migrations paths.
 *
 * Disabled modules are skipped entirely: no providers loaded → no routes,
 * no Filament resources, no observers. Their DB data is preserved
 * (migrations were already run when they were last enabled).
 *
 * Discovery results are cached per request via static memo, so cost of
 * scanning ~14 manifests is paid once per boot.
 */
class ModuleDiscovery
{
    /** @var array<string,array<string,mixed>>|null */
    private static ?array $manifests = null;

    /**
     * Read every modules/*\/module.json into memory. Skips `_example/`
     * and anything starting with `_`. Returned shape:
     *   [ 'loyalty' => ['name' => 'loyalty', 'providers' => [...], ...] ].
     *
     * @return array<string,array<string,mixed>>
     */
    public static function manifests(): array
    {
        if (self::$manifests !== null) {
            return self::$manifests;
        }

        $root = base_path('modules');
        if (! is_dir($root)) {
            return self::$manifests = [];
        }

        $result = [];
        foreach (scandir($root) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..' || str_starts_with($entry, '_')) {
                continue;
            }
            $dir = $root.DIRECTORY_SEPARATOR.$entry;
            if (! is_dir($dir)) {
                continue;
            }
            $manifestFile = $dir.'/module.json';
            if (! is_file($manifestFile)) {
                continue;
            }
            $data = json_decode((string) file_get_contents($manifestFile), true);
            if (! is_array($data) || empty($data['name'])) {
                continue;
            }
            $data['_path'] = $dir;
            $result[$data['name']] = $data;
        }

        return self::$manifests = $result;
    }

    /**
     * Called from AppServiceProvider::register() — boot ServiceProvider(s)
     * declared in module.json. We CANNOT call DB/Cache here (facades may not
     * be ready), so we register based on config/ENV defaults only. The
     * boot() phase later filters routes/views/migrations by DB-resolved state.
     *
     * If a module is disabled by ENV/config, its provider still doesn't run
     * (Laravel only boots registered ones via service container resolution),
     * so this is safe.
     */
    public static function registerProviders(Application $app): void
    {
        foreach (self::manifests() as $name => $manifest) {
            if (! self::isLikelyEnabled($name, $manifest)) {
                continue;
            }
            foreach ($manifest['providers'] ?? [] as $providerClass) {
                if (is_string($providerClass) && class_exists($providerClass)) {
                    $app->register($providerClass);
                }
            }
        }
    }

    /**
     * Cheap enabled-check using only config/ENV — no DB/Cache calls so it's
     * safe to invoke from register() phase. The full DB-aware resolution
     * happens in boot() via ModuleManager::enabled().
     */
    private static function isLikelyEnabled(string $name, array $manifest): bool
    {
        $envKey = 'MODULE_'.strtoupper($name);
        $env = env($envKey);
        if ($env !== null) {
            return filter_var($env, FILTER_VALIDATE_BOOLEAN);
        }

        $cfg = (array) config("modules.{$name}", []);
        if (array_key_exists('enabled', $cfg)) {
            return (bool) $cfg['enabled'];
        }

        return (bool) ($manifest['enabled_by_default'] ?? false);
    }

    /**
     * Called from AppServiceProvider::boot() — register views, routes,
     * and migration paths for each enabled module. Run AFTER providers
     * so module-specific providers had chance to register their own
     * bindings first.
     */
    public static function bootModuleResources(Application $app): void
    {
        foreach (self::manifests() as $name => $manifest) {
            if (! ModuleManager::for($name)->enabled()) {
                continue;
            }
            $path = $manifest['_path'];

            // Провайдери модулів, увімкнених ЛИШЕ через БД (config/ENV default
            // = false, як у installable-модулів): registerProviders() у register()
            // фазі їх пропускає (там не можна чіпати БД), тож реєструємо тут —
            // ця фаза DB-aware. Laravel boot'ить провайдер одразу. Ідемпотентно:
            // якщо вже зареєстрований у register(), повторний register() — no-op.
            foreach ($manifest['providers'] ?? [] as $providerClass) {
                if (is_string($providerClass) && class_exists($providerClass) && ! $app->providerIsLoaded($providerClass)) {
                    $app->register($providerClass);
                }
            }

            // Views with namespace from manifest (e.g. view('loyalty::tier.show'))
            if (! empty($manifest['views_path'])) {
                $viewsDir = $path.'/'.ltrim($manifest['views_path'], '/');
                $ns = $manifest['views_namespace'] ?? $name;
                if (is_dir($viewsDir)) {
                    $app->make('view')->addNamespace($ns, $viewsDir);
                }
            }

            // Migrations — registered so `php artisan migrate` picks them up
            // automatically without needing to publish anywhere.
            if (! empty($manifest['migrations_path'])) {
                $migDir = $path.'/'.ltrim($manifest['migrations_path'], '/');
                if (is_dir($migDir)) {
                    $app->make('migrator')->path($migDir);
                }
            }

            // Routes file (web middleware group). Use Route::middleware('web')
            // so session/CSRF behave normally.
            if (! empty($manifest['routes'])) {
                $routesFile = $path.'/'.ltrim($manifest['routes'], '/');
                if (is_file($routesFile)) {
                    Route::middleware('web')->group($routesFile);
                }
            }

            // Translations
            if (! empty($manifest['translations_path'])) {
                $langDir = $path.'/'.ltrim($manifest['translations_path'], '/');
                if (is_dir($langDir)) {
                    $app->make('translator')->addNamespace($name, $langDir);
                }
            }

            // Console commands — modules/<name>/src/Console/Commands/*.php.
            // Laravel auto-discover'ить ЛИШЕ app/Console/Commands; модульні
            // команди фізично в modules/ (autoload через composer classmap), тож
            // НІКОЛИ не реєструвались → scheduled-крони (np:track, up:track,
            // loyalty:*, checkbox:*, feeds:*, …) падали з NamespaceNotFoundException
            // на КОЖЕН запуск. Реєструємо тут для enabled-модулів.
            if ($app->runningInConsole()) {
                $cmdDir = $path.'/src/Console/Commands';
                if (is_dir($cmdDir)) {
                    $commands = [];
                    foreach (glob($cmdDir.'/*.php') ?: [] as $file) {
                        $src = @file_get_contents($file);
                        if (! $src || ! preg_match('/^namespace\s+([^;]+);/m', $src, $m)) {
                            continue;
                        }
                        $class = trim($m[1]).'\\'.basename($file, '.php');
                        if (class_exists($class)
                            && is_subclass_of($class, \Illuminate\Console\Command::class)
                            && ! (new \ReflectionClass($class))->isAbstract()) {
                            $commands[] = $class;
                        }
                    }
                    if ($commands) {
                        \Illuminate\Support\Facades\Artisan::starting(
                            fn ($artisan) => $artisan->resolveCommands($commands)
                        );
                    }
                }
            }
        }
    }

    /** Reset memo — primarily for tests. */
    public static function clearCache(): void
    {
        self::$manifests = null;
    }
}
