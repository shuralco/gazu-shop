<?php

namespace App\Filament\Pages;

use App\Models\DisplaySetting;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class ShopSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Налаштування магазину';

    protected static ?string $title = 'Налаштування магазину';

    protected static ?string $navigationGroup = 'Налаштування';

    protected static ?int $navigationSort = 99;

    protected static string $view = 'filament.pages.shop-settings';

    protected static ?string $slug = 'store-configuration';

    public ?array $data = [];

    public function mount(): void
    {
        $defaults = $this->getDefaults();

        $loaded = [];
        foreach (array_keys($defaults) as $key) {
            $loaded[$key] = DisplaySetting::get($key, $defaults[$key]);
        }

        $this->form->fill($loaded);
    }

    protected function getDefaults(): array
    {
        return [
            // General
            'shop_name' => 'SimpleShop',
            'shop_phone' => '+380000000000',
            'shop_email' => 'info@simpleshop.com',
            'shop_address' => '',
            'logo_type' => 'text',
            'logo_text' => 'SimpleShop',
            'logo_image' => null,
            'favicon' => null,

            // SEO
            'seo_title_template' => '{title} | {shop_name}',
            'seo_default_description' => '',
            'google_analytics_id' => '',
            'google_tag_manager_id' => '',
            'facebook_pixel_id' => '',
            'robots_txt_content' => "User-agent: *\nAllow: /\nSitemap: /sitemap.xml",
            'sitemap_auto_generate' => true,

            // Currencies
            'main_currency' => 'UAH',
            'available_currencies' => ['UAH'],
            'auto_update_rates' => false,
            'nbu_api_enabled' => false,

            // Languages
            'default_language' => 'uk',
            'available_languages' => ['uk'],
            'url_locale_prefix' => true,
            'transliteration_standard' => 'dstu_9112',

            // Shipping & Payment
            'free_delivery_threshold' => 1000,
            'delivery_novaposhta' => true,
            'delivery_ukrposhta' => false,
            'delivery_meest' => false,
            'delivery_justin' => false,
            'delivery_pickup' => true,
            'delivery_courier' => false,
            'payment_cash' => true,
            'payment_card' => true,
            'payment_liqpay' => false,
            'payment_wayforpay' => false,
            'payment_monobank' => false,
            'payment_privat24' => false,

            // Reviews
            'reviews_moderation_enabled' => true,
            'reviews_auto_approve' => false,
            'reviews_min_length' => 10,

            // Notifications
            'telegram_bot_token' => '',
            'telegram_chat_id' => '',
            'email_notifications' => true,
            'notify_new_order' => true,
            'notify_low_stock' => false,
            'low_stock_threshold' => 5,

            // Email
            'email_from_name' => 'SimpleShop',
            'email_from_address' => 'no-reply@simpleshop.com',
            'email_reply_to' => '',
            'email_show_logo' => true,
            'email_footer_text' => '',
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('settings')
                    ->tabs([
                        $this->generalTab(),
                        $this->seoTab(),
                        $this->currenciesTab(),
                        $this->languagesTab(),
                        $this->shippingPaymentTab(),
                        $this->emailTab(),
                        $this->reviewsTab(),
                        $this->notificationsTab(),
                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString(),
            ])
            ->statePath('data');
    }

    protected function generalTab(): Tabs\Tab
    {
        return Tabs\Tab::make('Загальнi')
            ->label('Загальнi')
            ->icon('heroicon-o-building-storefront')
            ->schema([
                Section::make('Iнформацiя про магазин')
                    ->description('Основна контактна iнформацiя магазину')
                    ->schema([
                        TextInput::make('shop_name')
                            ->label('Назва магазину')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('SimpleShop'),

                        TextInput::make('shop_phone')
                            ->label('Телефон магазину')
                            ->tel()
                            ->placeholder('+380123456789'),

                        TextInput::make('shop_email')
                            ->label('Email магазину')
                            ->email()
                            ->required()
                            ->placeholder('info@simpleshop.com'),

                        Textarea::make('shop_address')
                            ->label('Адреса магазину')
                            ->rows(3)
                            ->placeholder('вул. Хрещатик, 1, Київ, Україна'),
                    ])
                    ->columns(2),

                Section::make('Логотип та iконка')
                    ->description('Брендинг магазину')
                    ->schema([
                        Select::make('logo_type')
                            ->label('Тип логотипу')
                            ->options([
                                'text' => 'Текстовий',
                                'image' => 'Зображення',
                            ])
                            ->default('text')
                            ->reactive(),

                        TextInput::make('logo_text')
                            ->label('Текст логотипу')
                            ->placeholder('SimpleShop')
                            ->visible(fn (callable $get) => $get('logo_type') === 'text'),

                        FileUpload::make('logo_image')
                            ->label('Зображення логотипу')
                            ->image()
                            ->maxSize(2048)
                            ->directory('settings')
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/svg+xml', 'image/webp'])
                            ->visible(fn (callable $get) => $get('logo_type') === 'image'),

                        FileUpload::make('favicon')
                            ->label('Favicon')
                            ->image()
                            ->maxSize(512)
                            ->directory('settings')
                            ->acceptedFileTypes(['image/png', 'image/x-icon', 'image/vnd.microsoft.icon', 'image/svg+xml'])
                            ->helperText('ICO, PNG або SVG, до 512KB'),
                    ])
                    ->columns(2),
            ]);
    }

    protected function seoTab(): Tabs\Tab
    {
        return Tabs\Tab::make('SEO')
            ->icon('heroicon-o-magnifying-glass')
            ->schema([
                Section::make('Meta-теги за замовчуванням')
                    ->schema([
                        TextInput::make('seo_title_template')
                            ->label('Шаблон meta title')
                            ->placeholder('{title} | {shop_name}')
                            ->helperText('Доступнi змiннi: {title}, {shop_name}, {category}'),

                        Textarea::make('seo_default_description')
                            ->label('Meta description за замовчуванням')
                            ->rows(3)
                            ->maxLength(300),
                    ])
                    ->columns(1),

                Section::make('Аналiтика та трекiнг')
                    ->schema([
                        TextInput::make('google_analytics_id')
                            ->label('Google Analytics ID')
                            ->placeholder('G-XXXXXXXXXX'),

                        TextInput::make('google_tag_manager_id')
                            ->label('Google Tag Manager ID')
                            ->placeholder('GTM-XXXXXXX'),

                        TextInput::make('facebook_pixel_id')
                            ->label('Facebook Pixel ID')
                            ->placeholder('1234567890123456'),
                    ])
                    ->columns(3),

                Section::make('Robots та Sitemap')
                    ->schema([
                        Textarea::make('robots_txt_content')
                            ->label('Вмiст robots.txt')
                            ->rows(6)
                            ->columnSpanFull(),

                        Toggle::make('sitemap_auto_generate')
                            ->label('Автоматична генерацiя sitemap'),
                    ]),
            ]);
    }

    protected function currenciesTab(): Tabs\Tab
    {
        return Tabs\Tab::make('Валюти')
            ->icon('heroicon-o-currency-dollar')
            ->schema([
                Section::make('Налаштування валют')
                    ->schema([
                        Select::make('main_currency')
                            ->label('Основна валюта')
                            ->options([
                                'UAH' => 'UAH - Українська гривня',
                                'USD' => 'USD - Долар США',
                                'EUR' => 'EUR - Євро',
                                'PLN' => 'PLN - Польський злотий',
                            ])
                            ->default('UAH')
                            ->required(),

                        Select::make('available_currencies')
                            ->label('Доступнi валюти')
                            ->options([
                                'UAH' => 'UAH - Українська гривня',
                                'USD' => 'USD - Долар США',
                                'EUR' => 'EUR - Євро',
                                'PLN' => 'PLN - Польський злотий',
                                'GBP' => 'GBP - Британський фунт',
                                'CZK' => 'CZK - Чеська крона',
                            ])
                            ->multiple()
                            ->default(['UAH']),

                        Toggle::make('auto_update_rates')
                            ->label('Автоматичне оновлення курсiв')
                            ->helperText('Щоденне оновлення курсiв валют'),

                        Toggle::make('nbu_api_enabled')
                            ->label('НБУ API')
                            ->helperText('Використовувати API Нацiонального банку України для курсiв'),
                    ])
                    ->columns(2),
            ]);
    }

    protected function languagesTab(): Tabs\Tab
    {
        return Tabs\Tab::make('Мови')
            ->icon('heroicon-o-language')
            ->schema([
                Section::make('Налаштування мов')
                    ->schema([
                        Select::make('default_language')
                            ->label('Мова за замовчуванням')
                            ->options([
                                'uk' => 'Українська',
                                'en' => 'English',
                            ])
                            ->default('uk')
                            ->required(),

                        Select::make('available_languages')
                            ->label('Доступнi мови')
                            ->options([
                                'uk' => 'Українська',
                                'en' => 'English',
                                'pl' => 'Polski',
                                'de' => 'Deutsch',
                            ])
                            ->multiple()
                            ->default(['uk']),

                        Toggle::make('url_locale_prefix')
                            ->label('Префiкс мови в URL')
                            ->helperText('Додавати код мови в URL: /uk/products, /en/products'),

                        Select::make('transliteration_standard')
                            ->label('Стандарт транслiтерацiї')
                            ->options([
                                'dstu_9112' => 'ДСТУ 9112:2021 (рекомендований)',
                                'passport' => 'Паспортний стандарт',
                                'bgn_pcgn' => 'BGN/PCGN (мiжнародний)',
                            ])
                            ->default('dstu_9112'),
                    ])
                    ->columns(2),
            ]);
    }

    protected function shippingPaymentTab(): Tabs\Tab
    {
        return Tabs\Tab::make('Доставка та оплата')
            ->icon('heroicon-o-truck')
            ->schema([
                Section::make('Доставка')
                    ->schema([
                        TextInput::make('free_delivery_threshold')
                            ->label('Безкоштовна доставка вiд (грн)')
                            ->numeric()
                            ->step(1)
                            ->suffix('грн')
                            ->default(1000)
                            ->minValue(0),

                        Grid::make(3)->schema([
                            Toggle::make('delivery_novaposhta')
                                ->label('Нова Пошта')
                                ->default(true),

                            Toggle::make('delivery_ukrposhta')
                                ->label('Укрпошта'),

                            Toggle::make('delivery_meest')
                                ->label('Meest Express'),

                            Toggle::make('delivery_justin')
                                ->label('Justin'),

                            Toggle::make('delivery_pickup')
                                ->label('Самовивiз')
                                ->default(true),

                            Toggle::make('delivery_courier')
                                ->label("Кур'єрська доставка"),
                        ]),
                    ]),

                Section::make('Способи оплати')
                    ->schema([
                        Grid::make(3)->schema([
                            Toggle::make('payment_cash')
                                ->label('Готiвкою при отриманнi')
                                ->default(true),

                            Toggle::make('payment_card')
                                ->label('Оплата картою')
                                ->default(true),

                            Toggle::make('payment_liqpay')
                                ->label('LiqPay'),

                            Toggle::make('payment_wayforpay')
                                ->label('WayForPay'),

                            Toggle::make('payment_monobank')
                                ->label('Monobank'),

                            Toggle::make('payment_privat24')
                                ->label('Приват24'),
                        ]),
                    ]),
            ]);
    }

    protected function emailTab(): Tabs\Tab
    {
        return Tabs\Tab::make('Email')
            ->icon('heroicon-o-envelope')
            ->schema([
                Section::make('Налаштування email-листiв')
                    ->description('Параметри вiдправки та вiдображення email-повiдомлень')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('email_from_name')
                                ->label('Iм\'я вiдправника')
                                ->placeholder('SimpleShop')
                                ->helperText('Вiдображається як iм\'я вiдправника в листах'),

                            TextInput::make('email_from_address')
                                ->label('Email вiдправника')
                                ->email()
                                ->placeholder('no-reply@simpleshop.com')
                                ->helperText('Адреса, з якої надсилаються листи'),
                        ]),

                        TextInput::make('email_reply_to')
                            ->label('Reply-To адреса')
                            ->email()
                            ->placeholder('support@simpleshop.com')
                            ->helperText('Адреса для вiдповiдей клiєнтiв (якщо порожньо - використовується email вiдправника)'),

                        Toggle::make('email_show_logo')
                            ->label('Показувати логотип в листах')
                            ->helperText('Вiдображати логотип магазину у шапцi email-листiв')
                            ->default(true),

                        Textarea::make('email_footer_text')
                            ->label('Текст футера')
                            ->rows(2)
                            ->placeholder('Дякуємо, що обираєте нас!')
                            ->helperText('Додатковий текст у футерi кожного листа'),
                    ]),

                Section::make('Тестування')
                    ->description('Надiслати тестовий лист для перевiрки налаштувань')
                    ->schema([
                        Placeholder::make('email_test_hint')
                            ->label('')
                            ->content('Збережiть налаштування, а потiм скористайтесь кнопкою "Тестовий лист" для перевiрки вiдправки.'),
                    ]),
            ]);
    }

    protected function reviewsTab(): Tabs\Tab
    {
        return Tabs\Tab::make('Відгуки')
            ->icon('heroicon-o-star')
            ->schema([
                Section::make('Модерація відгуків')
                    ->description('Налаштування модерації та автоматичного схвалення відгуків')
                    ->schema([
                        Toggle::make('reviews_moderation_enabled')
                            ->label('Модерація увімкнена')
                            ->helperText('Якщо вимкнено, всі відгуки публікуються одразу без перевірки')
                            ->reactive(),

                        Toggle::make('reviews_auto_approve')
                            ->label('Автосхвалення для підтверджених покупок')
                            ->helperText('Відгуки від покупців, які придбали товар, автоматично схвалюються')
                            ->visible(fn (callable $get) => (bool) $get('reviews_moderation_enabled')),

                        TextInput::make('reviews_min_length')
                            ->label('Мінімальна довжина коментаря')
                            ->numeric()
                            ->default(10)
                            ->minValue(1)
                            ->maxValue(500)
                            ->suffix('символів')
                            ->helperText('Мінімальна кількість символів для тексту відгуку'),
                    ])
                    ->columns(2),
            ]);
    }

    protected function notificationsTab(): Tabs\Tab
    {
        return Tabs\Tab::make('Сповiщення')
            ->icon('heroicon-o-bell')
            ->schema([
                Section::make('Telegram')
                    ->description('Налаштування Telegram-бота для сповiщень')
                    ->schema([
                        TextInput::make('telegram_bot_token')
                            ->label('Telegram Bot Token')
                            ->password()
                            ->revealable()
                            ->placeholder('123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11'),

                        TextInput::make('telegram_chat_id')
                            ->label('Telegram Chat ID')
                            ->placeholder('-1001234567890'),
                    ])
                    ->columns(2),

                Section::make('Email та системнi сповiщення')
                    ->schema([
                        Toggle::make('email_notifications')
                            ->label('Email-сповiщення')
                            ->helperText('Надсилати сповiщення на email адмiнiстратора'),

                        Toggle::make('notify_new_order')
                            ->label('Сповiщення про нове замовлення')
                            ->helperText('Повiдомляти при кожному новому замовленнi'),

                        Toggle::make('notify_low_stock')
                            ->label('Сповiщення про низький залишок')
                            ->helperText('Повiдомляти, коли товар закiнчується')
                            ->reactive(),

                        TextInput::make('low_stock_threshold')
                            ->label('Порiг низького залишку')
                            ->numeric()
                            ->default(5)
                            ->minValue(1)
                            ->maxValue(1000)
                            ->suffix('шт')
                            ->visible(fn (callable $get) => (bool) $get('notify_low_stock')),
                    ])
                    ->columns(2),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Зберегти')
                ->icon('heroicon-o-check')
                ->color('success')
                ->action('save'),

            Action::make('reindex')
                ->label('Переiндексувати товари')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Переiндексувати всi товари?')
                ->modalDescription('Це запустить повну переiндексацiю товарiв. Процес може зайняти деякий час.')
                ->action('reindexProducts'),

            Action::make('test_email')
                ->label('Тестовий лист')
                ->icon('heroicon-o-envelope')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Надiслати тестовий лист?')
                ->modalDescription('Тестовий лист буде надiслано на email адмiнiстратора магазину.')
                ->action(function () {
                    try {
                        $adminEmail = auth()->user()->email;
                        $user = auth()->user();

                        Mail::to($adminEmail)->send(new \App\Mail\WelcomeEmail($user, 'TEST10'));

                        Notification::make()
                            ->success()
                            ->title('Тестовий лист надiслано')
                            ->body("Лист надiслано на {$adminEmail}")
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->danger()
                            ->title('Помилка вiдправки')
                            ->body($e->getMessage())
                            ->send();
                    }
                }),

            Action::make('clear_cache')
                ->label('Очистити кеш')
                ->icon('heroicon-o-trash')
                ->color('gray')
                ->action(function () {
                    Cache::flush();
                    Artisan::call('cache:clear');
                    Artisan::call('view:clear');

                    Notification::make()
                        ->success()
                        ->title('Кеш очищено')
                        ->send();
                }),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $typeMap = $this->getTypeMap();
        $groupMap = $this->getGroupMap();

        foreach ($data as $key => $value) {
            $type = $typeMap[$key] ?? 'string';
            $group = $groupMap[$key] ?? 'general';

            $storeValue = match ($type) {
                'boolean' => $value ? 'true' : 'false',
                'array', 'json' => is_array($value) ? json_encode($value) : (string) $value,
                'integer' => (string) (int) $value,
                default => (string) ($value ?? ''),
            };

            DisplaySetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $storeValue,
                    'type' => $type,
                    'group' => $group,
                    'title' => $this->getSettingTitle($key),
                    'is_active' => true,
                ]
            );
        }

        // Flush relevant caches
        DisplaySetting::flushHeaderCache();
        Cache::forget('shop_settings');
        Cache::forget('seo_settings');

        Notification::make()
            ->success()
            ->title('Налаштування збережено')
            ->body('Всi змiни збережено в базi даних')
            ->send();
    }

    public function reindexProducts(): void
    {
        try {
            Artisan::call('scout:flush', ['model' => 'App\\Models\\Product']);
            Artisan::call('scout:import', ['model' => 'App\\Models\\Product']);

            Cache::put('search_last_sync', now()->format('d.m.Y H:i:s'));

            Notification::make()
                ->success()
                ->title('Переiндексацiю завершено')
                ->body('Всi товари переiндексовано')
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Помилка переiндексацiї')
                ->body($e->getMessage())
                ->send();
        }
    }

    protected function getTypeMap(): array
    {
        return [
            // General
            'shop_name' => 'string',
            'shop_phone' => 'string',
            'shop_email' => 'string',
            'shop_address' => 'string',
            'logo_type' => 'string',
            'logo_text' => 'string',
            'logo_image' => 'string',
            'favicon' => 'string',

            // SEO
            'seo_title_template' => 'string',
            'seo_default_description' => 'string',
            'google_analytics_id' => 'string',
            'google_tag_manager_id' => 'string',
            'facebook_pixel_id' => 'string',
            'robots_txt_content' => 'string',
            'sitemap_auto_generate' => 'boolean',

            // Search

            // Currencies
            'main_currency' => 'string',
            'available_currencies' => 'json',
            'auto_update_rates' => 'boolean',
            'nbu_api_enabled' => 'boolean',

            // Languages
            'default_language' => 'string',
            'available_languages' => 'json',
            'url_locale_prefix' => 'boolean',
            'transliteration_standard' => 'string',

            // Shipping
            'free_delivery_threshold' => 'integer',
            'delivery_novaposhta' => 'boolean',
            'delivery_ukrposhta' => 'boolean',
            'delivery_meest' => 'boolean',
            'delivery_justin' => 'boolean',
            'delivery_pickup' => 'boolean',
            'delivery_courier' => 'boolean',
            'payment_cash' => 'boolean',
            'payment_card' => 'boolean',
            'payment_liqpay' => 'boolean',
            'payment_wayforpay' => 'boolean',
            'payment_monobank' => 'boolean',
            'payment_privat24' => 'boolean',

            // Email
            'email_from_name' => 'string',
            'email_from_address' => 'string',
            'email_reply_to' => 'string',
            'email_show_logo' => 'boolean',
            'email_footer_text' => 'string',

            // Reviews
            'reviews_moderation_enabled' => 'boolean',
            'reviews_auto_approve' => 'boolean',
            'reviews_min_length' => 'integer',

            // Notifications
            'telegram_bot_token' => 'string',
            'telegram_chat_id' => 'string',
            'email_notifications' => 'boolean',
            'notify_new_order' => 'boolean',
            'notify_low_stock' => 'boolean',
            'low_stock_threshold' => 'integer',
        ];
    }

    protected function getGroupMap(): array
    {
        $groups = [
            'general' => [
                'shop_name', 'shop_phone', 'shop_email', 'shop_address',
                'logo_type', 'logo_text', 'logo_image', 'favicon',
            ],
            'seo' => [
                'seo_title_template', 'seo_default_description',
                'google_analytics_id', 'google_tag_manager_id', 'facebook_pixel_id',
                'robots_txt_content', 'sitemap_auto_generate',
            ],
            'search' => [
            ],
            'currencies' => [
                'main_currency', 'available_currencies', 'auto_update_rates', 'nbu_api_enabled',
            ],
            'languages' => [
                'default_language', 'available_languages', 'url_locale_prefix', 'transliteration_standard',
            ],
            'shipping_payment' => [
                'free_delivery_threshold',
                'delivery_novaposhta', 'delivery_ukrposhta', 'delivery_meest',
                'delivery_justin', 'delivery_pickup', 'delivery_courier',
                'payment_cash', 'payment_card', 'payment_liqpay',
                'payment_wayforpay', 'payment_monobank', 'payment_privat24',
            ],
            'email' => [
                'email_from_name', 'email_from_address', 'email_reply_to',
                'email_show_logo', 'email_footer_text',
            ],
            'reviews' => [
                'reviews_moderation_enabled', 'reviews_auto_approve', 'reviews_min_length',
            ],
            'notifications' => [
                'telegram_bot_token', 'telegram_chat_id',
                'email_notifications', 'notify_new_order', 'notify_low_stock', 'low_stock_threshold',
            ],
        ];

        $map = [];
        foreach ($groups as $group => $keys) {
            foreach ($keys as $key) {
                $map[$key] = $group;
            }
        }

        return $map;
    }

    protected function getSettingTitle(string $key): string
    {
        $titles = [
            'shop_name' => 'Назва магазину',
            'shop_phone' => 'Телефон магазину',
            'shop_email' => 'Email магазину',
            'shop_address' => 'Адреса магазину',
            'logo_type' => 'Тип логотипу',
            'logo_text' => 'Текст логотипу',
            'logo_image' => 'Зображення логотипу',
            'favicon' => 'Favicon',
            'seo_title_template' => 'Шаблон meta title',
            'seo_default_description' => 'Meta description за замовчуванням',
            'google_analytics_id' => 'Google Analytics ID',
            'google_tag_manager_id' => 'Google Tag Manager ID',
            'facebook_pixel_id' => 'Facebook Pixel ID',
            'robots_txt_content' => 'Вмiст robots.txt',
            'sitemap_auto_generate' => 'Автогенерацiя sitemap',
            'main_currency' => 'Основна валюта',
            'available_currencies' => 'Доступнi валюти',
            'auto_update_rates' => 'Авто-оновлення курсiв',
            'nbu_api_enabled' => 'НБУ API',
            'default_language' => 'Мова за замовчуванням',
            'available_languages' => 'Доступнi мови',
            'url_locale_prefix' => 'Префiкс мови в URL',
            'transliteration_standard' => 'Стандарт транслiтерацiї',
            'free_delivery_threshold' => 'Безкоштовна доставка вiд',
            'delivery_novaposhta' => 'Нова Пошта',
            'delivery_ukrposhta' => 'Укрпошта',
            'delivery_meest' => 'Meest Express',
            'delivery_justin' => 'Justin',
            'delivery_pickup' => 'Самовивiз',
            'delivery_courier' => "Кур'єрська доставка",
            'payment_cash' => 'Готiвка',
            'payment_card' => 'Картка',
            'payment_liqpay' => 'LiqPay',
            'payment_wayforpay' => 'WayForPay',
            'payment_monobank' => 'Monobank',
            'payment_privat24' => 'Приват24',
            'email_from_name' => 'Iм\'я вiдправника email',
            'email_from_address' => 'Email вiдправника',
            'email_reply_to' => 'Reply-To адреса',
            'email_show_logo' => 'Логотип в листах',
            'email_footer_text' => 'Текст футера email',
            'reviews_moderation_enabled' => 'Модерація відгуків',
            'reviews_auto_approve' => 'Автосхвалення відгуків',
            'reviews_min_length' => 'Мін. довжина відгуку',
            'telegram_bot_token' => 'Telegram Bot Token',
            'telegram_chat_id' => 'Telegram Chat ID',
            'email_notifications' => 'Email-сповiщення',
            'notify_new_order' => 'Нове замовлення',
            'notify_low_stock' => 'Низький залишок',
            'low_stock_threshold' => 'Порiг низького залишку',
        ];

        return $titles[$key] ?? ucfirst(str_replace(['_', '-'], ' ', $key));
    }
}
