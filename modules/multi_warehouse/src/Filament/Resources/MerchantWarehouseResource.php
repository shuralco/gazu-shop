<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MerchantWarehouseResource\Pages;
use App\Models\MerchantWarehouse;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MerchantWarehouseResource extends Resource
{
    use \App\Filament\Concerns\RequiresModule;

    protected static string $moduleKey = 'multi_warehouse';

    protected static ?string $model = MerchantWarehouse::class;

    protected static ?string $slug = 'merchant-warehouses';

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationGroup = 'Доставка та оплата';

    protected static ?string $navigationLabel = 'Склади магазину';

    protected static ?string $modelLabel = 'Склад';

    protected static ?string $pluralModelLabel = 'Склади магазину';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('warehouse_tabs')
                ->columnSpanFull()
                ->tabs([
                    Forms\Components\Tabs\Tab::make('Основне')
                        ->icon('heroicon-o-identification')
                        ->schema([
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\TextInput::make('code')
                                    ->label('Код складу')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->placeholder('MAIN-01, KYIV-1, LVIV-2'),
                                Forms\Components\TextInput::make('name')
                                    ->label('Назва')
                                    ->required(),
                                Forms\Components\Select::make('type')
                                    ->label('Тип')
                                    ->options([
                                        MerchantWarehouse::TYPE_OWN => 'Власний',
                                        MerchantWarehouse::TYPE_DROP_SHIP => 'Drop-shipping',
                                        MerchantWarehouse::TYPE_VIRTUAL => 'Віртуальний',
                                    ])
                                    ->default(MerchantWarehouse::TYPE_OWN)
                                    ->required(),
                                Forms\Components\Select::make('manager_user_id')
                                    ->label('Менеджер')
                                    ->relationship('manager', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->nullable(),
                                Forms\Components\TextInput::make('phone')
                                    ->label('Телефон')
                                    ->tel(),
                                Forms\Components\TextInput::make('email')
                                    ->label('Email')
                                    ->email(),
                            ]),
                            Forms\Components\Grid::make(4)->schema([
                                Forms\Components\Select::make('status')
                                    ->label('Статус складу')
                                    ->options(fn () => \App\Models\WarehouseStatus::options())
                                    ->default(fn () => \App\Models\WarehouseStatus::defaultKey())
                                    ->helperText('Кастомні статуси — у «Доставка та оплата → Статуси складів»'),
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Активний')
                                    ->default(true),
                                Forms\Components\Toggle::make('is_default')
                                    ->label('За замовчуванням')
                                    ->helperText('Лише один склад може бути default.'),
                                Forms\Components\Toggle::make('pickup_supported')
                                    ->label('Підтримує самовивіз'),
                                Forms\Components\TextInput::make('sort_order')
                                    ->label('Порядок')
                                    ->numeric()
                                    ->default(0),
                            ]),
                        ]),

                    Forms\Components\Tabs\Tab::make('Адреса')
                        ->icon('heroicon-o-map-pin')
                        ->schema([
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\TextInput::make('country')
                                    ->label('Країна')
                                    ->maxLength(2)
                                    ->default('UA'),
                                Forms\Components\TextInput::make('region')
                                    ->label('Область'),
                                Forms\Components\TextInput::make('city')
                                    ->label('Місто'),
                                Forms\Components\TextInput::make('postcode')
                                    ->label('Індекс'),
                            ]),
                            Forms\Components\TextInput::make('address')
                                ->label('Адреса')
                                ->columnSpanFull(),
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\TextInput::make('latitude')
                                    ->label('Широта')
                                    ->numeric()
                                    ->step('0.0000001'),
                                Forms\Components\TextInput::make('longitude')
                                    ->label('Довгота')
                                    ->numeric()
                                    ->step('0.0000001'),
                            ]),
                            Forms\Components\KeyValue::make('working_hours')
                                ->label('Графік роботи')
                                ->keyLabel('День')
                                ->valueLabel('Години (наприклад 09:00–18:00)')
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('delivery_eta')
                                ->label('Термін доставки')
                                ->helperText('Короткий лейбл, який бачить клієнт у селекторі складу: «1 день», «2-3 дні»')
                                ->maxLength(64)
                                ->placeholder('1-3 дні'),
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\TextInput::make('shipping_cost')
                                    ->label('Ставка доставки (₴)')
                                    ->helperText('Базова ставка доставки із цього складу')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->prefix('₴')
                                    ->default(0),
                                Forms\Components\TextInput::make('free_shipping_threshold')
                                    ->label('Поріг безкоштовної доставки (₴)')
                                    ->helperText('Якщо сума замовлення з цього складу ≥ цього значення, доставка безкоштовна. Порожньо = поріг вимкнено')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->prefix('₴'),
                            ]),
                        ]),

                    Forms\Components\Tabs\Tab::make('Нова Пошта sender')
                        ->icon('heroicon-o-truck')
                        ->schema([
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\TextInput::make('np_sender_ref')
                                    ->label('Sender Ref')
                                    ->placeholder('uuid контрагента-відправника'),
                                Forms\Components\TextInput::make('np_sender_city_ref')
                                    ->label('City Ref')
                                    ->placeholder('uuid міста-відправника'),
                                Forms\Components\TextInput::make('np_sender_warehouse_ref')
                                    ->label('Sender Warehouse Ref')
                                    ->placeholder('uuid відділення відправлення'),
                                Forms\Components\TextInput::make('np_contact_person_ref')
                                    ->label('Contact Person Ref'),
                                Forms\Components\TextInput::make('np_sender_phone')
                                    ->label('Телефон відправника')
                                    ->tel()
                                    ->placeholder('+380...'),
                            ]),
                        ]),

                    Forms\Components\Tabs\Tab::make('УкрПошта sender')
                        ->icon('heroicon-o-envelope')
                        ->schema([
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\TextInput::make('up_sender_uuid')
                                    ->label('Sender UUID'),
                                Forms\Components\TextInput::make('up_sender_address_uuid')
                                    ->label('Sender Address UUID'),
                                Forms\Components\TextInput::make('up_counterparty_token')
                                    ->label('Counterparty Token')
                                    ->password()
                                    ->revealable(),
                                Forms\Components\TextInput::make('up_ecom_bearer')
                                    ->label('Ecom Bearer Token')
                                    ->password()
                                    ->revealable(),
                            ]),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('Код')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('name')->label('Назва')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Тип')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'own' => 'success',
                        'drop_ship' => 'warning',
                        'virtual' => 'gray',
                        default => 'primary',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'own' => 'Власний',
                        'drop_ship' => 'Drop-ship',
                        'virtual' => 'Віртуальний',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('city')->label('Місто'),
                Tables\Columns\TextColumn::make('delivery_eta')
                    ->label('Доставка')
                    ->placeholder('—')
                    ->badge()
                    ->color('info')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_default')->label('Default')->boolean(),
                Tables\Columns\TextColumn::make('status')->label('Статус')->badge()
                    ->formatStateUsing(fn (?string $state): string => \App\Models\WarehouseStatus::options()[$state] ?? ($state ?: '—'))
                    ->color(fn (?string $state): string => \App\Models\WarehouseStatus::colors()[$state] ?? 'gray')
                    ->icon(fn (?string $state): ?string => \App\Models\WarehouseStatus::icons()[$state] ?? null),
                Tables\Columns\IconColumn::make('is_active')->label('Активний')->boolean()->sortable(),
                Tables\Columns\TextColumn::make('inventory_count')
                    ->label('SKU')
                    ->counts('inventory'),
                Tables\Columns\TextColumn::make('manager.name')->label('Менеджер')->placeholder('—'),
                Tables\Columns\TextColumn::make('sort_order')->label('Порядок')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Активний'),
                Tables\Filters\SelectFilter::make('type')->label('Тип')->options([
                    'own' => 'Власний',
                    'drop_ship' => 'Drop-ship',
                    'virtual' => 'Віртуальний',
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) => ! $record->is_default),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMerchantWarehouses::route('/'),
            'create' => Pages\CreateMerchantWarehouse::route('/create'),
            'edit' => Pages\EditMerchantWarehouse::route('/{record}/edit'),
        ];
    }
}
