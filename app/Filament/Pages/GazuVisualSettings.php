<?php

namespace App\Filament\Pages;

use App\Models\DisplaySetting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Адмін-сторінка візуальних налаштувань GAZU storefront.
 * Редагує текстові/масивні блоки: top-bar, footer, trust-strip, hero CTA.
 * Зберігає у DisplaySetting (single source of truth).
 */
class GazuVisualSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-paint-brush';

    protected static ?string $navigationLabel = 'GAZU візуальні блоки';

    protected static ?string $navigationGroup = 'Налаштування';

    protected static ?string $title = 'GAZU — візуальні налаштування storefront';

    protected static ?int $navigationSort = 49;

    protected static ?string $slug = 'gazu-visual';

    protected static string $view = 'filament.pages.gazu-visual-settings';

    public ?array $data = [];

    public static array $defaults = [
        // Hero V2 (Car-picker, темний)
        'gazu_hero_v2_kicker' => 'Підбір за вашим авто',
        'gazu_hero_v2_title' => "Запчастини, які\nточно підійдуть.",
        'gazu_hero_v2_description' => 'Оберіть марку, модель та рік випуску — побачите тільки сумісні деталі. Без помилок і повернень.',
        'gazu_hero_v2_brands' => ['VW', 'Audi', 'BMW', 'Skoda', 'Toyota', 'Renault', 'Ford', 'Hyundai'],
        'gazu_hero_v2_brands_total' => 240,
        'gazu_hero_v2_vin_hint' => 'Або введіть VIN-код для миттєвого підбору',

        // Hero V3 (Split — для майстрів + водіїв)
        'gazu_hero_v3_left_kicker' => 'Для майстрів СТО',
        'gazu_hero_v3_left_title' => "Швидкий пошук\nза OEM-кодом",
        'gazu_hero_v3_left_description' => null, // composed dynamically from shopStats.products_label
        'gazu_hero_v3_left_perks' => ['VIN-декодер', 'Пакетний пошук', 'Гуртові ціни'],
        'gazu_hero_v3_right_kicker' => 'Для водіїв',
        'gazu_hero_v3_right_title' => "Підбір за вашим\nавто",
        'gazu_hero_v3_right_description' => 'Марка, модель, рік — і ви побачите тільки сумісні запчастини.',

        // Mobile
        'gazu_mobile_hero_kicker' => null, // composed from shopStats.products_label
        'gazu_mobile_hero_title_html' => 'Знайди деталь за <span style="color:var(--gazu-blue)">OEM</span>',
        'gazu_mobile_categories_title' => 'Категорії',
        'gazu_mobile_hits_title' => 'Хіти',
        'gazu_mobile_filter_pills' => ['Усі', 'Bosch', 'Mahle', 'Mann', 'TRW', 'KYB'],

        // Featured rows titles
        'gazu_section_specials' => 'Акції тижня',
        'gazu_section_hits' => 'Хіти продажів',
        'gazu_section_categories' => 'Каталог за категоріями',
        'gazu_section_brands' => 'Топ-бренди',
        'gazu_section_related' => 'Часто купують разом',

        // VIN block (на home V1, V3)
        'gazu_vin_label' => 'VIN-декодер',
        'gazu_vin_title' => 'Точний підбір за VIN-кодом авто.',
        'gazu_vin_description' => 'Введіть 17-значний код кузова — система визначить марку, модель, рік, двигун і покаже сумісні запчастини з оригінальних каталогів.',
        'gazu_vin_demo_code' => 'WVWZZZ3CZJE',
        'gazu_vin_demo_make' => 'Volkswagen',
        'gazu_vin_demo_model' => 'Passat B8',
        'gazu_vin_demo_year' => '2018',
        'gazu_vin_demo_engine' => '2.0 TDI · CKFC',

        // VIN page steps
        'gazu_vin_steps' => [
            ['num' => '1', 'title' => 'Знайдіть VIN', 'desc' => 'У техпаспорті, на лобовому склі або у дверному отворі водія'],
            ['num' => '2', 'title' => 'Введіть код', 'desc' => 'Система перевірить його за каталогами 240+ виробників'],
            ['num' => '3', 'title' => 'Отримайте список', 'desc' => 'Тільки сумісні запчастини, без помилок підбору'],
        ],

        // Auth bonuses
        'gazu_auth_bonuses' => [
            'Бонусна програма — кешбек 3% на замовлення',
            'Збережені адреси та швидке оформлення',
            'Історія замовлень + сервіс-нагадування',
        ],

        // STO services
        'gazu_sto_intro_title' => 'СТО та послуги',
        'gazu_sto_intro_desc' => 'Ми не лише продаємо запчастини — у нас власна мережа партнерських СТО з гарантією на роботи та фіксованими цінами.',
        'gazu_sto_services' => [
            ['icon' => 'wrench', 'title' => 'Заміна оливи', 'price' => '850 ₴', 'desc' => 'Будь-яка олива, фільтр у комплекті'],
            ['icon' => 'shield', 'title' => 'Діагностика', 'price' => '450 ₴', 'desc' => 'OBD-діагностика 50 параметрів'],
            ['icon' => 'truck', 'title' => 'Шиномонтаж', 'price' => '600 ₴', 'desc' => 'Балансування + сезонне зберігання'],
            ['icon' => 'box', 'title' => 'Гальмівна система', 'price' => 'від 1 200 ₴', 'desc' => 'Колодки, диски, рідина'],
            ['icon' => 'car', 'title' => 'Підвіска', 'price' => 'від 2 500 ₴', 'desc' => 'Заміна амортизаторів та сайлентблоків'],
            ['icon' => 'edit', 'title' => 'Кузовні роботи', 'price' => 'індивідуально', 'desc' => 'Малярка, рихтування, антикор'],
        ],
        'gazu_sto_partners' => [
            ['name' => 'Автосервіс на Хрещатику', 'addr' => 'Київ, вул. Хрещатик 22', 'rating' => '5.0 (124)'],
            ['name' => 'СТО ГазуПрофі', 'addr' => 'Львів, вул. Городоцька 134', 'rating' => '4.9 (87)'],
            ['name' => 'МотоМайстер', 'addr' => 'Дніпро, пр. Поля 18', 'rating' => '4.8 (54)'],
            ['name' => 'АвтоТехЦентр', 'addr' => 'Одеса, вул. Грушевського 17', 'rating' => '4.9 (96)'],
        ],

        // Contacts
        'gazu_contacts_email' => 'support@gazu.ua',
        'gazu_contacts_telegram' => '@gazu_support',
        'gazu_contacts_viber' => '+380 67 123 45 67',
        'gazu_contacts_offices' => [
            ['city' => 'Київ', 'addr' => 'вул. Хрещатик, 22'],
            ['city' => 'Львів', 'addr' => 'вул. Городоцька, 134'],
            ['city' => 'Дніпро', 'addr' => 'пр. Поля, 18'],
            ['city' => 'Одеса', 'addr' => 'вул. Грушевського, 17'],
            ['city' => 'Харків', 'addr' => 'вул. Сумська, 56'],
            ['city' => 'Запоріжжя', 'addr' => 'пр. Соборний, 91'],
        ],

        // 404 / cart empty
        'gazu_404_title' => 'Запчастину не знайдено',
        'gazu_404_desc' => 'Можливо, сторінку перенесли або URL застарів. Спробуйте знайти потрібну деталь через каталог чи VIN-пошук.',
        'gazu_cart_empty_title' => 'Кошик порожній',
        'gazu_cart_empty_desc' => 'Додайте товари з каталогу або скористайтесь VIN-пошуком, щоб знайти точні запчастини для свого авто.',

        // Top bar
        'gazu_topbar_cities' => null, // composed from shopStats.cities_with_count
        'gazu_topbar_hours' => 'Пн-Нд 8:00–20:00',
        'gazu_topbar_links' => [
            ['label' => 'Гуртом', 'url' => '#'],
            ['label' => 'Доставка та оплата', 'url' => '#'],
            ['label' => 'Гарантія', 'url' => '#'],
            ['label' => 'Контакти', 'url' => '/contacts'],
        ],

        // Header
        'gazu_phone' => '0 800 75 10 24',
        'gazu_phone_subtitle' => 'безкоштовно по Україні',
        'gazu_total_sku' => 50000,

        // Trust strip (4 пункти)
        'gazu_trust_items' => [
            ['icon' => 'truck',  'title' => 'Доставка по Україні',  'desc' => '1–3 дні · Нова Пошта · Укрпошта'],
            ['icon' => 'shield', 'title' => 'Гарантія на запчастини', 'desc' => 'Від 6 до 24 місяців'],
            ['icon' => 'return', 'title' => 'Повернення',            'desc' => '14 днів без пояснення причин'],
            ['icon' => 'wrench', 'title' => 'Допомога з підбором',   'desc' => 'Менеджер передзвонить за 5 хв'],
        ],

        // Hero
        'gazu_hero_subtitle' => null, // composed from shopStats.products_label
        'gazu_hero_title_1' => 'Знайди потрібну деталь',
        'gazu_hero_title_2_html' => 'за <span style="color:var(--gazu-blue)">OEM-кодом</span> або <span style="color:var(--gazu-blue)">VIN</span>.',
        'gazu_hero_description' => null, // composed from shopStats.warehouses_label

        // Hero V1 — visual картка справа (демо-товар у hero)
        'gazu_hero_visual_oem_code' => 'OEM 8V0·498·625·A',
        'gazu_hero_visual_image_kind' => 'bearing',
        'gazu_hero_visual_title' => 'Підшипник маточини передньої FAG',
        'gazu_hero_visual_subtitle' => '713 6107 70',
        'gazu_hero_visual_price' => '1 620 ₴',

        // Categories tile accent colors (в порядку появи)
        'gazu_category_accents' => [
            'var(--gazu-blue)',
            'var(--gazu-danger)',
            'var(--gazu-steel)',
            'var(--gazu-warn)',
            'var(--gazu-azure)',
            'var(--gazu-success)',
            'var(--gazu-graphite)',
            'var(--gazu-blue)',
        ],

        // Бренд-картка fallback опис (коли в БД нема)
        'gazu_brand_fallback_description' => 'Один з виробників, представлених у каталозі GAZU. Перейдіть до повного списку товарів цієї марки нижче.',

        // One-click buy
        'gazu_oneclick_enabled' => true,
        'gazu_oneclick_label' => 'Купити в один клік',
        'gazu_oneclick_message' => 'Менеджер передзвонить за 5 хвилин для уточнення доставки',

        // Product page — tab "Доставка та оплата"
        'gazu_product_delivery_text' => 'Нова Пошта по Україні · Доставка наступного дня для замовлень до 16:00 · Безкоштовно від 1500 ₴.',
        'gazu_product_payment_text'  => 'Visa / Mastercard, Apple Pay, Google Pay, готівка при отриманні (накладений платіж), безпечна оплата через LiqPay.',

        // Footer
        'gazu_footer_about' => null, // composed from shopStats.products_label
        'gazu_footer_columns' => [
            ['title' => 'Каталог', 'items' => ['Двигун', 'Гальмівна система', 'Підвіска', 'Електрика', 'Кузов', 'Салон']],
            ['title' => 'Клієнтам', 'items' => ['Доставка та оплата', 'Гарантія та повернення', 'Питання та відповіді', 'Бонусна програма', 'Гуртовим клієнтам']],
            ['title' => 'Компанія', 'items' => ['Про нас', 'Контакти', 'Вакансії', 'Сертифікати', 'Публічна оферта']],
        ],
        'gazu_footer_payments' => 'Visa, Mastercard, Apple Pay, Google Pay, Нова Пошта',
        'gazu_social_facebook' => '#',
        'gazu_social_instagram' => '#',
        'gazu_social_telegram' => '#',
        'gazu_social_youtube' => '#',
    ];

    public function mount(): void
    {
        $loaded = [];
        foreach (self::$defaults as $key => $default) {
            $val = DisplaySetting::get($key);
            $loaded[$key] = $val !== null ? $val : $default;
        }
        $this->form->fill($loaded);
    }

    public function form(Form $form): Form
    {
        return $form->statePath('data')->schema([
            Forms\Components\Tabs::make('Tabs')->tabs([
                // ── Top bar ──
                Forms\Components\Tabs\Tab::make('Верхня смуга')
                    ->icon('heroicon-o-bars-3-bottom-left')
                    ->schema([
                        Forms\Components\TextInput::make('gazu_topbar_cities')
                            ->label('Міста / відділення')
                            ->helperText('Текст лівого боку темної верхньої смуги'),
                        Forms\Components\TextInput::make('gazu_topbar_hours')
                            ->label('Час роботи'),
                        Forms\Components\Repeater::make('gazu_topbar_links')
                            ->label('Посилання у верхній смузі')
                            ->schema([
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('label')->label('Назва')->required(),
                                    Forms\Components\TextInput::make('url')->label('URL')->required(),
                                ]),
                            ])
                            ->defaultItems(0)
                            ->columnSpanFull(),
                    ]),

                // ── Header ──
                Forms\Components\Tabs\Tab::make('Шапка')
                    ->icon('heroicon-o-phone')
                    ->schema([
                        Forms\Components\TextInput::make('gazu_phone')
                            ->label('Телефон')
                            ->placeholder('0 800 75 10 24'),
                        Forms\Components\TextInput::make('gazu_phone_subtitle')
                            ->label('Підпис під телефоном'),
                        Forms\Components\TextInput::make('gazu_total_sku')
                            ->label('Загальна к-ть SKU (для бейджа в шапці)')
                            ->numeric()
                            ->default(50000),
                    ]),

                // ── Hero ──
                Forms\Components\Tabs\Tab::make('Hero (головна)')
                    ->icon('heroicon-o-megaphone')
                    ->schema([
                        Forms\Components\TextInput::make('gazu_hero_subtitle')
                            ->label('Підпис над заголовком (mono-uppercase)'),
                        Forms\Components\TextInput::make('gazu_hero_title_1')
                            ->label('Перший рядок заголовка'),
                        Forms\Components\Textarea::make('gazu_hero_title_2_html')
                            ->label('Другий рядок (HTML дозволено)')
                            ->helperText('Можна використовувати <span style="color:var(--gazu-blue)">…</span> для акцентів'),
                        Forms\Components\Textarea::make('gazu_hero_description')
                            ->label('Опис під заголовком'),

                        Forms\Components\Section::make('Visual картка (справа від форми)')
                            ->description('Демо-товар, що показується у візуальному блоці hero. Використовується для атмосфери.')
                            ->schema([
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('gazu_hero_visual_oem_code')->label('OEM-плашка (зверху)'),
                                    Forms\Components\Select::make('gazu_hero_visual_image_kind')->label('Тип ілюстрації')->options([
                                        'bearing' => 'Підшипник', 'filter' => 'Фільтр', 'pad' => 'Колодки', 'shock' => 'Амортизатор',
                                        'bulb' => 'Лампа', 'oil' => 'Олива', 'spark' => 'Свічка', 'wiper' => 'Щітка',
                                    ]),
                                    Forms\Components\TextInput::make('gazu_hero_visual_title')->label('Назва товару')->columnSpanFull(),
                                    Forms\Components\TextInput::make('gazu_hero_visual_subtitle')->label('Артикул (mono)'),
                                    Forms\Components\TextInput::make('gazu_hero_visual_price')->label('Ціна'),
                                ]),
                            ])
                            ->collapsible()
                            ->collapsed(),
                    ]),

                // ── Trust strip ──
                Forms\Components\Tabs\Tab::make('Trust-блок')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        Forms\Components\Repeater::make('gazu_trust_items')
                            ->label('Пункти')
                            ->helperText('4 рекомендовано. Іконки: truck, shield, return, wrench, check, star, phone, location')
                            ->schema([
                                Forms\Components\Grid::make(3)->schema([
                                    Forms\Components\Select::make('icon')->label('Іконка')->options([
                                        'truck' => '🚚 truck',
                                        'shield' => '🛡 shield',
                                        'return' => '↩ return',
                                        'wrench' => '🔧 wrench',
                                        'check' => '✓ check',
                                        'star' => '⭐ star',
                                        'phone' => '📞 phone',
                                        'location' => '📍 location',
                                    ])->required(),
                                    Forms\Components\TextInput::make('title')->label('Заголовок')->required(),
                                    Forms\Components\TextInput::make('desc')->label('Опис')->required(),
                                ]),
                            ])
                            ->defaultItems(0)
                            ->columnSpanFull(),
                    ]),

                // ── Footer ──
                Forms\Components\Tabs\Tab::make('Футер')
                    ->icon('heroicon-o-bars-3-bottom-right')
                    ->schema([
                        Forms\Components\Textarea::make('gazu_footer_about')
                            ->label('Текст «про магазин» у футері'),
                        Forms\Components\Repeater::make('gazu_footer_columns')
                            ->label('Колонки футеру')
                            ->schema([
                                Forms\Components\TextInput::make('title')->label('Заголовок')->required(),
                                Forms\Components\TagsInput::make('items')->label('Пункти')->required()
                                    ->placeholder('Натисніть Enter, щоб додати'),
                            ])
                            ->defaultItems(0)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('gazu_footer_payments')
                            ->label('Способи оплати (через кому)')
                            ->columnSpanFull(),
                    ]),

                // ── Social ──
                Forms\Components\Tabs\Tab::make('Соцмережі')
                    ->icon('heroicon-o-share')
                    ->schema([
                        Forms\Components\TextInput::make('gazu_social_facebook')->label('Facebook URL'),
                        Forms\Components\TextInput::make('gazu_social_instagram')->label('Instagram URL'),
                        Forms\Components\TextInput::make('gazu_social_telegram')->label('Telegram URL'),
                        Forms\Components\TextInput::make('gazu_social_youtube')->label('YouTube URL'),
                    ]),

                // ── Section titles ──
                Forms\Components\Tabs\Tab::make('Назви секцій')
                    ->icon('heroicon-o-rectangle-group')
                    ->schema([
                        Forms\Components\TextInput::make('gazu_section_categories')->label('Каталог за категоріями'),
                        Forms\Components\TextInput::make('gazu_section_specials')->label('Акції тижня'),
                        Forms\Components\TextInput::make('gazu_section_hits')->label('Хіти продажів'),
                        Forms\Components\TextInput::make('gazu_section_brands')->label('Топ-бренди'),
                        Forms\Components\TextInput::make('gazu_section_related')->label('Часто купують разом'),
                    ]),

                // ── VIN block ──
                Forms\Components\Tabs\Tab::make('VIN-блок')
                    ->icon('heroicon-o-key')
                    ->schema([
                        Forms\Components\TextInput::make('gazu_vin_label')->label('Підпис (mono-uppercase)'),
                        Forms\Components\TextInput::make('gazu_vin_title')->label('Заголовок'),
                        Forms\Components\Textarea::make('gazu_vin_description')->label('Опис'),
                        Forms\Components\Section::make('Демо-VIN (показується у блоці)')
                            ->schema([
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('gazu_vin_demo_code')->label('VIN-код')->maxLength(17),
                                    Forms\Components\TextInput::make('gazu_vin_demo_make')->label('Марка'),
                                    Forms\Components\TextInput::make('gazu_vin_demo_model')->label('Модель'),
                                    Forms\Components\TextInput::make('gazu_vin_demo_year')->label('Рік'),
                                    Forms\Components\TextInput::make('gazu_vin_demo_engine')->label('Двигун'),
                                ]),
                            ]),
                        Forms\Components\Section::make('Кроки на сторінці /gazu/vin')
                            ->schema([
                                Forms\Components\Repeater::make('gazu_vin_steps')
                                    ->label('Кроки')
                                    ->schema([
                                        Forms\Components\Grid::make(3)->schema([
                                            Forms\Components\TextInput::make('num')->label('#')->required(),
                                            Forms\Components\TextInput::make('title')->label('Заголовок')->required(),
                                            Forms\Components\TextInput::make('desc')->label('Опис')->required(),
                                        ]),
                                    ])
                                    ->defaultItems(0)->columnSpanFull(),
                            ]),
                    ]),

                // ── Auth ──
                Forms\Components\Tabs\Tab::make('Реєстрація · бонуси')
                    ->icon('heroicon-o-gift')
                    ->schema([
                        Forms\Components\TagsInput::make('gazu_auth_bonuses')
                            ->label('Список бонусів (3-5 пунктів)')
                            ->placeholder('Введіть пункт і Enter')
                            ->columnSpanFull(),
                    ]),

                // ── STO ──
                Forms\Components\Tabs\Tab::make('СТО')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->schema([
                        Forms\Components\TextInput::make('gazu_sto_intro_title')->label('Заголовок hero'),
                        Forms\Components\Textarea::make('gazu_sto_intro_desc')->label('Опис hero'),
                        Forms\Components\Section::make('Послуги')
                            ->schema([
                                Forms\Components\Repeater::make('gazu_sto_services')
                                    ->label('Послуги')
                                    ->schema([
                                        Forms\Components\Grid::make(4)->schema([
                                            Forms\Components\Select::make('icon')->label('Іконка')->options([
                                                'wrench' => '🔧 wrench', 'shield' => '🛡 shield', 'truck' => '🚚 truck',
                                                'box' => '📦 box', 'car' => '🚗 car', 'edit' => '✏ edit', 'phone' => '📞 phone',
                                            ]),
                                            Forms\Components\TextInput::make('title')->label('Назва'),
                                            Forms\Components\TextInput::make('price')->label('Ціна'),
                                            Forms\Components\TextInput::make('desc')->label('Опис'),
                                        ]),
                                    ])->defaultItems(0)->columnSpanFull(),
                            ]),
                        Forms\Components\Section::make('Партнерські СТО')
                            ->schema([
                                Forms\Components\Repeater::make('gazu_sto_partners')
                                    ->label('Партнери')
                                    ->schema([
                                        Forms\Components\Grid::make(3)->schema([
                                            Forms\Components\TextInput::make('name')->label('Назва'),
                                            Forms\Components\TextInput::make('addr')->label('Адреса'),
                                            Forms\Components\TextInput::make('rating')->label('Рейтинг'),
                                        ]),
                                    ])->defaultItems(0)->columnSpanFull(),
                            ]),
                    ]),

                // ── Contacts ──
                Forms\Components\Tabs\Tab::make('Контакти')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Forms\Components\TextInput::make('gazu_contacts_email')->label('Email')->email(),
                        Forms\Components\TextInput::make('gazu_contacts_telegram')->label('Telegram username'),
                        Forms\Components\TextInput::make('gazu_contacts_viber')->label('Viber номер'),
                        Forms\Components\Repeater::make('gazu_contacts_offices')
                            ->label('Відділення')
                            ->schema([
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('city')->label('Місто'),
                                    Forms\Components\TextInput::make('addr')->label('Адреса'),
                                ]),
                            ])->defaultItems(0)->columnSpanFull(),
                    ]),

                Forms\Components\Tabs\Tab::make('Доставка · Самовивіз')
                    ->icon('heroicon-o-building-storefront')
                    ->schema([
                        Forms\Components\Section::make('Точка самовивозу')
                            ->description('Адреса, яка показується у формі checkout при виборі «Самовивіз з магазину»')
                            ->schema([
                                Forms\Components\TextInput::make('gazu_pickup_address')
                                    ->label('Адреса')
                                    ->placeholder('м. Київ, вул. Промислова, 25'),
                                Forms\Components\TextInput::make('gazu_pickup_hours')
                                    ->label('Графік роботи')
                                    ->placeholder('Пн–Пт: 9:00–18:00, Сб: 10:00–15:00'),
                                Forms\Components\TextInput::make('gazu_pickup_phone')
                                    ->label('Телефон точки')
                                    ->tel(),
                            ])->columns(2),
                    ]),

                // ── Hero V2 (CarPicker) ──
                Forms\Components\Tabs\Tab::make('Hero V2 · CarPicker')
                    ->icon('heroicon-o-truck')
                    ->schema([
                        Forms\Components\TextInput::make('gazu_hero_v2_kicker')->label('Kicker (mono uppercase)'),
                        Forms\Components\Textarea::make('gazu_hero_v2_title')->label('Заголовок (можна на 2 рядки через Enter)')->rows(2),
                        Forms\Components\Textarea::make('gazu_hero_v2_description')->label('Опис'),
                        Forms\Components\TagsInput::make('gazu_hero_v2_brands')->label('Список марок (для перших 8 кнопок)')
                            ->placeholder('Введіть марку і Enter')->columnSpanFull(),
                        Forms\Components\TextInput::make('gazu_hero_v2_brands_total')->label('Загальна к-ть марок (для CTA "Усі N марок")')->numeric(),
                        Forms\Components\TextInput::make('gazu_hero_v2_vin_hint')->label('VIN-підказка під формою'),
                    ]),

                // ── Hero V3 (Split — майстри + водії) ──
                Forms\Components\Tabs\Tab::make('Hero V3 · Split')
                    ->icon('heroicon-o-rectangle-stack')
                    ->schema([
                        Forms\Components\Section::make('Ліва картка — для майстрів')->schema([
                            Forms\Components\TextInput::make('gazu_hero_v3_left_kicker')->label('Kicker'),
                            Forms\Components\Textarea::make('gazu_hero_v3_left_title')->label('Заголовок (2 рядки)')->rows(2),
                            Forms\Components\Textarea::make('gazu_hero_v3_left_description')->label('Опис'),
                            Forms\Components\TagsInput::make('gazu_hero_v3_left_perks')
                                ->label('Перки (3 пункти)')
                                ->placeholder('Введіть пункт і Enter'),
                        ])->columns(1),
                        Forms\Components\Section::make('Права картка — для водіїв')->schema([
                            Forms\Components\TextInput::make('gazu_hero_v3_right_kicker')->label('Kicker'),
                            Forms\Components\Textarea::make('gazu_hero_v3_right_title')->label('Заголовок (2 рядки)')->rows(2),
                            Forms\Components\Textarea::make('gazu_hero_v3_right_description')->label('Опис'),
                        ])->columns(1),
                    ]),

                // ── Mobile ──
                Forms\Components\Tabs\Tab::make('Mobile')
                    ->icon('heroicon-o-device-phone-mobile')
                    ->schema([
                        Forms\Components\TextInput::make('gazu_mobile_hero_kicker')->label('Hero kicker'),
                        Forms\Components\Textarea::make('gazu_mobile_hero_title_html')
                            ->label('Hero title (HTML)')
                            ->helperText('Дозволено <span style="color:..."> для акценту'),
                        Forms\Components\TextInput::make('gazu_mobile_categories_title')->label('Заголовок секції категорій'),
                        Forms\Components\TextInput::make('gazu_mobile_hits_title')->label('Заголовок секції товарів'),
                        Forms\Components\TagsInput::make('gazu_mobile_filter_pills')
                            ->label('Швидкі pill-фільтри (mobile catalog)')
                            ->placeholder('Введіть бренд і Enter')
                            ->columnSpanFull(),
                    ]),

                // ── Кольори категорій ──
                Forms\Components\Tabs\Tab::make('Кольори категорій')
                    ->icon('heroicon-o-swatch')
                    ->schema([
                        Forms\Components\Repeater::make('gazu_category_accents')
                            ->label('Кольорова смуга на category-картках (по черзі)')
                            ->helperText('Можна використати CSS-змінні (var(--gazu-blue)) або hex-коди (#2453A6)')
                            ->simple(
                                Forms\Components\TextInput::make('color')
                                    ->placeholder('var(--gazu-blue) або #2453A6')
                                    ->required()
                            )
                            ->defaultItems(0)
                            ->columnSpanFull(),
                    ]),

                // ── Бренди ──
                Forms\Components\Tabs\Tab::make('Бренди — фолбек опис')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Forms\Components\Textarea::make('gazu_brand_fallback_description')
                            ->label('Опис бренду коли він не заданий у БД')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                // ── 1-клік ──
                Forms\Components\Tabs\Tab::make('1-клік замовлення')
                    ->icon('heroicon-o-bolt')
                    ->schema([
                        Forms\Components\Toggle::make('gazu_oneclick_enabled')
                            ->label('Увімкнути кнопку «Купити в 1 клік»')
                            ->helperText('Якщо вимкнено — кнопка не показується на product page'),
                        Forms\Components\TextInput::make('gazu_oneclick_label')
                            ->label('Текст кнопки'),
                        Forms\Components\Textarea::make('gazu_oneclick_message')
                            ->label('Підпис у модалці після кліку (без email/адреси)')
                            ->rows(2),
                        Forms\Components\Section::make('Сторінка товару — таб «Доставка та оплата»')
                            ->schema([
                                Forms\Components\Textarea::make('gazu_product_delivery_text')
                                    ->label('Текст про доставку')
                                    ->rows(3),
                                Forms\Components\Textarea::make('gazu_product_payment_text')
                                    ->label('Текст про оплату')
                                    ->rows(3),
                            ]),
                    ]),

                // ── Empty / 404 / cart-empty ──
                Forms\Components\Tabs\Tab::make('Порожні стани')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->schema([
                        Forms\Components\Section::make('Сторінка 404')->schema([
                            Forms\Components\TextInput::make('gazu_404_title')->label('Заголовок'),
                            Forms\Components\Textarea::make('gazu_404_desc')->label('Опис'),
                        ]),
                        Forms\Components\Section::make('Порожній кошик')->schema([
                            Forms\Components\TextInput::make('gazu_cart_empty_title')->label('Заголовок'),
                            Forms\Components\Textarea::make('gazu_cart_empty_desc')->label('Опис'),
                        ]),
                    ]),

            ])->columnSpanFull(),
        ]);
    }

    public function save(): void
    {
        $state = $this->form->getState();
        foreach (self::$defaults as $key => $_) {
            DisplaySetting::set($key, $state[$key] ?? null);
        }

        DisplaySetting::flushSettingsCache();

        Notification::make()->title('Налаштування збережено')->success()->send();
    }
}
