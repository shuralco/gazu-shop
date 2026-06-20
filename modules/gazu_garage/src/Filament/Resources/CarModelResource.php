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
    use \App\Filament\Concerns\GatedResource;

    protected static ?string $model = CarModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Каталог';

    protected static ?string $navigationLabel = 'Моделі авто';

    protected static ?string $pluralLabel = 'Моделі';

    protected static ?string $label = 'Модель';

    protected static ?int $navigationSort = 90;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('make_id')
                ->label('Марка')
                ->relationship('make', 'name')
                ->options(CarMake::query()->orderBy('sort_order')->pluck('name', 'id'))
                ->required()
                ->searchable(),
            Forms\Components\TextInput::make('name')->label('Назва')->required()
                ->live(onBlur: true)
                ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, ?string $state) {
                    if (blank($get('slug')) && filled($state)) {
                        $set('slug', \Illuminate\Support\Str::slug($state));
                    }
                }),
            Forms\Components\TextInput::make('slug')->label('Slug')->required()->maxLength(80)
                // Унікальність у межах марки (make_id+slug) — інакше дубль слага
                // летів у DB-констрейнт car_models_make_id_slug_unique → 500.
                ->unique(
                    table: CarModel::class,
                    column: 'slug',
                    ignoreRecord: true,
                    modifyRuleUsing: fn (\Illuminate\Validation\Rules\Unique $rule, Forms\Get $get) => $rule->where('make_id', $get('make_id')),
                )
                ->validationMessages(['unique' => 'Модель із таким slug уже є для цієї марки.']),
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
                Tables\Columns\IconColumn::make('is_active')->label('Активна')->boolean(),
            ])
            ->defaultSort('make_id')
            ->reorderable('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('make_id')
                    ->label('Марка')
                    ->options(CarMake::query()->orderBy('sort_order')->pluck('name', 'id'))
                    ->searchable(),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Активність')
                    ->placeholder('Усі')
                    ->trueLabel('Лише активні')
                    ->falseLabel('Лише вимкнені'),
            ])
            ->filtersFormColumns(['sm' => 1, 'lg' => 2])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Активувати')
                        ->icon('heroicon-o-eye')
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Деактивувати')
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            EnginesRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        // Eager-load make — колонка make.name інакше = N+1 щорядка.
        return parent::getEloquentQuery()->with(['make']);
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
