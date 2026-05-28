<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogCategoryResource\Pages;
use App\Models\BlogCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class BlogCategoryResource extends Resource
{
    use Translatable;

    protected static ?string $model = BlogCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Контент та SEO';

    protected static ?string $navigationLabel = 'Рубрики блогу';

    protected static ?string $modelLabel = 'Рубрика';

    protected static ?string $pluralModelLabel = 'Рубрики блогу';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Назва')
                ->required()
                ->maxLength(120)
                ->live(onBlur: true)
                ->afterStateUpdated(function (Forms\Set $set, ?string $state, ?BlogCategory $record) {
                    if (! $record && $state) {
                        $set('slug', Str::slug($state));
                    }
                }),
            Forms\Components\TextInput::make('slug')
                ->label('URL (slug)')
                ->required()
                ->maxLength(120)
                ->helperText('Адреса рубрики: /blog/rubryka/{slug}')
                ->unique(BlogCategory::class, 'slug', ignoreRecord: true),
            Forms\Components\Textarea::make('description')
                ->label('Опис рубрики')
                ->rows(2)
                ->columnSpanFull(),
            Forms\Components\TextInput::make('sort_order')->label('Порядок')->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->label('Активна')->default(true),
            Forms\Components\Section::make('SEO')->collapsed()->schema([
                Forms\Components\TextInput::make('meta_title')->label('SEO Title')->maxLength(70),
                Forms\Components\Textarea::make('meta_description')->label('SEO Description')->maxLength(160)->rows(2),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Назва')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')->label('URL')->fontFamily('mono'),
                Tables\Columns\TextColumn::make('posts_count')->counts('posts')->label('Статей'),
                Tables\Columns\IconColumn::make('is_active')->label('Активна')->boolean(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Активна'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlogCategories::route('/'),
            'create' => Pages\CreateBlogCategory::route('/create'),
            'edit' => Pages\EditBlogCategory::route('/{record}/edit'),
        ];
    }
}
