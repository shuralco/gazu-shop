<?php

namespace App\Filament\Pages;

use App\Models\Module;
use App\Support\ModuleDiscovery;
use App\Support\ModuleManager;
use App\Support\Modules\ModuleActivityLogger;
use App\Support\Modules\ModuleHealthCheck;
use App\Support\Modules\ModuleLifecycleRunner;
use App\Support\Modules\ModuleSettingsValidator;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

/**
 * Детальна сторінка одного модуля — як «Extension details» в OpenCart
 * або plugin page у WordPress. Шлях:  /admin/modules/view?key=loyalty
 *
 * Показує: manifest, settings_schema → форма, deps graph, файли модуля,
 * routes, статистику. Кнопки: enable/disable, reset settings, очистка cache.
 */
class ModuleDetail extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cube-transparent';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'modules/view';

    protected static string $view = 'filament.pages.module-detail';

    public string $moduleKey = '';

    /**
     * @var array<string,mixed>
     */
    public array $settings = [];

    /**
     * @var array<string,string>
     */
    public array $settingsErrors = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->is_admin === true;
    }

    public function mount(): void
    {
        $this->moduleKey = (string) request()->query('key', '');

        if (! $this->moduleKey || ! ModuleManager::for($this->moduleKey)->exists()) {
            abort(404, "Модуль '{$this->moduleKey}' не знайдено");
        }

        $this->settings = ModuleManager::for($this->moduleKey)->settings();
    }

    public function getTitle(): string
    {
        return ModuleManager::for($this->moduleKey)->name() ?: $this->moduleKey;
    }

    public function getBreadcrumbs(): array
    {
        return [
            route('filament.admin.pages.modules') => 'Модулі',
            '#' => $this->getTitle(),
        ];
    }

    /**
     * @return array<string,mixed>
     */
    public function getModuleInfo(): array
    {
        $key = $this->moduleKey;
        $manager = ModuleManager::for($key);
        $manifests = ModuleDiscovery::manifests();
        $manifest = $manifests[$key] ?? [];

        // Module folder existence
        $modulePath = base_path("modules/{$key}");
        $folderExists = File::isDirectory($modulePath);

        // Count files in module dir
        $fileCount = $folderExists
            ? collect(File::allFiles($modulePath))->count()
            : 0;

        // Count migrations
        $migrationsPath = "{$modulePath}/database/migrations";
        $migrations = File::isDirectory($migrationsPath)
            ? collect(File::files($migrationsPath))->map(fn ($f) => $f->getFilename())->all()
            : [];

        // Module routes (count by inspecting routes/web.php)
        $routesPath = "{$modulePath}/routes/web.php";
        $hasRoutes = File::exists($routesPath);
        $registeredRoutes = collect(Route::getRoutes())
            ->filter(fn ($r) => str_contains((string) $r->getActionName(), "modules\\{$key}\\")
                || str_contains((string) $r->getActionName(), "modules/{$key}/"))
            ->count();

        // Settings count
        $settingsSchema = $manifest['settings_schema'] ?? [];

        // Dependents
        $dependents = ModuleManager::all()
            ->filter(fn ($x) => in_array($key, $x->requires(), true))
            ->mapWithKeys(fn ($x, $k) => [$k => $x->enabled()])
            ->all();

        // DB row for module (if exists)
        $dbRow = Module::where('key', $key)->first();

        return [
            'key' => $key,
            'name' => $manager->name(),
            'description' => $manager->description(),
            'enabled' => $manager->enabled(),
            'version' => $manifest['version'] ?? null,
            'author' => $manifest['author'] ?? null,
            'engine_requirement' => $manifest['engine'] ?? null,
            'requires' => $manager->requires(),
            'dependents' => $dependents,
            'providers' => $manifest['providers'] ?? [],
            'filament_resources' => $manifest['filament_resources'] ?? [],
            'filament_pages' => $manifest['filament_pages'] ?? [],
            'filament_widgets' => $manifest['filament_widgets'] ?? [],
            'composer_packages' => $manifest['composer_packages'] ?? [],
            'settings_schema' => $settingsSchema,
            'has_settings' => ! empty($settingsSchema),
            'folder_exists' => $folderExists,
            'module_path' => $modulePath,
            'file_count' => $fileCount,
            'migrations' => $migrations,
            'migrations_count' => count($migrations),
            'has_routes' => $hasRoutes,
            'registered_routes' => $registeredRoutes,
            'views_namespace' => $manifest['views_namespace'] ?? null,
            'enabled_by_default' => $manifest['enabled_by_default'] ?? false,
            'enabled_at' => $dbRow?->enabled_at,
            'disabled_at' => $dbRow?->disabled_at,
            'installed_version' => $dbRow?->installed_version,
            'raw_manifest' => $manifest,
        ];
    }

    public function toggleModule(): void
    {
        $key = $this->moduleKey;
        $info = $this->getModuleInfo();
        $enable = ! $info['enabled'];

        // Dependent check
        if (! $enable) {
            $activeDependents = collect($info['dependents'])->filter()->keys();
            if ($activeDependents->isNotEmpty()) {
                Notification::make()
                    ->title('Не можна вимкнути')
                    ->body("Активні залежності: ".$activeDependents->implode(', '))
                    ->danger()
                    ->send();

                return;
            }
        }

        Module::updateOrCreate(
            ['key' => $key],
            [
                'enabled' => $enable,
                'enabled_at' => $enable ? now() : null,
                'disabled_at' => $enable ? null : now(),
            ]
        );

        $report = $enable
            ? ModuleLifecycleRunner::onEnable($key)
            : ModuleLifecycleRunner::onDisable($key);

        ModuleActivityLogger::log($key, $enable ? 'enabled' : 'disabled', [
            'from_version' => $report['from_version'] ?? null,
            'to_version' => $report['to_version'] ?? null,
            'lifecycle_actions' => $report['actions'] ?? [],
            'errors' => $report['errors'] ?? [],
        ]);

        ModuleManager::clearCache();
        Artisan::call('responsecache:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');

        $body = [];
        if (! empty($report['actions'])) $body[] = 'Hooks: '.implode(', ', $report['actions']);
        if (! empty($report['from_version']) && $report['from_version'] !== $report['to_version']) {
            $body[] = "Версія: {$report['from_version']} → {$report['to_version']}";
        }

        Notification::make()
            ->title($enable ? "✓ Увімкнено: {$key}" : "Вимкнено: {$key}")
            ->body(implode("\n", $body))
            ->success(fn () => empty($report['errors']))
            ->warning(fn () => ! empty($report['errors']))
            ->send();
    }

    public function saveSettings(): void
    {
        $key = $this->moduleKey;
        $info = $this->getModuleInfo();

        $result = ModuleSettingsValidator::validate(
            $this->settings,
            $info['settings_schema']
        );

        $this->settingsErrors = $result['errors'];

        if (! empty($result['errors'])) {
            Notification::make()
                ->title('Помилки валідації')
                ->body(implode("\n", $result['errors']))
                ->danger()
                ->send();

            return;
        }

        Module::updateOrCreate(
            ['key' => $key],
            ['settings' => $result['values']]
        );

        $this->settings = $result['values'];

        ModuleActivityLogger::log($key, 'settings_saved', ['settings' => $result['values']]);

        ModuleManager::clearCache();
        Artisan::call('responsecache:clear');

        Notification::make()
            ->title("Налаштування збережено")
            ->body("Зміни до '{$key}' застосовано миттєво.")
            ->success()
            ->send();
    }

    /**
     * @return list<array{id:string,label:string,status:string,detail:?string}>
     */
    public function getHealthChecks(): array
    {
        return ModuleHealthCheck::run($this->moduleKey);
    }

    public function getRecentActivity(int $limit = 10)
    {
        return ModuleActivityLogger::recent($this->moduleKey, $limit);
    }

    public function resetSettings(): void
    {
        $key = $this->moduleKey;

        Module::where('key', $key)->update(['settings' => null]);
        ModuleManager::clearCache();
        $this->settings = ModuleManager::for($key)->settings();

        Notification::make()
            ->title("Налаштування скинуто до defaults")
            ->success()
            ->send();
    }

    public function clearModuleCache(): void
    {
        ModuleManager::clearCache();
        Artisan::call('responsecache:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');

        Notification::make()
            ->title("Module cache очищено")
            ->success()
            ->send();
    }

    public function runMigrations(): void
    {
        $key = $this->moduleKey;
        $manifest = ModuleDiscovery::manifests()[$key] ?? null;

        if (! $manifest) {
            Notification::make()->title("Manifest не знайдено")->danger()->send();
            return;
        }

        $path = base_path("modules/{$key}/".ltrim($manifest['migrations_path'] ?? 'database/migrations', '/'));

        if (! File::isDirectory($path)) {
            Notification::make()->title("Папка migrations не існує")->warning()->send();
            return;
        }

        $exitCode = Artisan::call('migrate', [
            '--path' => str_replace(base_path().'/', '', $path),
            '--force' => true,
        ]);

        Notification::make()
            ->title($exitCode === 0 ? "Migrations виконано" : "Migration ERROR")
            ->body(Artisan::output())
            ->success(fn () => $exitCode === 0)
            ->danger(fn () => $exitCode !== 0)
            ->send();
    }
}
