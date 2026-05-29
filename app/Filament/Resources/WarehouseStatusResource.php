<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WarehouseStatusResource\Pages;
use App\Models\WarehouseStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WarehouseStatusResource extends Resource
{
    protected static ?string $model = WarehouseStatus::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';
    protected static ?string $navigationGroup = 'Доставка та оплата';
    protected static ?string $navigationLabel = 'Статуси складів';
    protected static ?string $modelLabel = 'статус складу';
    protected static ?string $pluralModelLabel = 'Статуси складів';
    protected static ?int $navigationSort = 9;

    private const COLORS = [
        'gray' => 'Сірий', 'primary' => 'Основний', 'info' => 'Синій',
        'success' => 'Зелений', 'warning' => 'Жовтий', 'danger' => 'Червоний',
    ];

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('label')->label('Назва')->required()->maxLength(80),
            Forms\Components\TextInput::make('key')->label('Ключ (системний)')
                ->helperText('Зберігається у складі. Не змінюйте після створення.')
                ->required()->alphaDash()->unique(ignoreRecord: true)->disabledOn('edit'),
            Forms\Components\Select::make('color')->label('Колір бейджа')->options(self::COLORS)->default('gray')->required(),
            Forms\Components\TextInput::make('icon')->label('Іконка (heroicon)')->placeholder('heroicon-o-check-circle'),
            Forms\Components\TextInput::make('sort_order')->label('Порядок')->numeric()->default(0),
            Forms\Components\Toggle::make('is_default')->label('За замовчуванням для нових складів'),
            Forms\Components\Toggle::make('is_active')->label('Активний')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('label')->label('Назва')->badge()
                    ->color(fn ($record) => $record->color ?: 'gray')
                    ->icon(fn ($record) => $record->icon ?: null),
                Tables\Columns\TextColumn::make('key')->label('Ключ')->badge()->color('gray'),
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
            'index' => Pages\ListWarehouseStatuses::route('/'),
            'create' => Pages\CreateWarehouseStatus::route('/create'),
            'edit' => Pages\EditWarehouseStatus::route('/{record}/edit'),
        ];
    }
}
