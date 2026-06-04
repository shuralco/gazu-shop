<?php

namespace App\Filament\Pages;

use App\Models\DisplaySetting;
use App\Models\NpCity;
use App\Models\NpWarehouse;
use App\Services\Integrations\IntegrationManager;
use App\Services\NovaPoshtaApiService;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class NovaPoshtaSettings extends Page implements HasForms
{
    use \App\Filament\Concerns\GatedPage;

    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Нова Пошта: налаштування';

    protected static ?string $title = 'Налаштування Нової Пошти';

    protected static ?string $navigationGroup = 'Склад і доставка';

    protected static ?int $navigationSort = 100;

    protected static string $view = 'filament.pages.nova-poshta-settings';

    public ?array $data = [];

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
                ->url(fn () => \App\Filament\Resources\NpApiLogResource::getUrl('index')),
        ];
    }

    /**
     * Run a battery of NP module health checks.
     *
     * @return array<int, array{title: string, ok: bool, message: string}>
     */
    protected function runDiagnostics(): array
    {
        $report = [];

        // 1. API key
        $apiKey = DisplaySetting::get('np_api_key')
            ?: optional(\App\Models\ShippingProvider::where('code', 'novaposhta')->first())->configuration['api_key']
            ?? config('novaposhta.api_key');

        if (empty($apiKey)) {
            $report[] = ['title' => 'API ключ', 'ok' => false, 'message' => 'не налаштовано'];
            return $report;
        }
        $report[] = ['title' => 'API ключ', 'ok' => true, 'message' => substr($apiKey, 0, 8).'…'.substr($apiKey, -4)];

        // 2. Live API ping
        try {
            $api = app(NovaPoshtaApiService::class);
            $r = $api->getCounterparties('Sender');
            if (! ($r['success'] ?? false)) {
                $report[] = [
                    'title' => 'NP API доступність',
                    'ok' => false,
                    'message' => implode('; ', $r['errors'] ?? ['невідома помилка']),
                ];
            } else {
                $sender = $r['data'][0] ?? [];
                $report[] = [
                    'title' => 'NP API доступність',
                    'ok' => true,
                    'message' => 'OK, відправник «'.($sender['Description'] ?? '—').'»',
                ];
            }
        } catch (\Throwable $e) {
            $report[] = ['title' => 'NP API доступність', 'ok' => false, 'message' => $e->getMessage()];
        }

        // 3. Sender refs
        $missing = [];
        foreach (['np_sender_ref', 'np_contact_person_ref', 'np_sender_city_ref', 'np_sender_warehouse_ref', 'np_sender_phone'] as $key) {
            if (empty(DisplaySetting::get($key))) {
                $missing[] = $key;
            }
        }
        $report[] = empty($missing)
            ? ['title' => 'Reference відправника', 'ok' => true, 'message' => 'усі заповнено']
            : ['title' => 'Reference відправника', 'ok' => false, 'message' => 'не заповнено: '.implode(', ', $missing)];

        // 4. DB sync
        $cities = \Schema::hasTable('np_cities') ? NpCity::count() : 0;
        $warehouses = \Schema::hasTable('np_warehouses') ? NpWarehouse::count() : 0;
        $report[] = $cities > 0 && $warehouses > 0
            ? ['title' => 'База даних', 'ok' => true, 'message' => "міст: {$cities}, відділень: {$warehouses}"]
            : ['title' => 'База даних', 'ok' => false, 'message' => "міст: {$cities}, відділень: {$warehouses} — потрібна синхронізація"];

        // 5. Recent API errors
        if (\Schema::hasTable('np_api_logs')) {
            $errorsToday = \App\Models\NpApiLog::where('success', false)
                ->where('created_at', '>=', now()->subDay())
                ->count();
            $report[] = $errorsToday === 0
                ? ['title' => 'Помилки за 24 год', 'ok' => true, 'message' => 'немає']
                : ['title' => 'Помилки за 24 год', 'ok' => false, 'message' => "{$errorsToday} запитів з помилками — перегляньте API логи"];
        }

        return $report;
    }

    public function mount(): void
    {
        $rows = DisplaySetting::where('group', 'nova_poshta')
            ->get(['key', 'value', 'type']);

        $settings = [];
        foreach ($rows as $row) {
            $settings[$row->key] = $this->castSettingValue($row->value, $row->type);
        }

        $this->data = $settings;

        $this->form->fill($this->data);
    }

    /**
     * Cast DisplaySetting raw value to the right PHP type so Filament form components
     * receive expected shape (array for Repeater, bool for Toggle, etc.).
     */
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

    /**
     * Module enable/disable + status — shown in page header.
     */
    public function getModuleStatus(): array
    {
        $integration = app(IntegrationManager::class)->get('novaposhta');
        if (! $integration) {
            return [
                'enabled' => false,
                'level' => 'unknown',
                'message' => 'Інтеграцію не зареєстровано',
            ];
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
        $integration = app(IntegrationManager::class)->get('novaposhta');
        if (! $integration) {
            return;
        }

        if ($integration->isEnabled()) {
            $integration->disable();
            Cache::forget('shop_setting_integration_novaposhta_enabled');

            Notification::make()
                ->title('Модуль Нова Пошта вимкнено')
                ->warning()
                ->send();
        } else {
            $integration->enable();
            Cache::forget('shop_setting_integration_novaposhta_enabled');

            Notification::make()
                ->title('Модуль Нова Пошта увімкнено')
                ->success()
                ->send();
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('nova_poshta_settings')
                    ->tabs([
                        $this->apiTab(),
                        $this->senderTab(),
                        $this->shipmentParamsTab(),
                        $this->deliveryTab(),
                        $this->trackingTab(),
                        $this->databaseTab(),
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
                Forms\Components\Section::make('Підключення до API')
                    ->description('Налаштуйте ключ API Нової Пошти для роботи з ТТН')
                    ->schema([
                        Forms\Components\TextInput::make('np_api_key')
                            ->label('API ключ')
                            ->password()
                            ->revealable()
                            ->placeholder('Введіть API ключ з кабінету Нової Пошти')
                            ->helperText('Ключ можна отримати у особистому кабінеті my.novaposhta.ua')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('test_connection')
                                ->label('Перевірити зʼєднання')
                                ->icon('heroicon-o-signal')
                                ->color('info')
                                ->action(function () {
                                    $this->testApiConnection();
                                }),

                            Forms\Components\Actions\Action::make('auto_detect_sender')
                                ->label('Авто-визначити відправника')
                                ->icon('heroicon-o-sparkles')
                                ->color('success')
                                ->action(function () {
                                    $this->autoDetectSender();
                                })
                                ->requiresConfirmation()
                                ->modalDescription('Запитає в NP API дані вашого облікового запису і заповнить sender_ref, contact_ref, city, warehouse, телефон автоматично.'),
                        ]),

                        Forms\Components\Toggle::make('np_debug_mode')
                            ->label('Режим відлагодження')
                            ->helperText('Логувати всі API запити та відповіді'),
                    ]),
            ]);
    }

    protected function senderTab(): Forms\Components\Tabs\Tab
    {
        return Forms\Components\Tabs\Tab::make('Відправник')
            ->icon('heroicon-o-user')
            ->schema([
                Forms\Components\Section::make('Інформація про відправника')
                    ->description('Заповнюється автоматично з API. Натисніть «Авто-визначити відправника» на вкладці API.')
                    ->schema([
                        Forms\Components\TextInput::make('np_sender_name')
                            ->label('Назва відправника')
                            ->placeholder('—')
                            ->helperText('З Counterparty API'),

                        Forms\Components\TextInput::make('np_sender_edrpou')
                            ->label('ЄДРПОУ')
                            ->placeholder('—')
                            ->maxLength(10),

                        Forms\Components\TextInput::make('np_contact_person')
                            ->label('Контактна особа')
                            ->placeholder('ПІБ контактної особи'),

                        Forms\Components\TextInput::make('np_sender_phone')
                            ->label('Телефон')
                            ->tel()
                            ->placeholder('+380XXXXXXXXX'),

                        Forms\Components\TextInput::make('np_sender_city_name')
                            ->label('Місто')
                            ->placeholder('—')
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\TextInput::make('np_sender_address')
                            ->label('Адреса відправника')
                            ->placeholder('—')
                            ->disabled()
                            ->dehydrated()
                            ->helperText('NP SenderAddress: адреса лінкована до Counterparty (для приймання вантажу)'),

                        // Hidden internal IDs (used for API requests, never shown to user)
                        Forms\Components\Hidden::make('np_sender_ref'),
                        Forms\Components\Hidden::make('np_contact_person_ref'),
                        Forms\Components\Hidden::make('np_sender_city_ref'),
                        Forms\Components\Hidden::make('np_sender_warehouse_ref'),

                        Forms\Components\Placeholder::make('sender_internal_ids')
                            ->label('Внутрішні ID (для API)')
                            ->columnSpanFull()
                            ->content(function () {
                                $rows = [
                                    'Ref відправника' => DisplaySetting::get('np_sender_ref'),
                                    'Ref контактної особи' => DisplaySetting::get('np_contact_person_ref'),
                                    'Ref міста' => DisplaySetting::get('np_sender_city_ref'),
                                    'Ref адреси (SenderAddress)' => DisplaySetting::get('np_sender_warehouse_ref'),
                                ];
                                $html = '<div class="text-xs text-gray-500 dark:text-gray-400 space-y-1">';
                                foreach ($rows as $label => $value) {
                                    $short = $value ? '<code>'.htmlspecialchars(substr($value, 0, 8)).'…'.htmlspecialchars(substr($value, -4)).'</code>' : '<span class="text-gray-400">—</span>';
                                    $html .= "<div><strong>{$label}:</strong> {$short}</div>";
                                }
                                $html .= '</div>';

                                return new \Illuminate\Support\HtmlString($html);
                            }),
                    ])
                    ->columns(2),
            ]);
    }

    protected function shipmentParamsTab(): Forms\Components\Tabs\Tab
    {
        return Forms\Components\Tabs\Tab::make('Параметри відправлення')
            ->icon('heroicon-o-cube')
            ->schema([
                Forms\Components\Section::make('Параметри за замовчуванням')
                    ->description('Значення за замовчуванням при створенні нової ТТН')
                    ->schema([
                        Forms\Components\Select::make('np_default_cargo_type')
                            ->label('Тип вантажу')
                            ->options([
                                'Parcel' => 'Посилка',
                                'Cargo' => 'Вантаж',
                                'Documents' => 'Документи',
                                'TiresWheels' => 'Шини та диски',
                                'Pallet' => 'Палети',
                            ])
                            ->default('Parcel'),

                        Forms\Components\TextInput::make('np_default_weight')
                            ->label('Вага за замовчуванням (кг)')
                            ->numeric()
                            ->step(0.1)
                            ->minValue(0.1)
                            ->default(0.5),

                        Forms\Components\TextInput::make('np_default_seats_amount')
                            ->label('Кількість місць')
                            ->numeric()
                            ->minValue(1)
                            ->default(1),

                        Forms\Components\Toggle::make('np_auto_detect_shipment_type')
                            ->label('Автовизначення типу відправлення')
                            ->helperText('Автоматично визначати тип на основі вартості та ваги'),

                        Forms\Components\Select::make('np_default_payer')
                            ->label('Платник за замовчуванням')
                            ->options([
                                'Sender' => 'Відправник',
                                'Recipient' => 'Отримувач',
                                'ThirdPerson' => 'Третя особа',
                            ])
                            ->default('Recipient'),

                        Forms\Components\Select::make('np_default_payment_form')
                            ->label('Форма оплати')
                            ->options([
                                'Cash' => 'Готівка',
                                'NonCash' => 'Безготівкова',
                            ])
                            ->default('Cash'),

                        Forms\Components\Select::make('np_declared_value_method')
                            ->label('Метод оголошеної вартості')
                            ->options([
                                'order_total' => 'Сума замовлення',
                                'products_total' => 'Сума товарів',
                                'custom' => 'Фіксована сума',
                            ])
                            ->default('order_total')
                            ->live(),

                        Forms\Components\TextInput::make('np_default_declared_value')
                            ->label('Оголошена вартість за замовчуванням (грн)')
                            ->numeric()
                            ->minValue(0)
                            ->default(300)
                            ->visible(fn (Forms\Get $get) => $get('np_declared_value_method') === 'custom'),

                        Forms\Components\TextInput::make('np_min_declared_value')
                            ->label('Мінімальна оголошена вартість (грн)')
                            ->numeric()
                            ->minValue(0)
                            ->default(100),

                        Forms\Components\Textarea::make('np_description_template')
                            ->label('Шаблон опису')
                            ->placeholder('Замовлення #{order_id}: {products}')
                            ->helperText('Макроси: {order_id}, {products}, {total}, {customer_name}')
                            ->default('Замовлення #{order_id}')
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('np_volume_accounting')
                            ->label('Обʼємний облік ваги')
                            ->helperText('Розраховувати вартість на основі обʼємної ваги'),

                        Forms\Components\Select::make('np_package_type')
                            ->label('Тип упаковки')
                            ->options([
                                '' => 'Без упаковки',
                                'PackBig' => 'Велика упаковка',
                                'PackStandart' => 'Стандартна упаковка',
                                'PackSmall' => 'Мала упаковка',
                                'Bubble' => 'Бульбашкова плівка',
                                'Hardboard' => 'Жорсткий картон',
                            ]),

                        Forms\Components\Toggle::make('np_auto_detect_package')
                            ->label('Автовизначення упаковки')
                            ->helperText('Автоматично підбирати упаковку за розмірами'),
                    ])
                    ->columns(2),
            ]);
    }

    protected function deliveryTab(): Forms\Components\Tabs\Tab
    {
        return Forms\Components\Tabs\Tab::make('Доставка')
            ->icon('heroicon-o-truck')
            ->schema([
                Forms\Components\Section::make('Типи доставки')
                    ->schema([
                        Forms\Components\Toggle::make('np_enable_warehouse_delivery')
                            ->label('Доставка у відділення')
                            ->default(true),

                        Forms\Components\Toggle::make('np_enable_courier_delivery')
                            ->label('Курʼєрська доставка'),

                        Forms\Components\Toggle::make('np_enable_postomat_delivery')
                            ->label('Доставка у поштомат'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Безкоштовна доставка')
                    ->schema([
                        Forms\Components\TextInput::make('np_free_shipping_threshold')
                            ->label('Поріг безкоштовної доставки (грн)')
                            ->numeric()
                            ->minValue(0)
                            ->placeholder('0 = вимкнено'),

                        Forms\Components\TextInput::make('np_free_shipping_text')
                            ->label('Текст безкоштовної доставки')
                            ->placeholder('Безкоштовна доставка від {threshold} грн')
                            ->helperText('Макрос: {threshold}'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Відображення')
                    ->schema([
                        Forms\Components\Toggle::make('np_show_shipping_cost')
                            ->label('Показувати розрахунок вартості доставки')
                            ->default(true),

                        Forms\Components\Toggle::make('np_show_delivery_estimate')
                            ->label('Показувати орієнтовну дату доставки'),

                        Forms\Components\Toggle::make('np_filter_warehouses_by_weight')
                            ->label('Фільтрувати відділення за вагою')
                            ->helperText('Приховувати відділення що не можуть прийняти вантаж'),

                        Forms\Components\Toggle::make('np_filter_warehouses_by_dimensions')
                            ->label('Фільтрувати відділення за розмірами')
                            ->helperText('Приховувати відділення з обмеженнями на габарити'),
                    ])
                    ->columns(2),
            ]);
    }

    protected function trackingTab(): Forms\Components\Tabs\Tab
    {
        return Forms\Components\Tabs\Tab::make('Відстеження')
            ->icon('heroicon-o-map-pin')
            ->schema([
                Forms\Components\Section::make('Автоматичне відстеження')
                    ->schema([
                        Forms\Components\Toggle::make('np_enable_auto_tracking')
                            ->label('Увімкнути автоматичне відстеження')
                            ->live(),

                        Forms\Components\Select::make('np_tracking_interval')
                            ->label('Інтервал відстеження')
                            ->options([
                                '15' => 'Кожні 15 хвилин',
                                '30' => 'Кожні 30 хвилин',
                                '60' => 'Кожну годину',
                                '120' => 'Кожні 2 години',
                            ])
                            ->default('30')
                            ->visible(fn (Forms\Get $get) => $get('np_enable_auto_tracking')),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Маппінг статусів')
                    ->description('Відповідність статусів Нової Пошти до статусів замовлення')
                    ->schema([
                        Forms\Components\Repeater::make('np_status_mapping')
                            ->label('')
                            ->schema([
                                Forms\Components\Select::make('np_status')
                                    ->label('Статус НП')
                                    ->options([
                                        '1' => 'Створено',
                                        '4' => 'В дорозі',
                                        '5' => 'Прямує у місто',
                                        '6' => 'У місті',
                                        '7' => 'Прибув у відділення',
                                        '9' => 'Отримано',
                                        '10' => 'Отримано (з наложкою)',
                                        '14' => 'Відмова отримувача',
                                        '102' => 'Повернення',
                                        '108' => 'Повернуто',
                                    ])
                                    ->required(),

                                Forms\Components\Select::make('order_status')
                                    ->label('Статус замовлення')
                                    ->options([
                                        'new' => 'Нове',
                                        'processing' => 'В обробці',
                                        'shipped' => 'Відправлено',
                                        'delivered' => 'Доставлено',
                                        'completed' => 'Завершено',
                                        'returned' => 'Повернуто',
                                        'cancelled' => 'Скасовано',
                                    ])
                                    ->required(),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->addActionLabel('Додати маппінг')
                            ->reorderable(false)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Сповіщення')
                    ->schema([
                        Forms\Components\Toggle::make('np_notify_admin_status_change')
                            ->label('Сповіщати адміністратора при зміні статусу'),

                        Forms\Components\Toggle::make('np_notify_customer_status_change')
                            ->label('Сповіщати клієнта при зміні статусу'),

                        Forms\Components\Textarea::make('np_email_template')
                            ->label('Шаблон email повідомлення')
                            ->helperText('Макроси: {customer_name}, {ttn}, {status}, {order_id}, {tracking_url}')
                            ->placeholder('Шановний(а) {customer_name}, статус Вашого відправлення {ttn} змінено на: {status}')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    protected function databaseTab(): Forms\Components\Tabs\Tab
    {
        $citiesCount = 0;
        $warehousesCount = 0;
        $citiesLastSync = '-';
        $warehousesLastSync = '-';

        try {
            if (\Schema::hasTable('np_cities')) {
                $citiesCount = NpCity::count();
            }
            if (\Schema::hasTable('np_warehouses')) {
                $warehousesCount = NpWarehouse::count();
            }
        } catch (\Exception $e) {
            // Tables might not exist yet
        }

        $citiesLastSync = DisplaySetting::get('np_cities_last_sync', '-');
        $warehousesLastSync = DisplaySetting::get('np_warehouses_last_sync', '-');

        return Forms\Components\Tabs\Tab::make('База даних')
            ->icon('heroicon-o-circle-stack')
            ->schema([
                Forms\Components\Section::make('Стан бази даних')
                    ->schema([
                        Forms\Components\Placeholder::make('cities_info')
                            ->label('Міста')
                            ->content("Записів: {$citiesCount} | Остання синхронізація: {$citiesLastSync}"),

                        Forms\Components\Placeholder::make('warehouses_info')
                            ->label('Відділення')
                            ->content("Записів: {$warehousesCount} | Остання синхронізація: {$warehousesLastSync}"),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Синхронізація')
                    ->schema([
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('sync_cities')
                                ->label('Синхронізувати міста')
                                ->icon('heroicon-o-building-office-2')
                                ->color('info')
                                ->requiresConfirmation()
                                ->modalDescription('Це може зайняти декілька хвилин.')
                                ->action(function () {
                                    $this->syncCities();
                                }),

                            Forms\Components\Actions\Action::make('sync_warehouses')
                                ->label('Синхронізувати відділення')
                                ->icon('heroicon-o-building-storefront')
                                ->color('info')
                                ->requiresConfirmation()
                                ->modalDescription('Це може зайняти декілька хвилин.')
                                ->action(function () {
                                    $this->syncWarehouses();
                                }),

                            Forms\Components\Actions\Action::make('sync_all')
                                ->label('Синхронізувати все')
                                ->icon('heroicon-o-arrow-path')
                                ->color('warning')
                                ->requiresConfirmation()
                                ->modalDescription('Буде синхронізовано міста та відділення. Це може зайняти 5-10 хвилин.')
                                ->action(function () {
                                    $this->syncCities();
                                    $this->syncWarehouses();
                                }),
                        ]),
                    ]),

                Forms\Components\Section::make('Автосинхронізація')
                    ->schema([
                        Forms\Components\Toggle::make('np_auto_sync_enabled')
                            ->label('Автоматична синхронізація')
                            ->helperText('Запускати синхронізацію за розкладом')
                            ->live(),

                        Forms\Components\Select::make('np_auto_sync_schedule')
                            ->label('Розклад')
                            ->options([
                                'daily' => 'Щоденно',
                                'weekly' => 'Щотижня',
                                'monthly' => 'Щомісяця',
                            ])
                            ->default('weekly')
                            ->visible(fn (Forms\Get $get) => $get('np_auto_sync_enabled')),
                    ])
                    ->columns(2),
            ]);
    }

    public function testApiConnection(): void
    {
        $apiKey = $this->data['np_api_key'] ?? '';

        if (empty($apiKey)) {
            Notification::make()
                ->title('Введіть API ключ')
                ->body('Поле API ключ порожнє. Отримайте ключ у my.novaposhta.ua → Налаштування → Безпека → Мої ключі API.')
                ->warning()
                ->send();

            return;
        }

        try {
            $service = app(NovaPoshtaApiService::class);
            $result = $service->getSenderInfo();

            if (($result['success'] ?? false) && ! empty($result['data'])) {
                $sender = $result['data'][0];

                $this->data['np_sender_name'] = $sender['Description'] ?? '';
                $this->data['np_sender_ref'] = $sender['Ref'] ?? '';

                Notification::make()
                    ->title('Зʼєднання успішне!')
                    ->body("Відправник: {$sender['Description']}")
                    ->success()
                    ->send();

                $this->form->fill($this->data);
            } else {
                $this->showApiError($result['errors'] ?? ['Невідома помилка']);
            }
        } catch (\Exception $e) {
            $this->showApiError([$e->getMessage()]);
        }
    }

    /**
     * Auto-detect sender refs (counterparty + contact + warehouse) from API key.
     */
    public function autoDetectSender(): void
    {
        $apiKey = $this->data['np_api_key'] ?? '';
        if (empty($apiKey)) {
            Notification::make()
                ->title('Введіть API ключ')
                ->body('Введіть API ключ і збережіть, потім натисніть «Авто-визначити».')
                ->warning()
                ->send();
            return;
        }

        try {
            $detector = app(\App\Services\Shipping\NovaPoshtaAutoDetect::class);
            $r = $detector->detectAndSave();

            if (! $r['success']) {
                $this->showApiError($r['errors'] ?: ['Не знайдено Counterparty для цього API ключа']);
                return;
            }

            $det = $r['detected'];
            // Push to form fields too (for visual feedback)
            $this->data['np_sender_ref'] = $det['sender_ref'] ?? '';
            $this->data['np_sender_name'] = $det['sender_name'] ?? '';
            $this->data['np_contact_person_ref'] = $det['sender_contact_ref'] ?? '';
            $this->data['np_sender_phone'] = $det['sender_phone'] ?? '';
            $this->data['np_sender_city_ref'] = $det['sender_city_ref'] ?? '';
            $this->data['np_sender_warehouse_ref'] = $det['sender_warehouse_ref'] ?? '';
            $this->form->fill($this->data);

            $body = "Відправник: {$det['sender_name']}\n";
            $body .= 'Контакт: '.($det['sender_contact_name'] ?? '—')."\n";
            $body .= 'Адреса: '.($det['sender_address'] ?? '—')."\n";
            $body .= 'Телефон: '.($det['sender_phone'] ?? '—');

            Notification::make()
                ->title('Дані відправника визначено та збережено')
                ->body($body)
                ->success()
                ->duration(15000)
                ->send();
        } catch (\Throwable $e) {
            $this->showApiError([$e->getMessage()]);
        }
    }

    /**
     * Show user-friendly error mapped from NP API codes.
     */
    protected function showApiError(array $errors): void
    {
        $raw = implode('; ', $errors);
        $title = 'Помилка API';
        $body = $raw;

        // Friendly messages
        if (str_contains($raw, 'API key expired')) {
            $title = '⚠️ API ключ протермінований';
            $body = "Ваш ключ Нової Пошти більше не дійсний. Зайдіть у my.novaposhta.ua → Налаштування → Безпека → Мої ключі API і створіть новий ключ.";
        } elseif (str_contains($raw, 'API key is invalid') || str_contains($raw, 'incorrect')) {
            $title = '⚠️ Невірний API ключ';
            $body = "Ключ не розпізнано NP. Перевірте що скопійовано всі 32 символи без пробілів.";
        } elseif (str_contains($raw, 'API key is not configured')) {
            $title = 'Ключ не налаштовано';
            $body = "Введіть API ключ у поле і збережіть налаштування.";
        }

        Notification::make()
            ->title($title)
            ->body($body)
            ->danger()
            ->duration(15000)
            ->send();
    }

    public function syncCities(): void
    {
        try {
            $service = app(NovaPoshtaApiService::class);

            $page = 1;
            $total = 0;

            if (! \Schema::hasTable('np_cities')) {
                Notification::make()
                    ->title('Помилка')
                    ->body('Таблиця np_cities не існує. Виконайте міграції.')
                    ->danger()
                    ->send();

                return;
            }

            do {
                $result = $service->getCities('', $page, 500);

                if (! ($result['success'] ?? false) || empty($result['data'])) break;

                foreach ($result['data'] as $city) {
                    NpCity::updateOrCreate(
                        ['ref' => $city['Ref']],
                        [
                            'description' => $city['Description'] ?? '',
                            'description_ru' => $city['DescriptionRu'] ?? $city['Description'] ?? '',
                            'area_ref' => $city['Area'] ?? '',
                            'area_description' => $city['AreaDescription'] ?? '',
                            'settlement_type' => $city['SettlementTypeDescription'] ?? '',
                            'is_branch' => (bool) ($city['IsBranch'] ?? false),
                        ]
                    );
                    $total++;
                }

                $page++;
            } while (count($result['data']) >= 500);

            DisplaySetting::set('np_cities_last_sync', now()->toDateTimeString());
            DisplaySetting::set('np_cities_count', (string) $total);

            Notification::make()
                ->title('Міста синхронізовано')
                ->body("Оновлено {$total} міст")
                ->success()
                ->send();
        } catch (\Exception $e) {
            Log::error('NP Cities sync error: ' . $e->getMessage());
            Notification::make()
                ->title('Помилка синхронізації')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function syncWarehouses(): void
    {
        try {
            $service = app(NovaPoshtaApiService::class);

            $page = 1;
            $total = 0;

            if (! \Schema::hasTable('np_warehouses')) {
                Notification::make()
                    ->title('Помилка')
                    ->body('Таблиця np_warehouses не існує. Виконайте міграції.')
                    ->danger()
                    ->send();

                return;
            }

            do {
                $result = $service->getWarehouses('', '', 500, $page);

                if (! ($result['success'] ?? false) || empty($result['data'])) break;

                foreach ($result['data'] as $w) {
                    NpWarehouse::updateOrCreate(
                        ['ref' => $w['Ref']],
                        [
                            'site_key' => $w['SiteKey'] ?? $w['Number'] ?? '',
                            'description' => $w['Description'] ?? '',
                            'short_address' => $w['ShortAddress'] ?? '',
                            'city_ref' => $w['CityRef'] ?? '',
                            'city_description' => $w['CityDescription'] ?? '',
                            'type_ref' => $w['TypeOfWarehouse'] ?? '',
                            'type_description' => $w['CategoryOfWarehouse'] ?? '',
                            'longitude' => $w['Longitude'] ?? null,
                            'latitude' => $w['Latitude'] ?? null,
                            'total_max_weight' => $w['TotalMaxWeightAllowed'] ?? null,
                            'is_active' => true,
                        ]
                    );
                    $total++;
                }

                $page++;
            } while (count($result['data']) >= 500);

            DisplaySetting::set('np_warehouses_last_sync', now()->toDateTimeString());
            DisplaySetting::set('np_warehouses_count', (string) $total);

            Notification::make()
                ->title('Відділення синхронізовано')
                ->body("Оновлено {$total} відділень")
                ->success()
                ->send();
        } catch (\Exception $e) {
            Log::error('NP Warehouses sync error: ' . $e->getMessage());
            Notification::make()
                ->title('Помилка синхронізації')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'np_')) {
                DisplaySetting::updateOrCreate(
                    ['key' => $key],
                    [
                        'value' => is_array($value) ? json_encode($value) : $value,
                        'group' => 'nova_poshta',
                        'type' => match (true) {
                            is_bool($value) => 'boolean',
                            is_array($value) => 'json',
                            is_numeric($value) => 'number',
                            default => 'string',
                        },
                        'is_active' => true,
                        'title' => ucfirst(str_replace(['np_', '_'], ['', ' '], $key)),
                    ]
                );
            }
        }

        DisplaySetting::flushSettingsCache();

        Notification::make()
            ->title('Збережено')
            ->body('Налаштування Нової Пошти оновлено.')
            ->success()
            ->send();
    }
}
