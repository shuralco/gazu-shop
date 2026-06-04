<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Платежі';

    protected static ?string $modelLabel = 'Платіж';

    protected static ?string $pluralModelLabel = 'Платежі';

    protected static ?string $navigationGroup = 'Продажі';

    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('order_id')
                    ->label('Замовлення')
                    ->relationship('order', 'id')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('gateway')
                    ->label('Платіжний шлюз')
                    ->options([
                        'liqpay' => 'LiqPay',
                        'wayforpay' => 'WayForPay',
                        'monobank' => 'Monobank',
                    ])
                    ->required(),
                TextInput::make('amount')
                    ->label('Сума')
                    ->numeric()
                    ->step(0.01)
                    ->required(),
                Select::make('status')
                    ->label('Статус')
                    ->options([
                        'pending' => 'Очікує',
                        'processing' => 'Обробляється',
                        'success' => 'Успішно',
                        'failed' => 'Неуспішно',
                        'reversed' => 'Повернено',
                    ])
                    ->default('pending')
                    ->required(),
                TextInput::make('currency')
                    ->label('Валюта')
                    ->default('UAH')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->limit(8)
                    ->searchable(),
                TextColumn::make('order.name')
                    ->label('Замовник')
                    ->searchable(),
                TextColumn::make('order.email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('gateway')
                    ->label('Шлюз')
                    ->badge(),
                TextColumn::make('amount')
                    ->label('Сума')
                    ->formatStateUsing(fn ($state) => number_format($state, 2, '.', ' ').' грн'),
                BadgeColumn::make('status')
                    ->label('Статус')
                    ->colors([
                        'secondary' => 'pending',
                        'warning' => 'processing',
                        'success' => 'success',
                        'danger' => 'failed',
                        'info' => 'reversed',
                    ]),
                TextColumn::make('created_at')
                    ->label('Створено')
                    ->dateTime('d.m.Y H:i'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'pending' => 'Очікує',
                        'processing' => 'Обробляється',
                        'success' => 'Успішно',
                        'failed' => 'Неуспішно',
                        'reversed' => 'Повернено',
                    ]),
                SelectFilter::make('gateway')
                    ->label('Шлюз')
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
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Платежі не можна видаляти
                ]),
            ])
            ->headerActions([
                // Платежі створюються автоматично при замовленнях
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListPayments::route('/'),
        ];
    }
}
