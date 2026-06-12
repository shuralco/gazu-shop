<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HelpArticleResource\Pages;
use App\Models\HelpArticle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

/**
 * Редагування статей довідки (wiki) адмінки. Контент у Markdown.
 * Читацька вітрина — App\Filament\Pages\HelpCenter (/admin/help).
 */
class HelpArticleResource extends Resource
{
    use \App\Filament\Concerns\GatedResource;

    protected static ?string $model = HelpArticle::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'Обслуговування';

    protected static ?string $navigationLabel = 'Статті довідки';

    protected static ?string $modelLabel = 'стаття довідки';

    protected static ?string $pluralModelLabel = 'Статті довідки';

    protected static ?int $navigationSort = 90;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Заголовок')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, Forms\Set $set, ?HelpArticle $record) => $record ? null : $set('slug', Str::slug($state)))
                    ->columnSpan(2),
                Forms\Components\TextInput::make('slug')
                    ->label('Slug (URL)')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->helperText('Напр. products → /admin/help?topic=products'),
                Forms\Components\TextInput::make('section')
                    ->label('Розділ (група в сайдбарі)')
                    ->default('Загальне')
                    ->required(),
                Forms\Components\TextInput::make('icon')
                    ->label('Іконка (heroicon)')
                    ->placeholder('heroicon-o-cube'),
                Forms\Components\TextInput::make('match_path')
                    ->label('Шлях розділу (контекстна кнопка)')
                    ->placeholder('products')
                    ->helperText('admin-шлях, де показувати кнопку «Довідка» на цю статтю. Напр. products, categories.'),
                Forms\Components\TextInput::make('sort_order')
                    ->label('Порядок')
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_active')
                    ->label('Активна')
                    ->default(true),
            ]),
            Forms\Components\MarkdownEditor::make('content')
                ->label('Контент (Markdown)')
                ->helperText('Підтримує заголовки, списки, таблиці, зображення ![опис](/img/help/...). Зображення-скріни кладуться у public/img/help/.')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('title')->label('Заголовок')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('section')->label('Розділ')->badge()->sortable(),
                Tables\Columns\TextColumn::make('slug')->label('Slug')->color('gray')->fontFamily('mono'),
                Tables\Columns\IconColumn::make('is_active')->label('Активна')->boolean(),
            ])
            ->defaultSort('section')
            ->reorderable('sort_order')
            ->actions([
                Tables\Actions\Action::make('open')
                    ->label('Відкрити')
                    ->icon('heroicon-m-eye')
                    ->url(fn (HelpArticle $r) => url('/admin/help?topic='.$r->slug))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHelpArticles::route('/'),
            'create' => Pages\CreateHelpArticle::route('/create'),
            'edit' => Pages\EditHelpArticle::route('/{record}/edit'),
        ];
    }
}
