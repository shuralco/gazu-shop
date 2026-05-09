<?php

namespace App\Filament\Resources\ShopSettingsResource\Pages;

use App\Filament\Resources\ShopSettingsResource;
use App\Models\ShopSettings;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Cache;

class ManageShopSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = ShopSettingsResource::class;

    protected static ?string $title = 'Налаштування магазину';

    protected static ?string $breadcrumb = 'Налаштування';

    protected static string $view = 'filament.pages.manage-shop-settings';

    public ?array $data = [];

    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->statePath('data')
            ),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Settings')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('🏪 Загальні налаштування')
                            ->icon('heroicon-o-building-storefront')
                            ->schema([
                                Forms\Components\Section::make('🏢 Інформація про магазин')
                                    ->description('Основна інформація про ваш інтернет-магазин')
                                    ->schema([
                                        Forms\Components\TextInput::make('shop_name')
                                            ->label('📝 Назва магазину')
                                            ->helperText('Назва вашого магазину, що відображається на сайті')
                                            ->placeholder('SimpleShop')
                                            ->required(),

                                        Forms\Components\Textarea::make('shop_description')
                                            ->label('📄 Опис магазину')
                                            ->helperText('Короткий опис діяльності магазину для SEO та клієнтів')
                                            ->placeholder('Сучасний інтернет-магазин з широким асортиментом товарів')
                                            ->rows(3),

                                        Forms\Components\TextInput::make('shop_email')
                                            ->label('📧 Email магазину')
                                            ->helperText('Основний email для зв\'язку з клієнтами')
                                            ->email()
                                            ->placeholder('info@simpleshop.com')
                                            ->required(),

                                        Forms\Components\TextInput::make('shop_phone')
                                            ->label('📞 Телефон магазину')
                                            ->helperText('Контактний телефон для клієнтів')
                                            ->tel()
                                            ->placeholder('+380123456789'),

                                        Forms\Components\Textarea::make('shop_address')
                                            ->label('📍 Адреса магазину')
                                            ->helperText('Фізична адреса вашого магазину')
                                            ->placeholder('вул. Хрещатик, 1, Київ, Україна')
                                            ->rows(2),

                                        Forms\Components\FileUpload::make('shop_logo')
                                            ->label('🖼️ Логотип магазину')
                                            ->helperText('Логотип у форматі PNG/JPG, максимум 2МБ')
                                            ->image()
                                            ->maxSize(2048)
                                            ->acceptedFileTypes(['image/png', 'image/jpeg']),
                                    ])
                                    ->columns(2)
                                    ->collapsible(),

                                Forms\Components\Section::make('🔍 SEO налаштування')
                                    ->description('Налаштування для пошукової оптимізації')
                                    ->schema([
                                        Forms\Components\TextInput::make('meta_title')
                                            ->label('📑 Meta заголовок')
                                            ->helperText('Заголовок сторінки для пошукових систем (до 60 символів)')
                                            ->maxLength(60)
                                            ->placeholder('SimpleShop - Інтернет-магазин'),

                                        Forms\Components\Textarea::make('meta_description')
                                            ->label('📝 Meta опис')
                                            ->helperText('Опис сайту для пошукових систем (до 160 символів)')
                                            ->maxLength(160)
                                            ->placeholder('Купуйте якісні товари в нашому інтернет-магазині')
                                            ->rows(2),

                                        Forms\Components\TagsInput::make('meta_keywords')
                                            ->label('🏷️ Ключові слова')
                                            ->helperText('Ключові слова для пошукової оптимізації')
                                            ->placeholder('інтернет-магазин, товари, доставка'),
                                    ])
                                    ->columns(1)
                                    ->collapsible(),
                            ]),

                        Forms\Components\Tabs\Tab::make('🛒 Замовлення та покупки')
                            ->icon('heroicon-o-shopping-cart')
                            ->schema([
                                Forms\Components\Section::make('⚙️ Обробка замовлень')
                                    ->description('Налаштування процесу оформлення та обробки замовлень')
                                    ->schema([
                                        Forms\Components\Toggle::make('auto_order_confirmation')
                                            ->label('✅ Автоматичне підтвердження замовлень')
                                            ->helperText('Замовлення будуть автоматично підтверджуватись без участі адміністратора'),

                                        Forms\Components\Select::make('default_order_status')
                                            ->label('📊 Статус нових замовлень')
                                            ->helperText('Початковий статус для всіх нових замовлень')
                                            ->options([
                                                'pending' => '⏳ Очікує обробки',
                                                'confirmed' => '✅ Підтверджено',
                                                'processing' => '🔄 Обробляється',
                                                'shipped' => '📦 Відправлено',
                                            ])
                                            ->default('pending'),

                                        Forms\Components\TextInput::make('min_order_amount')
                                            ->label('💰 Мінімальна сума замовлення')
                                            ->helperText('Найменша сума для оформлення замовлення')
                                            ->numeric()
                                            ->step(0.01)
                                            ->suffix('грн')
                                            ->minValue(0),

                                        Forms\Components\TextInput::make('free_shipping_threshold')
                                            ->label('🚚 Безкоштовна доставка від суми')
                                            ->helperText('Сума замовлення для безкоштовної доставки')
                                            ->numeric()
                                            ->step(0.01)
                                            ->suffix('грн')
                                            ->minValue(0),
                                    ])
                                    ->columns(2)
                                    ->collapsible(),

                                Forms\Components\Section::make('📧 Email повідомлення')
                                    ->description('Налаштування автоматичних email розсилок')
                                    ->schema([
                                        Forms\Components\Toggle::make('send_order_emails')
                                            ->label('📬 Надсилати email при замовленні')
                                            ->helperText('Клієнт отримає підтвердження замовлення на email'),

                                        Forms\Components\Toggle::make('send_payment_emails')
                                            ->label('💳 Надсилати email при оплаті')
                                            ->helperText('Клієнт отримає чек після успішної оплати'),

                                        Forms\Components\TextInput::make('admin_notification_email')
                                            ->label('👨‍💼 Email адміністратора')
                                            ->helperText('Email для отримання сповіщень про нові замовлення')
                                            ->email()
                                            ->placeholder('admin@simpleshop.com'),
                                    ])
                                    ->columns(2)
                                    ->collapsible(),
                            ]),

                        Forms\Components\Tabs\Tab::make('💳 Способи оплати')
                            ->icon('heroicon-o-credit-card')
                            ->schema([
                                Forms\Components\Section::make('💰 Загальні налаштування оплати')
                                    ->description('Основні параметри платіжної системи')
                                    ->schema([
                                        Forms\Components\Select::make('default_currency')
                                            ->label('💱 Основна валюта')
                                            ->helperText('Валюта для відображення цін на сайті')
                                            ->options([
                                                'UAH' => '🇺🇦 Українська гривня (UAH)',
                                                'USD' => '🇺🇸 Долар США (USD)',
                                                'EUR' => '🇪🇺 Євро (EUR)',
                                            ])
                                            ->required(),

                                        Forms\Components\Toggle::make('allow_cash_payment')
                                            ->label('💵 Оплата готівкою при отриманні')
                                            ->helperText('Дозволити клієнтам платити готівкою кур\'єру'),

                                        Forms\Components\Toggle::make('require_phone_for_order')
                                            ->label('📱 Обов\'язковий номер телефону')
                                            ->helperText('Вимагати номер телефону при оформленні замовлення'),

                                        Forms\Components\TextInput::make('payment_timeout_minutes')
                                            ->label('⏰ Час на оплату (хвилини)')
                                            ->helperText('Скільки хвилин клієнт має на оплату до скасування')
                                            ->numeric()
                                            ->minValue(5)
                                            ->maxValue(1440)
                                            ->suffix('хв'),
                                    ])
                                    ->columns(2)
                                    ->collapsible(),

                                Forms\Components\Section::make('🏦 Платіжні системи')
                                    ->description('Налаштування підключених платіжних шлюзів')
                                    ->schema([
                                        Forms\Components\Placeholder::make('payment_gateways_info')
                                            ->label('')
                                            ->content('Для налаштування платіжних систем (LiqPay, WayForPay, Monobank) перейдіть до розділу "Методи оплати"'),
                                    ])
                                    ->collapsible(),
                            ]),

                        Forms\Components\Tabs\Tab::make('🚚 Доставка та логістика')
                            ->icon('heroicon-o-truck')
                            ->schema([
                                Forms\Components\Section::make('📦 Налаштування доставки')
                                    ->description('Параметри розрахунку та обробки доставки')
                                    ->schema([
                                        Forms\Components\TextInput::make('default_shipping_cost')
                                            ->label('💸 Базова вартість доставки')
                                            ->helperText('Стандартна вартість доставки, якщо не вдається розрахувати автоматично')
                                            ->numeric()
                                            ->step(0.01)
                                            ->suffix('грн')
                                            ->minValue(0),

                                        Forms\Components\Toggle::make('calculate_shipping_automatically')
                                            ->label('🤖 Автоматичний розрахунок доставки')
                                            ->helperText('Використовувати API служб доставки для точного розрахунку вартості'),

                                        Forms\Components\Select::make('default_shipping_provider')
                                            ->label('🚛 Основна служба доставки')
                                            ->helperText('Служба доставки, що пропонується клієнтам за замовчуванням')
                                            ->options([
                                                'novaposhta' => '📮 Нова Пошта',
                                                'ukrposhta' => '🏤 УкрПошта',
                                                'rozetka' => '🟢 Rozetka Delivery',
                                                'meest' => '📦 Meest Express',
                                                'justin' => '⚡ Justin',
                                            ])
                                            ->default('novaposhta'),

                                        Forms\Components\Toggle::make('enable_pickup_points')
                                            ->label('📍 Пункти самовивозу')
                                            ->helperText('Дозволити клієнтам забирати замовлення з пунктів видачі'),
                                    ])
                                    ->columns(2)
                                    ->collapsible(),
                            ]),

                        Forms\Components\Tabs\Tab::make('🔐 Безпека та захист')
                            ->icon('heroicon-o-shield-check')
                            ->schema([
                                Forms\Components\Section::make('🛡️ Захист від зловмисників')
                                    ->description('Налаштування безпеки для захисту магазину')
                                    ->schema([
                                        Forms\Components\Toggle::make('enable_captcha')
                                            ->label('🤖 Увімкнути CAPTCHA')
                                            ->helperText('Захист форм від спаму та ботів'),

                                        Forms\Components\Toggle::make('webhook_ip_whitelist')
                                            ->label('🌐 Перевіряти IP webhook\'ів')
                                            ->helperText('Приймати webhook тільки з офіційних IP адрес платіжних систем'),

                                        Forms\Components\TextInput::make('session_lifetime')
                                            ->label('⏱️ Час життя сесії')
                                            ->helperText('Через скільки хвилин неактивності користувач буде розлогінений')
                                            ->numeric()
                                            ->minValue(30)
                                            ->maxValue(1440)
                                            ->suffix('хв'),

                                        Forms\Components\Toggle::make('enable_admin_2fa')
                                            ->label('🔑 Двофакторна автентифікація')
                                            ->helperText('Додатковий захист для входу в адмін-панель'),

                                        Forms\Components\TextInput::make('max_login_attempts')
                                            ->label('🚫 Максимум спроб входу')
                                            ->helperText('Кількість невдалих спроб входу до блокування IP')
                                            ->numeric()
                                            ->minValue(3)
                                            ->maxValue(20),

                                        Forms\Components\Toggle::make('enable_rate_limiting')
                                            ->label('⏳ Обмеження частоти запитів')
                                            ->helperText('Захист від DDoS атак та надмірного навантаження'),
                                    ])
                                    ->columns(2)
                                    ->collapsible(),
                            ]),

                        Forms\Components\Tabs\Tab::make('📊 Аналітика та звіти')
                            ->icon('heroicon-o-chart-bar')
                            ->schema([
                                Forms\Components\Section::make('📈 Відстеження та аналітика')
                                    ->description('Налаштування збору статистики та аналітики')
                                    ->schema([
                                        Forms\Components\Toggle::make('enable_analytics')
                                            ->label('📊 Увімкнути збір аналітики')
                                            ->helperText('Збирати статистику відвідувань та конверсій'),

                                        Forms\Components\TextInput::make('google_analytics_id')
                                            ->label('🔍 Google Analytics ID')
                                            ->helperText('ID для Google Analytics (GA4 або Universal)')
                                            ->placeholder('G-XXXXXXXXXX або UA-XXXXXXX-X'),

                                        Forms\Components\TextInput::make('facebook_pixel_id')
                                            ->label('📘 Facebook Pixel ID')
                                            ->helperText('ID пікселя Facebook для відстеження конверсій')
                                            ->placeholder('1234567890123456'),

                                        Forms\Components\Toggle::make('enable_order_tracking')
                                            ->label('🔍 Відстеження замовлень')
                                            ->helperText('Дозволити клієнтам відстежувати статус замовлення'),
                                    ])
                                    ->columns(2)
                                    ->collapsible(),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString(),
            ]);
    }

    public function mount(): void
    {
        // Очистити кеш
        Cache::forget('shop_settings');

        // Завантажити всі налаштування з бази
        $settings = ShopSettings::all()->pluck('value', 'key')->toArray();

        // Конвертувати boolean значення
        foreach ($settings as $key => $value) {
            $setting = ShopSettings::where('key', $key)->first();
            if ($setting && $setting->type === 'boolean') {
                $settings[$key] = (bool) $value;
            } elseif ($setting && $setting->type === 'json') {
                $settings[$key] = json_decode($value, true);
            } elseif ($setting && $setting->type === 'float') {
                $settings[$key] = (float) $value;
            } elseif ($setting && $setting->type === 'integer') {
                $settings[$key] = (int) $value;
            }
        }

        // Додати default значення для полів які не збережені в БД
        $defaults = [
            'shop_name' => 'SimpleShop',
            'shop_email' => 'admin@simpleshop.com',
            'default_currency' => 'UAH',
            'default_order_status' => 'pending',
            'min_order_amount' => 50,
            'free_shipping_threshold' => 1000,
            'allow_cash_payment' => true,
            'require_phone_for_order' => true,
            'payment_timeout_minutes' => 60,
            'default_shipping_cost' => 50,
            'calculate_shipping_automatically' => true,
            'default_shipping_provider' => 'novaposhta',
            'enable_pickup_points' => true,
            'send_order_emails' => true,
            'send_payment_emails' => true,
        ];

        // Об'єднати з default значеннями
        $this->data = array_merge($defaults, $settings);

        $this->form->fill($this->data);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('reset_to_defaults')
                ->label('🔄 Скинути до значень за замовчуванням')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Скинути всі налаштування?')
                ->modalDescription('Це поверне всі налаштування до початкових значень. Дія незворотна.')
                ->action(function () {
                    // Запустити seeder знову
                    \Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\ShopSettingsSeeder']);

                    // Очистити кеш
                    Cache::forget('shop_settings');

                    Notification::make()
                        ->success()
                        ->title('Налаштування скинуто')
                        ->body('Всі налаштування повернуто до значень за замовчуванням')
                        ->send();

                    // Перезавантажити форму
                    $this->mount();
                }),

            Actions\Action::make('clear_cache')
                ->label('🗑️ Очистити кеш')
                ->icon('heroicon-o-trash')
                ->color('gray')
                ->action(function () {
                    Cache::forget('shop_settings');
                    \Artisan::call('cache:clear');

                    Notification::make()
                        ->success()
                        ->title('Кеш очищено')
                        ->body('Кеш налаштувань успішно очищено')
                        ->send();
                }),
        ];
    }

    public function save(): void
    {
        $this->data = $this->form->getState();

        foreach ($this->data as $key => $value) {
            $setting = ShopSettings::where('key', $key)->first();

            if ($setting) {
                // Конвертувати значення відповідно до типу
                if ($setting->type === 'boolean') {
                    $value = $value ? '1' : '0';
                } elseif ($setting->type === 'json') {
                    $value = json_encode($value);
                }

                $setting->update(['value' => $value]);
            } else {
                // Створити нове налаштування якщо не існує
                ShopSettings::create([
                    'key' => $key,
                    'value' => is_bool($value) ? ($value ? '1' : '0') : $value,
                    'type' => is_bool($value) ? 'boolean' : (is_numeric($value) ? 'float' : 'string'),
                    'group' => 'general',
                    'description' => "Налаштування {$key}",
                ]);
            }
        }

        // Очистити кеш
        Cache::forget('shop_settings');

        Notification::make()
            ->success()
            ->title('Налаштування збережено')
            ->body('Всі зміни успішно збережено в базі даних')
            ->send();
    }
}
