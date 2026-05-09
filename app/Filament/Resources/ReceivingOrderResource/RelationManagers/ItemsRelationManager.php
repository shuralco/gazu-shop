<?php

namespace App\Filament\Resources\ReceivingOrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Позиції приходування';

    protected static ?string $modelLabel = 'Позиція';

    protected static ?string $pluralModelLabel = 'Позиції';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('product_id')
                ->label('Товар')
                ->relationship('product', 'title')
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\TextInput::make('quantity')
                ->label('Кількість')
                ->numeric()
                ->minValue(1)
                ->required(),
            Forms\Components\TextInput::make('cost_price')
                ->label('Закупівельна ціна (опц.)')
                ->numeric()
                ->step('0.01')
                ->prefix('₴'),
            Forms\Components\TextInput::make('note')
                ->label('Примітка')
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_id')
            ->columns([
                Tables\Columns\TextColumn::make('product.title')->label('Товар')->searchable(),
                Tables\Columns\TextColumn::make('product.sku')->label('SKU')->placeholder('—'),
                Tables\Columns\TextColumn::make('quantity')->label('Кількість')->weight('bold'),
                Tables\Columns\TextColumn::make('cost_price')
                    ->label('Закупка')
                    ->placeholder('—')
                    ->formatStateUsing(fn ($state) => $state ? number_format((float) $state, 2, '.', ' ').' ₴' : '—'),
                Tables\Columns\TextColumn::make('note')->label('Примітка')->placeholder('—')->limit(50),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Додати товар')
                    ->visible(fn () => $this->getOwnerRecord()->isEditable()),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn () => $this->getOwnerRecord()->isEditable()),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => $this->getOwnerRecord()->isEditable()),
            ]);
    }
}
