<?php

namespace App\Filament\Pages;

use App\Models\Module;
use App\Services\Integrations\IntegrationManager;
use App\Services\Marketplace\LicenseClient;
use App\Support\ModuleDiscovery;
use App\Support\ModuleManager;
use App\Support\Modules\ModuleActivityLogger;
use App\Support\Modules\ModuleInstaller;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * Marketplace UI — каталог-вітрина усіх модулів магазину з картками
 * (іконка / опис / версія / категорія / стан) і one-click діями.
 *
 * Доповнює просту toggle-сторінку /admin/modules красивим каталогом:
 * картки згруповані по категорії, badge для стану, кнопки
 * Увімкнути / Вимкнути / Деталі / Експорт + ZIP-upload секція
 * (reuse ModuleInstaller::previewFromZip + installFromZip).
 *
 * Авто-discover'иться Filament discoverPages() — НЕ потребує правок
 * AdminPanelProvider. Уся логіка спирається на існуючі сервіси:
 *   - App\Support\ModuleManager           (стан enabled, all())
 *   - App\Support\ModuleDiscovery         (manifests з module.json)
 *   - App\Support\Modules\ModuleInstaller (export / preview / install ZIP)
 *
 * @see App\Filament\Pages\ModuleSettings  — toggle-логіка взята звідти
 * @see App\Filament\Pages\ModuleDetail    — Деталі → /admin/modules/view
 */
class ModuleMarketplace extends Page
{
    use WithFileUploads;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Система';

    protected static ?string $navigationLabel = 'Розширення';

    protected static ?string $title = 'Розширення';

    protected static ?int $navigationSort = 18;

    protected static ?string $slug = 'module-marketplace';

    protected static string $view = 'filament.pages.module-marketplace';

    /** Uploaded ZIP for installFromZip action. */
    public $installZip = null;

    /** Force-overwrite existing module of the same name. */
    public bool $installForce = false;

