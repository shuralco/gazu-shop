<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CarMakeResource\Pages;
use App\Filament\Resources\CarMakeResource\RelationManagers\ModelsRelationManager;
use App\Models\CarMake;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CarMakeResource extends Resource
{
    protected static ?string $model = CarMake::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Авто-сумісність';

    protected static ?string $navigationLabel = 'Марки авто';

    protected static ?string $pluralLabel = 'Марки';

    protected static ?string $label = 'Марка';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Назва')
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', Str::slug($state))),
            Forms\Components\TextInput::make('slug')
                ->label('Slug (для URL)')
                ->required()
                ->maxLength(60)
                ->unique(CarMake::class, 'slug', ignoreRecord: true),
            Forms\Components\TextInput::make('logo_path')
                ->label('Шлях до лого (опц.)')
                ->maxLength(255),
            Forms\Components\TextInput::make('sort_order')
                ->label('Порядок')
                ->numeric()
                ->default(0),
            Forms\Components\Toggle::make('is_active')
                ->label('Активна')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Назва')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')->label('Slug')->fontFamily('mono')->copyable(),
                Tables\Columns\TextColumn::make('models_count')
                    ->counts('models')
                    ->label('Моделей')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label('Активна')->boolean(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Активна'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ModelsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCarMakes::route('/'),
            'create' => Pages\CreateCarMake::route('/create'),
            'edit' => Pages\EditCarMake::route('/{record}/edit'),
        ];
    }
}
