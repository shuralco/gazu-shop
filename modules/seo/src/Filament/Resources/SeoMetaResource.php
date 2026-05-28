<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SeoMetaResource\Pages;
use App\Models\Category;
use App\Models\Product;
use App\Models\SeoMeta;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class SeoMetaResource extends Resource
{
    protected static ?string $model = SeoMeta::class;

    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass';

    protected static ?string $navigationGroup = 'Контент та SEO';

    protected static ?string $modelLabel = 'SEO Мета-дані';

    protected static ?string $pluralModelLabel = 'SEO Мета-дані';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('SEO Tabs')
                    ->tabs([
                        self::getBasicTab(),
                        self::getOpenGraphTab(),
                        self::getTwitterTab(),
                        self::getSitemapTab(),
                        self::getFaqTab(),
                        self::getStructuredDataTab(),
                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString(),
            ]);
    }

    private static function getBasicTab(): Forms\Components\Tabs\Tab
    {
        return Forms\Components\Tabs\Tab::make('🎯 Основні SEO')
            ->schema([
                Forms\Components\Section::make('Прив\'язка до об\'єкту')
                    ->schema([
                        Forms\Components\Select::make('seoable_type')
                            ->label('Тип об\'єкту')
                            ->options([
                                Category::class => 'Категорія',
                                Product::class => 'Товар',
                                'homepage' => 'Головна сторінка',
                                'specials' => 'Акції',
                                'hits' => 'Хіти продажів',
                                'new' => 'Новинки',
                                'search' => 'Пошук',
                                'about' => 'Про нас',
                                'contacts' => 'Контакти',
                                'delivery' => 'Доставка',
                                'payment' => 'Оплата',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('seoable_id', null)),

                        Forms\Components\Select::make('seoable_id')
                            ->label('Об\'єкт')
                            ->options(function (Forms\Get $get) {
                                $type = $get('seoable_type');

                                return match ($type) {
                                    Category::class => Category::pluck('title', 'id'),
                                    Product::class => Product::pluck('title', 'id'),
                                    default => [],
                                };
                            })
                            ->searchable()
                            ->visible(fn (Forms\Get $get) => in_array($get('seoable_type'), [Category::class, Product::class])),

                        Forms\Components\Select::make('language')
                            ->label('Мова')
                            ->options([
                                'uk' => 'Українська',
                            ])
                            ->default('uk')
                            ->required(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Основні мета-теги')
                    ->schema([
                        Forms\Components\TextInput::make('meta_title')
                            ->label('SEO Title')
                            ->required()
                            ->maxLength(fn () => \App\Models\DisplaySetting::get('seo_title_max_length', 60))
                            ->live(debounce: 500)
                            ->helperText(function (?string $state): string {
                                $maxLength = \App\Models\DisplaySetting::get('seo_title_max_length', 60);

                                return 'Символів: '.mb_strlen($state ?? '').'/'.$maxLength;
                            }),

                        Forms\Components\Textarea::make('meta_description')
                            ->label('SEO Description')
                            ->required()
                            ->maxLength(fn () => \App\Models\DisplaySetting::get('seo_description_max_length', 160))
                            ->rows(3)
                            ->live(debounce: 500)
                            ->helperText(function (?string $state): string {
                                $maxLength = \App\Models\DisplaySetting::get('seo_description_max_length', 160);

                                return 'Символів: '.mb_strlen($state ?? '').'/'.$maxLength;
                            }),

                        Forms\Components\TagsInput::make('meta_keywords')
                            ->label('Keywords')
                            ->helperText('Ключові слова (розділені Enter)'),

                        Forms\Components\TextInput::make('canonical_url')
                            ->label('Canonical URL')
                            ->url()
                            ->helperText('Залиште порожнім для автогенерації'),

                        Forms\Components\Select::make('robots')
                            ->label('Robots Meta')
                            ->options([
                                'index,follow' => 'index,follow - Індексувати та слідувати',
                                'noindex,follow' => 'noindex,follow - Не індексувати, але слідувати',
                                'index,nofollow' => 'index,nofollow - Індексувати, але не слідувати',
                                'noindex,nofollow' => 'noindex,nofollow - Не індексувати та не слідувати',
                            ])
                            ->default('index,follow'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Генератор SEO даних')
                    ->schema([
                        Forms\Components\Grid::make(4)
                            ->schema([
                                Forms\Components\Actions::make([
                                    Forms\Components\Actions\Action::make('generate_url')
                                        ->label('URL')
                                        ->icon('heroicon-o-link')
                                        ->size('sm')
                                        ->color('gray')
                                        ->visible(fn (Forms\Get $get) => in_array($get('seoable_type'), [Category::class, Product::class]))
                                        ->action(function (Forms\Set $set, Forms\Get $get) {
                                            $type = $get('seoable_type');
                                            $id = $get('seoable_id');

                                            if ($type && $id) {
                                                $model = $type::find($id);
                                                if ($model && $model->slug) {
                                                    $urlService = new \App\Services\UrlRouterService;
                                                    $url = match ($type) {
                                                        Category::class => $urlService->generateCategoryUrl($model->slug),
                                                        Product::class => $urlService->generateProductUrl($model->slug),
                                                        default => '/'.$model->slug,
                                                    };
                                                    $set('canonical_url', $url);
                                                }
                                            }
                                        }),

                                    Forms\Components\Actions\Action::make('generate_title')
                                        ->label('Title')
                                        ->icon('heroicon-o-document-text')
                                        ->size('sm')
                                        ->color('gray')
                                        ->visible(fn (Forms\Get $get) => in_array($get('seoable_type'), [Category::class, Product::class]))
                                        ->action(function (Forms\Set $set, Forms\Get $get) {
                                            $type = $get('seoable_type');
                                            $id = $get('seoable_id');
                                            $language = $get('language') ?? 'uk';

                                            if ($type && $id) {
                                                $model = $type::find($id);
                                                if ($model) {
                                                    $generator = new \App\Services\SeoMetaGenerator;
                                                    $seoData = match ($type) {
                                                        Category::class => $generator->generateForCategory($model, $language),
                                                        Product::class => $generator->generateForProduct($model, $language),
                                                        default => [],
                                                    };
                                                    if (isset($seoData['meta_title'])) {
                                                        $set('meta_title', $seoData['meta_title']);
                                                    }
                                                }
                                            }
                                        }),

                                    Forms\Components\Actions\Action::make('generate_description')
                                        ->label('Description')
                                        ->icon('heroicon-o-document-text')
                                        ->size('sm')
                                        ->color('gray')
                                        ->visible(fn (Forms\Get $get) => in_array($get('seoable_type'), [Category::class, Product::class]))
                                        ->action(function (Forms\Set $set, Forms\Get $get) {
                                            $type = $get('seoable_type');
                                            $id = $get('seoable_id');
                                            $language = $get('language') ?? 'uk';

                                            if ($type && $id) {
                                                $model = $type::find($id);
                                                if ($model) {
                                                    $generator = new \App\Services\SeoMetaGenerator;
                                                    $seoData = match ($type) {
                                                        Category::class => $generator->generateForCategory($model, $language),
                                                        Product::class => $generator->generateForProduct($model, $language),
                                                        default => [],
                                                    };
                                                    if (isset($seoData['meta_description'])) {
                                                        $set('meta_description', $seoData['meta_description']);
                                                    }
                                                }
                                            }
                                        }),

                                    Forms\Components\Actions\Action::make('generate_keywords')
                                        ->label('Keywords')
                                        ->icon('heroicon-o-tag')
                                        ->size('sm')
                                        ->color('gray')
                                        ->visible(fn (Forms\Get $get) => in_array($get('seoable_type'), [Category::class, Product::class]))
                                        ->action(function (Forms\Set $set, Forms\Get $get) {
                                            $type = $get('seoable_type');
                                            $id = $get('seoable_id');
                                            $language = $get('language') ?? 'uk';

                                            if ($type && $id) {
                                                $model = $type::find($id);
                                                if ($model) {
                                                    $generator = new \App\Services\SeoMetaGenerator;
                                                    $seoData = match ($type) {
                                                        Category::class => $generator->generateForCategory($model, $language),
                                                        Product::class => $generator->generateForProduct($model, $language),
                                                        default => [],
                                                    };
                                                    if (isset($seoData['meta_keywords'])) {
                                                        $keywords = is_string($seoData['meta_keywords'])
                                                            ? explode(', ', $seoData['meta_keywords'])
                                                            : $seoData['meta_keywords'];
                                                        $set('meta_keywords', $keywords);
                                                    }
                                                }
                                            }
                                        }),
                                ]),
                            ]),

                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('generate_all_seo')
                                ->label('🎯 Генерувати всі SEO дані')
                                ->color('primary')
                                ->size('lg')
                                ->visible(fn (Forms\Get $get) => in_array($get('seoable_type'), [Category::class, Product::class]))
                                ->action(function (Forms\Set $set, Forms\Get $get) {
                                    $type = $get('seoable_type');
                                    $id = $get('seoable_id');
                                    $language = $get('language') ?? 'uk';

                                    if ($type && $id) {
                                        $model = $type::find($id);
                                        if ($model) {
                                            $generator = new \App\Services\SeoMetaGenerator;
                                            $seoData = match ($type) {
                                                Category::class => $generator->generateForCategory($model, $language),
                                                Product::class => $generator->generateForProduct($model, $language),
                                                default => [],
                                            };

                                            foreach ($seoData as $field => $value) {
                                                if ($field === 'meta_keywords' && is_string($value)) {
                                                    $set($field, explode(', ', $value));
                                                } else {
                                                    $set($field, $value);
                                                }
                                            }

                                            // Генеруємо URL
                                            if ($model->slug) {
                                                $urlService = new \App\Services\UrlRouterService;
                                                $url = match ($type) {
                                                    Category::class => $urlService->generateCategoryUrl($model->slug),
                                                    Product::class => $urlService->generateProductUrl($model->slug),
                                                    default => '/'.$model->slug,
                                                };
                                                $set('canonical_url', $url);
                                            }
                                        }
                                    }
                                }),
                        ]),
                    ])
                    ->collapsible(),
            ]);
    }

    private static function getOpenGraphTab(): Forms\Components\Tabs\Tab
    {
        return Forms\Components\Tabs\Tab::make('📘 Open Graph')
            ->schema([
                Forms\Components\Section::make('Facebook/Open Graph')
                    ->schema([
                        Forms\Components\TextInput::make('og_title')
                            ->label('OG Title')
                            ->maxLength(60)
                            ->helperText('Залиште порожнім для використання SEO Title'),

                        Forms\Components\Textarea::make('og_description')
                            ->label('OG Description')
                            ->maxLength(200)
                            ->rows(3)
                            ->helperText('Залиште порожнім для використання SEO Description'),

                        Forms\Components\TextInput::make('og_image')
                            ->label('OG Image URL')
                            ->url()
                            ->helperText('Рекомендований розмір: 1200x630px'),

                        Forms\Components\Select::make('og_type')
                            ->label('OG Type')
                            ->options([
                                'website' => 'website',
                                'article' => 'article',
                                'product' => 'product',
                            ])
                            ->default('website'),
                    ])
                    ->columns(2),
            ]);
    }

    private static function getTwitterTab(): Forms\Components\Tabs\Tab
    {
        return Forms\Components\Tabs\Tab::make('🐦 Twitter')
            ->schema([
                Forms\Components\Section::make('Twitter Cards')
                    ->schema([
                        Forms\Components\Select::make('twitter_card')
                            ->label('Twitter Card Type')
                            ->options([
                                'summary' => 'Summary',
                                'summary_large_image' => 'Summary Large Image',
                                'app' => 'App',
                                'player' => 'Player',
                            ])
                            ->default('summary_large_image'),

                        Forms\Components\TextInput::make('twitter_title')
                            ->label('Twitter Title')
                            ->maxLength(70)
                            ->helperText('Залиште порожнім для використання SEO Title'),

                        Forms\Components\Textarea::make('twitter_description')
                            ->label('Twitter Description')
                            ->maxLength(200)
                            ->rows(3)
                            ->helperText('Залиште порожнім для використання SEO Description'),

                        Forms\Components\TextInput::make('twitter_image')
                            ->label('Twitter Image URL')
                            ->url()
                            ->helperText('Рекомендований розмір: 1200x675px'),

                        Forms\Components\TextInput::make('twitter_site')
                            ->label('Twitter Site (@username)')
                            ->helperText('@username вашого Twitter аккаунту'),
                    ])
                    ->columns(2),
            ]);
    }

    private static function getSitemapTab(): Forms\Components\Tabs\Tab
    {
        return Forms\Components\Tabs\Tab::make('🗺️ Sitemap')
            ->schema([
                Forms\Components\Section::make('Налаштування Sitemap')
                    ->schema([
                        Forms\Components\Toggle::make('sitemap_include')
                            ->label('Включати в sitemap')
                            ->default(true),

                        Forms\Components\Select::make('sitemap_priority')
                            ->label('Пріоритет')
                            ->options([
                                '1.0' => '1.0 - Найвищий',
                                '0.9' => '0.9 - Дуже високий',
                                '0.8' => '0.8 - Високий',
                                '0.7' => '0.7 - Середній',
                                '0.6' => '0.6 - Нижче середнього',
                                '0.5' => '0.5 - Низький',
                            ])
                            ->default('0.7'),

                        Forms\Components\Select::make('sitemap_changefreq')
                            ->label('Частота оновлення')
                            ->options([
                                'always' => 'always',
                                'hourly' => 'hourly',
                                'daily' => 'daily',
                                'weekly' => 'weekly',
                                'monthly' => 'monthly',
                                'yearly' => 'yearly',
                                'never' => 'never',
                            ])
                            ->default('weekly'),
                    ])
                    ->columns(3),
            ]);
    }

    private static function getFaqTab(): Forms\Components\Tabs\Tab
    {
        return Forms\Components\Tabs\Tab::make('❓ FAQ Schema')
            ->schema([
                Forms\Components\Section::make('FAQ Налаштування')
                    ->schema([
                        Forms\Components\Toggle::make('auto_generate_faq')
                            ->label('Автоматична генерація FAQ')
                            ->default(true)
                            ->helperText('Генерувати FAQ питання автоматично на основі типу сторінки'),

                        Forms\Components\Repeater::make('custom_faq_questions')
                            ->label('Додаткові FAQ питання')
                            ->schema([
                                Forms\Components\TextInput::make('question')
                                    ->label('Питання')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(2),

                                Forms\Components\Textarea::make('answer')
                                    ->label('Відповідь')
                                    ->required()
                                    ->maxLength(1000)
                                    ->rows(3)
                                    ->columnSpan(2),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['question'] ?? 'Нове питання')
                            ->addActionLabel('➕ Додати FAQ')
                            ->deleteAction(fn (Forms\Components\Actions\Action $action) => $action->requiresConfirmation()
                            ),
                    ]),
            ]);
    }

    private static function getStructuredDataTab(): Forms\Components\Tabs\Tab
    {
        return Forms\Components\Tabs\Tab::make('📊 Structured Data')
            ->schema([
                Forms\Components\Section::make('Schema.org Structured Data')
                    ->schema([
                        Forms\Components\Textarea::make('structured_data')
                            ->label('JSON-LD Structured Data')
                            ->rows(20)
                            ->helperText('Schema.org structured data у форматі JSON-LD')
                            ->rule(['json']),
                    ]),

                Forms\Components\Section::make('Швидкі шаблони')
                    ->schema([
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('generate_product_schema')
                                ->label('📦 Товар Schema')
                                ->color('primary')
                                ->visible(fn (Forms\Get $get) => $get('seoable_type') === Product::class)
                                ->action(function (Forms\Set $set, Forms\Get $get) {
                                    $productId = $get('seoable_id');
                                    if ($productId) {
                                        $product = Product::find($productId);
                                        $schema = [
                                            '@context' => 'https://schema.org/',
                                            '@type' => 'Product',
                                            'name' => $product->title,
                                            'description' => $product->short_description,
                                            'offers' => [
                                                '@type' => 'Offer',
                                                'price' => $product->price,
                                                'priceCurrency' => 'UAH',
                                                'availability' => 'https://schema.org/InStock',
                                            ],
                                        ];
                                        $set('structured_data', json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                                    }
                                }),

                            Forms\Components\Actions\Action::make('generate_category_schema')
                                ->label('📂 Категорія Schema')
                                ->color('success')
                                ->visible(fn (Forms\Get $get) => $get('seoable_type') === Category::class)
                                ->action(function (Forms\Set $set, Forms\Get $get) {
                                    $categoryId = $get('seoable_id');
                                    if ($categoryId) {
                                        $category = Category::find($categoryId);
                                        $schema = [
                                            '@context' => 'https://schema.org/',
                                            '@type' => 'CollectionPage',
                                            'name' => $category->title,
                                            'mainEntity' => [
                                                '@type' => 'ItemList',
                                                'numberOfItems' => $category->products()->count(),
                                            ],
                                        ];
                                        $set('structured_data', json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                                    }
                                }),
                        ]),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('seoable_type')
                    ->label('Тип')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Category::class => 'Категорія',
                        Product::class => 'Товар',
                        default => Str::title($state),
                    })
                    ->colors([
                        'primary' => Category::class,
                        'success' => Product::class,
                        'warning' => fn ($state) => ! in_array($state, [Category::class, Product::class]),
                    ]),

                Tables\Columns\TextColumn::make('seoable.title')
                    ->label('Об\'єкт')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('meta_title')
                    ->label('SEO Title')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(fn (?string $state): string => $state ?? ''),

                Tables\Columns\TextColumn::make('meta_description')
                    ->label('Description')
                    ->limit(60)
                    ->tooltip(fn (?string $state): string => $state ?? ''),

                Tables\Columns\TextColumn::make('language')
                    ->label('Мова')
                    ->badge()
                    ->colors([
                        'primary' => 'uk',
                        'success' => 'en',
                    ]),

                Tables\Columns\ToggleColumn::make('sitemap_include')
                    ->label('Sitemap'),

                Tables\Columns\TextColumn::make('sitemap_priority')
                    ->label('Пріоритет')
                    ->badge()
                    ->colors([
                        'danger' => fn ($state) => $state >= 0.9,
                        'warning' => fn ($state) => $state >= 0.7 && $state < 0.9,
                        'success' => fn ($state) => $state < 0.7,
                    ]),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Оновлено')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('seoable_type')
                    ->label('Тип об\'єкту')
                    ->options([
                        Category::class => 'Категорії',
                        Product::class => 'Товари',
                        'homepage' => 'Головна сторінка',
                        'specials' => 'Акції',
                        'hits' => 'Хіти',
                        'new' => 'Новинки',
                    ]),

                Tables\Filters\SelectFilter::make('language')
                    ->label('Мова')
                    ->options([
                        'uk' => 'Українська',
                    ]),

                Tables\Filters\TernaryFilter::make('sitemap_include')
                    ->label('В Sitemap'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('')
                    ->icon('heroicon-o-eye')
                    ->size('lg')
                    ->tooltip('Перегляд'),
                Tables\Actions\EditAction::make()
                    ->label('')
                    ->icon('heroicon-o-pencil')
                    ->size('lg')
                    ->tooltip('Змінити'),
                Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->icon('heroicon-o-trash')
                    ->size('lg')
                    ->tooltip('Видалити'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('generate_seo')
                        ->label('🎯 Генерувати SEO')
                        ->color('success')
                        ->icon('heroicon-o-sparkles')
                        ->action(function ($records) {
                            $generator = new \App\Services\SeoMetaGenerator;
                            $updated = 0;

                            foreach ($records as $record) {
                                if ($record->seoable_type === Category::class && $record->seoable) {
                                    $seoData = $generator->generateForCategory($record->seoable, $record->language);
                                    $record->update($seoData);
                                    $updated++;
                                } elseif ($record->seoable_type === Product::class && $record->seoable) {
                                    $seoData = $generator->generateForProduct($record->seoable, $record->language);
                                    $record->update($seoData);
                                    $updated++;
                                }
                            }

                            \Filament\Notifications\Notification::make()
                                ->title("SEO дані згенеровано для {$updated} записів")
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSeoMetas::route('/'),
            'create' => Pages\CreateSeoMeta::route('/create'),
            'edit' => Pages\EditSeoMeta::route('/{record}/edit'),
        ];
    }
}