    /** Dry-run preview результат (populated by previewInstall). */
    public ?array $installPreview = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->is_admin === true;
    }

    /**
     * Канонічна сторінка «Розширення» — єдина точка для модулів, інтеграцій
     * і майбутнього магазину. Старі сторінки «Модулі» (ModuleSettings) та
     * «Інтеграції» (IntegrationsPage) приховані з меню, але доступні за URL.
     */
    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    /**
     * Категорії-вітрина. Кожна — label + icon + список карток модулів.
     * Карта key→категорія співпадає з ModuleSettings; усе інше падає у 'tools'.
     *
     * @return array<string,array{label:string,icon:string,modules:array<int,array<string,mixed>>}>
     */
    /**
     * Уніфікована вітрина: модулі (ModuleManager) + інтеграції
     * (IntegrationManager) розкладені в ОДНУ таксономію категорій. Кожна
     * картка має 'type' => 'module' | 'integration'.
     *
     * @return array<string,array{label:string,icon:string,items:array<int,array<string,mixed>>}>
     */
    public function getCategories(): array
    {
        $groups = [
            'payments' => ['label' => 'Платежі', 'icon' => 'heroicon-o-credit-card', 'items' => []],
            'shipping' => ['label' => 'Доставка', 'icon' => 'heroicon-o-truck', 'items' => []],
            'fiscal' => ['label' => 'Фіскалізація', 'icon' => 'heroicon-o-receipt-percent', 'items' => []],
            'inventory' => ['label' => 'Склад / B2B', 'icon' => 'heroicon-o-cube', 'items' => []],
            'marketing' => ['label' => 'Маркетинг / лояльність', 'icon' => 'heroicon-o-megaphone', 'items' => []],
            'analytics' => ['label' => 'Аналітика', 'icon' => 'heroicon-o-chart-bar', 'items' => []],
            'communication' => ['label' => 'Комунікація / сповіщення', 'icon' => 'heroicon-o-chat-bubble-left-right', 'items' => []],
            'content' => ['label' => 'Контент / SEO', 'icon' => 'heroicon-o-document-text', 'items' => []],
            'search' => ['label' => 'Пошук', 'icon' => 'heroicon-o-magnifying-glass', 'items' => []],
            'auto' => ['label' => 'Auto-parts', 'icon' => 'heroicon-o-wrench-screwdriver', 'items' => []],
            'tools' => ['label' => 'Інструменти', 'icon' => 'heroicon-o-cog-6-tooth', 'items' => []],
        ];

        // --- Модулі ---
        $categoryMap = [
            'novaposhta' => 'shipping', 'ukrposhta' => 'shipping', 'rozetka_delivery' => 'shipping',
            'meest_express' => 'shipping', 'shipping_core' => 'shipping',
            'multi_warehouse' => 'inventory', 'wholesale' => 'inventory', 'batch_editor' => 'inventory',
            'loyalty' => 'marketing', 'coupons' => 'marketing', 'reviews' => 'marketing',
            'comparison' => 'marketing', 'feed_export' => 'marketing', 'wishlist' => 'marketing',
            'recently_viewed' => 'marketing', 'related_products' => 'marketing',
            'stock_notifications' => 'communication', 'callback' => 'communication', 'telegram_notify' => 'communication',
            'blog' => 'content', 'cms_pages' => 'content', 'info_pages' => 'content', 'faq' => 'content',
            'seo' => 'content', 'homepage_builder' => 'content', 'email_templates' => 'content',
            'theme_settings' => 'content', 'ai_content' => 'content',
            'search' => 'search',
            'payments' => 'payments', 'currency' => 'payments',
            'fiscal_checkbox' => 'fiscal',
            'gazu_garage' => 'auto', 'auto_parts_seed' => 'auto',
            'quick_fill' => 'tools', 'cache_manager' => 'tools', 'image_optimization' => 'tools',
            'error_pages' => 'tools', 'integrations' => 'tools',
        ];

        $manifests = ModuleDiscovery::manifests();

        foreach (ModuleManager::all() as $key => $manager) {
            $manifest = $manifests[$key] ?? [];
            $group = $manifest['category'] ?? $categoryMap[$key] ?? 'tools';
            if (! isset($groups[$group])) {
                $group = 'tools';
            }

            $groups[$group]['items'][] = [
                'type' => 'module',
                'key' => $key,
                'name' => $manifest['label'] ?? $manager->name(),
                'description' => $manifest['description'] ?? $manager->description(),
                'enabled' => $manager->enabled(),
                'version' => $manifest['version'] ?? null,
                'icon_emoji' => $manifest['icon'] ?? null,
                'requires' => $manager->requires(),
                'in_modules_dir' => isset($manifests[$key]),
                'config_url' => url('/admin/modules/view?key='.$key),
            ];
        }

        // --- Інтеграції ---
        $intGroupMap = [
            'payments' => 'payments', 'shipping' => 'shipping', 'fiscal' => 'fiscal',
            'analytics' => 'analytics', 'marketing' => 'marketing', 'communication' => 'communication',
            'search' => 'search', 'tools' => 'tools',
        ];

        foreach (app(IntegrationManager::class)->all() as $key => $integration) {
            $group = $intGroupMap[$integration->getGroup()] ?? 'tools';
            $status = method_exists($integration, 'getStatus') ? $integration->getStatus() : [];
            $configUrl = null;
            try {
                $configUrl = $integration->getSettingsRoute() ?: $integration->getGenericConfigUrl();
            } catch (\Throwable) {
                $configUrl = null;
            }

            $groups[$group]['items'][] = [
                'type' => 'integration',
                'key' => $key,
                'name' => $integration->getName(),
                'description' => $integration->getDescription(),
                'enabled' => $integration->isEnabled(),
                'icon_emoji' => $integration->getIcon(),
                'status_level' => $status['level'] ?? null,
                'status_message' => $status['message'] ?? null,
                'config_url' => $configUrl,
            ];
        }

        // Sort each category: enabled first, then alpha by name.
        foreach ($groups as &$g) {
            usort($g['items'], function ($a, $b) {
                if ($a['enabled'] !== $b['enabled']) {
                    return $a['enabled'] ? -1 : 1;
                }

                return strcmp((string) $a['name'], (string) $b['name']);
            });
        }
        unset($g);

        return array_filter($groups, fn ($g) => ! empty($g['items']));
    }

    public function getStats(): array
    {
        $modules = ModuleManager::all();
        $integrations = collect(app(IntegrationManager::class)->all());

        return [
            'total' => $modules->count() + $integrations->count(),
            'enabled' => $modules->filter(fn ($m) => $m->enabled())->count()
                + $integrations->filter(fn ($i) => $i->isEnabled())->count(),
            'modules' => $modules->count(),
            'integrations' => $integrations->count(),
        ];
    }

    /**
     * Магазин розширень (ліцензійний сервер Lionex). Зараз — стуб-каталог.
     *
     * @return array<int,array<string,mixed>>
     */
    public function getStoreCatalog(): array
    {
        return app(LicenseClient::class)->catalog();
    }

    public function isStoreConfigured(): bool
    {
        return app(LicenseClient::class)->isConfigured();
    }

    /**
     * Увімкнути/вимкнути інтеграцію прямо з вітрини (логіку взято з
     * IntegrationsPage::toggleIntegration).
     */
    public function toggleIntegration(string $key): void
    {
        $integration = app(IntegrationManager::class)->get($key);
        if (! $integration) {
            Notification::make()->title('Невідома інтеграція')->danger()->send();

            return;
        }

        if ($integration->isEnabled()) {
            $integration->disable();
            \Illuminate\Support\Facades\Cache::forget("shop_setting_integration_{$key}_enabled");
            Notification::make()->title("{$integration->getName()} вимкнено")->warning()->send();
        } else {
            $integration->enable();
            \Illuminate\Support\Facades\Cache::forget("shop_setting_integration_{$key}_enabled");
            Notification::make()->title("{$integration->getName()} увімкнено")->success()->send();
        }
    }

    /**
     * Купівля розширення з ліцензійного сервера — стуб (див. LicenseClient).
     */
    public function purchaseModule(string $key): void
    {
        $result = app(LicenseClient::class)->purchase($key);

        Notification::make()
            ->title($result['ok'] ? 'Готово' : 'Магазин розширень')
            ->body($result['message'])
            ->{$result['ok'] ? 'success' : 'info'}()
            ->send();
    }

    /**
     * One-click toggle. Прямий Module::updateOrCreate + clearCache + rebuild
     * Filament-кешу (інакше вимкнений модуль лишається в сайдбарі — кеш
     * bootstrap/cache/filament має пріоритет над живою реєстрацією).
     */
    public function toggleModule(string $key, bool $enable): void
    {
        $manager = ModuleManager::for($key);

        if (! $manager->exists()) {
            Notification::make()->title('Невідомий модуль')->danger()->send();

            return;
        }

        // Enable: переконатися що required-залежності увімкнені (каскадно).
        if ($enable) {
            foreach ($manager->requires() as $depKey) {
                $dep = ModuleManager::for($depKey);
                if (! $dep->exists()) {
                    Notification::make()
                        ->title("Бракує залежності «{$depKey}»")
                        ->body("Модуль «{$key}» потребує «{$depKey}», який не встановлено.")
                        ->danger()
                        ->send();

                    return;
                }
                if (! $dep->enabled()) {
                    $this->toggleModule($depKey, true);
                }
            }
        }

        // Disable: блокуємо якщо є активні залежні модулі.
        if (! $enable) {
            $activeDependents = ModuleManager::all()
                ->filter(fn ($x) => in_array($key, $x->requires(), true) && $x->enabled())
                ->keys()
                ->all();

            if (! empty($activeDependents)) {
                Notification::make()
                    ->title('Не можна вимкнути')
                    ->body("Від «{$key}» залежать активні модулі: ".implode(', ', $activeDependents).'. Спершу вимкніть їх.')
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

        ModuleActivityLogger::log($key, $enable ? 'enabled' : 'disabled', [
            'source' => 'marketplace',
        ]);

        ModuleManager::clearCache();
        Artisan::call('responsecache:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');
        $this->rebuildFilamentCache();

        Notification::make()
            ->title($enable ? "Увімкнено: {$key}" : "Вимкнено: {$key}")
            ->success()
            ->send();

        // Full reload — скидаємо stale Livewire-снапшоти що посилаються на
        // віджети/ресурси щойно перемкнутого модуля.
        $this->redirect(url('/admin/module-marketplace'), navigate: false);
    }

    /**
     * Dry-run preview перед install — показує що буде створено.
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
                ->title('Preview не вдався')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Install з ZIP — reuse ModuleInstaller::installFromZip.
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

        Module::updateOrCreate(['key' => $result['key']], [
            'enabled' => false, // safe default — admin вирішує коли вмикати
            'enabled_at' => null,
        ]);

        ModuleActivityLogger::log($result['key'], 'installed_from_zip', [
            'action' => $result['action'],
            'version' => $result['version'],
            'force' => $this->installForce,
            'source' => 'marketplace',
        ]);

        ModuleManager::clearCache();
        $this->rebuildFilamentCache();

        $this->installZip = null;
        $this->installForce = false;
        $this->installPreview = null;

        Notification::make()
            ->title("Модуль «{$result['key']}» {$result['action']}")
            ->body('Знайди картку у каталозі і натисни «Увімкнути».')
            ->success()
            ->send();

        $this->redirect(url('/admin/module-marketplace'), navigate: false);
    }

    /**
     * Експорт модуля у ZIP — reuse ModuleInstaller::exportToZip.
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
     * Без цього вимкнений модуль лишається у сайдбарі (закешований
     * bootstrap/cache/filament має пріоритет над живою реєстрацією).
     */
    private function rebuildFilamentCache(): void
    {
        try {
            Artisan::call('filament:clear-cached-components');
        } catch (\Throwable) {
            // команда може бути відсутня у деяких версіях — не критично
        }
        try {
            Artisan::call('filament:cache-components');
        } catch (\Throwable $e) {
            \Log::warning('[ModuleMarketplace] filament:cache-components failed: '.$e->getMessage());
        }
    }
}
