<?php

namespace App\Filament\Resources\CustomerGroupResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $title = 'Користувачі групи';

    protected static ?string $modelLabel = 'користувач';

    protected static ?string $pluralModelLabel = 'користувачі';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Імʼя')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Електронна пошта')
                    ->searchable(),
                Tables\Columns\TextColumn::make('loyalty_tier')
                    ->label('Рівень лояльності')
                    ->badge(),
                Tables\Columns\TextColumn::make('total_spent')
                    ->label('Загальна сума покупок')
                    ->formatStateUsing(fn ($state) => number_format($state, 2, '.', ' ').' грн'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата реєстрації')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Додати користувача')
                    ->recordSelectSearchColumns(['name', 'email'])
                    ->preloadRecordSelect(),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()
                    ->label('Видалити з групи'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
