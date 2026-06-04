<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NpShipmentResource\Pages;
use App\Models\DisplaySetting;
use App\Models\NpCity;
use App\Models\NpShipment;
use App\Models\NpWarehouse;
use App\Models\Order;
use App\Services\NovaPoshtaApiService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class NpShipmentResource extends Resource
{
    use \App\Filament\Concerns\GatedResource;

    use \App\Filament\Concerns\RequiresModule;

    protected static string $moduleKey = 'novaposhta';

    protected static ?string $model = NpShipment::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Склад і доставка';

    protected static ?string $modelLabel = 'ТТН';

    protected static ?string $pluralModelLabel = 'ТТН (Нова Пошта)';

    protected static ?int $navigationSort = 110;

    protected static ?string $navigationLabel = 'Нова Пошта: ТТН';

    public static function getNavigationBadge(): ?string
    {
        $count = NpShipment::active()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        // Sender section
                        static::senderSection(),

                        // Recipient section
                        static::recipientSection(),

                        // Shipment section
                        static::shipmentSection(),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        // Payment section
                        static::paymentSection(),

                        // Extra section
                        static::extraSection(),

                        // Order info
                        Forms\Components\Section::make('Замовлення')
                            ->schema([
                                Forms\Components\Select::make('order_id')
                                    ->label('Замовлення')
                                    ->relationship('order', 'id')
                                    ->getOptionLabelFromRecordUsing(function (Order $record) {
                                        $name = trim("{$record->first_name} {$record->last_name}");

                                        return "#{$record->id} - {$name} ({$record->total} грн)";
                                    })
                                    ->searchable(['id', 'first_name', 'last_name', 'phone'])
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        if (! $state) return;
                                        static::fillFromOrder($state, $set);
                                    }),

                                Forms\Components\Placeholder::make('ttn_display')
                                    ->label('Номер ТТН')
                                    ->content(fn (?NpShipment $record) => $record?->ttn ?? 'Буде створено після збереження')
                                    ->visible(fn (?NpShipment $record) => $record !== null),

                                Forms\Components\Placeholder::make('status_display')
                                    ->label('Статус')
                                    ->content(fn (?NpShipment $record) => $record?->np_status ?? '-')
                                    ->visible(fn (?NpShipment $record) => $record !== null),
                            ])
                            ->collapsible(),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    protected static function senderSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Відправник')
            ->icon('heroicon-o-user')
            ->schema([
                Forms\Components\TextInput::make('sender_ref')
                    ->label('Ref відправника')
                    ->default(fn () => DisplaySetting::get('np_sender_ref', ''))
                    ->hidden(),

                Forms\Components\Placeholder::make('sender_info')
                    ->label('Відправник')
                    ->content(function () {
                        $name = DisplaySetting::get('np_sender_name', 'Не налаштовано');
                        $city = DisplaySetting::get('np_sender_city_ref', '');
                        $cityName = '';
                        if ($city) {
                            $cityName = NpCity::where('ref', $city)->value('description') ?? '';
                        }

                        return $cityName ? "{$name} ({$cityName})" : $name;
                    }),

                Forms\Components\Hidden::make('sender_city_ref')
                    ->default(fn () => DisplaySetting::get('np_sender_city_ref', '')),

                Forms\Components\Hidden::make('sender_warehouse_ref')
                    ->default(fn () => DisplaySetting::get('np_sender_warehouse_ref', '')),
            ])
            ->collapsible()
            ->collapsed();
    }

    protected static function recipientSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Отримувач')
            ->icon('heroicon-o-user-plus')
            ->schema([
                Forms\Components\TextInput::make('recipient_name')
                    ->label('ПІБ отримувача')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('recipient_phone')
                    ->label('Телефон')
                    ->tel()
                    ->required()
                    ->maxLength(20)
                    ->placeholder('+380XXXXXXXXX'),

                Forms\Components\Select::make('service_type')
                    ->label('Тип доставки')
                    ->options([
                        'WarehouseWarehouse' => 'Відділення - Відділення',
                        'WarehouseDoors' => 'Відділення - Адреса',
                        'DoorsWarehouse' => 'Адреса - Відділення',
                        'DoorsDoors' => 'Адреса - Адреса',
                    ])
                    ->default('WarehouseWarehouse')
                    ->required()
                    ->live(),

                Forms\Components\Select::make('recipient_city_ref')
                    ->label('Місто')
                    ->searchable()
                    ->required()
                    ->getSearchResultsUsing(function (string $search) {
                        if (strlen($search) < 2) return [];

                        // First search local DB
                        $local = NpCity::search($search)
                            ->limit(30)
                            ->pluck('description', 'ref')
                            ->toArray();

                        if (count($local) > 0) return $local;

                        // Fallback to API
                        try {
                            $service = app(NovaPoshtaApiService::class);
                            $result = $service->searchCities($search);
                            if ($result['success'] ?? false) {
                                return collect($result['data'] ?? [])
                                    ->take(30)
                                    ->pluck('Description', 'Ref')
                                    ->toArray();
                            }
                        } catch (\Exception $e) {
                            Log::warning('NP city search fallback failed: ' . $e->getMessage());
                        }

                        return [];
                    })
                    ->getOptionLabelUsing(function ($value) {
                        return NpCity::where('ref', $value)->value('description')
                            ?? $value;
                    })
                    ->live()
                    ->afterStateUpdated(function (Forms\Set $set) {
                        $set('recipient_warehouse_ref', null);
                        $set('recipient_warehouse_name', null);
                    }),

                // Warehouse select (for Warehouse delivery)
                Forms\Components\Select::make('recipient_warehouse_ref')
                    ->label('Відділення')
                    ->searchable()
                    ->required(fn (Forms\Get $get) => in_array($get('service_type'), ['WarehouseWarehouse', 'DoorsWarehouse']))
                    ->options(function (Forms\Get $get) {
                        $cityRef = $get('recipient_city_ref');
                        if (! $cityRef) return [];

                        return NpWarehouse::forCity($cityRef)
                            ->active()
                            ->limit(100)
                            ->pluck('description', 'ref')
                            ->toArray();
                    })
                    ->getSearchResultsUsing(function (string $search, Forms\Get $get) {
                        $cityRef = $get('recipient_city_ref');
                        if (! $cityRef) return [];

                        $local = NpWarehouse::forCity($cityRef)
                            ->active()
                            ->search($search)
                            ->limit(50)
                            ->pluck('description', 'ref')
                            ->toArray();

                        if (count($local) > 0) return $local;

                        // Fallback to API
                        try {
                            $service = app(NovaPoshtaApiService::class);
                            $result = $service->searchWarehouses($cityRef, $search);
                            if ($result['success'] ?? false) {
                                return collect($result['data'] ?? [])
                                    ->take(50)
                                    ->mapWithKeys(fn ($w) => [$w['Ref'] => $w['Description']])
                                    ->toArray();
                            }
                        } catch (\Exception $e) {
                            // silent
                        }

                        return [];
                    })
                    ->getOptionLabelUsing(function ($value) {
                        return NpWarehouse::where('ref', $value)->value('description') ?? $value;
                    })
                    ->visible(fn (Forms\Get $get) => in_array($get('service_type'), ['WarehouseWarehouse', 'DoorsWarehouse', null, '']))
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        if ($state) {
                            $warehouse = NpWarehouse::where('ref', $state)->first();
                            if ($warehouse) {
                                $set('recipient_warehouse_name', $warehouse->description);
                            }
                        }
                    })
                    ->live(),

                Forms\Components\Hidden::make('recipient_warehouse_name'),

                Forms\Components\Hidden::make('recipient_city_name'),

                // Contact details
                Forms\Components\TextInput::make('recipient_contact_name')
                    ->label('Контактна особа (якщо інша)')
                    ->maxLength(200)
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('recipient_email')
                    ->label('Email')
                    ->email()
                    ->maxLength(200),

                // Юр. особа
                Forms\Components\Toggle::make('is_company')
                    ->label('Юридична особа')
                    ->dehydrated(false)
                    ->live()
                    ->afterStateHydrated(function ($component, ?string $state, Forms\Get $get) {
                        $component->state(! empty($get('recipient_edrpou')));
                    }),

                Forms\Components\TextInput::make('recipient_company_name')
                    ->label('Назва ТОВ/ФОП')
                    ->visible(fn (Forms\Get $get) => $get('is_company')),

                Forms\Components\TextInput::make('recipient_edrpou')
                    ->label('ЄДРПОУ')
                    ->maxLength(20)
                    ->visible(fn (Forms\Get $get) => $get('is_company')),

                // Address fields (for Doors delivery)
                Forms\Components\TextInput::make('recipient_street')
                    ->label('Вулиця')
                    ->visible(fn (Forms\Get $get) => in_array($get('service_type'), ['WarehouseDoors', 'DoorsDoors'])),

                Forms\Components\TextInput::make('recipient_house')
                    ->label('Будинок')
                    ->visible(fn (Forms\Get $get) => in_array($get('service_type'), ['WarehouseDoors', 'DoorsDoors'])),

                Forms\Components\TextInput::make('recipient_flat')
                    ->label('Квартира')
                    ->visible(fn (Forms\Get $get) => in_array($get('service_type'), ['WarehouseDoors', 'DoorsDoors'])),

                Forms\Components\TextInput::make('recipient_floor')
                    ->label('Поверх')
                    ->numeric()
                    ->visible(fn (Forms\Get $get) => in_array($get('service_type'), ['WarehouseDoors', 'DoorsDoors'])),

                Forms\Components\Toggle::make('recipient_has_elevator')
                    ->label('Є ліфт')
                    ->visible(fn (Forms\Get $get) => in_array($get('service_type'), ['WarehouseDoors', 'DoorsDoors'])),
            ])
            ->columns(2);
    }

    protected static function shipmentSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Відправлення')
            ->icon('heroicon-o-cube')
            ->schema([
                Forms\Components\Select::make('cargo_type')
                    ->label('Тип вантажу')
                    ->options([
                        'Parcel' => 'Посилка',
                        'Cargo' => 'Вантаж',
                        'Documents' => 'Документи',
                        'TiresWheels' => 'Шини та диски',
                        'Pallet' => 'Палети',
                    ])
                    ->default(fn () => DisplaySetting::get('np_default_cargo_type', 'Parcel'))
                    ->required(),

                Forms\Components\TextInput::make('weight')
                    ->label('Вага (кг)')
                    ->numeric()
                    ->step(0.1)
                    ->minValue(0.1)
                    ->default(fn () => DisplaySetting::get('np_default_weight', 0.5))
                    ->required(),

                Forms\Components\TextInput::make('volume')
                    ->label('Обʼєм (м3)')
                    ->numeric()
                    ->step(0.001)
                    ->minValue(0)
                    ->default(0.004),

                Forms\Components\TextInput::make('seats_amount')
                    ->label('Кількість місць')
                    ->numeric()
                    ->minValue(1)
                    ->default(fn () => DisplaySetting::get('np_default_seats_amount', 1))
                    ->required(),

                Forms\Components\TextInput::make('cost')
                    ->label('Оголошена вартість (грн)')
                    ->numeric()
                    ->minValue(0)
                    ->required()
                    ->prefix('₴'),

                Forms\Components\Textarea::make('description')
                    ->label('Опис вмісту')
                    ->default(fn () => DisplaySetting::get('np_description_template', 'Замовлення'))
                    ->rows(2)
                    ->columnSpanFull(),

                // Multi-parcel (occupies full width when collapsed)
                Forms\Components\Repeater::make('parcels')
                    ->label('Окремі місця (опційно)')
                    ->helperText('Якщо посилка з кількох коробок різних розмірів — додайте кожну. Якщо ні — лиште порожнім')
                    ->schema([
                        Forms\Components\TextInput::make('weight')->label('Вага кг')->numeric()->step(0.1)->required(),
                        Forms\Components\TextInput::make('length')->label('Д см')->numeric()->step(1),
                        Forms\Components\TextInput::make('width')->label('Ш см')->numeric()->step(1),
                        Forms\Components\TextInput::make('height')->label('В см')->numeric()->step(1),
                        Forms\Components\Select::make('pack_type')->label('Упаковка')->options([
                            'box' => 'Коробка',
                            'envelope' => 'Конверт',
                            'pallet' => 'Палета',
                            'tires' => 'Шини',
                        ]),
                    ])
                    ->columns(5)
                    ->collapsible()
                    ->collapsed()
                    ->columnSpanFull(),

                // Delivery preferences
                Forms\Components\DatePicker::make('preferred_delivery_date')
                    ->label('Бажана дата доставки')
                    ->minDate(now()->addDay()),

                Forms\Components\Select::make('preferred_delivery_time_from')
                    ->label('Бажаний час')
                    ->options([
                        '09:00' => '9:00 - 14:00',
                        '14:00' => '14:00 - 18:00',
                        '18:00' => '18:00 - 22:00',
                    ])
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        $map = ['09:00' => '14:00', '14:00' => '18:00', '18:00' => '22:00'];
                        $set('preferred_delivery_time_to', $map[$state] ?? null);
                    }),

                Forms\Components\Hidden::make('preferred_delivery_time_to'),

                // Options
                Forms\Components\Toggle::make('avia_delivery')
                    ->label('Авіадоставка'),

                Forms\Components\TextInput::make('packing_number')
                    ->label('№ упаковки (опц.)')
                    ->maxLength(50),

                Forms\Components\Textarea::make('additional_information')
                    ->label('Додаткова інформація')
                    ->rows(2)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    protected static function paymentSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Оплата')
            ->icon('heroicon-o-banknotes')
            ->schema([
                Forms\Components\Select::make('payer_type')
                    ->label('Платник')
                    ->options([
                        'Sender' => 'Відправник',
                        'Recipient' => 'Отримувач',
                        'ThirdPerson' => 'Третя особа',
                    ])
                    ->default(fn () => DisplaySetting::get('np_default_payer', 'Recipient'))
                    ->required(),

                Forms\Components\Select::make('payment_method')
                    ->label('Форма оплати')
                    ->options([
                        'Cash' => 'Готівка',
                        'NonCash' => 'Безготівкова',
                    ])
                    ->default(fn () => DisplaySetting::get('np_default_payment_form', 'Cash'))
                    ->required(),

                Forms\Components\TextInput::make('declared_cost')
                    ->label('Оголошена вартість (грн)')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->prefix('₴')
                    ->helperText('Якщо 0 — буде використано вартість замовлення'),

                Forms\Components\TextInput::make('cod_amount')
                    ->label('Сума наложеного платежу (грн)')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->prefix('₴')
                    ->helperText('0 = без наложеного платежу'),

                Forms\Components\Toggle::make('payment_control')
                    ->label('Контроль оплати')
                    ->helperText('Видача посилки тільки після оплати'),

                Forms\Components\Toggle::make('backward_delivery')
                    ->label('Зворотна доставка')
                    ->live()
                    ->dehydrated(false),

                Forms\Components\Select::make('backward_delivery_type')
                    ->label('Тип зворотної доставки')
                    ->options([
                        'Documents' => 'Документи',
                        'Money' => 'Грошовий переказ',
                    ])
                    ->visible(fn (Forms\Get $get) => $get('backward_delivery')),

                Forms\Components\TextInput::make('backward_delivery_amount')
                    ->label('Сума зворотної доставки (грн)')
                    ->numeric()
                    ->prefix('₴')
                    ->visible(fn (Forms\Get $get) => $get('backward_delivery')),

                Forms\Components\Select::make('backward_delivery_payer')
                    ->label('Платник зворотної')
                    ->options([
                        'Sender' => 'Відправник',
                        'Recipient' => 'Отримувач',
                    ])
                    ->visible(fn (Forms\Get $get) => $get('backward_delivery')),
            ])
            ->collapsible();
    }

    protected static function extraSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Додатково')
            ->icon('heroicon-o-adjustments-horizontal')
            ->schema([
                Forms\Components\DatePicker::make('shipment_date')
                    ->label('Дата відправлення')
                    ->default(now())
                    ->dehydrated(false),

                Forms\Components\TextInput::make('client_internal_number')
                    ->label('Внутрішній номер клієнта')
                    ->dehydrated(false),
            ])
            ->collapsible()
            ->collapsed();
    }

    /**
     * Auto-fill form from order data
     */
    public static function fillFromOrder(int $orderId, Forms\Set $set): void
    {
        $order = Order::with('orderProducts.product')->find($orderId);
        if (! $order) return;

        $recipientName = trim("{$order->last_name} {$order->first_name} {$order->middle_name}");
        $set('recipient_name', $recipientName ?: $order->name ?? '');
        $set('recipient_phone', $order->phone ?? '');

        // Determine declared value based on settings
        $method = DisplaySetting::get('np_declared_value_method', 'order_total');
        $declaredValue = match ($method) {
            'order_total' => (float) $order->total,
            'products_total' => (float) $order->orderProducts->sum(fn ($op) => $op->price * $op->quantity),
            'custom' => (float) DisplaySetting::get('np_default_declared_value', 300),
            default => (float) $order->total,
        };

        $minValue = (float) DisplaySetting::get('np_min_declared_value', 100);
        $set('cost', max($declaredValue, $minValue));

        // COD based on payment method
        if (in_array($order->payment_method, ['cod', 'cash_on_delivery', 'cash'])) {
            $set('cod_amount', (float) $order->total);
        }

        // Weight from order
        $set('weight', $order->calculateTotalWeight());

        // Description
        $template = DisplaySetting::get('np_description_template', 'Замовлення #{order_id}');
        $products = $order->orderProducts->map(fn ($op) => $op->product?->name ?? 'Товар')->implode(', ');
        $description = str_replace(
            ['{order_id}', '{products}', '{total}', '{customer_name}'],
            [$order->id, $products, $order->total, $recipientName],
            $template
        );
        $set('description', $description);

        // Pre-fill city/warehouse from shipping_data
        $shippingData = is_array($order->shipping_data) ? $order->shipping_data : json_decode($order->shipping_data ?? '{}', true);
        if (! empty($shippingData['city_ref'])) {
            $set('recipient_city_ref', $shippingData['city_ref']);
        }
        if (! empty($shippingData['warehouse_ref'])) {
            $set('recipient_warehouse_ref', $shippingData['warehouse_ref']);
        }

        // Determine service type
        $shippingMethod = $order->shipping_method ?? '';
        $serviceType = match ($shippingMethod) {
            'courier' => 'WarehouseDoors',
            'warehouse', 'postomat' => 'WarehouseWarehouse',
            default => 'WarehouseWarehouse',
        };
        $set('service_type', $serviceType);

        // Courier address
        if ($shippingMethod === 'courier') {
            $set('recipient_street', $shippingData['street'] ?? '');
            $set('recipient_building', $shippingData['building'] ?? '');
            $set('recipient_apartment', $shippingData['apartment'] ?? '');
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ttn')
                    ->label('ТТН')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('ТТН скопійовано')
                    ->fontFamily('mono')
                    ->weight('bold')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('order.id')
                    ->label('Замовлення')
                    ->formatStateUsing(fn ($state) => $state ? "#{$state}" : '-')
                    ->url(fn (NpShipment $record) => $record->order_id
                        ? OrderResource::getUrl('edit', ['record' => $record->order_id])
                        : null)
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('recipient_name')
                    ->label('Отримувач')
                    ->searchable()
                    ->limit(25),

                Tables\Columns\TextColumn::make('recipient_city_name')
                    ->label('Місто')
                    ->searchable()
                    ->limit(20),

                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        NpShipment::STATUS_NEW => 'gray',
                        NpShipment::STATUS_CREATED => 'info',
                        NpShipment::STATUS_SENT => 'warning',
                        NpShipment::STATUS_DELIVERED => 'success',
                        NpShipment::STATUS_RETURNED => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        NpShipment::STATUS_NEW => 'Нова',
                        NpShipment::STATUS_CREATED => 'Створена',
                        NpShipment::STATUS_SENT => 'В дорозі',
                        NpShipment::STATUS_DELIVERED => 'Доставлена',
                        NpShipment::STATUS_RETURNED => 'Повернена',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('np_status')
                    ->label('Статус НП')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('shipping_cost')
                    ->label('Вартість')
                    ->money('UAH')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('cod_amount')
                    ->label('Наложка')
                    ->money('UAH')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Створено')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_tracked_at')
                    ->label('Відстежено')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        NpShipment::STATUS_NEW => 'Нова',
                        NpShipment::STATUS_CREATED => 'Створена',
                        NpShipment::STATUS_SENT => 'В дорозі',
                        NpShipment::STATUS_DELIVERED => 'Доставлена',
                        NpShipment::STATUS_RETURNED => 'Повернена',
                    ]),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Від'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('До'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['created_from'], fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'], fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators[] = Tables\Filters\Indicator::make('Від ' . $data['created_from'])
                                ->removeField('created_from');
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators[] = Tables\Filters\Indicator::make('До ' . $data['created_until'])
                                ->removeField('created_until');
                        }

                        return $indicators;
                    }),

                Tables\Filters\SelectFilter::make('recipient_city_name')
                    ->label('Місто')
                    ->searchable()
                    ->options(function () {
                        return NpShipment::whereNotNull('recipient_city_name')
                            ->distinct()
                            ->limit(100)
                            ->pluck('recipient_city_name', 'recipient_city_name')
                            ->toArray();
                    }),

                Tables\Filters\TernaryFilter::make('has_ttn')
                    ->label('TTN присвоєно')
                    ->placeholder('Усі')
                    ->trueLabel('Має TTN')
                    ->falseLabel('Без TTN (потребує дії)')
                    ->queries(
                        true: fn (Builder $q) => $q->whereNotNull('ttn')->where('ttn', '!=', ''),
                        false: fn (Builder $q) => $q->where(fn ($qq) => $qq->whereNull('ttn')->orWhere('ttn', '')),
                        blank: fn (Builder $q) => $q,
                    ),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),

                    Tables\Actions\Action::make('track')
                        ->label('Відстежити')
                        ->icon('heroicon-o-map-pin')
                        ->color('info')
                        ->action(function (NpShipment $record) {
                            static::trackShipment($record);
                        })
                        ->visible(fn (NpShipment $record) => $record->ttn && $record->needsTracking()),

                    Tables\Actions\Action::make('print_ttn')
                        ->label('Друк ТТН')
                        ->icon('heroicon-o-printer')
                        ->color('success')
                        ->url(function (NpShipment $record) {
                            if (! $record->ref) return null;
                            $apiKey = DisplaySetting::get('np_api_key', '');

                            return "https://my.novaposhta.ua/orders/printDocument/orders[]/{$record->ref}/type/pdf/apiKey/{$apiKey}";
                        })
                        ->openUrlInNewTab()
                        ->visible(fn (NpShipment $record) => ! empty($record->ref)),

                    Tables\Actions\Action::make('print_marking')
                        ->label('Друк маркування')
                        ->icon('heroicon-o-tag')
                        ->color('warning')
                        ->url(function (NpShipment $record) {
                            if (! $record->ref) return null;
                            $apiKey = DisplaySetting::get('np_api_key', '');

                            return "https://my.novaposhta.ua/orders/printMarkings/orders[]/{$record->ref}/type/pdf/apiKey/{$apiKey}";
                        })
                        ->openUrlInNewTab()
                        ->visible(fn (NpShipment $record) => ! empty($record->ref)),

                    Tables\Actions\Action::make('tracking_url')
                        ->label('Відстеження на НП')
                        ->icon('heroicon-o-arrow-top-right-on-square')
                        ->url(fn (NpShipment $record) => $record->getTrackingUrl())
                        ->openUrlInNewTab()
                        ->visible(fn (NpShipment $record) => ! empty($record->ttn)),

                    Tables\Actions\DeleteAction::make()
                        ->before(function (NpShipment $record) {
                            // Attempt to delete from NP API
                            if ($record->ref && $record->canDelete()) {
                                try {
                                    $service = app(NovaPoshtaApiService::class);
                                    $service->deleteShipment($record->ref);
                                } catch (\Exception $e) {
                                    Log::warning('Failed to delete TTN from NP: ' . $e->getMessage());
                                }
                            }
                        })
                        ->visible(fn (NpShipment $record) => $record->canDelete()),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('retry_ttn_creation')
                        ->label('Повторити створення ТТН')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Повторити запит до НП для обраних shipments?')
                        ->modalDescription('Запит буде відправлено лише для записів без TTN. Кожен result буде записано в API логи.')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $candidates = $records->filter(fn (NpShipment $r) => empty($r->ttn));

                            if ($candidates->isEmpty()) {
                                Notification::make()
                                    ->title('Немає shipments без TTN')
                                    ->body('Усі обрані вже мають TTN.')
                                    ->warning()
                                    ->send();

                                return;
                            }

                            $creator = app(\App\Services\Shipping\NovaPoshtaTtnCreator::class);
                            $ok = 0;
                            $failed = 0;
                            $firstError = null;

                            foreach ($candidates as $shipment) {
                                $result = $creator->createForShipment($shipment);
                                if ($result['success']) {
                                    $ok++;
                                } else {
                                    $failed++;
                                    $firstError = $firstError ?? "#{$shipment->id}: ".implode('; ', $result['errors']);
                                }
                            }

                            $body = "Успішно: {$ok}\nПомилок: {$failed}";
                            if ($firstError) {
                                $body .= "\n\nПерша помилка:\n{$firstError}";
                            }

                            Notification::make()
                                ->title($failed === 0 ? 'Усі ТТН створено' : 'Завершено з помилками')
                                ->body($body)
                                ->color($failed === 0 ? 'success' : 'warning')
                                ->duration(20000)
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('track_selected')
                        ->label('Відстежити обрані')
                        ->icon('heroicon-o-map-pin')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $trackingNumbers = $records
                                ->filter(fn (NpShipment $r) => $r->ttn && $r->needsTracking())
                                ->pluck('ttn')
                                ->toArray();

                            if (empty($trackingNumbers)) {
                                Notification::make()
                                    ->title('Немає ТТН для відстеження')
                                    ->warning()
                                    ->send();

                                return;
                            }

                            $service = app(NovaPoshtaApiService::class);
                            $updated = 0;

                            foreach ($trackingNumbers as $ttn) {
                                $shipment = $records->firstWhere('ttn', $ttn);
                                if ($shipment) {
                                    static::trackShipment($shipment);
                                    $updated++;
                                }
                            }

                            Notification::make()
                                ->title("Відстежено {$updated} ТТН")
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('print_selected')
                        ->label('Друк обраних PDF')
                        ->icon('heroicon-o-printer')
                        ->form([
                            \Filament\Forms\Components\Select::make('format')
                                ->label('Формат')
                                ->options([
                                    'pdf' => 'PDF (A4)',
                                    'pdf100x100' => 'PDF (100×100 мм)',
                                    'html' => 'HTML',
                                ])
                                ->default('pdf')
                                ->required(),
                            \Filament\Forms\Components\Select::make('type')
                                ->label('Тип')
                                ->options([
                                    'orders' => 'Накладна (повна)',
                                    'marking' => 'Маркування',
                                ])
                                ->default('orders')
                                ->required(),
                        ])
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records, array $data) {
                            $refs = $records->filter(fn ($r) => ! empty($r->ref))->pluck('ref')->toArray();
                            if (empty($refs)) {
                                Notification::make()->warning()->title('Немає ТТН з Ref для друку')->send();
                                return;
                            }
                            $apiKey = config('novaposhta.api_key');
                            $refsStr = implode(',', $refs);
                            $endpoint = $data['type'] === 'marking' ? 'printMarkings' : 'printDocument';
                            $url = "https://my.novaposhta.ua/orders/{$endpoint}/orders[]/{$refsStr}/type/{$data['format']}/apiKey/{$apiKey}";

                            // Mark printed_at for tracking
                            \App\Models\NpShipment::whereIn('ref', $refs)->update(['printed_at' => now()]);

                            return redirect()->away($url);
                        })
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('resend_notification')
                        ->label('Надіслати email повторно')
                        ->icon('heroicon-o-envelope')
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalDescription('Надіслати email клієнту з поточним статусом ТТН для обраних замовлень.')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $sent = 0;
                            $skipped = 0;
                            foreach ($records as $shipment) {
                                $order = $shipment->order;
                                if (! $order || empty($order->email)) {
                                    $skipped++;
                                    continue;
                                }
                                $template = match ($shipment->status) {
                                    NpShipment::STATUS_DELIVERED => 'delivered',
                                    NpShipment::STATUS_RETURNED => 'returned',
                                    NpShipment::STATUS_SENT => in_array($shipment->np_status_code, ['7', '8']) ? 'in_warehouse' : 'shipped',
                                    default => 'shipped',
                                };
                                try {
                                    \Illuminate\Support\Facades\Mail::to($order->email)
                                        ->send(new \App\Mail\NpStatusChangedMail($shipment, $template));
                                    $sent++;
                                } catch (\Throwable $e) {
                                    Log::error("Resend NP email failed for shipment #{$shipment->id}: ".$e->getMessage());
                                    $skipped++;
                                }
                            }
                            Notification::make()
                                ->title("Надіслано: {$sent}, пропущено: {$skipped}")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('export_csv')
                        ->label('Експорт CSV')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('gray')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $headers = ['ID', 'TTN', 'Order', 'Recipient', 'Phone', 'City', 'Warehouse', 'Weight', 'Cost', 'Shipping cost', 'Status', 'NP Status', 'Created'];
                            $rows = [];
                            foreach ($records as $r) {
                                $rows[] = [
                                    $r->id,
                                    $r->ttn ?? '',
                                    $r->order_id,
                                    $r->recipient_name ?? '',
                                    $r->recipient_phone ?? '',
                                    $r->recipient_city_name ?? '',
                                    $r->recipient_warehouse_name ?? '',
                                    $r->weight,
                                    $r->cost,
                                    $r->shipping_cost,
                                    $r->status,
                                    $r->np_status ?? '',
                                    $r->created_at?->format('Y-m-d H:i') ?? '',
                                ];
                            }

                            $filename = 'np-shipments-' . now()->format('Y-m-d-His') . '.csv';
                            return response()->streamDownload(function () use ($headers, $rows) {
                                echo "\xEF\xBB\xBF"; // UTF-8 BOM for Excel
                                $out = fopen('php://output', 'w');
                                fputcsv($out, $headers, ';');
                                foreach ($rows as $row) {
                                    fputcsv($out, $row, ';');
                                }
                                fclose($out);
                            }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
                        })
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('track_all')
                    ->label('Відстежити всі активні')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalDescription('Відстежити всі ТТН зі статусом "Створена" або "В дорозі"?')
                    ->action(function () {
                        $shipments = NpShipment::needsTracking()->get();
                        $updated = 0;

                        foreach ($shipments as $shipment) {
                            static::trackShipment($shipment);
                            $updated++;
                        }

                        Notification::make()
                            ->title("Відстежено {$updated} ТТН")
                            ->success()
                            ->send();
                    }),
            ]);
    }

    /**
     * Track a single shipment
     */
    public static function trackShipment(NpShipment $shipment): void
    {
        if (! $shipment->ttn) return;

        try {
            $service = app(NovaPoshtaApiService::class);
            $result = $service->getShipmentTrackingInfo($shipment->ttn);

            if (($result['success'] ?? false) && ! empty($result['data'])) {
                $doc = $result['data'][0];

                $statusCode = $doc['StatusCode'] ?? null;
                $newStatus = NpShipment::resolveStatusFromCode($statusCode);

                $history = $shipment->tracking_history ?? [];
                $history[] = [
                    'status' => $doc['Status'] ?? '',
                    'status_code' => $statusCode,
                    'date' => now()->toDateTimeString(),
                    'city' => $doc['CityRecipient'] ?? '',
                    'warehouse' => $doc['WarehouseRecipient'] ?? '',
                ];

                $shipment->update([
                    'status' => $newStatus,
                    'np_status' => $doc['Status'] ?? $shipment->np_status,
                    'np_status_code' => $statusCode,
                    'shipping_cost' => $doc['DocumentCost'] ?? $shipment->shipping_cost,
                    'estimated_delivery_date' => $doc['ScheduledDeliveryDate'] ?? $shipment->estimated_delivery_date,
                    'tracking_history' => $history,
                    'last_tracked_at' => now(),
                ]);

                Notification::make()
                    ->title("ТТН {$shipment->ttn}")
                    ->body($doc['Status'] ?? 'Статус оновлено')
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title("ТТН {$shipment->ttn}")
                    ->body('Не вдалося отримати статус')
                    ->warning()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Помилка відстеження')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNpShipments::route('/'),
            'create' => Pages\CreateNpShipment::route('/create'),
            'edit' => Pages\EditNpShipment::route('/{record}/edit'),
        ];
    }
}
