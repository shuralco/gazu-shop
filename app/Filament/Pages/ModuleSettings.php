<?php

namespace App\Filament\Pages;

use App\Models\Module;
use App\Support\ModuleDiscovery;
use App\Support\ModuleManager;
use App\Support\Modules\ModuleActivityLogger;
use App\Support\Modules\ModuleInstaller;
use App\Support\Modules\ModuleLifecycleRunner;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * Admin UI to toggle modules + edit per-module settings without CLI.
 *
 * Persists to `modules` DB table (Phase 1 plugin system) via
 * App\Models\Module — no .env editing. ModuleObserver auto-invalidates
 * cache so the change takes effect on the next request.
 */
class ModuleSettings extends Page
{
    use WithFileUploads;

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static ?string $navigationGroup = 'Система';

    protected static ?string $navigationLabel = 'Модулі';

    protected static ?string $title = 'Модулі магазину';

    protected static ?int $navigationSort = 20;

    protected static ?string $slug = 'modules';

    protected static string $view = 'filament.pages.module-settings';

    /**
     * Приховано з меню — обʼєднано в єдину сторінку «Розширення»
     * (App\Filament\Pages\ModuleMarketplace). Лишається доступною за URL
     * /admin/modules заради старих закладок і прямих посилань.
     */
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    /**
     * @var array<string,array<string,mixed>>  key → settings array
     */
    public array $settings = [];

    /** Uploaded ZIP for `installFromZip` action. */
    public $installZip = null;

    /** Force-overwrite existing module of the same name. */
    public bool $installForce = false;

    /** Dry-run preview results (populated by previewInstall action). */
    public ?array $installPreview = null;

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

