<?php

namespace App\Filament\Resources\CarModelResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class EnginesRelationManager extends RelationManager
{
    protected static string $relationship = 'engines';

    protected static ?string $title = 'Двигуни';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')->label('Код')->required()->maxLength(40)
                ->helperText('Напр. "1.5T", "2.0 TDI", "GW4G15B"'),
            Forms\Components\TextInput::make('label')->label('Назва (для випадайки)')->maxLength(60),
            Forms\Components\Select::make('fuel_type')->label('Тип палива')
                ->options([
                    'petrol' => 'Бензин',
                    'diesel' => 'Дизель',
                    'hybrid' => 'Гібрид',
                    'electric' => 'Електро',
                    'lpg' => 'LPG',
                ])->default('petrol'),
            Forms\Components\TextInput::make('displacement')->label('Об\'єм (л)')->numeric()->step('0.1'),
            Forms\Components\TextInput::make('hp')->label('Потужність (к.с.)')->numeric(),
            Forms\Components\TextInput::make('years_range')->label('Роки')->placeholder('2018-2024'),
            Forms\Components\TextInput::make('sort_order')->label('Порядок')->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->label('Активна')->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('code')
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')->label('#'),
                Tables\Columns\TextColumn::make('code')->label('Код')->fontFamily('mono')->searchable(),
                Tables\Columns\TextColumn::make('label')->label('Назва'),
                Tables\Columns\TextColumn::make('fuel_type')->label('Паливо')->badge(),
                Tables\Columns\TextColumn::make('displacement')->label('Об\'єм')->numeric(1),
                Tables\Columns\TextColumn::make('hp')->label('к.с.')->numeric(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->defaultSort('sort_order')
            ->headerActions([Tables\Actions\CreateAction::make()])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
