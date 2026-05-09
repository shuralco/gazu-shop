<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PageResource extends Resource
{
    use Translatable;

    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Контент та SEO';

    protected static ?string $modelLabel = 'Сторінка';

    protected static ?string $pluralModelLabel = 'Сторінки';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Page')
                    ->tabs([
                        self::contentTab(),
                        self::seoTab(),
                        self::settingsTab(),
                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString(),
            ]);
    }

    private static function contentTab(): Forms\Components\Tabs\Tab
    {
        return Forms\Components\Tabs\Tab::make('Контент')
            ->icon('heroicon-o-document-text')
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Заголовок')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                        if (empty($get('slug')) && $state) {
                            $service = app(\App\Services\TransliterationService::class);
                            $set('slug', $service->generateSlug($state));
                        }
                    })
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('slug')
                    ->label('URL (slug)')
                    ->maxLength(255)
                    ->alphaDash()
                    ->helperText('Автоматично генерується з заголовку. Це поле перекладається для кожної мови.')
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('excerpt')
                    ->label('Короткий опис')
                    ->rows(3)
                    ->maxLength(500)
                    ->columnSpanFull(),

                Forms\Components\RichEditor::make('content')
                    ->label('Контент')
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'underline',
                        'strike',
                        'h2',
                        'h3',
                        'h4',
                        'bulletList',
                        'orderedList',
                        'link',
                        'attachFiles',
                        'table',
                        'codeBlock',
                        'blockquote',
                        'redo',
                        'undo',
                    ])
                    ->fileAttachmentsDisk('public')
                    ->fileAttachmentsDirectory('pages/content')
                    ->columnSpanFull(),

                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Select::make('template')
                            ->label('Шаблон')
                            ->options([
                                'default' => 'Стандартний',
                                'contact' => 'Контакти',
                                'faq' => 'FAQ',
                                'landing' => 'Лендінг',
                            ])
                            ->default('default')
                            ->required(),

                        Forms\Components\Select::make('layout')
                            ->label('Макет')
                            ->options([
                                'full' => 'На всю ширину',
                                'sidebar-left' => 'Сайдбар зліва',
                                'sidebar-right' => 'Сайдбар справа',
                            ])
                            ->default('full')
                            ->required(),
                    ]),
            ]);
    }

    private static function seoTab(): Forms\Components\Tabs\Tab
    {
        return Forms\Components\Tabs\Tab::make('SEO')
            ->icon('heroicon-o-globe-alt')
            ->schema([
                Forms\Components\TextInput::make('meta_title')
                    ->label('Meta Title')
                    ->maxLength(70)
                    ->hint(fn (Get $get): string => strlen($get('meta_title') ?? '') . '/70')
                    ->live(onBlur: true)
                    ->helperText('Оптимальна довжина: 50-70 символів')
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('meta_description')
                    ->label('Meta Description')
                    ->maxLength(160)
                    ->rows(3)
                    ->hint(fn (Get $get): string => strlen($get('meta_description') ?? '') . '/160')
                    ->live(onBlur: true)
                    ->helperText('Оптимальна довжина: 120-160 символів')
                    ->columnSpanFull(),

                Forms\Components\TagsInput::make('meta_keywords')
                    ->label('Meta Keywords')
                    ->helperText('Натисніть Enter після кожного ключового слова')
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('canonical_url')
                    ->label('Canonical URL')
                    ->url()
                    ->maxLength(500)
                    ->placeholder('https://...')
                    ->columnSpanFull(),

                Forms\Components\Section::make('Robots')
                    ->description('Налаштування індексації пошуковими системами')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Toggle::make('is_indexable')
                                    ->label('Індексувати (index)')
                                    ->default(true)
                                    ->helperText('Дозволити пошуковим системам індексувати сторінку'),

                                Forms\Components\Toggle::make('is_followable')
                                    ->label('Слідувати за посиланнями (follow)')
                                    ->default(true)
                                    ->helperText('Дозволити пошуковим системам слідувати за посиланнями'),

                                Forms\Components\TextInput::make('robots_custom')
                                    ->label('Додаткові директиви')
                                    ->placeholder('noarchive, nosnippet...')
                                    ->maxLength(255),
                            ]),
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('Open Graph')
                    ->description('Налаштування відображення при шерінгу в соцмережах')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\FileUpload::make('og_image')
                                    ->label('OG Image')
                                    ->image()
                                    ->directory('pages/og')
                                    ->imageResizeMode('cover')
                                    ->imageCropAspectRatio('1.91:1')
                                    ->imageResizeTargetWidth(1200)
                                    ->imageResizeTargetHeight(630)
                                    ->helperText('Рекомендований розмір: 1200x630 px'),

                                Forms\Components\Select::make('og_type')
                                    ->label('OG Type')
                                    ->options([
                                        'article' => 'Article',
                                        'website' => 'Website',
                                        'product' => 'Product',
                                    ])
                                    ->default('article'),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }

    private static function settingsTab(): Forms\Components\Tabs\Tab
    {
        return Forms\Components\Tabs\Tab::make('Налаштування')
            ->icon('heroicon-o-cog-6-tooth')
            ->schema([
                Forms\Components\Section::make('Видимість')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Активна')
                                    ->default(true)
                                    ->helperText('Вимкніть щоб приховати сторінку від відвідувачів'),

                                Forms\Components\Toggle::make('show_in_menu')
                                    ->label('Показувати в меню')
                                    ->helperText('Відображати посилання у головному меню'),

                                Forms\Components\Toggle::make('show_in_footer')
                                    ->label('Показувати в футері')
                                    ->helperText('Відображати посилання у нижній частині сайту'),
                            ]),
                    ]),

                Forms\Components\Section::make('Навігація')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('menu_group')
                                    ->label('Група меню')
                                    ->options([
                                        'information' => 'Інформація',
                                        'help' => 'Допомога',
                                        'company' => 'Компанія',
                                    ])
                                    ->placeholder('Без групи')
                                    ->helperText('Група для відображення у футері'),

                                Forms\Components\TextInput::make('sort_order')
                                    ->label('Порядок сортування')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->helperText('Менше число = вище у списку'),

                                Forms\Components\TextInput::make('icon')
                                    ->label('Іконка')
                                    ->placeholder('heroicon-o-... або emoji')
                                    ->maxLength(100)
                                    ->helperText('Назва іконки Heroicon або emoji'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Заголовок')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(50),

                Tables\Columns\TextColumn::make('slug')
                    ->label('URL')
                    ->searchable()
                    ->color('gray')
                    ->limit(30)
                    ->toggleable(),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Активна')
                    ->sortable(),

                Tables\Columns\IconColumn::make('show_in_footer')
                    ->label('Футер')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('template')
                    ->label('Шаблон')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'default' => 'gray',
                        'contact' => 'info',
                        'faq' => 'warning',
                        'landing' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'default' => 'Стандартний',
                        'contact' => 'Контакти',
                        'faq' => 'FAQ',
                        'landing' => 'Лендінг',
                        default => $state,
                    }),

                Tables\Columns\IconColumn::make('is_indexable')
                    ->label('Індекс')
                    ->boolean()
                    ->trueIcon('heroicon-o-eye')
                    ->falseIcon('heroicon-o-eye-slash')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(fn (Page $record): string => ($record->is_indexable ? 'index' : 'noindex')
                        . ', ' . ($record->is_followable ? 'follow' : 'nofollow')),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Оновлено')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Активна')
                    ->trueLabel('Активні')
                    ->falseLabel('Неактивні'),

                Tables\Filters\SelectFilter::make('template')
                    ->label('Шаблон')
                    ->options([
                        'default' => 'Стандартний',
                        'contact' => 'Контакти',
                        'faq' => 'FAQ',
                        'landing' => 'Лендінг',
                    ]),

                Tables\Filters\TernaryFilter::make('show_in_footer')
                    ->label('У футері')
                    ->trueLabel('Показуються')
                    ->falseLabel('Не показуються'),
            ])
            ->actions([
                Tables\Actions\Action::make('preview')
                    ->label('')
                    ->icon('heroicon-o-eye')
                    ->size('lg')
                    ->tooltip('Перегляд на сайті')
                    ->color('gray')
                    ->url(fn (Page $record): string => $record->getUrl())
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make()
                    ->label('')
                    ->icon('heroicon-o-pencil')
                    ->size('lg')
                    ->tooltip('Редагувати'),
                Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->icon('heroicon-o-trash')
                    ->size('lg')
                    ->tooltip('Видалити'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->striped();
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
