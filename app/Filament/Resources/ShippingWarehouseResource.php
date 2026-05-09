<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShippingWarehouseResource\Pages;
use App\Models\ShippingWarehouse;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ShippingWarehouseResource extends Resource
{
    protected static ?string $model = ShippingWarehouse::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationGroup = 'Доставка та оплата';

    protected static ?string $modelLabel = 'Відділення/Поштомат';

    protected static ?string $pluralModelLabel = 'Відділення та Поштомати';

    protected static ?int $navigationSort = 21;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основна інформація')
                    ->schema([
                        Forms\Components\TextInput::make('external_id')
                            ->label('Зовнішній ID (Ref)')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('name')
                            ->label('Назва')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('short_address')
                            ->label('Адреса')
                            ->required()
                            ->rows(2),
                        Forms\Components\Select::make('type')
                            ->label('Тип')
                            ->options([
                                'warehouse' => 'Відділення',
                                'postomat' => 'Поштомат',
                            ])
                            ->required()
                            ->default('warehouse'),
                        Forms\Components\Select::make('provider_code')
                            ->label('Провайдер')
                            ->options([
                                'novaposhta' => 'Нова Пошта',
                            ])
                            ->required()
                            ->default('novaposhta'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Активний')
                            ->default(true),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Розташування')
                    ->schema([
                        Forms\Components\TextInput::make('city_name')
                            ->label('Назва міста')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('city_ref')
                            ->label('Ref міста')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('latitude')
                            ->label('Широта')
                            ->numeric()
                            ->step(0.0000001),
                        Forms\Components\TextInput::make('longitude')
                            ->label('Довгота')
                            ->numeric()
                            ->step(0.0000001),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Додаткові дані')
                    ->schema([
                        Forms\Components\Textarea::make('schedule')
                            ->label('Графік роботи (JSON)')
                            ->rows(3)
                            ->helperText('JSON формат графіку роботи'),
                        Forms\Components\Textarea::make('additional_data')
                            ->label('Додаткові дані (JSON)')
                            ->rows(3)
                            ->helperText('Додаткові дані з API'),
                    ])
                    ->columns(1)
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('external_id')
                    ->label('ID')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Назва')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('city_name')
                    ->label('Місто')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('short_address')
                    ->label('Адреса')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Тип')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'warehouse' => 'Відділення',
                        'postomat' => 'Поштомат',
                        default => 'Невідомо',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'warehouse' => 'primary',
                        'postomat' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\BadgeColumn::make('provider_code')
                    ->label('Провайдер')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'novaposhta' => 'Нова Пошта',
                        default => $state,
                    })
                    ->color('info'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активний')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Створено')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Тип')
                    ->options([
                        'warehouse' => 'Відділення',
                        'postomat' => 'Поштомат',
                    ]),
                Tables\Filters\SelectFilter::make('provider_code')
                    ->label('Провайдер')
                    ->options([
                        'novaposhta' => 'Нова Пошта',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Статус')
                    ->placeholder('Всі')
                    ->trueLabel('Активні')
                    ->falseLabel('Неактивні'),
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
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Активувати')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_active' => true])),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Деактивувати')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['is_active' => false])),
                ]),
            ])
            ->defaultSort('city_name', 'asc')
            ->striped();
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
            'index' => Pages\ListShippingWarehouses::route('/'),
            'create' => Pages\CreateShippingWarehouse::route('/create'),
            'edit' => Pages\EditShippingWarehouse::route('/{record}/edit'),
        ];
    }
}
