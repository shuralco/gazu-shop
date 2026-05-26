<?php

namespace App\Filament\Pages;

use App\Models\Module;
use App\Support\ModuleDiscovery;
use App\Support\ModuleManager;
use App\Support\Modules\ModuleActivityLogger;
use App\Support\Modules\ModuleLifecycleRunner;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;

/**
 * Admin UI to toggle modules + edit per-module settings without CLI.
 *
 * Persists to `modules` DB table (Phase 1 plugin system) via
 * App\Models\Module — no .env editing. ModuleObserver auto-invalidates
 * cache so the change takes effect on the next request.
 */
class ModuleSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static ?string $navigationGroup = 'Налаштування';

    protected static ?string $navigationLabel = 'Модулі';

    protected static ?string $title = 'Модулі магазину';

    protected static ?int $navigationSort = 51;

    protected static ?string $slug = 'modules';

    protected static string $view = 'filament.pages.module-settings';

    /**
     * @var array<string,array<string,mixed>>  key → settings array
     */
    public array $settings = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->is_admin === true;
    }

    public function mount(): void
    {
        // Pre-fill form state from current DB settings + manifest defaults
        foreach (ModuleManager::all() as $key => $manager) {
            $this->settings[$key] = $manager->settings();
        }
    }

    /**
     * Modules grouped by functional category for nicer UI.
     * Returns: [ 'group_label' => [ {key, name, description, enabled, requires, dependents, settings_schema}, ... ] ]
     */
    public function getGroupedModules(): array
    {
        $groups = [
            'shipping' => ['label' => 'Доставка', 'icon' => 'heroicon-o-truck', 'modules' => []],
            'inventory' => ['label' => 'Склад / B2B', 'icon' => 'heroicon-o-cube', 'modules' => []],
            'marketing' => ['label' => 'Маркетинг / лояльність', 'icon' => 'heroicon-o-megaphone', 'modules' => []],
            'auto' => ['label' => 'Auto-parts', 'icon' => 'heroicon-o-wrench-screwdriver', 'modules' => []],
            'tools' => ['label' => 'Інструменти', 'icon' => 'heroicon-o-cog-6-tooth', 'modules' => []],
        ];

        $categoryMap = [
            'novaposhta' => 'shipping',
            'ukrposhta' => 'shipping',
            'rozetka_delivery' => 'shipping',
            'meest_express' => 'shipping',
            'multi_warehouse' => 'inventory',
            'wholesale' => 'inventory',
            'loyalty' => 'marketing',
            'coupons' => 'marketing',
            'reviews' => 'marketing',
            'comparison' => 'marketing',
            'feed_export' => 'marketing',
            'gazu_garage' => 'auto',
            'auto_parts_seed' => 'auto',
            'quick_fill' => 'tools',
        ];

        $manifests = ModuleDiscovery::manifests();

        foreach (ModuleManager::all() as $key => $manager) {
            $manifest = $manifests[$key] ?? [];
            $group = $categoryMap[$key] ?? 'tools';

            $groups[$group]['modules'][] = [
                'key' => $key,
                'name' => $manager->name(),
                'description' => $manager->description(),
                'enabled' => $manager->enabled(),
                'requires' => $manager->requires(),
                'dependents' => ModuleManager::all()
                    ->filter(fn ($x) => in_array($key, $x->requires(), true))
                    ->keys()
                    ->all(),
                'version' => $manifest['version'] ?? null,
                'settings_schema' => $manifest['settings_schema'] ?? [],
                'has_settings' => ! empty($manifest['settings_schema']),
                'in_modules_dir' => isset($manifests[$key]),
            ];
        }

        return array_filter($groups, fn ($g) => ! empty($g['modules']));
    }

    public function toggleModule(string $key, bool $enable): void
    {
        if (! ModuleManager::for($key)->exists()) {
            Notification::make()->title('Невідомий модуль')->danger()->send();

            return;
        }

        // Prevent disabling if other enabled modules depend on this one
        if (! $enable) {
            $activeDependents = ModuleManager::all()
                ->filter(fn ($x) => in_array($key, $x->requires(), true) && $x->enabled())
                ->keys();
            if ($activeDependents->isNotEmpty()) {
                Notification::make()
                    ->title('Не можна вимкнути')
                    ->body("Від «{$key}» залежать активні модулі: ".$activeDependents->implode(', '))
                    ->danger()
                    ->send();

                return;
            }
        }

        // Persist enabled flag first (runner needs the DB row)
        Module::updateOrCreate(
            ['key' => $key],
            [
                'enabled' => $enable,
                'enabled_at' => $enable ? now() : null,
                'disabled_at' => $enable ? null : now(),
            ]
        );

        // Run lifecycle: install/upgrade/boot or disable
        $report = $enable
            ? ModuleLifecycleRunner::onEnable($key)
            : ModuleLifecycleRunner::onDisable($key);

        // Log activity
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

        // Build notification body with what happened
        $bodyParts = [];
        if (! empty($report['actions'])) {
            $bodyParts[] = 'Hooks: '.implode(', ', $report['actions']);
        }
        if (! empty($report['from_version']) && ! empty($report['to_version'])
            && $report['from_version'] !== $report['to_version']) {
            $bodyParts[] = "Версія: {$report['from_version']} → {$report['to_version']}";
        }
        if (empty($bodyParts)) {
            $bodyParts[] = $enable
                ? 'Routes/views зареєстровано.'
                : 'Sidebar/routes приховано. Дані в БД залишилися.';
        }
        if (! empty($report['errors'])) {
            Notification::make()
                ->title($enable ? "Увімкнено з помилками: {$key}" : "Вимкнено з помилками: {$key}")
                ->body(implode("\n", $report['errors']))
                ->warning()
                ->persistent()
                ->send();

            return;
        }

        Notification::make()
            ->title($enable ? "✓ Увімкнено: {$key}" : "Вимкнено: {$key}")
            ->body(implode("\n", $bodyParts))
            ->success()
            ->send();
    }

    public function saveModuleSettings(string $key): void
    {
        if (! ModuleManager::for($key)->exists()) {
            return;
        }

        $values = $this->settings[$key] ?? [];

        Module::updateOrCreate(
            ['key' => $key],
            ['settings' => $values]
        );

        ModuleActivityLogger::log($key, 'settings_saved', ['settings' => $values]);

        ModuleManager::clearCache();
        Artisan::call('responsecache:clear');

        Notification::make()
            ->title("Налаштування збережено: {$key}")
            ->success()
            ->send();
    }
}
