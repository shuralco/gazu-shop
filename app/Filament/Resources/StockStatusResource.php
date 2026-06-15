<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockStatusResource\Pages;
use App\Models\StockStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StockStatusResource extends Resource
{
    use \App\Filament\Concerns\GatedResource;

    protected static ?string $model = StockStatus::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Каталог';
    protected static ?string $navigationLabel = 'Статуси наявності';
    protected static ?string $modelLabel = 'статус наявності';
    protected static ?string $pluralModelLabel = 'Статуси наявності';
    protected static ?int $navigationSort = 45;

    private const COLORS = [
        'gray' => 'Сірий', 'primary' => 'Основний', 'info' => 'Синій',
        'success' => 'Зелений', 'warning' => 'Жовтий', 'danger' => 'Червоний',
    ];

    private const AVAILABILITY = [
        'InStock' => 'В наявності (InStock)',
        'BackOrder' => 'Під замовлення (BackOrder)',
        'PreOrder' => 'Передзамовлення (PreOrder)',
        'OutOfStock' => 'Немає (OutOfStock)',
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
                ->helperText('Зберігається у товарі. Не змінюйте після створення — зламає наявні товари.')
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
                ->placeholder('heroicon-o-check-circle')
                ->helperText('Необов\'язково. Напр. heroicon-o-clock'),
            Forms\Components\Select::make('availability')
                ->label('Доступність (schema.org)')
                ->options(self::AVAILABILITY)
                ->default('InStock')
                ->required()
                ->helperText('Впливає на rich-snippet товару в Google'),
            Forms\Components\TextInput::make('sort_order')
                ->label('Порядок')
                ->numeric()
                ->default(0),
            Forms\Components\Toggle::make('is_orderable')
                ->label('Можна купувати')
                ->helperText('Якщо вимкнено — кнопка купівлі прихована/заблокована')
                ->default(true),
            Forms\Components\Toggle::make('is_default')
                ->label('За замовчуванням для нових товарів')
                ->helperText('Має бути лише в одного статусу'),
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
                Tables\Columns\TextColumn::make('availability')->label('schema.org')->badge()->color('gray'),
                Tables\Columns\IconColumn::make('is_orderable')->label('Купівля')->boolean(),
                Tables\Columns\IconColumn::make('is_default')->label('Дефолт')->boolean(),
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
            'index' => Pages\ListStockStatuses::route('/'),
            'create' => Pages\CreateStockStatus::route('/create'),
            'edit' => Pages\EditStockStatus::route('/{record}/edit'),
        ];
    }
}
