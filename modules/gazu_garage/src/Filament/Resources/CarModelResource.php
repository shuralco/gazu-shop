<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CarModelResource\Pages;
use App\Filament\Resources\CarModelResource\RelationManagers\EnginesRelationManager;
use App\Models\CarMake;
use App\Models\CarModel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CarModelResource extends Resource
{
    protected static ?string $model = CarModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Авто-сумісність';

    protected static ?string $navigationLabel = 'Моделі авто';

    protected static ?string $pluralLabel = 'Моделі';

    protected static ?string $label = 'Модель';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('make_id')
                ->label('Марка')
                ->relationship('make', 'name')
                ->options(CarMake::query()->orderBy('sort_order')->pluck('name', 'id'))
                ->required()
                ->searchable(),
            Forms\Components\TextInput::make('name')->label('Назва')->required(),
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
            Forms\Components\TextInput::make('years_range')->label('Роки')->placeholder('2018-2024'),
            Forms\Components\TextInput::make('sort_order')->label('Порядок')->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->label('Активна')->default(true),
            Forms\Components\Section::make('SEO')
                ->description('Meta для сторінки /zapchastyny/{марка}/{модель}. Якщо порожньо — генерується автоматично.')
                ->collapsed()
                ->schema([
                    Forms\Components\TextInput::make('meta_title')->label('SEO Title')->maxLength(70),
                    Forms\Components\Textarea::make('meta_description')->label('SEO Description')->maxLength(160)->rows(2),
                    Forms\Components\RichEditor::make('description')->label('Опис моделі'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('make.name')->label('Марка')->sortable()->badge(),
                Tables\Columns\TextColumn::make('name')->label('Модель')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('body_type')->label('Кузов')->badge(),
                Tables\Columns\TextColumn::make('years_range')->label('Роки'),
                Tables\Columns\TextColumn::make('engines_count')->counts('engines')->label('Двигунів'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->defaultSort('make_id')
            ->reorderable('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('make_id')
                    ->label('Марка')
                    ->options(CarMake::query()->orderBy('sort_order')->pluck('name', 'id')),
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            EnginesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCarModels::route('/'),
            'create' => Pages\CreateCarModel::route('/create'),
            'edit' => Pages\EditCarModel::route('/{record}/edit'),
        ];
    }
}
