<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';

    protected static ?string $title = 'Адреси';

    protected static ?string $modelLabel = 'адреса';

    protected static ?string $pluralModelLabel = 'адреси';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('label')
                    ->label('Назва адреси')
                    ->maxLength(255),
                Forms\Components\TextInput::make('first_name')
                    ->label('Імʼя')
                    ->maxLength(255),
                Forms\Components\TextInput::make('last_name')
                    ->label('Прізвище')
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->label('Телефон')
                    ->tel()
                    ->maxLength(255),
                Forms\Components\TextInput::make('city')
                    ->label('Місто')
                    ->maxLength(255),
                Forms\Components\TextInput::make('address')
                    ->label('Адреса')
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('postal_code')
                    ->label('Поштовий індекс')
                    ->maxLength(20),
                Forms\Components\Toggle::make('is_default')
                    ->label('За замовчуванням'),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('label')
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->label('Назва'),
                Tables\Columns\TextColumn::make('city')
                    ->label('Місто'),
                Tables\Columns\IconColumn::make('is_default')
                    ->label('За замовчуванням')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата створення')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Додати адресу'),
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
