<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShippingMethodResource\Pages;
use App\Models\ShippingMethod;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ShippingMethodResource extends Resource
{
    protected static ?string $model = ShippingMethod::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Доставка та оплата';

    protected static ?string $navigationLabel = 'Методи доставки';

    protected static ?string $pluralLabel = 'Методи доставки';

    protected static ?string $label = 'Метод доставки';

    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основна інформація')
                    ->schema([
                        Forms\Components\Select::make('provider_id')
                            ->label('Провайдер')
                            ->relationship('provider', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Назва')
                                    ->required(),
                                Forms\Components\TextInput::make('code')
                                    ->label('Код')
                                    ->required()
                                    ->unique(),
                            ]),

                        Forms\Components\TextInput::make('name')
                            ->label('Назва методу')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('method_code')
                            ->label('Код методу')
                            ->required()
                            ->maxLength(100)
                            ->helperText('Унікальний код в рамках провайдера'),

                        Forms\Components\Textarea::make('description')
                            ->label('Опис')
                            ->rows(3)
                            ->columnSpan('full'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Активний')
                            ->default(true)
                            ->columnSpan('full'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Тарифи та обмеження')
                    ->schema([
                        Forms\Components\TextInput::make('base_cost')
                            ->label('Базова вартість (грн)')
                            ->numeric()
                            ->default(0)
                            ->required()
                            ->prefix('₴')
                            ->helperText('Фіксована вартість доставки'),

                        Forms\Components\TextInput::make('per_kg_cost')
                            ->label('Вартість за кг (грн)')
                            ->numeric()
                            ->default(0)
                            ->required()
                            ->prefix('₴/кг')
                            ->helperText('Додаткова вартість за кілограм'),

                        Forms\Components\TextInput::make('estimated_days')
                            ->label('Орієнтовний час доставки (днів)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(30)
                            ->suffix('днів'),

                        Forms\Components\TextInput::make('max_weight')
                            ->label('Максимальна вага (кг)')
                            ->numeric()
                            ->suffix('кг')
                            ->helperText('Залиште порожнім для необмеженої ваги'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Додаткова конфігурація')
                    ->schema([
                        Forms\Components\KeyValue::make('additional_config')
                            ->label('Додаткові параметри')
                            ->addButtonLabel('Додати параметр')
                            ->keyLabel('Параметр')
                            ->valueLabel('Значення')
                            ->helperText('Специфічні налаштування для цього методу')
                            ->columnSpan('full'),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('provider.name')
                    ->label('Провайдер')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Назва методу')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('method_code')
                    ->label('Код')
                    ->badge()
                    ->color('gray')
                    ->searchable(),

                Tables\Columns\TextColumn::make('base_cost')
                    ->label('Базова вартість')
                    ->formatStateUsing(fn ($state) => number_format($state, 2, '.', ' ').' грн')
                    ->sortable(),

                Tables\Columns\TextColumn::make('per_kg_cost')
                    ->label('За кг')
                    ->formatStateUsing(fn ($state) => number_format($state, 2, '.', ' ').' грн')
                    ->sortable(),

                Tables\Columns\TextColumn::make('estimated_days')
                    ->label('Час доставки')
                    ->suffix(' дн.')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state <= 1 => 'success',
                        $state <= 3 => 'warning',
                        default => 'danger',
                    }),

                Tables\Columns\TextColumn::make('max_weight')
                    ->label('Макс. вага')
                    ->suffix(' кг')
                    ->placeholder('Необмежено'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активний')
                    ->boolean(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Оновлено')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('provider_id')
                    ->label('Провайдер')
                    ->relationship('provider', 'name'),

                Tables\Filters\SelectFilter::make('is_active')
                    ->label('Статус')
                    ->options([
                        true => 'Активні',
                        false => 'Неактивні',
                    ]),

                Tables\Filters\Filter::make('express')
                    ->label('Експрес доставка')
                    ->query(fn (Builder $query): Builder => $query->where('estimated_days', '<=', 2)),
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
                Tables\Actions\Action::make('toggle_active')
                    ->label('')
                    ->icon(fn ($record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn ($record) => $record->is_active ? 'danger' : 'success')
                    ->size('lg')
                    ->tooltip(fn ($record) => $record->is_active ? 'Деактивувати' : 'Активувати')
                    ->action(fn ($record) => $record->update(['is_active' => ! $record->is_active]))
                    ->requiresConfirmation(),
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
            ])
            ->defaultSort('provider_id');
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
            'index' => Pages\ListShippingMethods::route('/'),
            'create' => Pages\CreateShippingMethod::route('/create'),
            'edit' => Pages\EditShippingMethod::route('/{record}/edit'),
            'view' => Pages\ViewShippingMethod::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::where('is_active', true)->count() > 0 ? 'success' : 'warning';
    }
}
