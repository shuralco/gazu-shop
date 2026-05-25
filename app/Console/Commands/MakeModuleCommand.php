<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

/**
 * Scaffold a new module under modules/{key}/ with manifest, ServiceProvider,
 * empty routes file, and standard subfolders. Intended for fresh modules —
 * for migrating existing code, copy files manually into the generated skeleton.
 *
 * Usage:
 *   php artisan make:module {key} [--label="Display Name"]
 */
class MakeModuleCommand extends Command
{
    protected $signature = 'make:module {key : Module key (snake_case)} {--label= : Human-readable label}';

    protected $description = 'Scaffold a new module under modules/{key}/ with manifest and skeleton.';

    public function handle(Filesystem $fs): int
    {
        $key = (string) $this->argument('key');
        if (! preg_match('/^[a-z][a-z0-9_]*$/', $key)) {
            $this->error("Invalid module key '{$key}'. Use snake_case ([a-z][a-z0-9_]*).");

            return self::FAILURE;
        }

        $label = (string) ($this->option('label') ?: Str::title(str_replace('_', ' ', $key)));
        $root = base_path("modules/{$key}");

        if ($fs->isDirectory($root)) {
            $this->error("Module directory already exists: modules/{$key}/");

            return self::FAILURE;
        }

        $className = Str::studly($key);
        $providerClass = "Modules\\{$className}\\Providers\\{$className}ServiceProvider";
        $providerFqcn = "App\\Providers\\Modules\\{$className}ServiceProvider";

        $dirs = [
            'src/Providers',
            'src/Models',
            'src/Http/Controllers',
            'src/Filament/Resources',
            'src/Services',
            'database/migrations',
            'database/seeders',
            'routes',
            'resources/views',
            'resources/lang/uk',
            'tests/Feature',
        ];
        foreach ($dirs as $d) {
            $fs->makeDirectory("{$root}/{$d}", 0755, true);
        }

        $fs->put("{$root}/module.json", $this->manifestJson($key, $label, $providerClass));
        $fs->put("{$root}/src/Providers/{$className}ServiceProvider.php", $this->providerStub($className));
        $fs->put("{$root}/routes/web.php", $this->routesStub($key));
        $fs->put("{$root}/README.md", $this->readmeStub($key, $label));

        $this->info("✓ Module '{$key}' scaffolded at modules/{$key}/");
        $this->line('Next steps:');
        $this->line("  1. Run: composer dump-autoload");
        $this->line("  2. Fill in module.json (providers, filament_resources, etc.)");
        $this->line("  3. Toggle: php artisan module:enable {$key}");

        return self::SUCCESS;
    }

    private function manifestJson(string $key, string $label, string $providerClass): string
    {
        return json_encode([
            'name' => $key,
            'label' => $label,
            'description' => '',
            'version' => '0.1.0',
            'author' => '',
            'engine' => '>=2.0',
            'requires_modules' => [],
            'composer_packages' => [],
            'providers' => [$providerClass],
            'filament_resources' => [],
            'migrations_path' => 'database/migrations',
            'views_path' => 'resources/views',
            'views_namespace' => $key,
            'routes' => 'routes/web.php',
            'translations_path' => 'resources/lang',
            'settings_schema' => new \stdClass,
            'enabled_by_default' => false,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n";
    }

    private function providerStub(string $className): string
    {
        return <<<PHP
        <?php

        namespace Modules\\{$className}\\Providers;

        use Illuminate\\Support\\ServiceProvider;

        class {$className}ServiceProvider extends ServiceProvider
        {
            public function register(): void
            {
                //
            }

            public function boot(): void
            {
                //
            }
        }

        PHP;
    }

    private function routesStub(string $key): string
    {
        return <<<PHP
        <?php

        use Illuminate\\Support\\Facades\\Route;

        // Routes for module: {$key}
        // Auto-loaded by ModuleDiscovery::bootModuleResources() when module is enabled.
        // Uses 'web' middleware group (session + CSRF).

        PHP;
    }

    private function readmeStub(string $key, string $label): string
    {
        return "# {$label}\n\nKey: `{$key}`\n\nDescribe what this module does.\n";
    }
}
