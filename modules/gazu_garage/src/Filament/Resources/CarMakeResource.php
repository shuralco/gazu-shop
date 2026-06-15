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
    use \App\Filament\Concerns\GatedResource;

    protected static ?string $model = CarMake::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Каталог';

    protected static ?string $navigationLabel = 'Марки авто';

    protected static ?string $pluralLabel = 'Марки';

    protected static ?string $label = 'Марка';

    protected static ?int $navigationSort = 80;

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
            Forms\Components\FileUpload::make('logo_path')
                ->label('Логотип марки')
                ->helperText('PNG/SVG/WEBP з прозорим фоном. Показується у підборі по авто на головній та в каталозі.')
                ->image()
                ->disk('public')
                ->directory('car-makes')
                ->visibility('public')
                ->imageEditor()
                ->maxSize(1024)
                ->acceptedFileTypes(['image/png', 'image/svg+xml', 'image/webp', 'image/jpeg']),
            Forms\Components\TextInput::make('sort_order')
                ->label('Порядок')
                ->numeric()
                ->default(0),
            Forms\Components\Toggle::make('is_active')
                ->label('Активна')
                ->default(true),
            Forms\Components\Section::make('SEO')
                ->description('Meta для сторінки /zapchastyny/{марка}. Якщо порожньо — генерується автоматично.')
                ->collapsed()
                ->schema([
                    Forms\Components\TextInput::make('meta_title')
                        ->label('SEO Title')
                        ->maxLength(70)
                        ->helperText('Оптимально 50–60 символів'),
                    Forms\Components\Textarea::make('meta_description')
                        ->label('SEO Description')
                        ->maxLength(160)
                        ->rows(2)
                        ->helperText('Оптимально 150–160 символів'),
                    Forms\Components\RichEditor::make('description')
                        ->label('Опис марки')
                        ->helperText('SEO-текст на сторінці марки.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')->label('#')->sortable(),
                Tables\Columns\ImageColumn::make('logo_path')->label('Лого')->disk('public')->height(28),
                Tables\Columns\TextColumn::make('name')->label('Назва')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')->label('Slug')->fontFamily('mono')->copyable(),
                Tables\Columns\TextColumn::make('models_count')
                    ->counts('models')
                    ->label('Моделей')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label('Активна')->boolean(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Активна'),
                Tables\Filters\TernaryFilter::make('has_models')
                    ->label('Моделі')
                    ->placeholder('Усі')
                    ->trueLabel('З моделями')
                    ->falseLabel('Без моделей')
                    ->queries(
                        true: fn ($query) => $query->whereHas('models'),
                        false: fn ($query) => $query->whereDoesntHave('models'),
                        blank: fn ($query) => $query,
                    ),
            ])
            ->filtersFormColumns(['sm' => 1, 'lg' => 2])
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
