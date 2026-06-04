<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BrandResource\Pages;
use App\Models\Brand;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BrandResource extends Resource
{
    use Translatable;
    protected static ?string $model = Brand::class;

    protected static ?string $navigationIcon = 'heroicon-o-bookmark';

    protected static ?string $navigationGroup = 'Каталог';

    protected static ?string $navigationLabel = 'Бренди';

    protected static ?string $pluralLabel = 'Бренди';

    protected static ?string $label = 'Бренд';

    protected static ?int $navigationSort = 30;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основна інформація')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Назва бренду')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', \Str::slug($state))),

                        Forms\Components\TextInput::make('slug')
                            ->label('URL слаг')
                            ->required()
                            ->maxLength(255)
                            ->unique(Brand::class, 'slug', ignoreRecord: true),

                        Forms\Components\FileUpload::make('logo')
                            ->label('Логотип')
                            ->image()
                            ->directory('brands/logos')
                            ->visibility('public')
                            ->imageEditor()
                            ->imageEditorAspectRatios(['1:1']),

                        Forms\Components\Textarea::make('description')
                            ->label('Опис')
                            ->rows(3)
                            ->maxLength(1000),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Активний')
                            ->default(true),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Порядок сортування')
                            ->numeric()
                            ->default(0),
                    ]),

                Forms\Components\Section::make('SEO налаштування')
                    ->schema([
                        Forms\Components\TextInput::make('meta_title')
                            ->label('SEO заголовок')
                            ->maxLength(255),

                        Forms\Components\Textarea::make('meta_description')
                            ->label('SEO опис')
                            ->rows(3)
                            ->maxLength(500),

                        Forms\Components\TextInput::make('meta_keywords')
                            ->label('SEO ключові слова')
                            ->maxLength(255),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->label('Лого')
                    ->size(48)
                    ->extraImgAttributes(['class' => 'rounded-lg ring-1 ring-black/5 object-contain bg-white p-1'])
                    ->defaultImageUrl(asset('assets/img/placeholder.svg'))
                    ->checkFileExistence(false),

                Tables\Columns\TextColumn::make('name')
                    ->label('Назва')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('URL слаг')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('products_count')
                    ->label('Товарів')
                    ->counts('products')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активний')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Порядок')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Створено')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->reorderable('sort_order')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Активні')
                    ->trueLabel('Тільки активні')
                    ->falseLabel('Тільки неактивні')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBrands::route('/'),
            'create' => Pages\CreateBrand::route('/create'),
            'edit' => Pages\EditBrand::route('/{record}/edit'),
        ];
    }
}
