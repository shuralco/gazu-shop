<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    use Translatable;
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Каталог';

    protected static ?string $modelLabel = 'Товар';

    protected static ?string $pluralModelLabel = 'Товари';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $low = static::getModel()::where('is_active', true)
            ->whereBetween('quantity', [1, 5])
            ->count();
        return $low > 0 ? 'warning' : 'primary';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        $low = static::getModel()::where('is_active', true)
            ->whereBetween('quantity', [1, 5])
            ->count();
        $out = static::getModel()::where('is_active', true)
            ->where('quantity', '=', 0)
            ->count();
        return "Низький залишок: {$low} · Немає: {$out}";
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Product Information')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Основна інформація')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label('Назва')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull()
                                    ->suffixAction(
                                        Forms\Components\Actions\Action::make('copyTitle')
                                            ->icon('heroicon-o-clipboard')
                                            ->tooltip('Копіювати назву')
                                            ->action(function (Get $get) {
                                                $title = $get('title');

                                                return \Filament\Notifications\Notification::make()
                                                    ->title('Назву скопійовано!')
                                                    ->body($title)
                                                    ->success()
                                                    ->send();
                                            })
                                    ),

                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Select::make('category_id')
                                            ->label('Категорія')
                                            ->relationship('category', 'title')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(fn (Forms\Set $set) => $set('filters', [])),

                                        Forms\Components\Select::make('brand_id')
                                            ->label('Бренд')
                                            ->relationship('brandModel', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->createOptionForm([
                                                Forms\Components\TextInput::make('name')
                                                    ->label('Назва бренду')
                                                    ->required()
                                                    ->maxLength(255),
                                                Forms\Components\Textarea::make('description')
                                                    ->label('Опис')
                                                    ->rows(2),
                                            ])
                                            ->createOptionUsing(function (array $data) {
                                                return \App\Models\Brand::create([
                                                    'name' => $data['name'],
                                                    'slug' => \Str::slug($data['name']),
                                                    'description' => $data['description'] ?? null,
                                                    'is_active' => true,
                                                ])->id;
                                            }),

                                        Forms\Components\TextInput::make('sku')
                                            ->label('Код товару')
                                            ->unique(ignoreRecord: true)
                                            ->alphaDash()
                                            ->default(fn () => Product::generateUniqueSku())
                                            ->readonly()
                                            ->formatStateUsing(function ($state) {
                                                if (empty($state)) {
                                                    return '';
                                                }

                                                return str_replace('SKU-', '', $state);
                                            })
                                            ->dehydrateStateUsing(function ($state) {
                                                if (empty($state)) {
                                                    return null;
                                                }
                                                if (str_starts_with($state, 'SKU-')) {
                                                    return $state;
                                                }

                                                return 'SKU-'.$state;
                                            })
                                            ->suffixAction(
                                                Forms\Components\Actions\Action::make('copySku')
                                                    ->icon('heroicon-o-clipboard')
                                                    ->tooltip('Копіювати код')
                                                    ->action(function (Get $get) {
                                                        $sku = $get('sku');
                                                        $displaySku = $sku ? str_replace('SKU-', '', $sku) : '';

                                                        return \Filament\Notifications\Notification::make()
                                                            ->title('Код скопійовано!')
                                                            ->body($displaySku)
                                                            ->success()
                                                            ->send();
                                                    })
                                            )
                                            ->helperText('Унікальний код товару (без префікса SKU-)'),
                                    ]),

                                Forms\Components\TextInput::make('excerpt')
                                    ->label('Короткий опис')
                                    ->maxLength(255)
                                    ->columnSpanFull()
                                    ->helperText('Відображається на картці товару')
                                    ->suffixAction(
                                        Forms\Components\Actions\Action::make('copyExcerpt')
                                            ->icon('heroicon-o-clipboard')
                                            ->tooltip('Копіювати опис')
                                            ->action(function (Get $get) {
                                                $excerpt = $get('excerpt');

                                                return \Filament\Notifications\Notification::make()
                                                    ->title('Опис скопійовано!')
                                                    ->body($excerpt ?: 'Порожнє значення')
                                                    ->success()
                                                    ->send();
                                            })
                                    ),

                                Forms\Components\RichEditor::make('content')
                                    ->label('Детальний опис')
                                    ->required()
                                    ->columnSpanFull()
                                    ->toolbarButtons([
                                        'bold',
                                        'italic',
                                        'link',
                                        'orderedList',
                                        'unorderedList',
                                        'h2',
                                        'h3',
                                    ])
                                    ->lazy(),
                            ]),

                        Forms\Components\Tabs\Tab::make('Ціноутворення')
                            ->icon('heroicon-o-currency-dollar')
                            ->schema([
                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('price')
                                            ->label('Ціна')
                                            ->required()
                                            ->numeric()
                                            ->prefix('₴')
                                            ->minValue(0)
                                            ->rules(['regex:/^\d+(\.\d{1,2})?$/'])
                                            ->live()
                                            ->suffixActions([
                                                Forms\Components\Actions\Action::make('copyPrice')
                                                    ->icon('heroicon-o-clipboard')
                                                    ->tooltip('Копіювати ціну')
                                                    ->action(function (Get $get) {
                                                        $price = $get('price');

                                                        return \Filament\Notifications\Notification::make()
                                                            ->title('Ціну скопійовано!')
                                                            ->body('₴'.number_format($price, 2))
                                                            ->success()
                                                            ->send();
                                                    }),
                                                Forms\Components\Actions\Action::make('calculate_discount')
                                                    ->icon('heroicon-o-calculator')
                                                    ->tooltip('Розрахувати знижку')
                                                    ->action(function (Set $set, Get $get) {
                                                        $price = $get('price');
                                                        $oldPrice = $get('old_price');
                                                        if ($price && $oldPrice && $oldPrice > $price) {
                                                            $discount = round((($oldPrice - $price) / $oldPrice) * 100);
                                                            \Filament\Notifications\Notification::make()
                                                                ->title("Знижка: {$discount}%")
                                                                ->success()
                                                                ->send();
                                                        }
                                                    }),
                                            ]),

                                        Forms\Components\TextInput::make('old_price')
                                            ->label('Стара ціна')
                                            ->numeric()
                                            ->prefix('₴')
                                            ->minValue(0)
                                            ->default(0)
                                            ->rules([
                                                function () {
                                                    return function (string $attribute, $value, \Closure $fail) {
                                                        if ($value > 0 && $value <= request()->get('price')) {
                                                            $fail('Стара ціна має бути більшою за поточну ціну або дорівнювати 0');
                                                        }
                                                    };
                                                },
                                            ])
                                            ->helperText('0 = без старої ціни, або більша за поточну ціну')
                                            ->live()
                                            ->suffixAction(
                                                Forms\Components\Actions\Action::make('copyOldPrice')
                                                    ->icon('heroicon-o-clipboard')
                                                    ->tooltip('Копіювати стару ціну')
                                                    ->action(function (Get $get) {
                                                        $oldPrice = $get('old_price');

                                                        return \Filament\Notifications\Notification::make()
                                                            ->title('Стару ціну скопійовано!')
                                                            ->body($oldPrice > 0 ? '₴'.number_format($oldPrice, 2) : 'Немає старої ціни')
                                                            ->success()
                                                            ->send();
                                                    })
                                            ),

                                        Forms\Components\TextInput::make('quantity')
                                            ->label('Кількість на складі')
                                            ->numeric()
                                            ->minValue(0)
                                            ->default(0),
                                    ]),

                                Forms\Components\TextInput::make('wholesale_min_quantity')
                                    ->label('Мін. кількість для гурту')
                                    ->numeric()
                                    ->nullable()
                                    ->helperText('Мінімальна кількість для гуртової ціни'),

                                Forms\Components\Fieldset::make('Статус товару')
                                    ->schema([
                                        Forms\Components\Toggle::make('is_hit')
                                            ->label('Популярний товар')
                                            ->helperText('Відображається в секції хітів'),
                                        Forms\Components\Toggle::make('is_new')
                                            ->label('Новий товар')
                                            ->helperText('Відображається в секції новинок'),
                                    ])
                                    ->columns(2),
                            ]),

                        Forms\Components\Tabs\Tab::make('Варіанти')
                            ->icon('heroicon-o-squares-2x2')
                            ->schema([
                                Forms\Components\Repeater::make('options')
                                    ->relationship('options')
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->label('Назва опції')
                                            ->placeholder('Колір, Розмір, Пам\'ять...'),

                                        Forms\Components\Select::make('type')
                                            ->options([
                                                'select' => 'Список',
                                                'color' => 'Колір',
                                                'button' => 'Кнопка',
                                            ])
                                            ->default('select')
                                            ->label('Тип'),

                                        Forms\Components\TextInput::make('sort_order')
                                            ->numeric()
                                            ->default(0)
                                            ->label('Порядок'),

                                        Forms\Components\Toggle::make('is_active')
                                            ->default(true)
                                            ->label('Активна'),

                                        Forms\Components\Repeater::make('values')
                                            ->relationship('values')
                                            ->schema([
                                                Forms\Components\TextInput::make('value')
                                                    ->required()
                                                    ->label('Значення'),

                                                Forms\Components\ColorPicker::make('color_hex')
                                                    ->label('Колір'),

                                                Forms\Components\TextInput::make('price_modifier')
                                                    ->numeric()
                                                    ->default(0)
                                                    ->prefix('₴')
                                                    ->label('Модифікатор ціни'),

                                                Forms\Components\Toggle::make('is_active')
                                                    ->default(true)
                                                    ->label('Активне'),
                                            ])
                                            ->columns(4)
                                            ->label('Значення опції')
                                            ->addActionLabel('Додати значення')
                                            ->collapsible()
                                            ->defaultItems(0)
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(4)
                                    ->label('Опції товару')
                                    ->addActionLabel('Додати опцію')
                                    ->collapsible()
                                    ->defaultItems(0)
                                    ->columnSpanFull(),

                                Forms\Components\Placeholder::make('variants_info')
                                    ->label('')
                                    ->content('Після збереження опцій перейдіть до таблиці "Варіанти товару" нижче та натисніть "Генерувати варіанти" для автоматичного створення всіх комбінацій.')
                                    ->columnSpanFull(),
                            ]),

                        Forms\Components\Tabs\Tab::make('Медіа')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Forms\Components\Group::make([
                                    Forms\Components\FileUpload::make('image')
                                        ->label('Головне зображення')
                                        ->image()
                                        ->imageEditor()
                                        ->imageCropAspectRatio('16:9')
                                        ->imageResizeMode('cover')
                                        ->imageResizeTargetWidth('800')
                                        ->imageResizeTargetHeight('450')
                                        ->maxSize(5120)
                                        ->directory('products/main')
                                        ->visibility('public')
                                        ->moveFiles(),

                                    Forms\Components\FileUpload::make('gallery_images')
                                        ->label('Галерея зображень')
                                        ->image()
                                        ->imageEditor()
                                        ->multiple()
                                        ->maxFiles(10)
                                        ->maxSize(5120)
                                        ->directory('products/gallery')
                                        ->visibility('public')
                                        ->reorderable()
                                        ->appendFiles()
                                        ->helperText('Максимум 10 зображень. Автоматично оптимізуються'),
                                ])->columnSpanFull(),
                            ]),

                        Forms\Components\Tabs\Tab::make('SEO')
                            ->icon('heroicon-o-globe-alt')
                            ->badge(function ($record) {
                                if (! $record) {
                                    return 'Новий';
                                }
                                $seoMeta = $record->seoMeta()->where('language', 'uk')->first();

                                return $seoMeta && $seoMeta->meta_title ? 'Заповнено' : 'Порожньо';
                            })
                            ->badgeColor(function ($record) {
                                if (! $record) {
                                    return 'gray';
                                }
                                $seoMeta = $record->seoMeta()->where('language', 'uk')->first();

                                return $seoMeta && $seoMeta->meta_title ? 'success' : 'warning';
                            })
                            ->schema([
                                Forms\Components\Grid::make(4)
                                    ->schema([
                                        Forms\Components\Actions::make([
                                            Forms\Components\Actions\Action::make('generate_url')
                                                ->label('URL')
                                                ->icon('heroicon-o-link')
                                                ->size('sm')
                                                ->color('gray')
                                                ->action(function ($record, Set $set, Get $get) {
                                                    $title = $get('title');
                                                    if ($title) {
                                                        $urlService = new \App\Services\UrlRouterService;
                                                        $set('slug', $urlService->generateSlug($title));

                                                        \Filament\Notifications\Notification::make()
                                                            ->title('URL згенеровано')
                                                            ->success()
                                                            ->send();
                                                    }
                                                }),
                                        ]),

                                        Forms\Components\Actions::make([
                                            Forms\Components\Actions\Action::make('generate_title')
                                                ->label('Title')
                                                ->icon('heroicon-o-document-text')
                                                ->size('sm')
                                                ->color('gray')
                                                ->action(function ($record, Set $set, Get $get) {
                                                    $title = $get('title');

                                                    if (! $title) {
                                                        \Filament\Notifications\Notification::make()
                                                            ->title('Помилка')
                                                            ->body('Спочатку введіть назву товару')
                                                            ->danger()
                                                            ->send();

                                                        return;
                                                    }

                                                    $titleTemplate = \App\Models\DisplaySetting::get('seo_product_title_template', 'Купити %s | SimpleShop');
                                                    $seoTitle = sprintf($titleTemplate, $title);
                                                    $set('seo_title', $seoTitle);

                                                    \Filament\Notifications\Notification::make()
                                                        ->title('Title згенеровано')
                                                        ->success()
                                                        ->send();
                                                }),
                                        ]),

                                        Forms\Components\Actions::make([
                                            Forms\Components\Actions\Action::make('generate_description')
                                                ->label('Description')
                                                ->icon('heroicon-o-document')
                                                ->size('sm')
                                                ->color('gray')
                                                ->action(function ($record, Set $set, Get $get) {
                                                    $title = $get('title');
                                                    $description = $get('description');

                                                    if (! $title) {
                                                        \Filament\Notifications\Notification::make()
                                                            ->title('Помилка')
                                                            ->body('Спочатку введіть назву товару')
                                                            ->danger()
                                                            ->send();

                                                        return;
                                                    }

                                                    $price = $get('price') ?: '0';
                                                    $descriptionTemplate = \App\Models\DisplaySetting::get('seo_product_description_template', 'Купити %s за найкращою ціною %s грн. %s. Швидка доставка по Україні.');
                                                    $productDescription = $description ? substr(strip_tags($description), 0, 100) : 'Якісний товар';
                                                    $seoDescription = sprintf($descriptionTemplate, $title, $price, $productDescription);
                                                    $set('seo_description', $seoDescription);

                                                    \Filament\Notifications\Notification::make()
                                                        ->title('Description згенеровано')
                                                        ->success()
                                                        ->send();
                                                }),
                                        ]),

                                        Forms\Components\Actions::make([
                                            Forms\Components\Actions\Action::make('generate_keywords')
                                                ->label('Keywords')
                                                ->icon('heroicon-o-hashtag')
                                                ->size('sm')
                                                ->color('gray')
                                                ->action(function ($record, Set $set, Get $get) {
                                                    $title = $get('title');
                                                    $categoryId = $get('category_id');

                                                    if (! $title) {
                                                        \Filament\Notifications\Notification::make()
                                                            ->title('Помилка')
                                                            ->body('Спочатку введіть назву товару')
                                                            ->danger()
                                                            ->send();

                                                        return;
                                                    }

                                                    $keywords = [strtolower($title)];

                                                    if ($categoryId) {
                                                        $category = \App\Models\Category::find($categoryId);
                                                        if ($category) {
                                                            $keywords[] = strtolower($category->title);
                                                        }
                                                    }

                                                    $keywords = array_merge($keywords, ['купити', 'ціна', 'україна', 'доставка']);
                                                    $set('seo_keywords', $keywords);

                                                    \Filament\Notifications\Notification::make()
                                                        ->title('Keywords згенеровано')
                                                        ->success()
                                                        ->send();
                                                }),
                                        ]),
                                    ]),

                                Forms\Components\Actions::make([
                                    Forms\Components\Actions\Action::make('generate_all_seo')
                                        ->label('Згенерувати всі SEO поля')
                                        ->icon('heroicon-o-bolt')
                                        ->color('primary')
                                        ->size('lg')
                                        ->action(function ($record, Set $set, Get $get) {
                                            $title = $get('title');
                                            $description = $get('description');
                                            $categoryId = $get('category_id');

                                            if (! $title) {
                                                \Filament\Notifications\Notification::make()
                                                    ->title('Помилка')
                                                    ->body('Спочатку введіть назву товару')
                                                    ->danger()
                                                    ->send();

                                                return;
                                            }

                                            // Генеруємо URL з назви тільки якщо slug порожній
                                            $currentSlug = $get('slug');
                                            if (! $currentSlug) {
                                                $urlService = new \App\Services\UrlRouterService;
                                                $set('slug', $urlService->generateSlug($title));
                                            }

                                            // Генеруємо SEO title
                                            $titleTemplate = \App\Models\DisplaySetting::get('seo_product_title_template', 'Купити %s | SimpleShop');
                                            $seoTitle = sprintf($titleTemplate, $title);
                                            $set('seo_title', $seoTitle);

                                            // Генеруємо SEO description
                                            $price = $get('price') ?: '0';
                                            $descriptionTemplate = \App\Models\DisplaySetting::get('seo_product_description_template', 'Купити %s за найкращою ціною %s грн. %s. Швидка доставка по Україні.');
                                            $productDescription = $description ? substr(strip_tags($description), 0, 100) : 'Якісний товар';
                                            $seoDescription = sprintf($descriptionTemplate, $title, $price, $productDescription);
                                            $set('seo_description', $seoDescription);

                                            // Генеруємо keywords
                                            $keywords = [strtolower($title)];
                                            if ($categoryId) {
                                                $category = \App\Models\Category::find($categoryId);
                                                if ($category) {
                                                    $keywords[] = strtolower($category->title);
                                                }
                                            }
                                            $keywords = array_merge($keywords, ['купити', 'ціна', 'україна', 'доставка']);
                                            $set('seo_keywords', $keywords);

                                            \Filament\Notifications\Notification::make()
                                                ->title('Всі SEO поля згенеровано')
                                                ->body('URL, Title, Description та Keywords оновлено')
                                                ->success()
                                                ->send();
                                        }),
                                ])->fullWidth(),

                                Forms\Components\TextInput::make('slug')
                                    ->label('SEO URL (slug)')
                                    ->maxLength(255)
                                    ->alphaDash()
                                    ->helperText('SEO дружній URL для товару. Автоматично генерується при збереженні якщо порожній. Це поле перекладається для кожної мови.'),

                                Forms\Components\TextInput::make('seo_title')
                                    ->label('SEO Заголовок')
                                    ->maxLength(fn () => \App\Models\DisplaySetting::get('seo_title_max_length', 60))
                                    ->live(debounce: 500)
                                    ->afterStateHydrated(function (Forms\Components\TextInput $component, $record) {
                                        if (! $record) {
                                            return;
                                        }
                                        $seoMeta = $record->seoMeta()->where('language', 'uk')->first();
                                        $component->state($seoMeta?->meta_title);
                                    })
                                    ->helperText(function (Get $get) {
                                        $maxLength = \App\Models\DisplaySetting::get('seo_title_max_length', 60);
                                        $length = mb_strlen($get('seo_title') ?? '');
                                        $status = $length === 0 ? 'Порожньо' :
                                                 ($length < 30 ? 'Замало' :
                                                 ($length > $maxLength ? 'Забагато' : 'Добре'));

                                        return "Символів: {$length}/{$maxLength} - {$status}";
                                    }),

                                Forms\Components\Textarea::make('seo_description')
                                    ->label('SEO Опис')
                                    ->maxLength(fn () => \App\Models\DisplaySetting::get('seo_description_max_length', 160))
                                    ->rows(3)
                                    ->live(debounce: 500)
                                    ->afterStateHydrated(function (Forms\Components\Textarea $component, $record) {
                                        if (! $record) {
                                            return;
                                        }
                                        $seoMeta = $record->seoMeta()->where('language', 'uk')->first();
                                        $component->state($seoMeta?->meta_description);
                                    })
                                    ->helperText(function (Get $get) {
                                        $maxLength = \App\Models\DisplaySetting::get('seo_description_max_length', 160);
                                        $minLength = \App\Models\DisplaySetting::get('seo_description_min_length', 50);
                                        $length = mb_strlen($get('seo_description') ?? '');
                                        $status = $length === 0 ? 'Порожньо' :
                                                 ($length < $minLength ? 'Замало' :
                                                 ($length > $maxLength ? 'Забагато' : 'Добре'));

                                        return "Символів: {$length}/{$maxLength} - {$status}";
                                    }),

                                Forms\Components\TagsInput::make('seo_keywords')
                                    ->label('SEO Ключові слова')
                                    ->afterStateHydrated(function (Forms\Components\TagsInput $component, $record) {
                                        if (! $record) {
                                            return;
                                        }
                                        $seoMeta = $record->seoMeta()->where('language', 'uk')->first();
                                        if ($seoMeta?->meta_keywords) {
                                            $keywords = is_string($seoMeta->meta_keywords) ?
                                                explode(',', $seoMeta->meta_keywords) :
                                                $seoMeta->meta_keywords;
                                            $component->state(array_map('trim', $keywords));
                                        }
                                    })
                                    ->helperText('Додавайте ключові слова через Enter або кому'),

                                Forms\Components\Textarea::make('search_tags')
                                    ->label('Пошукові теги')
                                    ->rows(3)
                                    ->placeholder('бюджетний, дешевий, ігри, геймінг, подарунок...')
                                    ->helperText('Через кому. Допомагають знаходити товар за семантичними запитами ("дешевий телефон", "щось для ігор"). Генеруються автоматично командою search:generate-tags.'),

                                Forms\Components\Actions::make([
                                    Forms\Components\Actions\Action::make('view_seo_table')
                                        ->label('📊 Відкрити в SEO таблиці')
                                        ->icon('heroicon-o-table-cells')
                                        ->color('gray')
                                        ->url(function ($record) {
                                            if (! $record) {
                                                return \App\Filament\Resources\SeoMetaResource::getUrl('index');
                                            }
                                            $seoMeta = $record->seoMeta()->where('language', 'uk')->first();

                                            return $seoMeta ?
                                                \App\Filament\Resources\SeoMetaResource::getUrl('edit', ['record' => $seoMeta->id]) :
                                                \App\Filament\Resources\SeoMetaResource::getUrl('index');
                                        })
                                        ->openUrlInNewTab(),
                                ])->fullWidth(),
                            ]),

                        // GAZU storefront — детальні поля автозапчастини
                        Forms\Components\Tabs\Tab::make('GAZU автозапчастини')
                            ->icon('heroicon-o-wrench-screwdriver')
                            ->schema([
                                Forms\Components\Section::make('Технічні характеристики')
                                    ->description('Ключ-значення (Висота: 79 мм, M20×1.5 і т.д.)')
                                    ->collapsible()
                                    ->schema([
                                        Forms\Components\KeyValue::make('specifications')
                                            ->label('Характеристики')
                                            ->keyLabel('Параметр')
                                            ->valueLabel('Значення')
                                            ->reorderable()
                                            ->columnSpanFull(),
                                    ]),

                                Forms\Components\Section::make('Сумісність з авто')
                                    ->description('Список марок/моделей/років, для яких підходить деталь')
                                    ->collapsible()
                                    ->schema([
                                        Forms\Components\Repeater::make('compatibility')
                                            ->label('Авто')
                                            ->schema([
                                                Forms\Components\Grid::make(4)->schema([
                                                    Forms\Components\TextInput::make('make')->label('Марка')->placeholder('Volkswagen')->required(),
                                                    Forms\Components\TextInput::make('model')->label('Модель')->placeholder('Passat B8')->required(),
                                                    Forms\Components\TextInput::make('years')->label('Роки')->placeholder('2014–2024'),
                                                    Forms\Components\TextInput::make('engine')->label('Двигун')->placeholder('2.0 TDI'),
                                                ]),
                                            ])
                                            ->itemLabel(fn (array $state) => trim(($state['make'] ?? '').' '.($state['model'] ?? '').' '.($state['years'] ?? '')))
                                            ->reorderable()
                                            ->collapsible()
                                            ->columnSpanFull(),
                                    ]),

                                Forms\Components\Section::make('Аналоги / замінники')
                                    ->description('Аналогічні артикули від інших виробників')
                                    ->collapsible()
                                    ->collapsed()
                                    ->schema([
                                        Forms\Components\Repeater::make('analogs')
                                            ->label('Аналоги')
                                            ->schema([
                                                Forms\Components\Grid::make(5)->schema([
                                                    Forms\Components\TextInput::make('brand')->label('Бренд')->required(),
                                                    Forms\Components\TextInput::make('oem')->label('OEM/артикул')->required(),
                                                    Forms\Components\TextInput::make('price')->label('Ціна, ₴')->numeric()->step('0.01'),
                                                    Forms\Components\TextInput::make('qty')->label('Залишок')->numeric()->default(0),
                                                    Forms\Components\TextInput::make('rating')->label('Рейтинг')->numeric()->step('0.1')->minValue(0)->maxValue(5),
                                                ]),
                                            ])
                                            ->itemLabel(fn (array $state) => trim(($state['brand'] ?? '').' '.($state['oem'] ?? '')))
                                            ->reorderable()
                                            ->collapsible()
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->with(['category:id,title', 'brandModel:id,name'])
                    ->select([
                        'id', 'title', 'slug', 'sku', 'category_id', 'brand_id',
                        'price', 'old_price', 'is_hit', 'is_new',
                        'image', 'created_at',
                    ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('sku')
                    ->label('Код товару')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Код скопійовано!')
                    ->badge()
                    ->color('primary')
                    ->formatStateUsing(function ($state) {
                        if (empty($state)) {
                            return 'Немає коду';
                        }

                        return str_replace('SKU-', '', $state);
                    }),
                Tables\Columns\ImageColumn::make('image')
                    ->label('Фото')
                    ->size(56)
                    ->extraImgAttributes(['class' => 'rounded-lg ring-1 ring-black/5 object-cover bg-gray-50'])
                    ->defaultImageUrl(asset('assets/img/placeholder.svg'))
                    ->checkFileExistence(false),
                Tables\Columns\TextInputColumn::make('title')
                    ->label('Назва')
                    ->searchable()
                    ->sortable()
                    ->rules(['required', 'string', 'max:255'])
                    ->updateStateUsing(function ($record, $state) {
                        $record->update(['title' => $state]);
                        // Slug auto-generation is handled by the model's boot() saving event
                    }),
                Tables\Columns\TextColumn::make('category.title')
                    ->label('Категорія')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                Tables\Columns\TextColumn::make('brand.name')
                    ->label('Бренд')
                    ->badge()
                    ->color('warning')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Ціна')
                    ->formatStateUsing(fn ($state) => number_format($state, 2, '.', ' ').' грн')
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('old_price')
                    ->label('Стара ціна')
                    ->formatStateUsing(fn ($state) => $state > 0 ? number_format($state, 2, '.', ' ').' грн' : '-')
                    ->sortable()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата створення')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Категорія')
                    ->relationship('category', 'title')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('brand')
                    ->label('Бренд')
                    ->relationship('brandModel', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_hit')
                    ->label('Популярні товари'),
                Tables\Filters\TernaryFilter::make('is_new')
                    ->label('Нові товари'),
                Tables\Filters\Filter::make('price_range')
                    ->label('Діапазон цін')
                    ->form([
                        Forms\Components\TextInput::make('price_from')
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\TextInput::make('price_to')
                            ->numeric()
                            ->prefix('$'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['price_from'],
                                fn (Builder $query, $price): Builder => $query->where('price', '>=', $price),
                            )
                            ->when(
                                $data['price_to'],
                                fn (Builder $query, $price): Builder => $query->where('price', '<=', $price),
                            );
                    }),
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
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\Action::make('export')
                    ->label('Експорт CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function () {
                        return response()->streamDownload(function () {
                            $handle = fopen('php://output', 'w');
                            fputcsv($handle, ['ID', 'Title', 'Category', 'Price', 'Old Price', 'Created At']);

                            Product::with('category')->chunk(100, function ($products) use ($handle) {
                                foreach ($products as $product) {
                                    fputcsv($handle, [
                                        $product->id,
                                        $product->title,
                                        $product->category->title ?? '',
                                        $product->price,
                                        $product->old_price,
                                        $product->created_at->format('Y-m-d H:i:s'),
                                    ]);
                                }
                            });

                            fclose($handle);
                        }, 'products-'.now()->format('Y-m-d').'.csv');
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ExportBulkAction::make(),
                    Tables\Actions\BulkAction::make('markAsHit')
                        ->label('Позначити як популярні')
                        ->icon('heroicon-o-fire')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['is_hit' => true]))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('markAsNew')
                        ->label('Позначити як нові')
                        ->icon('heroicon-o-sparkles')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_new' => true]))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(25)
            ->striped()
            ->poll('60s')
            ->persistFiltersInSession()
            ->persistSortInSession()
            ->deferLoading();
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\InventoryRelationManager::class,
            RelationManagers\VariantsRelationManager::class,
            RelationManagers\RelatedProductsRelationManager::class,
            RelationManagers\GroupPricesRelationManager::class,
            RelationManagers\FiltersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
