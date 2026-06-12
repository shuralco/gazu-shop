<?php

namespace App\Filament\Pages;

use App\Services\Integrations\IntegrationManager;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;

class IntegrationsPage extends Page
{
    use \App\Filament\Concerns\GatedPage;

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static ?string $navigationLabel = 'Інтеграції';

    protected static ?string $title = 'Інтеграції та сервіси';

    protected static ?string $navigationGroup = 'Система';

    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.pages.integrations';

    /**
     * Приховано з меню — обʼєднано в єдину сторінку «Розширення»
     * (App\Filament\Pages\ModuleMarketplace). Лишається доступною за URL
     * /admin/integrations-page. Перекриває GatedPage::shouldRegisterNavigation.
     */
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public array $configData = [];

    public ?string $editingIntegration = null;

    public string $search = '';

    public string $filter = 'all'; // all|enabled|disabled|ok|warning|error

    public function getIntegrationManager(): IntegrationManager
    {
        return app(IntegrationManager::class);
    }

    public function getGroups(): array
    {
        return $this->getIntegrationManager()->getGroups();
    }

    public function getIntegrationsByGroup(string $group): array
    {
        $list = $this->getIntegrationManager()->getByGroup($group)->all();
        $needle = mb_strtolower(trim($this->search));

        return collect($list)->filter(function ($i) use ($needle) {
            // Search filter
            if ($needle !== '') {
                $haystack = mb_strtolower($i->getName().' '.$i->getDescription().' '.$i->getKey());
                if (! str_contains($haystack, $needle)) {
                    return false;
                }
            }

            // Status / enabled filters
            $enabled = $i->isEnabled();
            $level = $i->getStatus()['level'] ?? 'unknown';

            return match ($this->filter) {
                'enabled' => $enabled,
                'disabled' => ! $enabled,
                'ok' => $level === 'ok',
                'warning' => $level === 'warning',
                'error' => $level === 'error',
                default => true,
            };
        })->values()->all();
    }

    public function getMatchedTotal(): int
    {
        $total = 0;
        foreach (array_keys($this->getGroups()) as $g) {
            $total += count($this->getIntegrationsByGroup($g));
        }

        return $total;
    }

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
    }

    public function clearSearch(): void
    {
        $this->search = '';
    }

    public function toggleIntegration(string $key): void
    {
        $integration = $this->getIntegrationManager()->get($key);
        if (! $integration) {
            return;
        }

        if ($integration->isEnabled()) {
            $integration->disable();
            Cache::forget("shop_setting_integration_{$key}_enabled");

            Notification::make()
                ->title("{$integration->getName()} вимкнено")
                ->warning()
                ->send();
        } else {
            $integration->enable();
            Cache::forget("shop_setting_integration_{$key}_enabled");

            Notification::make()
                ->title("{$integration->getName()} увімкнено")
                ->success()
                ->send();
        }
    }

    public function openConfig(string $key): void
    {
        $integration = $this->getIntegrationManager()->get($key);
        if (! $integration) {
            return;
        }

        $this->editingIntegration = $key;
        $this->configData = $integration->getConfig();

        // Fill defaults for toggle fields that are not yet set
        foreach ($integration->getConfigFields() as $field) {
            if ($field['type'] === 'toggle' && ! isset($this->configData[$field['key']])) {
                $this->configData[$field['key']] = $field['default'] ?? false;
            }
        }

        $this->dispatch('open-modal', id: 'integration-config');
    }

    public function saveConfig(): void
    {
        if (! $this->editingIntegration) {
            return;
        }

        $integration = $this->getIntegrationManager()->get($this->editingIntegration);
        if (! $integration) {
            return;
        }

        $integration->setConfig($this->configData);
        Cache::forget("shop_setting_integration_{$this->editingIntegration}_config");

        Notification::make()
            ->title("Налаштування {$integration->getName()} збережено")
            ->success()
            ->send();

        $this->editingIntegration = null;
        $this->configData = [];
        $this->dispatch('close-modal', id: 'integration-config');
    }

    public function closeConfig(): void
    {
        $this->editingIntegration = null;
        $this->configData = [];
        $this->dispatch('close-modal', id: 'integration-config');
    }

    public function testTelegram(): void
    {
        $telegram = $this->getIntegrationManager()->get('telegram');
        if (! $telegram || ! $telegram->isEnabled()) {
            Notification::make()
                ->title('Telegram бот не увімкнений')
                ->danger()
                ->send();

            return;
        }

        $service = app(\App\Services\TelegramService::class);
        $result = $service->sendMessage("✅ Тестове повідомлення від SimpleShop\n\n📅 ".now()->format('d.m.Y H:i:s'));

        if ($result) {
            Notification::make()
                ->title('Повідомлення надіслано успішно!')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Помилка відправки повідомлення')
                ->body('Перевірте Bot Token та Chat ID')
                ->danger()
                ->send();
        }
    }
}
