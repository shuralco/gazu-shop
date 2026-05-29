<?php

namespace App\Filament\Pages;

use App\Models\DisplaySetting;
use App\Services\Integrations\IntegrationManager;
use App\Services\UkrPoshtaApiService;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;

class UkrPoshtaSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Налаштування УП';

    protected static ?string $title = 'Налаштування УкрПошти';

    protected static ?string $navigationGroup = 'Доставка та оплата';

    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.pages.ukr-poshta-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $rows = DisplaySetting::where('group', 'ukrposhta')->get(['key', 'value', 'type']);
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row->key] = $this->castSettingValue($row->value, $row->type);
        }
        $this->data = $settings;
        $this->form->fill($this->data);
    }

    protected function castSettingValue(mixed $value, ?string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'json' => is_string($value) ? (json_decode($value, true) ?? []) : (is_array($value) ? $value : []),
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'number' => is_numeric($value) ? $value + 0 : null,
            default => $value,
        };
    }

    public function getModuleStatus(): array
    {
        $integration = app(IntegrationManager::class)->get('ukrposhta');
        if (! $integration) {
            return ['enabled' => false, 'level' => 'unknown', 'message' => 'Інтеграцію не зареєстровано'];
        }
        $status = $integration->getStatus();

        return [
            'enabled' => $integration->isEnabled(),
            'level' => $status['level'],
            'message' => $status['message'],
        ];
    }

    public function toggleModule(): void
    {
        $integration = app(IntegrationManager::class)->get('ukrposhta');
        if (! $integration) {
            return;
        }
        if ($integration->isEnabled()) {
            $integration->disable();
            Cache::forget('shop_setting_integration_ukrposhta_enabled');
            Notification::make()->title('Модуль УкрПошта вимкнено')->warning()->send();
        } else {
            $integration->enable();
            Cache::forget('shop_setting_integration_ukrposhta_enabled');
            Notification::make()->title('Модуль УкрПошта увімкнено')->success()->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('diagnose')
                ->label('Діагностика модуля')
                ->icon('heroicon-o-heart')
                ->color('info')
                ->action(function () {
                    $report = $this->runDiagnostics();
                    $allOk = collect($report)->every(fn ($r) => $r['ok']);
                    $body = '';
                    foreach ($report as $check) {
                        $icon = $check['ok'] ? '✅' : '❌';
                        $body .= "{$icon} {$check['title']}: {$check['message']}\n";
                    }
                    Notification::make()
                        ->title($allOk ? '✅ Усе працює' : '⚠️ Знайдено проблеми')
                        ->body($body)
                        ->color($allOk ? 'success' : 'warning')
                        ->duration(20000)
                        ->send();
                }),

            \Filament\Actions\Action::make('view_logs')
                ->label('API логи')
                ->icon('heroicon-o-bug-ant')
                ->color('gray')
                ->visible(fn () => class_exists(\App\Filament\Resources\NpApiLogResource::class) && \Illuminate\Support\Facades\Route::has('filament.admin.resources.shipping-api-logs.index'))
                ->url(fn () => \Illuminate\Support\Facades\Route::has('filament.admin.resources.shipping-api-logs.index')
                    ? \App\Filament\Resources\NpApiLogResource::getUrl('index').'?tableFilters[provider][values][0]=ukrposhta'
                    : null),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('ukrposhta_settings')
                    ->tabs([
                        $this->apiTab(),
                        $this->shipmentParamsTab(),
                        $this->deliveryTab(),
                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString(),
            ])
            ->statePath('data');
    }

    protected function apiTab(): Forms\Components\Tabs\Tab
    {
        return Forms\Components\Tabs\Tab::make('API')
            ->icon('heroicon-o-key')
            ->schema([
                Forms\Components\Section::make('Address Classifier (публічний)')
                    ->description('Address Classifier — безкоштовний public API УкрПошти для довідника адрес. Не потребує ключа.')
                    ->schema([
                        Forms\Components\Placeholder::make('api_url_info')
                            ->label('URL')
                            ->content(fn () => config('ukrposhta.api', 'https://www.ukrposhta.ua/')),

                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('test_classifier')
                                ->label('Перевірити Address Classifier')
                                ->icon('heroicon-o-signal')
                                ->color('info')
                                ->action(function () {
                                    $this->testClassifier();
                                }),
                        ]),
                    ]),

                Forms\Components\Section::make('Bearer Token (для TTN)')
                    ->description('Опціонально. Якщо є валідний Bearer Token, можна реалізувати створення ТТН через ecom API. Зараз ecom API повертає 404 — функція недоступна без правильних ключів.')
                    ->schema([
                        Forms\Components\TextInput::make('up_bearer_token')
                            ->label('Bearer Token')
                            ->password()
                            ->revealable()
                            ->placeholder('UUID-формат, наприклад 5a2c62b3-c867-...'),

                        Forms\Components\TextInput::make('up_counterparty_token')
                            ->label('Counterparty Token')
                            ->password()
                            ->revealable(),

                        Forms\Components\Toggle::make('up_debug_mode')
                            ->label('Режим відлагодження')
                            ->helperText('Логувати всі API виклики (вкл. успішні)'),
                    ])
                    ->columns(2),
            ]);
    }

    protected function shipmentParamsTab(): Forms\Components\Tabs\Tab
    {
        return Forms\Components\Tabs\Tab::make('Параметри')
            ->icon('heroicon-o-cube')
            ->schema([
                Forms\Components\Section::make('Тариф (флет)')
                    ->description('Тарифікація doormat — UkrPoshta має фіксовану таблицю тарифів. Тут можна задати локальний multiplier для розрахунку.')
                    ->schema([
                        Forms\Components\TextInput::make('up_base_cost')
                            ->label('Базова вартість (грн)')
                            ->numeric()
                            ->step(0.5)
                            ->minValue(0)
                            ->default(45)
                            ->helperText('Стартова сума для до-1кг посилки'),

                        Forms\Components\TextInput::make('up_per_kg_cost')
                            ->label('За кожен кг (грн)')
                            ->numeric()
                            ->step(0.5)
                            ->minValue(0)
                            ->default(8),

                        Forms\Components\TextInput::make('up_max_weight')
                            ->label('Макс. вага (кг)')
                            ->numeric()
                            ->step(0.1)
                            ->minValue(0)
                            ->default(30),

                        Forms\Components\TextInput::make('up_max_dimension')
                            ->label('Макс. сума габаритів (см)')
                            ->numeric()
                            ->minValue(0)
                            ->default(120)
                            ->helperText('довжина+ширина+висота'),

                        Forms\Components\TextInput::make('up_free_shipping_threshold')
                            ->label('Поріг безкоштовної доставки (грн)')
                            ->numeric()
                            ->minValue(0)
                            ->placeholder('0 = вимкнено')
                            ->default(2000),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Опис відправлення')
                    ->schema([
                        Forms\Components\Textarea::make('up_description_template')
                            ->label('Шаблон опису')
                            ->placeholder('Замовлення #{order_id}')
                            ->helperText('Макроси: {order_id}, {products}, {total}, {customer_name}')
                            ->default('Замовлення #{order_id}')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    protected function deliveryTab(): Forms\Components\Tabs\Tab
    {
        return Forms\Components\Tabs\Tab::make('Доставка')
            ->icon('heroicon-o-truck')
            ->schema([
                Forms\Components\Section::make('Типи доставки')
                    ->schema([
                        Forms\Components\Toggle::make('up_enable_branch')
                            ->label('Доставка у відділення (стандарт)')
                            ->default(true)
                            ->helperText('ПВ + ВПЗ типи відділень'),

                        Forms\Components\Toggle::make('up_enable_courier')
                            ->label('Курʼєрська доставка')
                            ->helperText('Доставка на адресу — потребує ecom API'),

                        Forms\Components\Toggle::make('up_enable_express')
                            ->label('Експрес-доставка')
                            ->helperText('Швидкі відправлення Експрес — потребує ecom API'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Відображення на сайті')
                    ->schema([
                        Forms\Components\Toggle::make('up_show_shipping_cost')
                            ->label('Показувати розрахунок вартості')
                            ->default(true),

                        Forms\Components\Toggle::make('up_show_delivery_estimate')
                            ->label('Показувати орієнтовну дату доставки'),
                    ])
                    ->columns(2),
            ]);
    }

    public function testClassifier(): void
    {
        try {
            $result = app(UkrPoshtaApiService::class)->ping();
            if ($result['success']) {
                $sample = collect($result['sample'] ?? [])
                    ->map(fn ($r) => is_object($r) ? ($r->REGION_UA ?? $r->REGION_NAME_UA ?? '?') : '?')
                    ->take(3)
                    ->implode(', ');
                Notification::make()
                    ->title('Address Classifier OK')
                    ->body("Знайдено областей: {$result['count']}. Приклад: {$sample}")
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Address Classifier недоступний')
                    ->body($result['error'] ?? 'невідома помилка')
                    ->danger()
                    ->send();
            }
        } catch (\Throwable $e) {
            Notification::make()->title('Помилка')->body($e->getMessage())->danger()->send();
        }
    }

    protected function runDiagnostics(): array
    {
        $report = [];
        $api = config('ukrposhta.api');
        $report[] = [
            'title' => 'API URL',
            'ok' => ! empty($api),
            'message' => $api ?: 'не налаштовано',
        ];

        try {
            $ping = app(UkrPoshtaApiService::class)->ping();
            $report[] = [
                'title' => 'Address Classifier',
                'ok' => $ping['success'] ?? false,
                'message' => ($ping['success'] ?? false) ? "OK — {$ping['count']} областей" : ($ping['error'] ?? 'недоступно'),
            ];
        } catch (\Throwable $e) {
            $report[] = ['title' => 'Address Classifier', 'ok' => false, 'message' => $e->getMessage()];
        }

        $bearer = DisplaySetting::get('up_bearer_token') ?: config('ukrposhta.bearer_token');
        $report[] = [
            'title' => 'Bearer Token',
            'ok' => ! empty($bearer),
            'message' => $bearer ? substr((string) $bearer, 0, 8).'…' : 'не задано (TTN недоступне)',
        ];

        if (\Schema::hasTable('shipping_api_logs')) {
            $errorsToday = \App\Models\ShippingApiLog::forProvider('ukrposhta')
                ->where('success', false)
                ->where('created_at', '>=', now()->subDay())
                ->count();
            $report[] = [
                'title' => 'Помилки за 24 год',
                'ok' => $errorsToday === 0,
                'message' => $errorsToday === 0 ? 'немає' : "{$errorsToday} запитів з помилками",
            ];
        }

        return $report;
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'up_')) {
                DisplaySetting::updateOrCreate(
                    ['key' => $key],
                    [
                        'value' => is_array($value) ? json_encode($value) : $value,
                        'group' => 'ukrposhta',
                        'type' => match (true) {
                            is_bool($value) => 'boolean',
                            is_array($value) => 'json',
                            is_numeric($value) => 'number',
                            default => 'string',
                        },
                        'is_active' => true,
                        'title' => ucfirst(str_replace(['up_', '_'], ['', ' '], $key)),
                    ]
                );
            }
        }

        DisplaySetting::flushSettingsCache();

        Notification::make()
            ->title('Збережено')
            ->body('Налаштування УкрПошти оновлено.')
            ->success()
            ->send();
    }
}
