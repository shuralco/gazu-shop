<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderStatusResource\Pages;
use App\Models\OrderStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderStatusResource extends Resource
{
    protected static ?string $model = OrderStatus::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';
    protected static ?string $navigationGroup = 'Продажі';
    protected static ?string $navigationLabel = 'Статуси замовлень';
    protected static ?string $modelLabel = 'статус замовлення';
    protected static ?string $pluralModelLabel = 'Статуси замовлень';
    protected static ?int $navigationSort = 30;

    private const COLORS = [
        'gray' => 'Сірий', 'primary' => 'Основний', 'info' => 'Синій',
        'success' => 'Зелений', 'warning' => 'Жовтий', 'danger' => 'Червоний',
    ];

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('label')
                ->label('Назва')
                ->required()
                ->maxLength(80),
            Forms\Components\TextInput::make('key')
                ->label('Ключ (системний)')
                ->helperText('Зберігається в замовленні. Не змінюйте після створення — зламає наявні замовлення.')
                ->required()
                ->alphaDash()
                ->unique(ignoreRecord: true)
                ->disabledOn('edit'),
            Forms\Components\Select::make('color')
                ->label('Колір бейджа')
                ->options(self::COLORS)
                ->default('gray')
                ->required(),
            Forms\Components\TextInput::make('icon')
                ->label('Іконка (heroicon)')
                ->placeholder('heroicon-o-clock')
                ->helperText('Необов\'язково. Напр. heroicon-o-check-circle'),
            Forms\Components\TextInput::make('sort_order')
                ->label('Порядок')
                ->numeric()
                ->default(0),
            Forms\Components\Toggle::make('is_default')
                ->label('За замовчуванням для нових замовлень')
                ->helperText('Має бути лише в одного статусу'),
            Forms\Components\Toggle::make('is_final')
                ->label('Термінальний (виконано/скасовано)'),
            Forms\Components\Toggle::make('is_active')
                ->label('Активний')
                ->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('label')->label('Назва')
                    ->badge()
                    ->color(fn ($record) => $record->color ?: 'gray')
                    ->icon(fn ($record) => $record->icon ?: null),
                Tables\Columns\TextColumn::make('key')->label('Ключ')->badge()->color('gray'),
                Tables\Columns\IconColumn::make('is_default')->label('Дефолт')->boolean(),
                Tables\Columns\IconColumn::make('is_final')->label('Термінальний')->boolean(),
                Tables\Columns\IconColumn::make('is_active')->label('Активний')->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->label('Порядок')->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrderStatuses::route('/'),
            'create' => Pages\CreateOrderStatus::route('/create'),
            'edit' => Pages\EditOrderStatus::route('/{record}/edit'),
        ];
    }
}
