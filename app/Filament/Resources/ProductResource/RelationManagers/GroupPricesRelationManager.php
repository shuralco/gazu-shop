<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class GroupPricesRelationManager extends RelationManager
{
    protected static string $relationship = 'groupPrices';

    protected static ?string $title = 'Гуртові ціни';
    protected static ?string $icon = 'heroicon-o-currency-dollar';

    protected static ?string $modelLabel = 'ціна для групи';

    protected static ?string $pluralModelLabel = 'ціни для груп';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('customer_group_id')
                    ->label('Група клієнтів')
                    ->relationship('customerGroup', 'display_name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('price_currency')
                    ->label('Валюта')
                    ->options(fn () => \App\Models\Currency::selectOptions())
                    ->default(fn () => \App\Models\Currency::baseCode() ?: 'UAH')
                    ->selectablePlaceholder(false)
                    ->native(false),
                Forms\Components\TextInput::make('price')
                    ->label('Ціна')
                    ->helperText('На сайті показується в грн за курсом /admin/currencies')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('min_quantity')
                    ->label('Мінімальна кількість')
                    ->numeric()
                    ->default(1),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('price')
            ->columns([
                Tables\Columns\TextColumn::make('customerGroup.display_name')
                    ->label('Група клієнтів')
                    ->badge(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Ціна')
                    ->formatStateUsing(fn ($state) => number_format($state, 2, '.', ' ').' грн'),
                Tables\Columns\TextColumn::make('min_quantity')
                    ->label('Мін. кількість'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Додати ціну для групи'),
            ])
            ->actions([
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
                ]),
            ]);
    }
}
