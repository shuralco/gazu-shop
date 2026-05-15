<?php

namespace App\Filament\Resources\CarMakeResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ModelsRelationManager extends RelationManager
{
    protected static string $relationship = 'models';

    protected static ?string $title = 'Моделі';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Назва моделі')->required(),
            Forms\Components\TextInput::make('slug')->label('Slug')->required()->maxLength(80),
            Forms\Components\Select::make('body_type')->label('Тип кузова')
                ->options([
                    'sedan' => 'Sedan',
                    'hatchback' => 'Hatchback',
                    'suv' => 'SUV',
                    'crossover' => 'Crossover',
                    'pickup' => 'Pickup',
                    'wagon' => 'Wagon',
                ])->nullable(),
            Forms\Components\TextInput::make('years_range')->label('Роки випуску')->placeholder('2018-2024'),
            Forms\Components\TextInput::make('sort_order')->label('Порядок')->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->label('Активна')->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')->label('#'),
                Tables\Columns\TextColumn::make('name')->label('Модель')->searchable(),
                Tables\Columns\TextColumn::make('body_type')->label('Кузов')->badge(),
                Tables\Columns\TextColumn::make('years_range')->label('Роки'),
                Tables\Columns\TextColumn::make('engines_count')->counts('engines')->label('Двигунів'),
                Tables\Columns\IconColumn::make('is_active')->label('Активна')->boolean(),
            ])
            ->defaultSort('sort_order')
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('engines')
                    ->label('Двигуни')
                    ->icon('heroicon-o-cog-8-tooth')
                    ->url(fn ($record) => route('filament.admin.resources.car-models.edit', $record)),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