    public function toggleModule(string $key, bool $enable, bool $cascade = false, bool $rollbackMigrations = false): void
    {
        if (! ModuleManager::for($key)->exists()) {
            Notification::make()->title('Невідомий модуль')->danger()->send();

            return;
        }

        // Pre-enable health-gate — блокуємо коли manifest broken або folder зник.
        if ($enable) {
            $errors = ModuleLifecycleRunner::preEnableCheck($key);
            if (! empty($errors)) {
                Notification::make()
                    ->title("Не можна увімкнути «{$key}»")
                    ->body(implode("\n", $errors))
                    ->danger()
                    ->persistent()
                    ->send();
                return;
            }

            // Dependency auto-resolver — рекурсивно enable всіх що цей модуль потребує.
            // Якщо вимкнений required module існує — каскадно enable його ПЕРШИМ.
            $requires = ModuleManager::for($key)->requires();
            $missingDeps = [];
            foreach ($requires as $depKey) {
                if (! ModuleManager::for($depKey)->exists()) {
                    $missingDeps[] = $depKey;
                    continue;
                }
                if (! ModuleManager::for($depKey)->enabled()) {
                    // Recursive enable перед поточним.
                    $this->toggleModule($depKey, true, cascade: false, rollbackMigrations: false);
                }
            }
            if (! empty($missingDeps)) {
                Notification::make()
                    ->title("Бракує модулів-залежностей для «{$key}»")
                    ->body('Не встановлено: '.implode(', ', $missingDeps).'. Завантажте їх через ZIP installer.')
                    ->danger()
                    ->persistent()
                    ->send();
                return;
            }
        }

        // Disable + dependent handling. Cascade=true → каскадно disable
        // всі залежні модулі. Інакше — блокуємо як раніше.
        if (! $enable) {
            $activeDependents = ModuleManager::all()
                ->filter(fn ($x) => in_array($key, $x->requires(), true) && $x->enabled())
                ->keys()
                ->all();

            if (! empty($activeDependents)) {
                if (! $cascade) {
                    Notification::make()
                        ->title('Потрібно каскадне вимкнення')
                        ->body("Від «{$key}» залежать активні модулі: ".implode(', ', $activeDependents).". Запустіть з cascade=true.")
                        ->danger()
                        ->send();
                    return;
                }
                // Cascade — recursively disable dependents first.
                foreach ($activeDependents as $dep) {
                    $this->toggleModule($dep, false, cascade: true, rollbackMigrations: $rollbackMigrations);
                }
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

        // Run lifecycle: install/upgrade/boot or disable (з опцією rollback)
        $report = $enable
            ? ModuleLifecycleRunner::onEnable($key)
            : ModuleLifecycleRunner::onDisable($key, rollbackMigrations: $rollbackMigrations);

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
        // CRITICAL: перебудувати кеш Filament-компонентів. Без цього вимкнений
        // модуль ЛИШАЄТЬСЯ в адмін-навігації (його Resources/Pages залишаються
        // у закешованому bootstrap/cache/filament/panels/*). filament:cache-components
        // перезапускає collectModuleClasses() який гейтить по enabled().
        $this->rebuildFilamentCache();

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

        // CRITICAL: Force full page reload to drop any stale Livewire snapshots
        // that reference widgets/resources from the just-toggled module.
        // Without this — opening dashboard after disable throws
        // ComponentNotFoundException for widgets that no longer exist.
        $this->redirect(request()->header('Referer') ?: url('/admin/modules'), navigate: false);
    }

    /**
     * Dry-run preview — показує що буде створено перед install (без зміни сайту).
     */
    public function previewInstall(): void
    {
        if (! $this->installZip instanceof TemporaryUploadedFile) {
            Notification::make()->title('Спочатку оберіть ZIP-файл')->warning()->send();
            return;
        }
        try {
            $this->installPreview = ModuleInstaller::previewFromZip($this->installZip);
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Preview failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Install a module from an uploaded ZIP file. Triggered from the
     * "Завантажити модуль" form in the page header.
     */
    public function installFromZip(): void
    {
        if (! $this->installZip instanceof TemporaryUploadedFile) {
            Notification::make()->title('Спочатку оберіть ZIP-файл')->warning()->send();
            return;
        }

        try {
            $result = ModuleInstaller::installFromZip($this->installZip, $this->installForce);
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Помилка встановлення')
                ->body($e->getMessage())
                ->danger()
                ->persistent()
                ->send();
            return;
        }

        // Persist module record so /admin/modules toggle works immediately.
        Module::updateOrCreate(['key' => $result['key']], [
            'enabled' => false, // safe default — admin вирішує
            'enabled_at' => null,
        ]);

        ModuleActivityLogger::log($result['key'], 'installed_from_zip', [
            'action' => $result['action'],
            'version' => $result['version'],
            'force' => $this->installForce,
        ]);

        ModuleManager::clearCache();
        $this->rebuildFilamentCache();

        $this->installZip = null;
        $this->installForce = false;

        Notification::make()
            ->title("✓ Модуль «{$result['key']}» {$result['action']}")
            ->body('Тепер відкрий деталі модуля і натисни «Увімкнути».')
            ->success()
            ->send();

        $this->redirect(url('/admin/modules'), navigate: false);
    }

    /**
     * Permanently uninstall a module. Confirmation handled in the view via
     * Alpine modal — controller just receives `key` and `mode` ('soft'|'hard').
     */
    public function uninstallModule(string $key, string $mode = 'soft'): void
    {
        $purge = $mode === 'hard';
        try {
            $report = ModuleInstaller::uninstall($key, $purge);
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Не вдалося видалити')
                ->body($e->getMessage())
                ->danger()
                ->persistent()
                ->send();
            return;
        }

        ModuleActivityLogger::log($key, $purge ? 'purged' : 'uninstalled', [
            'files_removed' => $report['files_removed'] ?? 0,
            'tables_dropped' => $report['tables_dropped'] ?? null,
        ]);
        ModuleManager::clearCache();
        $this->rebuildFilamentCache();

        Notification::make()
            ->title($purge ? "✓ Повністю видалено «{$key}»" : "✓ Папку модуля «{$key}» видалено")
            ->body($purge
                ? "Файли ({$report['files_removed']}) + БД дані стерто. Reinstall створить чистий модуль."
                : "Файли ({$report['files_removed']}) видалено. Дані в БД залишилися — reinstall відновить доступ.")
            ->success()
            ->send();

        $this->redirect(url('/admin/modules'), navigate: false);
    }

    /**
     * Export a module as a downloadable ZIP archive.
     * Used by the "Експорт" action on each module card.
     */
    public function exportModule(string $key)
    {
        try {
            $archive = ModuleInstaller::exportToZip($key);
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Помилка експорту')
                ->body($e->getMessage())
                ->danger()
                ->send();
            return null;
        }

        return response()->download($archive, basename($archive))->deleteFileAfterSend();
    }

    /**
     * Перебудувати кеш Filament-панелі після зміни складу модулів.
     *
     * Filament кешує зареєстровані Resources/Pages/навігацію у
     * bootstrap/cache/filament/panels/*. Поки цей кеш є, він має пріоритет
     * над живою реєстрацією → вимкнений модуль ЛИШАЄТЬСЯ в сайдбарі адмінки.
     * Спершу чистимо старий кеш, потім будуємо заново (collectModuleClasses
     * гейтить по enabled() → новий кеш відображає актуальний стан).
     */
    private function rebuildFilamentCache(): void
    {
        try {
            Artisan::call('filament:clear-cached-components');
        } catch (\Throwable $e) {
            // команда може бути відсутня у деяких версіях — не критично
        }
        try {
            Artisan::call('filament:cache-components');
        } catch (\Throwable $e) {
            \Log::warning('[ModuleSettings] filament:cache-components failed: '.$e->getMessage());
        }
        // Перезавантажити воркери Octane: роути/Filament-панель реєструються лише
        // при boot воркера, тож без reload щойно ввімкнений модуль не зʼявиться
        // (його роути → 404), а вимкнений лишиться в памʼяті. SIGUSR1 graceful —
        // zero-downtime (тут лише стан БД змінився, не код, тож opcache не заважає).
        try {
            Artisan::call('octane:reload', ['--server' => 'swoole']);
        } catch (\Throwable $e) {
            \Log::warning('[ModuleSettings] octane:reload failed: '.$e->getMessage());
        }
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
