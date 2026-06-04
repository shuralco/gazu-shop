<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogResource\Pages;
use App\Models\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * Dedicated blog management. Blog posts are Page records with template=blog_post;
 * this resource gives them a focused, blog-friendly admin UI (cover/excerpt/body/
 * SEO) separate from the generic Pages resource.
 */
class BlogResource extends Resource
{
    use Translatable;

    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $navigationGroup = 'Контент і SEO';

    protected static ?string $navigationLabel = 'Блог';

    protected static ?string $modelLabel = 'Стаття блогу';

    protected static ?string $pluralModelLabel = 'Блог';

    protected static ?int $navigationSort = 20;

    /** Scope the resource to blog posts only. */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('template', 'blog_post');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make()->columnSpanFull()->tabs([
                Forms\Components\Tabs\Tab::make('Стаття')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Заголовок')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, ?string $state, ?Page $record) {
                                if (! $record && $state) {
                                    $set('slug', Str::slug($state));
                                }
                            })
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('slug')
                            ->label('URL (slug)')
                            ->helperText('Адреса статті: /blog/{slug}')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('og_image')
                            ->label('Обкладинка')
                            ->helperText('Головне зображення статті (картка + шапка). Рекомендовано 1200×630.')
                            ->image()
                            ->disk('public')
                            ->directory('blog')
                            ->visibility('public')
                            ->imageEditor()
                            ->maxSize(4096)
                            ->columnSpanFull(),
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\Select::make('blog_category_id')
                                ->label('Рубрика')
                                ->relationship('blogCategory', 'name')
                                ->searchable()
                                ->preload()
                                ->createOptionForm([
                                    Forms\Components\TextInput::make('name')->label('Назва')->required(),
                                    Forms\Components\TextInput::make('slug')->label('Slug')->required(),
                                ])
                                ->createOptionUsing(fn (array $data) => \App\Models\BlogCategory::create($data + ['is_active' => true])->id),
                            Forms\Components\TextInput::make('author')
                                ->label('Автор')
                                ->placeholder('Команда GAZU'),
                            Forms\Components\DateTimePicker::make('published_at')
                                ->label('Дата публікації')
                                ->helperText('Порожньо → дата створення')
                                ->seconds(false),
                        ]),
                        Forms\Components\Textarea::make('excerpt')
                            ->label('Короткий опис (анонс)')
                            ->helperText('1–2 речення для картки у списку блогу.')
                            ->rows(2)
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('content')
                            ->label('Текст статті')
                            ->columnSpanFull(),
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\Toggle::make('is_active')
                                ->label('Опубліковано')
                                ->default(true),
                            Forms\Components\Toggle::make('is_featured')
                                ->label('Рекомендована (на головній блогу)')
                                ->default(false),
                        ]),
                    ]),
                Forms\Components\Tabs\Tab::make('SEO')
                    ->icon('heroicon-o-globe-alt')
                    ->schema([
                        Forms\Components\TextInput::make('meta_title')->label('SEO Title')->maxLength(70),
                        Forms\Components\Textarea::make('meta_description')->label('SEO Description')->maxLength(160)->rows(2),
                    ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('og_image')->label('Обкладинка')->disk('public')->height(40),
                Tables\Columns\TextColumn::make('title')->label('Заголовок')->searchable()->limit(50)->sortable(),
                Tables\Columns\TextColumn::make('blogCategory.name')->label('Рубрика')->badge()->toggleable(),
                Tables\Columns\IconColumn::make('is_featured')->label('Реком.')->boolean()->toggleable(),
                Tables\Columns\IconColumn::make('is_active')->label('Опубл.')->boolean(),
                Tables\Columns\TextColumn::make('views')->label('👁')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('published_at')->label('Дата')->date('d.m.Y')->sortable()->placeholder('—'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Опубліковано'),
                Tables\Filters\SelectFilter::make('blog_category_id')
                    ->label('Рубрика')
                    ->relationship('blogCategory', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlogPosts::route('/'),
            'create' => Pages\CreateBlogPost::route('/create'),
            'edit' => Pages\EditBlogPost::route('/{record}/edit'),
        ];
    }
}
