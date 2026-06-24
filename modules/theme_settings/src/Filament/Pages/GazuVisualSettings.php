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
 *
 * Форма згрупована у 8 логічних табів (Головна / Шапка / Футер / Картка
 * товару / Контакти / СТО / Mobile / SEO+тексти). Лише ключі, які реально
 * читаються вʼюхами storefront — осиротілі (VIN-декодер, hero-visual картка
 * тощо) прибрано, бо їх редагування не давало результату.
 */
class GazuVisualSettings extends Page implements HasForms
{
    use \App\Filament\Concerns\GatedPage;

    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-paint-brush';

    protected static ?string $navigationLabel = 'GAZU візуальні блоки';

    protected static ?string $navigationGroup = 'Налаштування';

    protected static ?string $title = 'GAZU — візуальні налаштування storefront';

    protected static ?int $navigationSort = 50;

    protected static ?string $slug = 'gazu-visual';

    protected static string $view = 'filament.pages.gazu-visual-settings';

    public ?array $data = [];

    public static array $defaults = [
        // SEO / Privacy
        'seo_noindex_all' => true, // презентаційний домен закритий за замовчуванням

        // Hero V2 (Car-picker, темний)
        'gazu_hero_v2_kicker' => 'Підбір за вашим авто',
        'gazu_hero_v2_title' => "Запчастини, які\nточно підійдуть.",
        'gazu_hero_v2_description' => 'Оберіть марку, модель та рік випуску — побачите тільки сумісні деталі. Без помилок і повернень.',
        'gazu_hero_v2_brands' => ['VW', 'Audi', 'BMW', 'Skoda', 'Toyota', 'Renault', 'Ford', 'Hyundai'],
        'gazu_hero_v2_brands_total' => 240,

        // Hero V3 (Split — для майстрів + водіїв)
        'gazu_hero_v3_left_kicker' => 'Для майстрів СТО',
        'gazu_hero_v3_left_title' => "Швидкий пошук\nза артикулом",
        'gazu_hero_v3_left_description' => null, // composed dynamically from shopStats.products_label
        'gazu_hero_v3_left_perks' => ['VIN-декодер', 'Пакетний пошук', 'Гуртові ціни'],
        'gazu_hero_v3_right_kicker' => 'Для водіїв',
        'gazu_hero_v3_right_title' => "Підбір за вашим\nавто",
        'gazu_hero_v3_right_description' => 'Марка, модель, рік — і ви побачите тільки сумісні запчастини.',

        // Mobile
        'gazu_mobile_hero_kicker' => null, // composed from shopStats.products_label
        'gazu_mobile_hero_title_html' => 'Знайди деталь за <span style="color:var(--gazu-blue)">артикулом</span>',
        'gazu_mobile_categories_title' => 'Категорії',
        'gazu_mobile_hits_title' => 'Хіти',

        // Featured rows titles
        'gazu_section_specials' => 'Акції тижня',
        'gazu_section_categories' => 'Усе для вашого авто',
        'gazu_section_brands' => 'Топ-бренди',

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
        'gazu_contacts_map' => null,            // Google Maps iframe embed або URL
        // Реальні контакти/адреси вписує клієнт. Дефолти нейтральні.
        'gazu_contacts_email' => 'info@gazu.uno',
        'gazu_contacts_telegram' => '',
        'gazu_contacts_viber' => '',
        'gazu_contacts_offices' => [],

        // Самовивіз (checkout)
        'gazu_pickup_address' => null,
        'gazu_pickup_hours' => null,
        'gazu_pickup_phone' => null,

        // 404 / cart empty
        'gazu_404_title' => 'Запчастину не знайдено',
        'gazu_404_desc' => 'Можливо, сторінку перенесли або URL застарів. Спробуйте знайти потрібну деталь через каталог.',
        'gazu_404_badge' => '', // маленький бейдж біля «404»; порожньо = прихований
        'gazu_cart_empty_title' => 'Кошик порожній',
        'gazu_cart_empty_desc' => 'Додайте товари з каталогу — підберемо точні запчастини для вашого авто.',

        // Top bar
        'gazu_topbar_cities' => null, // composed from shopStats.cities_with_count
        'gazu_topbar_hours' => 'Пн-Нд 8:00–20:00',
        'gazu_topbar_links' => [
            ['label' => 'Гуртом', 'url' => '#'],
            ['label' => 'Доставка та оплата', 'url' => '#'],
            ['label' => 'Гарантія', 'url' => '#'],
            ['label' => 'Контакти', 'url' => '/contacts'],
        ],
        'gazu_subnav' => null, // null → стандартні пункти меню в blade

        // Header
        'gazu_logo' => null,            // null → стандартний GZ-знак
        'gazu_brand_name' => 'GAZU',
        'gazu_default_warranty' => '12 місяців',  // дефолт для картки товару без specs
        'gazu_phone' => '0 800 750 010',
        'gazu_phone_subtitle' => 'безкоштовно по Україні',

        // Trust-бейджі в картці товару (під кнопкою «Додати в кошик»).
        // {date} у title → автоматично підставляється завтрашня дата.
        'gazu_product_trust' => [
            ['icon' => 'truck',  'title' => 'Доставка завтра, {date}', 'subtitle' => 'Замовте сьогодні до 16:00 · Нова Пошта'],
            ['icon' => 'shield', 'title' => 'Гарантія 12 місяців',     'subtitle' => 'Повернення коштів при дефекті'],
            ['icon' => 'return', 'title' => '14 днів на повернення',   'subtitle' => 'Без пояснення причин'],
        ],

        // Trust strip (4 пункти)
        'gazu_trust_items' => [
            ['icon' => 'truck',  'title' => 'Доставка по Україні',  'desc' => '1–3 дні · Нова Пошта · Укрпошта'],
            ['icon' => 'shield', 'title' => 'Гарантія на запчастини', 'desc' => 'Від 6 до 24 місяців'],
            ['icon' => 'return', 'title' => 'Повернення',            'desc' => '14 днів без пояснення причин'],
            ['icon' => 'headset', 'title' => 'Допомога з підбором',   'desc' => 'Менеджер передзвонить за 5 хв'],
        ],

        // Hero
        'gazu_hero_subtitle' => 'Запчастини для китайських авто',
        'gazu_hero_title_1' => 'Підбір по авто',
        'gazu_hero_title_2_html' => 'за <span style="color:var(--gazu-blue)">маркою</span> і двигуном.',
        'gazu_hero_description' => null, // composed from shopStats.warehouses_label

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

        // Backorder — дозволити додавати в кошик товари без складського залишку
        'gazu_allow_backorder' => true,
        'gazu_backorder_button_label' => 'Замовити',

        // Product page — tab "Доставка та оплата"
        'gazu_product_delivery_text' => 'Нова Пошта по Україні · Доставка наступного дня для замовлень до 16:00 · Безкоштовно від 1500 ₴.',
        'gazu_product_payment_text'  => 'Visa / Mastercard, Apple Pay, Google Pay, готівка при отриманні (накладений платіж), безпечна оплата через LiqPay.',
        // Warehouse selector — closest warehouse label (картка товару)
        'gazu_warehouse_closest_label' => 'найшвидша відправка',

        // Footer
        'gazu_footer_about' => null, // composed from shopStats.products_label
        'gazu_footer_columns' => [
            ['title' => 'Каталог', 'items' => ['Двигун', 'Гальмівна система', 'Підвіска', 'Електрика', 'Кузов', 'Салон']],
            ['title' => 'Клієнтам', 'items' => ['Доставка та оплата', 'Гарантія та повернення', 'Питання та відповіді', 'Бонусна програма', 'Гуртовим клієнтам']],
            ['title' => 'Компанія', 'items' => ['Про нас', 'Контакти', 'Вакансії', 'Сертифікати', 'Публічна оферта']],
        ],
        'gazu_footer_payments' => 'Накладений платіж, Нова Пошта, УкрПошта',
        'gazu_social_facebook' => '#',
        'gazu_social_instagram' => '#',
        'gazu_social_telegram' => '#',
        'gazu_social_youtube' => '#',

        // SEO-текст на головній (рендериться після brand-strip, перед footer)
        'gazu_seo_enabled' => true,
        'gazu_seo_title' => 'Автозапчастини для китайських авто в Україні',
        'gazu_seo_html' => '<p><strong>GAZU</strong> — спеціалізований інтернет-магазин запчастин для китайських автомобілів: <strong>BYD, Chery, Geely, Haval, Great Wall, JAC, MG, FAW, DongFeng</strong>. У каталозі понад <strong>1 278 артикулів</strong> оригінальних запчастин та перевірених аналогів — для самостійного ремонту або СТО.</p>

<h3>Що ви знайдете в каталозі</h3>
<ul>
  <li><strong>Двигун та системи живлення</strong> — масляні, повітряні та паливні фільтри, свічки запалювання, ремені ГРМ, ланцюги, помпи.</li>
  <li><strong>Гальмівна система</strong> — колодки, диски, циліндри, гальмівні рідини.</li>
  <li><strong>Підвіска та рульове</strong> — амортизатори, пружини, сайлентблоки, кульові, ШРУСи.</li>
  <li><strong>Електрика</strong> — генератори, стартери, акумулятори, датчики, реле, лампи.</li>
  <li><strong>Мастила та технічні рідини</strong> — оливи Mobil, Liqui Moly, Shell; антифризи, гальмівні рідини.</li>
  <li><strong>Кузов та оптика</strong> — фари, склоочисники, бампери, дзеркала.</li>
</ul>

<h3>Чому обирають GAZU</h3>
<ul>
  <li><strong>Без передоплати</strong> — оплата при отриманні через Нову Пошту або Укрпошту.</li>
  <li><strong>Гарантія 12+ місяців</strong> на всі оригінальні запчастини.</li>
  <li><strong>Доставка 1-3 дні</strong> по всій Україні — Київ, Львів, Одеса, Харків, Дніпро, Запоріжжя.</li>
  <li><strong>Повернення 14 днів</strong> — без пояснення причин.</li>
  <li><strong>Підбір за артикулом OEM</strong> або через селектор «марка → модель → двигун».</li>
  <li><strong>Допомога з підбором</strong> — менеджер передзвонить за 5 хвилин.</li>
</ul>

<h3>Як підібрати запчастину</h3>
<p>Найшвидший спосіб — введіть <strong>артикул OEM</strong> у пошуку. Або скористайтеся селектором на головній: оберіть марку, модель та двигун — система покаже сумісні деталі. Якщо не впевнені — телефонуйте <a href="tel:0800750010">0 800 750 010</a>, безкоштовно проконсультуємо.</p>

<h3>Бренди в каталозі</h3>
<p>Працюємо з провідними виробниками: <strong>Bosch, Mann Filter, Mahle, ATE, Brembo, NGK, TRW, Sachs, KYB, Valeo, Liqui Moly, Mobil, Shell, Castrol</strong>. Кожна позиція проходить перевірку справжності — підробок у нашому магазині немає.</p>',
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

                // ═══ 1. ГОЛОВНА / HERO ═══
                Forms\Components\Tabs\Tab::make('Головна')
                    ->icon('heroicon-o-megaphone')
                    ->schema([
                        Forms\Components\Section::make('Hero — головний банер')
                            ->schema([
                                Forms\Components\TextInput::make('gazu_hero_subtitle')->label('Підпис над заголовком (mono-uppercase)'),
                                Forms\Components\TextInput::make('gazu_hero_title_1')->label('Перший рядок заголовка'),
                                Forms\Components\Textarea::make('gazu_hero_title_2_html')
                                    ->label('Другий рядок (HTML дозволено)')
                                    ->helperText('Можна використовувати <span style="color:var(--gazu-blue)">…</span> для акцентів'),
                                Forms\Components\Textarea::make('gazu_hero_description')->label('Опис під заголовком'),
                            ]),
                        Forms\Components\Section::make('Hero V2 · CarPicker')
                            ->collapsible()->collapsed()
                            ->schema([
                                Forms\Components\TextInput::make('gazu_hero_v2_kicker')->label('Kicker (mono uppercase)'),
                                Forms\Components\Textarea::make('gazu_hero_v2_title')->label('Заголовок (2 рядки через Enter)')->rows(2),
                                Forms\Components\Textarea::make('gazu_hero_v2_description')->label('Опис'),
                                Forms\Components\TagsInput::make('gazu_hero_v2_brands')->label('Список марок (перші 8 кнопок)')
                                    ->placeholder('Введіть марку і Enter')->columnSpanFull(),
                                Forms\Components\TextInput::make('gazu_hero_v2_brands_total')->label('Загальна к-ть марок (CTA "Усі N марок")')->numeric(),
                            ]),
                        Forms\Components\Section::make('Hero V3 · Split (майстри + водії)')
                            ->collapsible()->collapsed()
                            ->schema([
                                Forms\Components\Fieldset::make('Ліва картка — для майстрів')->schema([
                                    Forms\Components\TextInput::make('gazu_hero_v3_left_kicker')->label('Kicker'),
                                    Forms\Components\Textarea::make('gazu_hero_v3_left_title')->label('Заголовок (2 рядки)')->rows(2),
                                    Forms\Components\Textarea::make('gazu_hero_v3_left_description')->label('Опис'),
                                    Forms\Components\TagsInput::make('gazu_hero_v3_left_perks')->label('Перки (3 пункти)')->placeholder('Введіть пункт і Enter'),
                                ])->columns(1),
                                Forms\Components\Fieldset::make('Права картка — для водіїв')->schema([
                                    Forms\Components\TextInput::make('gazu_hero_v3_right_kicker')->label('Kicker'),
                                    Forms\Components\Textarea::make('gazu_hero_v3_right_title')->label('Заголовок (2 рядки)')->rows(2),
                                    Forms\Components\Textarea::make('gazu_hero_v3_right_description')->label('Опис'),
                                ])->columns(1),
                            ]),
                        Forms\Components\Section::make('Назви секцій головної')
                            ->schema([
                                Forms\Components\TextInput::make('gazu_section_categories')->label('Каталог за категоріями'),
                                Forms\Components\TextInput::make('gazu_section_specials')->label('Акції тижня'),
                                Forms\Components\TextInput::make('gazu_section_brands')->label('Топ-бренди'),
                            ]),
                    ]),

                // ═══ 2. ШАПКА / ВЕРХНЯ СМУГА ═══
                Forms\Components\Tabs\Tab::make('Шапка')
                    ->icon('heroicon-o-bars-3-bottom-left')
                    ->schema([
                        Forms\Components\Section::make('Верхня смуга')
                            ->schema([
                                Forms\Components\TextInput::make('gazu_topbar_cities')->label('Міста / відділення')->helperText('Текст лівого боку темної верхньої смуги'),
                                Forms\Components\TextInput::make('gazu_topbar_hours')->label('Час роботи'),
                                Forms\Components\Repeater::make('gazu_topbar_links')->label('Посилання у верхній смузі')
                                    ->schema([
                                        Forms\Components\Grid::make(2)->schema([
                                            Forms\Components\TextInput::make('label')->label('Назва')->required(),
                                            Forms\Components\TextInput::make('url')->label('URL')->required(),
                                        ]),
                                    ])->defaultItems(0)->columnSpanFull(),
                                Forms\Components\Repeater::make('gazu_subnav')->label('Горизонтальне меню під шапкою')
                                    ->helperText('Акції / Хіти / Новинки / Бренди / Блог. Порожньо — стандартні пункти.')
                                    ->schema([
                                        Forms\Components\Grid::make(2)->schema([
                                            Forms\Components\TextInput::make('label')->label('Назва')->required(),
                                            Forms\Components\TextInput::make('url')->label('URL')->required()->placeholder('/akcii'),
                                        ]),
                                    ])->defaultItems(0)->columnSpanFull(),
                            ]),
                        Forms\Components\Section::make('Логотип і контакт у шапці')
                            ->schema([
                                Forms\Components\FileUpload::make('gazu_logo')->label('Логотип сайту')
                                    ->helperText('PNG/SVG/WEBP з прозорим фоном, висотою ≥ 60px. Якщо не задано — стандартний знак GAZU (GZ).')
                                    ->image()->disk('public')->directory('branding')->visibility('public')->imageEditor()->maxSize(1024)
                                    ->acceptedFileTypes(['image/png', 'image/svg+xml', 'image/webp', 'image/jpeg']),
                                Forms\Components\TextInput::make('gazu_brand_name')->label('Назва бренду')->placeholder('GAZU')
                                    ->helperText('Alt-текст лого та текст, якщо лого не завантажено.'),
                                Forms\Components\TextInput::make('gazu_phone')->label('Телефон')->placeholder('0 800 750 010'),
                                Forms\Components\TextInput::make('gazu_phone_subtitle')->label('Підпис під телефоном'),
                                Forms\Components\TextInput::make('gazu_default_warranty')->label('Гарантія за замовчуванням')->placeholder('12 місяців')
                                    ->helperText('У характеристиках товару, якщо не задано власні specifications.'),
                            ]),
                    ]),

                // ═══ 3. ФУТЕР / СОЦМЕРЕЖІ ═══
                Forms\Components\Tabs\Tab::make('Футер')
                    ->icon('heroicon-o-bars-3-bottom-right')
                    ->schema([
                        Forms\Components\Textarea::make('gazu_footer_about')->label('Текст «про магазин» у футері'),
                        Forms\Components\Repeater::make('gazu_footer_columns')->label('Колонки футеру')
                            ->schema([
                                Forms\Components\TextInput::make('title')->label('Заголовок')->required(),
                                Forms\Components\TagsInput::make('items')->label('Пункти')->required()->placeholder('Натисніть Enter, щоб додати'),
                            ])->defaultItems(0)->columnSpanFull(),
                        Forms\Components\TextInput::make('gazu_footer_payments')->label('Способи оплати (через кому)')->columnSpanFull(),
                        Forms\Components\Section::make('Соцмережі')
                            ->schema([
                                Forms\Components\TextInput::make('gazu_social_facebook')->label('Facebook URL'),
                                Forms\Components\TextInput::make('gazu_social_instagram')->label('Instagram URL'),
                                Forms\Components\TextInput::make('gazu_social_telegram')->label('Telegram URL'),
                                Forms\Components\TextInput::make('gazu_social_youtube')->label('YouTube URL'),
                            ])->columns(2),
                    ]),

                // ═══ 4. КАРТКА ТОВАРУ ═══
                Forms\Components\Tabs\Tab::make('Картка товару')
                    ->icon('heroicon-o-bolt')
                    ->schema([
                        Forms\Components\Section::make('Trust-блоки')
                            ->schema([
                                Forms\Components\Repeater::make('gazu_trust_items')->label('Пункти (головна сторінка)')
                                    ->helperText('4 рекомендовано. Іконки: truck, shield, return, wrench, headset')
                                    ->schema([
                                        Forms\Components\Grid::make(3)->schema([
                                            Forms\Components\Select::make('icon')->label('Іконка')->options(['truck' => '🚚 truck', 'shield' => '🛡 shield', 'return' => '↩ return', 'wrench' => '🔧 wrench', 'headset' => '🎧 headset'])->required(),
                                            Forms\Components\TextInput::make('title')->label('Заголовок')->required(),
                                            Forms\Components\TextInput::make('desc')->label('Опис')->required(),
                                        ]),
                                    ])->defaultItems(0)->columnSpanFull(),
                                Forms\Components\Repeater::make('gazu_product_trust')->label('Бейджі в картці товару')
                                    ->helperText('Під кнопкою «Додати в кошик». Токен {date} → завтрашня дата.')
                                    ->schema([
                                        Forms\Components\Grid::make(3)->schema([
                                            Forms\Components\Select::make('icon')->label('Іконка')->options(['truck' => '🚚 truck', 'shield' => '🛡 shield', 'return' => '↩ return', 'wrench' => '🔧 wrench', 'headset' => '🎧 headset'])->required(),
                                            Forms\Components\TextInput::make('title')->label('Заголовок')->required(),
                                            Forms\Components\TextInput::make('subtitle')->label('Підпис'),
                                        ]),
                                    ])->defaultItems(0)->columnSpanFull(),
                            ]),
                        Forms\Components\Section::make('Кнопка «Купити в 1 клік»')
                            ->schema([
                                Forms\Components\Toggle::make('gazu_oneclick_enabled')->label('Увімкнути кнопку «Купити в 1 клік»')
                                    ->helperText('Якщо вимкнено — кнопка не показується на product page'),
                                Forms\Components\TextInput::make('gazu_oneclick_label')->label('Текст кнопки'),
                                Forms\Components\Textarea::make('gazu_oneclick_message')->label('Підпис у модалці після кліку')->rows(2),
                            ]),
                        Forms\Components\Section::make('Замовлення «під замовлення»')
                            ->description('Товари без складського залишку — чи можна додати в кошик і замовити')
                            ->schema([
                                Forms\Components\Toggle::make('gazu_allow_backorder')->label('Дозволити замовлення товарів без залишку')
                                    ->helperText('Увімкнено — кнопка «Замовити» активна навіть без складу (постачання під замовлення). Вимкнено — кнопка неактивна, лише «Купити в 1 клік».'),
                                Forms\Components\TextInput::make('gazu_backorder_button_label')->label('Текст кнопки для backorder')
                                    ->placeholder('Замовити під замовлення'),
                            ]),
                        Forms\Components\Section::make('Таб «Доставка та оплата» на товарі')
                            ->schema([
                                Forms\Components\Textarea::make('gazu_product_delivery_text')->label('Текст про доставку')->rows(3),
                                Forms\Components\Textarea::make('gazu_product_payment_text')->label('Текст про оплату')->rows(3),
                            ]),
                        Forms\Components\Section::make('Інше (склади, бренди, кольори)')
                            ->collapsible()->collapsed()
                            ->schema([
                                Forms\Components\TextInput::make('gazu_warehouse_closest_label')->label('Бейдж пріоритетного складу')
                                    ->helperText('Chip біля найшвидшого складу. Напр.: «найшвидша відправка».')->maxLength(40),
                                Forms\Components\Textarea::make('gazu_brand_fallback_description')->label('Опис бренду, коли він не заданий у БД')->rows(3),
                                Forms\Components\Repeater::make('gazu_category_accents')->label('Кольорова смуга на category-картках (по черзі)')
                                    ->helperText('CSS-змінні (var(--gazu-blue)) або hex (#2453A6)')
                                    ->simple(Forms\Components\TextInput::make('color')->placeholder('var(--gazu-blue) або #2453A6')->required())
                                    ->defaultItems(0)->columnSpanFull(),
                            ]),
                    ]),

                // ═══ 5. КОНТАКТИ / ДОСТАВКА ═══
                Forms\Components\Tabs\Tab::make('Контакти / Доставка')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Forms\Components\Section::make('Контакти')
                            ->schema([
                                Forms\Components\TextInput::make('gazu_contacts_email')->label('Email')->email(),
                                Forms\Components\TextInput::make('gazu_contacts_telegram')->label('Telegram username'),
                                Forms\Components\TextInput::make('gazu_contacts_viber')->label('Viber номер'),
                                Forms\Components\Textarea::make('gazu_contacts_map')->label('Карта (Google Maps)')->rows(3)
                                    ->helperText('Embed-код <iframe …> з Google Maps або URL. Порожньо → заглушка.')->columnSpanFull(),
                                Forms\Components\Repeater::make('gazu_contacts_offices')->label('Відділення')
                                    ->schema([
                                        Forms\Components\Grid::make(2)->schema([
                                            Forms\Components\TextInput::make('city')->label('Місто'),
                                            Forms\Components\TextInput::make('addr')->label('Адреса'),
                                        ]),
                                    ])->defaultItems(0)->columnSpanFull(),
                            ]),
                        Forms\Components\Section::make('Точка самовивозу (checkout)')
                            ->description('Адреса у формі checkout при виборі «Самовивіз з магазину»')
                            ->schema([
                                Forms\Components\TextInput::make('gazu_pickup_address')->label('Адреса')->placeholder('м. Київ, вул. Промислова, 25'),
                                Forms\Components\TextInput::make('gazu_pickup_hours')->label('Графік роботи')->placeholder('Пн–Пт: 9:00–18:00, Сб: 10:00–15:00'),
                                Forms\Components\TextInput::make('gazu_pickup_phone')->label('Телефон точки')->tel(),
                            ])->columns(2),
                    ]),

                // ═══ 6. СТО / ПОСЛУГИ ═══
                Forms\Components\Tabs\Tab::make('СТО / послуги')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->schema([
                        Forms\Components\TextInput::make('gazu_sto_intro_title')->label('Заголовок hero'),
                        Forms\Components\Textarea::make('gazu_sto_intro_desc')->label('Опис hero'),
                        Forms\Components\Section::make('Послуги')
                            ->schema([
                                Forms\Components\Repeater::make('gazu_sto_services')->label('Послуги')
                                    ->schema([
                                        Forms\Components\Grid::make(4)->schema([
                                            Forms\Components\Select::make('icon')->label('Іконка')->options(['wrench' => '🔧 wrench', 'shield' => '🛡 shield', 'truck' => '🚚 truck', 'box' => '📦 box', 'car' => '🚗 car', 'edit' => '✏ edit', 'phone' => '📞 phone']),
                                            Forms\Components\TextInput::make('title')->label('Назва'),
                                            Forms\Components\TextInput::make('price')->label('Ціна'),
                                            Forms\Components\TextInput::make('desc')->label('Опис'),
                                        ]),
                                    ])->defaultItems(0)->columnSpanFull(),
                            ]),
                        Forms\Components\Section::make('Партнерські СТО')
                            ->schema([
                                Forms\Components\Repeater::make('gazu_sto_partners')->label('Партнери')
                                    ->schema([
                                        Forms\Components\Grid::make(3)->schema([
                                            Forms\Components\TextInput::make('name')->label('Назва'),
                                            Forms\Components\TextInput::make('addr')->label('Адреса'),
                                            Forms\Components\TextInput::make('rating')->label('Рейтинг'),
                                        ]),
                                    ])->defaultItems(0)->columnSpanFull(),
                            ]),
                    ]),

                // ═══ 7. MOBILE ═══
                Forms\Components\Tabs\Tab::make('Mobile')
                    ->icon('heroicon-o-device-phone-mobile')
                    ->schema([
                        Forms\Components\TextInput::make('gazu_mobile_hero_kicker')->label('Hero kicker'),
                        Forms\Components\Textarea::make('gazu_mobile_hero_title_html')->label('Hero title (HTML)')
                            ->helperText('Дозволено <span style="color:..."> для акценту'),
                        Forms\Components\TextInput::make('gazu_mobile_categories_title')->label('Заголовок секції категорій'),
                        Forms\Components\TextInput::make('gazu_mobile_hits_title')->label('Заголовок секції товарів'),
                    ]),

                // ═══ 8. SEO / СИСТЕМНІ ТЕКСТИ ═══
                Forms\Components\Tabs\Tab::make('SEO / тексти')
                    ->icon('heroicon-o-lock-closed')
                    ->schema([
                        Forms\Components\Section::make('Індексація пошуковими системами')
                            ->description('Презентаційний домен закрийте від Google/Bing поки сайт у тестовому стані.')
                            ->schema([
                                Forms\Components\Toggle::make('seo_noindex_all')
                                    ->label('Закрити від індексації (noindex + robots.txt Disallow: /)')
                                    ->helperText('Додає <meta name="robots" content="noindex,nofollow"> + /robots.txt "Disallow: /". Для продакшну вимикайте.')
                                    ->default(true),
                            ]),
                        Forms\Components\Section::make('SEO-блок на головній')
                            ->description('Довгий описовий текст з ключовими словами між брендами і футером.')
                            ->collapsible()->collapsed()
                            ->schema([
                                Forms\Components\Toggle::make('gazu_seo_enabled')->label('Показувати SEO-блок на головній')->default(true)->columnSpanFull(),
                                Forms\Components\TextInput::make('gazu_seo_title')->label('Заголовок секції (H2)')->maxLength(160)->columnSpanFull(),
                                Forms\Components\RichEditor::make('gazu_seo_html')->label('SEO-контент (HTML)')
                                    ->toolbarButtons(['h2', 'h3', 'bold', 'italic', 'link', 'bulletList', 'orderedList', 'undo', 'redo'])
                                    ->columnSpanFull(),
                            ]),
                        Forms\Components\Section::make('Сторінка 404')
                            ->schema([
                                Forms\Components\TextInput::make('gazu_404_title')->label('Заголовок'),
                                Forms\Components\Textarea::make('gazu_404_desc')->label('Опис'),
                                Forms\Components\TextInput::make('gazu_404_badge')->label('Бейдж біля «404» (порожньо = прихований)')->maxLength(20),
                            ]),
                        Forms\Components\Section::make('Порожній кошик')
                            ->schema([
                                Forms\Components\TextInput::make('gazu_cart_empty_title')->label('Заголовок'),
                                Forms\Components\Textarea::make('gazu_cart_empty_desc')->label('Опис'),
                            ]),
                        Forms\Components\Section::make('Реєстрація · бонуси')
                            ->schema([
                                Forms\Components\TagsInput::make('gazu_auth_bonuses')->label('Список бонусів (3-5 пунктів)')
                                    ->placeholder('Введіть пункт і Enter')->columnSpanFull(),
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
