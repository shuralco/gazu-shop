<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShopSettingsResource\Pages;
use App\Models\ShopSettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ShopSettingsResource extends Resource
{
    protected static ?string $model = ShopSettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-8-tooth';

    protected static ?string $navigationGroup = 'Система';

    protected static ?string $navigationLabel = 'Налаштування магазину';

    protected static ?string $pluralModelLabel = 'Налаштування магазину';

    protected static ?string $modelLabel = 'Налаштування';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
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
                                            ->default('SimpleShop')
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
                                            ->helperText('Замовлення будуть автоматично підтверджуватись без участі адміністратора')
                                            ->default(false),

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
                                            ->default(50)
                                            ->minValue(0),

                                        Forms\Components\TextInput::make('free_shipping_threshold')
                                            ->label('🚚 Безкоштовна доставка від суми')
                                            ->helperText('Сума замовлення для безкоштовної доставки')
                                            ->numeric()
                                            ->step(0.01)
                                            ->suffix('грн')
                                            ->default(1000)
                                            ->minValue(0),
                                    ])
                                    ->columns(2)
                                    ->collapsible(),

                                Forms\Components\Section::make('📧 Email повідомлення')
                                    ->description('Налаштування автоматичних email розсилок')
                                    ->schema([
                                        Forms\Components\Toggle::make('send_order_emails')
                                            ->label('📬 Надсилати email при замовленні')
                                            ->helperText('Клієнт отримає підтвердження замовлення на email')
                                            ->default(true),

                                        Forms\Components\Toggle::make('send_payment_emails')
                                            ->label('💳 Надсилати email при оплаті')
                                            ->helperText('Клієнт отримає чек після успішної оплати')
                                            ->default(true),

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
                                            ->default('UAH')
                                            ->required(),

                                        Forms\Components\Toggle::make('allow_cash_payment')
                                            ->label('💵 Оплата готівкою при отриманні')
                                            ->helperText('Дозволити клієнтам платити готівкою кур\'єру')
                                            ->default(true),

                                        Forms\Components\Toggle::make('require_phone_for_order')
                                            ->label('📱 Обов\'язковий номер телефону')
                                            ->helperText('Вимагати номер телефону при оформленні замовлення')
                                            ->default(true),

                                        Forms\Components\TextInput::make('payment_timeout_minutes')
                                            ->label('⏰ Час на оплату (хвилини)')
                                            ->helperText('Скільки хвилин клієнт має на оплату до скасування')
                                            ->numeric()
                                            ->default(60)
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
                                            ->default(50)
                                            ->minValue(0),

                                        Forms\Components\Toggle::make('calculate_shipping_automatically')
                                            ->label('🤖 Автоматичний розрахунок доставки')
                                            ->helperText('Використовувати API служб доставки для точного розрахунку вартості')
                                            ->default(true),

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
                                            ->helperText('Дозволити клієнтам забирати замовлення з пунктів видачі')
                                            ->default(true),
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
                                            ->helperText('Захист форм від спаму та ботів')
                                            ->default(false),

                                        Forms\Components\Toggle::make('webhook_ip_whitelist')
                                            ->label('🌐 Перевіряти IP webhook\'ів')
                                            ->helperText('Приймати webhook тільки з офіційних IP адрес платіжних систем')
                                            ->default(true),

                                        Forms\Components\TextInput::make('session_lifetime')
                                            ->label('⏱️ Час життя сесії')
                                            ->helperText('Через скільки хвилин неактивності користувач буде розлогінений')
                                            ->numeric()
                                            ->default(120)
                                            ->minValue(30)
                                            ->maxValue(1440)
                                            ->suffix('хв'),

                                        Forms\Components\Toggle::make('enable_admin_2fa')
                                            ->label('🔑 Двофакторна автентифікація')
                                            ->helperText('Додатковий захист для входу в адмін-панель')
                                            ->default(false),

                                        Forms\Components\TextInput::make('max_login_attempts')
                                            ->label('🚫 Максимум спроб входу')
                                            ->helperText('Кількість невдалих спроб входу до блокування IP')
                                            ->numeric()
                                            ->default(5)
                                            ->minValue(3)
                                            ->maxValue(20),

                                        Forms\Components\Toggle::make('enable_rate_limiting')
                                            ->label('⏳ Обмеження частоти запитів')
                                            ->helperText('Захист від DDoS атак та надмірного навантаження')
                                            ->default(true),
                                    ])
                                    ->columns(2)
                                    ->collapsible(),
                            ]),

                        Forms\Components\Tabs\Tab::make('📦 Товари')
                            ->icon('heroicon-o-cube')
                            ->schema([
                                Forms\Components\Section::make('🛍️ Відображення товарів')
                                    ->description('Налаштування показу товарів на сторінці')
                                    ->schema([
                                        Forms\Components\Toggle::make('show_product_excerpt')
                                            ->label('📝 Показувати короткий опис товару')
                                            ->helperText('Відображати короткий опис товару на сторінці продукту')
                                            ->default(true),

                                        Forms\Components\Toggle::make('show_product_sku')
                                            ->label('🏷️ Показувати артикул товару')
                                            ->helperText('Відображати SKU товару на сторінці продукту')
                                            ->default(true),

                                        Forms\Components\Toggle::make('show_product_brand')
                                            ->label('🏢 Показувати бренд товару')
                                            ->helperText('Відображати бренд товару на сторінці продукту')
                                            ->default(true),

                                        Forms\Components\Select::make('products_per_page')
                                            ->label('📄 Кількість товарів на сторінці')
                                            ->helperText('Скільки товарів показувати на одній сторінці категорії')
                                            ->options([
                                                '12' => '12 товарів',
                                                '24' => '24 товари',
                                                '36' => '36 товарів',
                                                '48' => '48 товарів',
                                            ])
                                            ->default('24'),
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
                                            ->helperText('Збирати статистику відвідувань та конверсій')
                                            ->default(true),

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
                                            ->helperText('Дозволити клієнтам відстежувати статус замовлення')
                                            ->default(true),
                                    ])
                                    ->columns(2)
                                    ->collapsible(),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->label('Ключ')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('value')
                    ->label('Значення')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Тип')
                    ->badge(),

                Tables\Columns\TextColumn::make('group')
                    ->label('Група')
                    ->badge()
                    ->color('info'),

                Tables\Columns\IconColumn::make('is_public')
                    ->label('Публічне')
                    ->boolean(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Оновлено')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('group')
                    ->label('Група')
                    ->options([
                        'general' => 'Загальні',
                        'payment' => 'Оплата',
                        'shipping' => 'Доставка',
                        'security' => 'Безпека',
                        'email' => 'Email',
                    ]),

                Tables\Filters\SelectFilter::make('type')
                    ->label('Тип')
                    ->options([
                        'string' => 'Рядок',
                        'boolean' => 'Булеве',
                        'integer' => 'Число',
                        'float' => 'Десяткове',
                        'json' => 'JSON',
                    ]),
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
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Налаштування не видаляються
                ]),
            ])
            ->defaultSort('group')
            ->groupedBulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('export_settings')
                        ->label('Експорт налаштувань')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function ($records) {
                            $settings = $records->mapWithKeys(function ($record) {
                                return [$record->key => $record->value];
                            });

                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('Налаштування експортовані')
                                ->body('Скопіюйте JSON: '.json_encode($settings))
                                ->send();
                        }),
                    Tables\Actions\BulkAction::make('clear_cache')
                        ->label('🧹 Очистити ВСЬ кеш')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Очистити весь кеш сайту?')
                        ->modalDescription('Це очистить усі кеші: Laravel, views, config, routes та інше.')
                        ->action(function () {
                            \Artisan::call('cache:clear');
                            \Artisan::call('view:clear');
                            \Artisan::call('config:clear');
                            \Artisan::call('route:clear');
                            \Artisan::call('optimize:clear');

                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('🧹 Кеш очищено!')
                                ->body('Усі кеші Laravel очищено успішно')
                                ->send();
                        }),
                ])
                    ->label('Групові дії'),
            ]);
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
            'index' => Pages\ManageShopSettings::route('/'),
        ];
    }
}
