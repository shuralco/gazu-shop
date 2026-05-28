<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShippingProviderResource\Pages;
use App\Models\ShippingProvider;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ShippingProviderResource extends Resource
{
    protected static ?string $model = ShippingProvider::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $navigationGroup = 'Доставка та оплата';

    protected static ?string $navigationLabel = 'Провайдери доставки';

    protected static ?string $pluralLabel = 'Провайдери доставки';

    protected static ?string $label = 'Провайдер доставки';

    protected static ?int $navigationSort = 4;

    /**
     * Hidden from sidebar — this CRUD duplicates the cards on /admin/integrations-page.
     * Still reachable directly via /admin/shipping-providers for legacy access.
     */
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основна інформація')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Назва')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('code')
                            ->label('Код')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->helperText('Унікальний код провайдера (напр. novaposhta, ukrposhta)'),

                        Forms\Components\TextInput::make('api_endpoint')
                            ->label('API Endpoint')
                            ->url()
                            ->maxLength(255)
                            ->helperText('URL для API запитів'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Активний')
                            ->default(true)
                            ->helperText('Чи доступний провайдер для використання'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Конфігурація')
                    ->schema([
                        Forms\Components\KeyValue::make('configuration')
                            ->label('Параметри конфігурації')
                            ->addButtonLabel('Додати параметр')
                            ->keyLabel('Ключ')
                            ->valueLabel('Значення')
                            ->helperText('API ключі та інші налаштування провайдера')
                            ->columnSpan('full'),
                    ]),

                Forms\Components\Section::make('API Налаштування')
                    ->schema([
                        Forms\Components\Fieldset::make('Нова Пошта')
                            ->schema([
                                Forms\Components\TextInput::make('configuration.api_key')
                                    ->label('API Ключ')
                                    ->default('737254fe131eca6c3ab91925ef9eff45')
                                    ->password()
                                    ->revealable()
                                    ->helperText('API ключ: 737254fe131eca6c3ab91925ef9eff45'),

                                Forms\Components\Toggle::make('configuration.sandbox')
                                    ->label('Тестовий режим')
                                    ->helperText('Використовувати тестове середовище'),

                                Forms\Components\TextInput::make('configuration.sender_city_ref')
                                    ->label('Ref міста відправника')
                                    ->helperText('ID міста в системі Нової Пошти'),

                                Forms\Components\TextInput::make('configuration.sender_phone')
                                    ->label('Телефон відправника')
                                    ->tel(),
                            ])
                            ->visible(fn ($get) => $get('code') === 'novaposhta'),

                        Forms\Components\Fieldset::make('УкрПошта')
                            ->schema([
                                Forms\Components\TextInput::make('configuration.bearer_token')
                                    ->label('🔑 Bearer Token')
                                    ->password()
                                    ->revealable()
                                    ->helperText('Основний токен доступу до API УкрПошти')
                                    ->placeholder('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...'),

                                Forms\Components\TextInput::make('configuration.counterparty_token')
                                    ->label('👤 Counterparty Token')
                                    ->password()
                                    ->revealable()
                                    ->helperText('Токен контрагента для операцій з клієнтами')
                                    ->placeholder('cp-xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx'),

                                Forms\Components\TextInput::make('configuration.api_key')
                                    ->label('🗝️ API Key')
                                    ->password()
                                    ->revealable()
                                    ->helperText('Додатковий API ключ для спеціальних операцій')
                                    ->placeholder('uk_api_xxxxxxxxxxxxxxxxxxxxxxxx'),

                                Forms\Components\Toggle::make('configuration.sandbox')
                                    ->label('🧪 Тестовий режим')
                                    ->helperText('Використовувати тестове середовище API')
                                    ->default(true),

                                Forms\Components\TextInput::make('configuration.sender_region_id')
                                    ->label('🏴󠁵󠁡󠁫󠁹󠁿 ID регіону відправника')
                                    ->helperText('Код регіону в системі УкрПошти (напр. 80000000000 для Києва)')
                                    ->placeholder('80000000000'),

                                Forms\Components\TextInput::make('configuration.sender_city_id')
                                    ->label('🏙️ ID міста відправника')
                                    ->helperText('Код міста в системі УкрПошти')
                                    ->placeholder('80000000001'),

                                Forms\Components\TextInput::make('configuration.delengine_api_key')
                                    ->label('🗺️ DelEngine API Key')
                                    ->password()
                                    ->revealable()
                                    ->helperText('API ключ DelEngine для реальних даних міст та відділень')
                                    ->placeholder('v4n208uaysugpqe6v3ijelusl601fduv'),
                            ])
                            ->columns(2)
                            ->visible(fn ($get) => $get('code') === 'ukrposhta'),

                        Forms\Components\Fieldset::make('Rozetka Delivery')
                            ->schema([
                                Forms\Components\TextInput::make('configuration.api_key')
                                    ->label('API Ключ')
                                    ->password()
                                    ->revealable(),

                                Forms\Components\TextInput::make('configuration.merchant_id')
                                    ->label('Merchant ID')
                                    ->helperText('Ідентифікатор продавця'),

                                Forms\Components\Toggle::make('configuration.sandbox')
                                    ->label('Тестовий режим'),
                            ])
                            ->visible(fn ($get) => $get('code') === 'rozetka'),
                    ])
                    ->collapsible()
                    ->collapsed(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Назва')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('code')
                    ->label('Код')
                    ->badge()
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активний')
                    ->boolean(),

                Tables\Columns\TextColumn::make('shippingMethods_count')
                    ->counts('shippingMethods')
                    ->label('Методи доставки')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('api_endpoint')
                    ->label('API Endpoint')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Створено')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Оновлено')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_active')
                    ->label('Статус')
                    ->options([
                        true => 'Активні',
                        false => 'Неактивні',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('')
                    ->icon('heroicon-o-eye')
                    ->size('lg')
                    ->tooltip('Перегляд'),
                Tables\Actions\EditAction::make()
                    ->label('')
                    ->icon('heroicon-o-pencil')
                    ->size('lg')
                    ->tooltip('Змінити'),
                Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->icon('heroicon-o-trash')
                    ->size('lg')
                    ->tooltip('Видалити'),
                Tables\Actions\Action::make('test_connection')
                    ->label('')
                    ->icon('heroicon-o-signal')
                    ->size('lg')
                    ->tooltip('Тест з\'єднання')
                    ->action(function (ShippingProvider $record) {
                        try {
                            // Тестуємо пряме API підключення
                            if ($record->code === 'novaposhta') {
                                $apiKey = $record->configuration['api_key'] ?? config('novaposhta.api_key');

                                if (empty($apiKey)) {
                                    \Filament\Notifications\Notification::make()
                                        ->warning()
                                        ->title('API ключ відсутній')
                                        ->body('Налаштуйте API ключ в конфігурації провайдера')
                                        ->send();

                                    return;
                                }

                                $response = \Illuminate\Support\Facades\Http::timeout(10)->post('https://api.novaposhta.ua/v2.0/json/', [
                                    'modelName' => 'Common',
                                    'calledMethod' => 'getTypesOfPayers',
                                    'methodProperties' => new \stdClass,
                                    'apiKey' => $apiKey,
                                ]);

                                $data = $response->json();

                                if ($data['success'] ?? false) {
                                    \Filament\Notifications\Notification::make()
                                        ->success()
                                        ->title('З\'єднання успішне')
                                        ->body('API Нової Пошти працює. Ключ: '.substr($apiKey, 0, 8).'...')
                                        ->send();
                                } else {
                                    $errors = implode(', ', $data['errors'] ?? ['Невідома помилка']);
                                    \Filament\Notifications\Notification::make()
                                        ->danger()
                                        ->title('API помилка')
                                        ->body("Помилки: {$errors}")
                                        ->send();
                                }
                            } elseif ($record->code === 'ukrposhta') {
                                $bearerToken = $record->configuration['bearer_token'] ?? '';
                                $counterpartyToken = $record->configuration['counterparty_token'] ?? '';

                                if (empty($bearerToken)) {
                                    \Filament\Notifications\Notification::make()
                                        ->warning()
                                        ->title('Bearer Token відсутній')
                                        ->body('Налаштуйте Bearer Token в конфігурації УкрПошти')
                                        ->send();

                                    return;
                                }

                                try {
                                    // Тестуємо API УкрПошти через отримання міст
                                    $response = \Illuminate\Support\Facades\Http::timeout(10)
                                        ->withHeaders([
                                            'Authorization' => 'Bearer '.$bearerToken,
                                            'Content-Type' => 'application/json',
                                        ])
                                        ->get('https://api.ukrposhta.ua/v1/cities', [
                                            'limit' => 1,
                                        ]);

                                    if ($response->successful()) {
                                        $data = $response->json();

                                        \Filament\Notifications\Notification::make()
                                            ->success()
                                            ->title('З\'єднання з УкрПошта успішне')
                                            ->body('API працює. Bearer: '.substr($bearerToken, 0, 8).'...')
                                            ->send();
                                    } else {
                                        \Filament\Notifications\Notification::make()
                                            ->warning()
                                            ->title('УкрПошта API недоступне')
                                            ->body('HTTP статус: '.$response->status())
                                            ->send();
                                    }
                                } catch (\Exception $e) {
                                    \Filament\Notifications\Notification::make()
                                        ->danger()
                                        ->title('Помилка з\'єднання з УкрПошта')
                                        ->body($e->getMessage())
                                        ->send();
                                }
                            } else {
                                \Filament\Notifications\Notification::make()
                                    ->warning()
                                    ->title('Провайдер не налаштований')
                                    ->body("Тестування для {$record->code} ще не реалізовано")
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('Помилка тестування')
                                ->body($e->getMessage())
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->color('info'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Активувати')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->requiresConfirmation()
                        ->color('success'),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Деактивувати')
                        ->icon('heroicon-o-x-circle')
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->requiresConfirmation()
                        ->color('danger'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShippingProviders::route('/'),
            'create' => Pages\CreateShippingProvider::route('/create'),
            'edit' => Pages\EditShippingProvider::route('/{record}/edit'),
            'view' => Pages\ViewShippingProvider::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::where('is_active', true)->count() > 0 ? 'success' : 'danger';
    }
}
