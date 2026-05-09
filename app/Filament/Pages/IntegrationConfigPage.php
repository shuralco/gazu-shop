<?php

namespace App\Filament\Pages;

use App\Services\Integrations\IntegrationManager;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;

/**
 * Universal settings page for any integration that uses the standard
 * config-fields pattern (LiqPay, Monobank, WayForPay, Meest, Telegram, etc.).
 *
 * Drives form layout from $integration->getConfigFields(), handles save
 * via setConfig(), shows the same status header + enable/disable toggle
 * as bespoke pages (NovaPoshtaSettings / UkrPoshtaSettings).
 *
 * Routed at /admin/integration-config/{key}.
 */
class IntegrationConfigPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $title = 'Налаштування модуля';

    protected static string $view = 'filament.pages.integration-config';

    protected static bool $shouldRegisterNavigation = false;

    public string $key = '';

    public ?array $data = [];

    public static function getSlug(): string
    {
        return 'integration-config/{key}';
    }

    public function mount(string $key): void
    {
        $this->key = $key;

        $integration = $this->getIntegration();
        if (! $integration) {
            abort(404, "Integration «{$key}» not registered");
        }

        $this->data = $integration->getConfig();

        // Fill defaults
        foreach ($integration->getConfigFields() as $field) {
            $k = $field['key'] ?? null;
            if ($k && ! isset($this->data[$k])) {
                $this->data[$k] = $field['default'] ?? ($field['type'] === 'toggle' ? false : '');
            }
        }

        $this->form->fill($this->data);
    }

    public function getTitle(): string
    {
        $i = $this->getIntegration();

        return $i ? "{$i->getIcon()} Налаштування — {$i->getName()}" : 'Налаштування модуля';
    }

    protected function getIntegration()
    {
        return app(IntegrationManager::class)->get($this->key);
    }

    public function getModuleStatus(): array
    {
        $i = $this->getIntegration();
        if (! $i) {
            return ['enabled' => false, 'level' => 'unknown', 'message' => 'Не зареєстровано'];
        }
        $st = $i->getStatus();

        return [
            'enabled' => $i->isEnabled(),
            'level' => $st['level'],
            'message' => $st['message'],
            'name' => $i->getName(),
            'icon' => $i->getIcon(),
            'description' => $i->getDescription(),
        ];
    }

    public function toggleModule(): void
    {
        $i = $this->getIntegration();
        if (! $i) {
            return;
        }

        if ($i->isEnabled()) {
            $i->disable();
            Cache::forget("shop_setting_integration_{$this->key}_enabled");
            Notification::make()->title("{$i->getName()} вимкнено")->warning()->send();
        } else {
            $i->enable();
            Cache::forget("shop_setting_integration_{$this->key}_enabled");
            Notification::make()->title("{$i->getName()} увімкнено")->success()->send();
        }
    }

    public function form(Form $form): Form
    {
        $i = $this->getIntegration();
        if (! $i) {
            return $form->schema([]);
        }

        $components = [];
        foreach ($i->getConfigFields() as $field) {
            $components[] = $this->buildField($field);
        }

        return $form
            ->schema([
                Forms\Components\Section::make('Конфігурація')
                    ->description('Параметри підключення модуля')
                    ->schema($components)
                    ->columns(2),
            ])
            ->statePath('data');
    }

    protected function buildField(array $field): Forms\Components\Component
    {
        $key = $field['key'];
        $label = $field['label'] ?? $key;
        $placeholder = $field['placeholder'] ?? '';
        $type = $field['type'] ?? 'text';

        return match ($type) {
            'toggle' => Forms\Components\Toggle::make($key)->label($label)->default($field['default'] ?? false),
            'password' => Forms\Components\TextInput::make($key)->label($label)->password()->revealable()->placeholder($placeholder),
            'textarea' => Forms\Components\Textarea::make($key)->label($label)->placeholder($placeholder)->rows(3)->columnSpanFull(),
            'select' => Forms\Components\Select::make($key)->label($label)->options($field['options'] ?? []),
            default => Forms\Components\TextInput::make($key)->label($label)->placeholder($placeholder),
        };
    }

    public function save(): void
    {
        $i = $this->getIntegration();
        if (! $i) {
            return;
        }

        $data = $this->form->getState();
        $i->setConfig($data);
        Cache::forget("shop_setting_integration_{$this->key}_config");

        Notification::make()
            ->title("Налаштування {$i->getName()} збережено")
            ->success()
            ->send();
    }
}
