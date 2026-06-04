<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentGatewaySettingsResource\Pages;
use App\Models\PaymentGatewaySettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentGatewaySettingsResource extends Resource
{
    protected static ?string $model = PaymentGatewaySettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Склад і доставка';

    protected static ?string $navigationLabel = 'Платіжні системи';

    protected static ?string $pluralModelLabel = 'Платіжні системи';

    protected static ?string $modelLabel = 'Платіжна система';

    protected static ?int $navigationSort = 80;

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
                            ->helperText('Унікальний код платіжної системи (liqpay, wayforpay, monobank)'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Активна')
                            ->default(true)
                            ->helperText('Чи доступна платіжна система для використання'),

                        Forms\Components\Textarea::make('description')
                            ->label('Опис')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Фінансові налаштування')
                    ->schema([
                        Forms\Components\TextInput::make('fee_percentage')
                            ->label('Комісія (%)')
                            ->numeric()
                            ->step(0.01)
                            ->suffix('%')
                            ->default(0),

                        Forms\Components\TextInput::make('min_amount')
                            ->label('Мінімальна сума')
                            ->numeric()
                            ->step(0.01)
                            ->suffix('грн')
                            ->default(1),

                        Forms\Components\TextInput::make('max_amount')
                            ->label('Максимальна сума')
                            ->numeric()
                            ->step(0.01)
                            ->suffix('грн')
                            ->default(999999),

                        Forms\Components\TextInput::make('currency')
                            ->label('Валюта')
                            ->default('UAH')
                            ->maxLength(3),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('API Налаштування')
                    ->schema([
                        Forms\Components\Fieldset::make('LiqPay')
                            ->schema([
                                Forms\Components\TextInput::make('configuration.public_key')
                                    ->label('Public Key')
                                    ->helperText('Публічний ключ LiqPay'),

                                Forms\Components\TextInput::make('configuration.private_key')
                                    ->label('Private Key')
                                    ->password()
                                    ->revealable()
                                    ->helperText('Приватний ключ LiqPay'),

                                Forms\Components\Toggle::make('configuration.sandbox')
                                    ->label('Тестовий режим')
                                    ->default(true),
                            ])
                            ->visible(fn ($get) => $get('code') === 'liqpay'),

                        Forms\Components\Fieldset::make('WayForPay')
                            ->schema([
                                Forms\Components\TextInput::make('configuration.merchant_account')
                                    ->label('Merchant Account')
                                    ->helperText('Ідентифікатор мерчанта'),

                                Forms\Components\TextInput::make('configuration.merchant_secret_key')
                                    ->label('Secret Key')
                                    ->password()
                                    ->revealable()
                                    ->helperText('Секретний ключ мерчанта'),

                                Forms\Components\TextInput::make('configuration.merchant_domain')
                                    ->label('Домен мерчанта')
                                    ->url()
                                    ->helperText('Домен сайту для верифікації'),

                                Forms\Components\Toggle::make('configuration.sandbox')
                                    ->label('Тестовий режим')
                                    ->default(true),
                            ])
                            ->visible(fn ($get) => $get('code') === 'wayforpay'),

                        Forms\Components\Fieldset::make('Monobank')
                            ->schema([
                                Forms\Components\TextInput::make('configuration.merchant_id')
                                    ->label('Merchant ID')
                                    ->helperText('Ідентифікатор мерчанта в Monobank'),

                                Forms\Components\TextInput::make('configuration.api_token')
                                    ->label('API Token')
                                    ->password()
                                    ->revealable()
                                    ->helperText('API токен для роботи з Monobank'),

                                Forms\Components\Textarea::make('configuration.webhook_public_key')
                                    ->label('Webhook Public Key')
                                    ->rows(3)
                                    ->helperText('Публічний ключ для верифікації webhook'),

                                Forms\Components\Toggle::make('configuration.sandbox')
                                    ->label('Тестовий режим')
                                    ->default(true),
                            ])
                            ->visible(fn ($get) => $get('code') === 'monobank'),
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
                    ->label('Активна')
                    ->boolean(),

                Tables\Columns\TextColumn::make('fee_percentage')
                    ->label('Комісія')
                    ->suffix('%')
                    ->sortable(),

                Tables\Columns\TextColumn::make('min_amount')
                    ->label('Мін. сума')
                    ->formatStateUsing(fn ($state) => number_format($state, 2, '.', ' ').' грн')
                    ->sortable(),

                Tables\Columns\TextColumn::make('max_amount')
                    ->label('Макс. сума')
                    ->formatStateUsing(fn ($state) => number_format($state, 2, '.', ' ').' грн')
                    ->sortable(),

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

                Tables\Filters\SelectFilter::make('code')
                    ->label('Платіжна система')
                    ->options([
                        'liqpay' => 'LiqPay',
                        'wayforpay' => 'WayForPay',
                        'monobank' => 'Monobank',
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
                    ->action(function (PaymentGatewaySettings $record) {
                        try {
                            $gateway = $record->code;
                            $config = $record->configuration;

                            if ($gateway === 'liqpay') {
                                $publicKey = $config['public_key'] ?? '';
                                $privateKey = $config['private_key'] ?? '';

                                if (empty($publicKey) || empty($privateKey)) {
                                    \Filament\Notifications\Notification::make()
                                        ->warning()
                                        ->title('Налаштування відсутні')
                                        ->body('Налаштуйте публічний та приватний ключі LiqPay')
                                        ->send();

                                    return;
                                }

                                \Filament\Notifications\Notification::make()
                                    ->success()
                                    ->title('LiqPay налаштовано')
                                    ->body('Ключі встановлені. Public: '.substr($publicKey, 0, 8).'...')
                                    ->send();

                            } elseif ($gateway === 'monobank') {
                                $apiToken = $config['api_token'] ?? '';

                                if (empty($apiToken)) {
                                    \Filament\Notifications\Notification::make()
                                        ->warning()
                                        ->title('API Token відсутній')
                                        ->body('Налаштуйте API токен Monobank')
                                        ->send();

                                    return;
                                }

                                $response = \Illuminate\Support\Facades\Http::withHeaders([
                                    'X-Token' => $apiToken,
                                ])->get('https://api.monobank.ua/api/merchant/pubkey');

                                if ($response->successful()) {
                                    \Filament\Notifications\Notification::make()
                                        ->success()
                                        ->title('З\'єднання успішне')
                                        ->body('API Monobank працює')
                                        ->send();
                                } else {
                                    \Filament\Notifications\Notification::make()
                                        ->danger()
                                        ->title('Помилка API')
                                        ->body('Перевірте API токен Monobank')
                                        ->send();
                                }

                            } else {
                                \Filament\Notifications\Notification::make()
                                    ->info()
                                    ->title('Тестування недоступне')
                                    ->body("Тестування для {$gateway} ще не реалізовано")
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
            ])
            ->defaultSort('code');
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
            'index' => Pages\ListPaymentGatewaySettings::route('/'),
            'create' => Pages\CreatePaymentGatewaySettings::route('/create'),
            'edit' => Pages\EditPaymentGatewaySettings::route('/{record}/edit'),
        ];
    }
}
