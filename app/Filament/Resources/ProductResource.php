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
    use \App\Filament\Concerns\GatedResource;

    use Translatable;
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Каталог';
    protected static ?string $navigationLabel = 'Товари';

    protected static ?string $modelLabel = 'Товар';

    protected static ?string $pluralModelLabel = 'Товари';

    protected static ?int $navigationSort = 10;

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

    /** Підпис опції товару-аналога: SKU · назва · виробник · ціна. */
    public static function analogOptionLabel(Product $p): string
    {
        $title = is_array($p->title)
            ? ($p->title['uk'] ?? (array_values($p->title)[0] ?? ''))
            : (string) $p->title;
        $parts = array_filter([
            $p->sku,
            $title,
            $p->manufacturer,
            $p->price > 0 ? number_format((float) $p->price, 0, '.', ' ').' ₴' : null,
        ]);

        return implode(' · ', $parts);
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
                                            // OEM/каталожні коди часто з пробілами/крапками/слешами
                                            // (напр. "E73 914 731 I-PVK", "06A 115 561 B") — alphaDash
                                            // блокував пробіли й валив збереження. Дозволяємо ці символи.
                                            ->rule('regex:/^[A-Za-z0-9\s\-\.\/_+]+$/')
                                            ->validationMessages(['regex' => 'Код товару може містити літери, цифри, пробіли та символи - . / _ +'])
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
                                    ->helperText('Необовʼязково — можна лишити порожнім при масовому наповненні.')
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
                                                // Стара ціна = 0 (без знижки) АБО більша за поточну ціну.
                                                // Читаємо price з live form-стану (Get) — у Livewire
                                                // request()->get('price') завжди null, тож правило раніше
                                                // ніколи не спрацьовувало.
                                                fn (Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                                    $price = (float) $get('price');
                                                    if ((float) $value > 0 && (float) $value <= $price) {
                                                        $fail('Стара ціна має бути більшою за поточну ціну або дорівнювати 0');
                                                    }
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
                                        Forms\Components\Select::make('stock_status')
                                            ->label('Наявність')
                                            ->options(fn () => \App\Models\StockStatus::options())
                                            ->default(fn () => \App\Models\StockStatus::defaultKey())
                                            ->required()
                                            ->native(false)
                                            ->helperText('Довідник: Каталог → Статуси наявності')
                                            ->columnSpanFull(),
                                        Forms\Components\Toggle::make('is_active')
                                            ->label('Активний (показувати на сайті)')
                                            ->default(true)
                                            ->helperText('Вимкніть, щоб приховати товар із вітрини без видалення'),
                                        Forms\Components\Toggle::make('is_hit')
                                            ->label('Популярний товар')
                                            ->helperText('Відображається в секції хітів'),
                                        Forms\Components\Toggle::make('is_new')
                                            ->label('Новий товар')
                                            ->helperText('Відображається в секції новинок'),
                                    ])
                                    ->columns(2),
                            ]),

                        Forms\Components\Tabs\Tab::make('Медіа')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Forms\Components\Section::make('Головне зображення')
                                    ->description('Велике фото на product page та list. 16:9, до 5 MB.')
                                    ->collapsible()
                                    ->schema([
                                        Forms\Components\FileUpload::make('image')
                                            ->hiddenLabel()
                                            ->image()
                                            ->imageEditor()
                                            ->imageCropAspectRatio('16:9')
                                            ->imageResizeMode('cover')
                                            ->imageResizeTargetWidth('1200')
                                            ->imageResizeTargetHeight('675')
                                            ->maxSize(5120)
                                            ->directory('products/main')
                                            ->visibility('public')
                                            ->moveFiles()
                                            ->panelLayout('grid')
                                            ->imagePreviewHeight('200'),
                                    ])->columnSpanFull(),

                                Forms\Components\Section::make('Галерея зображень')
                                    ->description('Drag-and-drop для зміни порядку · клік для редагування · хрестик для видалення.')
                                    ->collapsible()
                                    ->schema([
                                        Forms\Components\FileUpload::make('gallery_images')
                                            ->hiddenLabel()
                                            ->image()
                                            ->imageEditor()
                                            ->imageEditorAspectRatios([
                                                null,
                                                '1:1',
                                                '4:3',
                                                '16:9',
                                            ])
                                            ->multiple()
                                            ->maxFiles(12)
                                            ->maxSize(5120)
                                            ->directory('products/gallery')
                                            ->visibility('public')
                                            ->reorderable()
                                            ->appendFiles()
                                            ->panelLayout('grid')
                                            ->imagePreviewHeight('140')
                                            ->loadingIndicatorPosition('center')
                                            ->removeUploadedFileButtonPosition('right')
                                            ->uploadButtonPosition('left')
                                            ->uploadProgressIndicatorPosition('center')
                                            ->helperText('До 12 зображень · WebP/JPG/PNG до 5 MB · перетягуй щоб змінити порядок'),
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

                                            // Генеруємо SEO title/description за шаблонами таксономії «Товари»
                                            $seoVars = [
                                                'name' => $title,
                                                'price' => number_format((float) ($get('price') ?: 0), 0, '.', ' '),
                                                'sku' => (string) ($get('sku') ?? ''),
                                                'brand' => (string) ($get('manufacturer') ?? ''),
                                                'excerpt' => $description ? \Illuminate\Support\Str::limit(strip_tags($description), 100, '') : '',
                                            ];
                                            $set('seo_title', \App\Support\SeoTemplates::title('product', $seoVars));
                                            $set('seo_description', \App\Support\SeoTemplates::description('product', $seoVars));

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
                                // Характеристики з таксономії (Filter/FilterGroup) — призначаються
                                // прямо тут, без переходу в режим редагування. Pivot filter_group_id
                                // підставляється автоматично (App\Models\Pivots\FilterProduct) — він
                                // потрібен faceted-фільтру каталогу.
                                Forms\Components\Section::make('Характеристики (фільтри каталогу)')
                                    ->description('Виробник, марка авто, тип, в\'язкість… — за ними працює фільтрація в каталозі')
                                    ->collapsible()
                                    ->schema([
                                        Forms\Components\Select::make('filters')
                                            ->label('Характеристики')
                                            ->relationship(
                                                name: 'filters',
                                                titleAttribute: 'title',
                                                // with('filterGroup') усуває N+1: лейбл кожного
                                                // фільтра бере group->title; без eager-load preload
                                                // 84 фільтрів = ~85 запитів. Тепер 1.
                                                modifyQueryUsing: fn ($query) => $query
                                                    ->with('filterGroup')
                                                    ->where('is_active', true)
                                                    ->orderBy('filter_group_id')
                                                    ->orderBy('sort_order'),
                                            )
                                            ->getOptionLabelFromRecordUsing(function (\App\Models\Filter $f) {
                                                $group = optional($f->filterGroup)->title;
                                                $value = $f->name ?: $f->title;
                                                return $group ? "{$group}: {$value}" : $value;
                                            })
                                            ->multiple()
                                            ->preload()
                                            ->searchable()
                                            // Filament sync() робить bulk-insert у pivot і НЕ запускає
                                            // events моделі pivot, тож filter_group_id (NOT NULL,
                                            // потрібен faceted-фільтру) підставляємо тут вручну,
                                            // мапою filter_id → filter_group_id.
                                            ->saveRelationshipsUsing(function (\App\Models\Product $record, $state): void {
                                                $ids = collect($state)->filter()->values();
                                                $groupByFilter = \App\Models\Filter::whereIn('id', $ids)
                                                    ->pluck('filter_group_id', 'id');
                                                $sync = $ids->mapWithKeys(fn ($id) => [
                                                    $id => ['filter_group_id' => $groupByFilter[$id] ?? null],
                                                ])->all();
                                                $record->filters()->sync($sync);
                                            })
                                            ->helperText('Почніть вводити назву групи або значення. Групу характеристики додаємо автоматично.')
                                            ->columnSpanFull(),
                                    ]),

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
                                                    // Марка — з car_makes (пошук). live → каскадить модель.
                                                    Forms\Components\Select::make('make')
                                                        ->label('Марка')
                                                        ->options(function (Forms\Get $get) {
                                                            // Кеш списку марок (статичний) — repeater запитує на КОЖЕН рядок.
                                                            $opts = \Illuminate\Support\Facades\Cache::remember('admin:car_makes:options', 3600, fn () => \App\Models\CarMake::query()->where('is_active', true)
                                                                ->orderBy('sort_order')->orderBy('name')->pluck('name', 'name')->all());
                                                            $cur = $get('make'); // зберегти наявне значення (навіть поза каталогом)
                                                            if ($cur && ! isset($opts[$cur])) $opts[$cur] = $cur;
                                                            return $opts;
                                                        })
                                                        ->searchable()
                                                        ->live()
                                                        ->afterStateUpdated(function (Forms\Set $set) {
                                                            $set('model', null);
                                                            $set('years', null);
                                                            $set('engine', null);
                                                        })
                                                        ->required(),
                                                    // Модель — car_models відфільтровані за обраною маркою.
                                                    Forms\Components\Select::make('model')
                                                        ->label('Модель')
                                                        ->options(function (Forms\Get $get) {
                                                            $make = $get('make');
                                                            $opts = $make
                                                                ? \App\Models\CarModel::query()->where('is_active', true)
                                                                    ->whereHas('make', fn ($q) => $q->where('name', $make))
                                                                    ->orderBy('sort_order')->orderBy('name')->pluck('name', 'name')->all()
                                                                : [];
                                                            $cur = $get('model');
                                                            if ($cur && ! isset($opts[$cur])) $opts[$cur] = $cur;
                                                            return $opts;
                                                        })
                                                        ->searchable()
                                                        ->live()
                                                        ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                                                            $model = \App\Models\CarModel::query()
                                                                ->whereHas('make', fn ($q) => $q->where('name', $get('make')))
                                                                ->where('name', $get('model'))->first();
                                                            $set('years', $model?->years_range); // авто-підказка років з БД
                                                            $set('engine', null);
                                                        })
                                                        ->placeholder('Спершу оберіть марку')
                                                        ->required(),
                                                    // Роки — діапазон моделі з car_models.years_range + окремі роки.
                                                    Forms\Components\Select::make('years')
                                                        ->label('Роки')
                                                        ->options(function (Forms\Get $get) {
                                                            $model = \App\Models\CarModel::query()
                                                                ->whereHas('make', fn ($q) => $q->where('name', $get('make')))
                                                                ->where('name', $get('model'))->first();
                                                            $opts = [];
                                                            if ($range = $model?->years_range) {
                                                                $opts[$range] = $range; // повний діапазон моделі
                                                                if (preg_match('/(\d{4})\D+(\d{4})?/', $range, $mm)) {
                                                                    $from = (int) $mm[1];
                                                                    // $mm[2] може бути відсутнім (напр. "2016–present"/"2016–н.в.") → беремо поточний рік.
                                                                    $to = (! empty($mm[2])) ? (int) $mm[2] : (int) date('Y');
                                                                    for ($y = $from; $y <= $to && $y <= 2035; $y++) $opts[(string) $y] = (string) $y;
                                                                }
                                                            }
                                                            $cur = $get('years');
                                                            if ($cur && ! isset($opts[$cur])) $opts[$cur] = $cur;
                                                            return $opts;
                                                        })
                                                        ->searchable()
                                                        ->placeholder('Оберіть модель'),
                                                    // Двигун — car_engines обраної моделі.
                                                    Forms\Components\Select::make('engine')
                                                        ->label('Двигун')
                                                        ->options(function (Forms\Get $get) {
                                                            $model = \App\Models\CarModel::query()
                                                                ->whereHas('make', fn ($q) => $q->where('name', $get('make')))
                                                                ->where('name', $get('model'))->first();
                                                            $opts = $model
                                                                ? \App\Models\CarEngine::query()->where('model_id', $model->id)
                                                                    ->where('is_active', true)->orderBy('sort_order')->pluck('label', 'label')->all()
                                                                : [];
                                                            $cur = $get('engine');
                                                            if ($cur && ! isset($opts[$cur])) $opts[$cur] = $cur;
                                                            return $opts;
                                                        })
                                                        ->searchable()
                                                        ->placeholder('Оберіть модель'),
                                                ]),
                                            ])
                                            ->itemLabel(fn (array $state) => trim(($state['make'] ?? '').' '.($state['model'] ?? '').' '.($state['years'] ?? '')))
                                            ->reorderable()
                                            ->collapsible()
                                            ->columnSpanFull(),
                                    ]),

                                Forms\Components\Section::make('Аналоги / замінники')
                                    ->description('Реальні товари каталогу, що можуть замінити цей — показуються у вкладці «Аналоги» на сторінці товару')
                                    ->collapsible()
                                    ->collapsed()
                                    ->schema([
                                        Forms\Components\Select::make('analogProducts')
                                            ->label('Товари-аналоги')
                                            ->relationship('analogProducts', 'title')
                                            ->multiple()
                                            ->searchable()
                                            ->getSearchResultsUsing(function (string $search, ?Product $record) {
                                                return Product::query()
                                                    ->where('is_active', true)
                                                    ->when($record, fn ($q) => $q->where('id', '!=', $record->id))
                                                    ->where(function ($q) use ($search) {
                                                        $like = '%'.$search.'%';
                                                        $q->where('sku', 'like', $like)
                                                            ->orWhere('title', 'like', $like)
                                                            ->orWhere('manufacturer', 'like', $like);
                                                    })
                                                    ->limit(30)
                                                    ->get()
                                                    ->mapWithKeys(fn (Product $p) => [$p->id => static::analogOptionLabel($p)]);
                                            })
                                            ->getOptionLabelsUsing(fn (array $values) => Product::query()
                                                ->whereIn('id', $values)
                                                ->get()
                                                ->mapWithKeys(fn (Product $p) => [$p->id => static::analogOptionLabel($p)])
                                                ->all())
                                            ->placeholder('Пошук за назвою, SKU або виробником…')
                                            ->helperText('Підбирайте ту саму деталь від інших виробників. Ціна та наявність підтягнуться автоматично з картки товару.')
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
                    })
                    ->toggleable(),
                Tables\Columns\ImageColumn::make('image')
                    ->label('Фото')
                    ->size(56)
                    ->extraImgAttributes(['class' => 'rounded-lg ring-1 ring-black/5 object-cover bg-gradient-to-br from-gray-50 to-gray-100'])
                    // Use PartImage helper — same chain as storefront:
                    // explicit image → kind-pool webp → monogram SVG.
                    ->getStateUsing(function ($record) {
                        $categoryTitle = $record->category
                            ? (is_array($record->category->title)
                                ? ($record->category->title['uk'] ?? '')
                                : (string) $record->category->title)
                            : null;
                        $kind = \App\Support\PartImage::kindFromCategory($categoryTitle);
                        $title = is_array($record->title)
                            ? ($record->title['uk'] ?? '')
                            : (string) $record->title;
                        return \App\Support\PartImage::resolve(
                            explicit: $record->image,
                            kind: $kind,
                            seed: $record->id,
                            title: $title,
                        );
                    })
                    ->checkFileExistence(false)
                    ->toggleable(),
                // Раніше TextInputColumn (інлайн-редагування) — editable-колонки
                // Filament інстансують повноцінний form-компонент на КОЖЕН рядок
                // (~100мс/рядок → ~2с на 25 рядків). Звичайна TextColumn рендериться
                // у рази дешевше; назву редагуємо через дію-олівець.
                Tables\Columns\TextColumn::make('title')
                    ->label('Назва')
                    ->searchable()
                    ->sortable()
                    ->limit(60)
                    ->tooltip(fn ($state) => is_array($state) ? ($state['uk'] ?? reset($state)) : $state)
                    ->wrap(),
                Tables\Columns\TextColumn::make('category.title')
                    ->label('Категорія')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->toggleable(),
                // brandModel (а не brand) — саме цей зв'язок eager-load'иться у
                // ->query(); інакше Filament довантажував brand окремо щорядка (N+1).
                Tables\Columns\TextColumn::make('brandModel.name')
                    ->label('Бренд')
                    ->badge()
                    ->color('warning')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Ціна')
                    ->formatStateUsing(fn ($state) => number_format($state, 2, '.', ' ').' грн')
                    ->sortable()
                    ->weight('bold')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('old_price')
                    ->label('Стара ціна')
                    ->formatStateUsing(fn ($state) => $state > 0 ? number_format($state, 2, '.', ' ').' грн' : '-')
                    ->sortable()
                    ->color('gray')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_hit')
                    ->label('Популярний')
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_new')
                    ->label('Новинка')
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата створення')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Раніше: relationship('category','title')->preload() — preload
                // вантажив УСІ 165 категорій і викликав full_path на кожній, а той
                // піднімається по ->parent до 6 рівнів (N+1) → сотні запитів на
                // КОЖЕН рендер таблиці (~2s). Тепер: один запит усіх категорій +
                // побудова breadcrumb-шляхів у пам'яті, кеш 10 хв.
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Категорія')
                    ->options(fn () => \Illuminate\Support\Facades\Cache::remember(
                        'admin:products:category_filter_paths',
                        600,
                        function () {
                            $cats = \App\Models\Category::query()->get(['id', 'parent_id', 'title']);
                            $byId = $cats->keyBy('id');

                            return $cats->mapWithKeys(function ($c) use ($byId) {
                                $titles = [];
                                $node = $c;
                                $depth = 6;
                                while ($node && $depth-- > 0) {
                                    $t = is_array($node->title)
                                        ? ($node->title['uk'] ?? reset($node->title))
                                        : (string) $node->title;
                                    $titles[] = $t;
                                    $node = $node->parent_id ? ($byId[$node->parent_id] ?? null) : null;
                                }

                                return [$c->id => implode(' → ', array_reverse(array_filter($titles)))];
                            })->sort()->all();
                        },
                    ))
                    ->searchable(),
                Tables\Filters\SelectFilter::make('brand')
                    ->label('Бренд')
                    ->relationship('brandModel', 'name')
                    ->searchable()
                    ->preload(),
                // Марка авто — для деталей фільтруємо за сумісністю (JSON compatibility[].make).
                Tables\Filters\SelectFilter::make('car_make')
                    ->label('Марка авто')
                    ->options(fn () => \Illuminate\Support\Facades\Cache::remember(
                        'admin:car_makes:filter_options',
                        3600,
                        fn () => \App\Models\CarMake::query()->where('is_active', true)
                            ->orderBy('sort_order')->orderBy('name')->pluck('name', 'name')->all(),
                    ))
                    ->searchable()
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['value'] ?? null,
                        fn (Builder $q, $make): Builder => $q->where('compatibility', 'like', '%"make":"'.$make.'"%'),
                    )),
                Tables\Filters\SelectFilter::make('stock_status')
                    ->label('Наявність')
                    ->options(fn () => \App\Models\StockStatus::options()),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Активність')
                    ->trueLabel('Активні')
                    ->falseLabel('Приховані'),
                Tables\Filters\TernaryFilter::make('is_hit')
                    ->label('Популярні товари'),
                Tables\Filters\TernaryFilter::make('is_new')
                    ->label('Нові товари'),
                // Зі знижкою — стара ціна більша за поточну.
                Tables\Filters\TernaryFilter::make('has_discount')
                    ->label('Зі знижкою')
                    ->placeholder('Усі')
                    ->trueLabel('Лише зі знижкою')
                    ->falseLabel('Без знижки')
                    ->queries(
                        true: fn (Builder $q): Builder => $q->whereNotNull('old_price')->whereColumn('old_price', '>', 'price'),
                        false: fn (Builder $q): Builder => $q->where(fn (Builder $q) => $q->whereNull('old_price')->orWhereColumn('old_price', '<=', 'price')),
                        blank: fn (Builder $q): Builder => $q,
                    ),
                Tables\Filters\Filter::make('price_range')
                    ->label('Діапазон цін')
                    ->form([
                        Forms\Components\TextInput::make('price_from')
                            ->label('Ціна від')
                            ->numeric()
                            ->suffix('грн'),
                        Forms\Components\TextInput::make('price_to')
                            ->label('Ціна до')
                            ->numeric()
                            ->suffix('грн'),
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
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['price_from'] ?? null) {
                            $indicators[] = 'Ціна від '.$data['price_from'].' грн';
                        }
                        if ($data['price_to'] ?? null) {
                            $indicators[] = 'Ціна до '.$data['price_to'].' грн';
                        }

                        return $indicators;
                    }),
            ])
            ->filtersFormColumns(['sm' => 1, 'lg' => 2, 'xl' => 3])
            ->filtersFormWidth(\Filament\Support\Enums\MaxWidth::FourExtraLarge)
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
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Активувати (показати)')
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Деактивувати (приховати)')
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
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
        $relations = [];

        // Inventory tab — частина модуля multi_warehouse.
        if (module('multi_warehouse')->enabled()) {
            $relations[] = RelationManagers\InventoryRelationManager::class;
        }

        // Options/Variants/Related — частина модуля related_products.
        // Всі 3 класи живуть у modules/related_products/src/Filament/...
        // (composer classmap резолвить namespace).
        if (module('related_products')->enabled()) {
            if (class_exists(RelationManagers\OptionsRelationManager::class)) {
                $relations[] = RelationManagers\OptionsRelationManager::class;
            }
            if (class_exists(RelationManagers\VariantsRelationManager::class)) {
                $relations[] = RelationManagers\VariantsRelationManager::class;
            }
            if (class_exists(RelationManagers\RelatedProductsRelationManager::class)) {
                $relations[] = RelationManagers\RelatedProductsRelationManager::class;
            }
        }

        $relations[] = RelationManagers\GroupPricesRelationManager::class;
        $relations[] = RelationManagers\FiltersRelationManager::class;

        return $relations;
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
