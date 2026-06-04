<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CurrencyResource\Pages;
use App\Models\Currency;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CurrencyResource extends Resource
{
    use \App\Filament\Concerns\GatedResource;

    protected static ?string $model = Currency::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Налаштування';
    protected static ?string $navigationLabel = 'Валюти';
    protected static ?string $modelLabel = 'валюта';
    protected static ?string $pluralModelLabel = 'Валюти';
    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')
                ->label('Код')
                ->helperText('ISO-код, напр. UAH/USD/EUR. Не змінюйте після створення.')
                ->required()->alphaDash()->maxLength(8)
                ->unique(ignoreRecord: true)
                ->disabledOn('edit'),
            Forms\Components\TextInput::make('name')->label('Назва')->required()->maxLength(60),
            Forms\Components\TextInput::make('symbol')->label('Символ')->required()->maxLength(8)->placeholder('₴'),
            Forms\Components\TextInput::make('rate')
                ->label('Курс до базової')
                ->helperText('Скільки одиниць цієї валюти = 1 базовій. Базова = 1.0')
                ->numeric()->required()->default(1)->step('0.000001')->minValue(0),
            Forms\Components\Select::make('position')
                ->label('Позиція символу')
                ->options(['before' => 'Перед сумою ($100)', 'after' => 'Після суми (100 ₴)'])
                ->default('after')->required(),
            Forms\Components\TextInput::make('decimals')->label('Знаків після коми')
                ->numeric()->default(2)->minValue(0)->maxValue(4),
            Forms\Components\TextInput::make('sort_order')->label('Порядок')->numeric()->default(0),
            Forms\Components\Toggle::make('is_base')
                ->label('Базова валюта')
                ->helperText('Курс = 1.0. Має бути лише одна базова.'),
            Forms\Components\Toggle::make('is_active')->label('Активна')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('Код')->badge()->color('primary'),
                Tables\Columns\TextColumn::make('name')->label('Назва')->searchable(),
                Tables\Columns\TextColumn::make('symbol')->label('Символ'),
                Tables\Columns\TextColumn::make('rate')->label('Курс')->numeric(decimalPlaces: 4)->sortable(),
                Tables\Columns\IconColumn::make('is_base')->label('Базова')->boolean(),
                Tables\Columns\IconColumn::make('is_active')->label('Активна')->boolean(),
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
            'index' => Pages\ListCurrencies::route('/'),
            'create' => Pages\CreateCurrency::route('/create'),
            'edit' => Pages\EditCurrency::route('/{record}/edit'),
        ];
    }
}
