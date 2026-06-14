<?php

/**
 * Module registry. Toggle individual features without removing code.
 *
 * Set MODULE_{NAME}=false in .env to disable a feature for a specific
 * client deploy. Disabled modules:
 *   - hide their Filament navigation entries
 *   - skip their scheduled jobs
 *   - block their routes via the `module:` middleware
 *   - return false from module('name')->enabled() helper
 *
 * Data IS preserved — re-enable restores everything intact.
 */

return [

    'multi_warehouse' => [
        'name' => 'Багатоскладовий облік',
        'description' => 'Декілька власних складів, переміщення, приходування, авто-списання при ТТН',
        'enabled' => env('MODULE_MULTI_WAREHOUSE', true),
        'requires' => [],
    ],

    'loyalty' => [
        'name' => 'Програма лояльності',
        'description' => 'Бонусні бали, рівні клієнтів, історія транзакцій',
        'enabled' => env('MODULE_LOYALTY', true),
        'requires' => [],
    ],

    'wholesale' => [
        'name' => 'Гуртові ціни (B2B)',
        'description' => 'Групи клієнтів, спеціальні ціни, гуртові мінімалки',
        'enabled' => env('MODULE_WHOLESALE', true),
        'requires' => [],
    ],

    'comparison' => [
        'name' => 'Порівняння товарів',
        'description' => 'Кнопка «порівняти», окрема сторінка зі списком та характеристиками',
        'enabled' => env('MODULE_COMPARISON', true),
        'requires' => [],
    ],

    'coupons' => [
        'name' => 'Купони / промокоди',
        'description' => 'Знижки за кодами на checkout',
        'enabled' => env('MODULE_COUPONS', true),
        'requires' => [],
    ],

    'reviews' => [
        'name' => 'Відгуки про товари',
        'description' => 'Користувацькі рейтинги, модерація з адмінки',
        'enabled' => env('MODULE_REVIEWS', true),
        'requires' => [],
    ],

    'novaposhta' => [
        'name' => 'Нова Пошта',
        'description' => 'API + selector + ТТН + scan-sheets + tracking',
        'enabled' => env('MODULE_NOVAPOSHTA', true),
        'requires' => [],
    ],

    'ukrposhta' => [
        'name' => 'УкрПошта',
        'description' => 'API + selector + eCom ТТН',
        'enabled' => env('MODULE_UKRPOSHTA', true),
        'requires' => [],
    ],

    'rozetka_delivery' => [
        'name' => 'Rozetka Delivery',
        'description' => 'Інтеграція з Розетка Delivery',
        'enabled' => env('MODULE_ROZETKA_DELIVERY', false),
        'requires' => [],
    ],

    'meest_express' => [
        'name' => 'Meest Express',
        'description' => 'Інтеграція з Meest',
        'enabled' => env('MODULE_MEEST_EXPRESS', false),
        'requires' => [],
    ],

    'auto_parts_seed' => [
        'name' => 'Auto-parts demo каталог',
        'description' => 'Готовий пакет з категоріями, брендами, товарами автозапчастин',
        'enabled' => env('MODULE_AUTO_PARTS_SEED', false),
        'requires' => [],
    ],

    'quick_fill' => [
        'name' => 'Швидке наповнення (Chinese supplier)',
        'description' => 'Excel-стиль набивання товарів з 1688/Aliexpress: закупка в CNY/USD, авто-розрахунок ціни, CSV-імпорт',
        'enabled' => env('MODULE_QUICK_FILL', true),
        'requires' => [],
    ],

    'feed_export' => [
        'name' => 'Експорт фідів (Rozetka/Prom/OLX)',
        'description' => 'Генерація YML/XML фідів для Rozetka, Prom, OLX, Google Shopping. Адмін-панель з фільтрами та регенерацією.',
        'enabled' => env('MODULE_FEED_EXPORT', true),
        'requires' => [],
    ],

    'gazu_garage' => [
        'name' => 'Гараж користувача (GAZU)',
        'description' => 'Користувацька модель UserCar: збереження своїх авто, primary, фільтр "Ваш автомобіль" у каталозі, "Підходить для X" на product page. Вимкнено за замовчуванням до активації клієнтом.',
        'enabled' => env('MODULE_GAZU_GARAGE', false),
        'requires' => [],
    ],

    'related_products' => [
        'name' => 'Пов\'язані товари (варіанти)',
        'description' => 'Variant picker на картці товару, AJAX-перемикання, RelationManager в адмінці, авто-зв\'язування за характеристиками (Rozetka-стиль).',
        'enabled' => env('MODULE_RELATED_PRODUCTS', true),
        'requires' => [],
    ],

    'payments' => [
        'name' => 'Платіжні шлюзи',
        'description' => 'LiqPay, WayForPay, Monobank — українські gateways з admin CRUD + callback handler.',
        'enabled' => env('MODULE_PAYMENTS', true),
        'requires' => [],
    ],

    'shipping_core' => [
        'name' => 'Доставка — ядро',
        'description' => 'Базові shipping моделі/services (Shipment, ShippingMethod, Provider, Zone, Warehouse) — фундамент для НП/УП/Meest модулів.',
        'enabled' => env('MODULE_SHIPPING_CORE', true),
        'requires' => [],
    ],

    'checkout_settings' => [
        'name' => 'Налаштування кошика та оформлення',
        'description' => 'Мін. сума замовлення, поріг безкоштовної доставки, 1-клік/промокоди, ліміти кількості, логіка полів checkout та кастомні поля.',
        'enabled' => env('MODULE_CHECKOUT_SETTINGS', true),
        'requires' => [],
    ],

    'faq' => [
        'name' => 'FAQ — Часті питання',
        'description' => 'Сторінка частих питань з admin CRUD.',
        'enabled' => env('MODULE_FAQ', true),
        'requires' => [],
    ],

    'info_pages' => [
        'name' => 'Інфо-сторінки',
        'description' => 'about/delivery/warranty/privacy/terms/careers/certificates/offer.',
        'enabled' => env('MODULE_INFO_PAGES', true),
        'requires' => [],
    ],

    'email_templates' => [
        'name' => 'Email-шаблони',
        'description' => 'Editable email templates для order/shipment/registration notifications.',
        'enabled' => env('MODULE_EMAIL_TEMPLATES', true),
        'requires' => [],
    ],

    'turbosms' => [
        'name' => 'TurboSMS (SMS + Viber)',
        'description' => 'SMS/Viber через шлюз TurboSMS: повідомлення по подіях замовлення, шаблони текстів у БД, журнал відправок, гібрид Viber→SMS.',
        'enabled' => env('MODULE_TURBOSMS', false),
        'requires' => [],
    ],

    'callback' => [
        'name' => 'Замовити дзвінок',
        'description' => 'Форма callback-запиту з phone + name + admin CRUD.',
        'enabled' => env('MODULE_CALLBACK', true),
        'requires' => [],
    ],

    'blog' => [
        'name' => 'Блог',
        'description' => 'Статті блогу + категорії. Маршрути /blog, /blog/rubryka/{slug}.',
        'enabled' => env('MODULE_BLOG', true),
        'requires' => [],
    ],

    'wishlist' => [
        'name' => 'Список бажань',
        'description' => 'Wishlist для users + guest localStorage, merge при логіні.',
        'enabled' => env('MODULE_WISHLIST', true),
        'requires' => [],
    ],

    'stock_notifications' => [
        'name' => 'Сповіщення про надходження',
        'description' => 'Підписка на email коли товар буде в наявності.',
        'enabled' => env('MODULE_STOCK_NOTIFICATIONS', true),
        'requires' => [],
    ],

    'cms_pages' => [
        'name' => 'CMS-сторінки',
        'description' => 'Editable Page model + Resource (storage для blog статей теж).',
        'enabled' => env('MODULE_CMS_PAGES', true),
        'requires' => [],
    ],

    'recently_viewed' => [
        'name' => 'Нещодавно переглянуті',
        'description' => 'Service для збереження 8 останніх товарів у session.',
        'enabled' => env('MODULE_RECENTLY_VIEWED', true),
        'requires' => [],
    ],

    'seo' => [
        'name' => 'SEO meta + sitemap',
        'description' => 'SEO Resource + 4 admin Pages + 3 artisan commands.',
        'enabled' => env('MODULE_SEO', true),
        'requires' => [],
    ],

    'search' => [
        'name' => 'Пошук (Meilisearch)',
        'description' => 'SearchService + LemmatizationService + 3 commands + admin page.',
        'enabled' => env('MODULE_SEARCH', true),
        'requires' => [],
    ],

    'ai_content' => [
        'name' => 'AI-генератор контенту',
        'description' => 'AI-driven генерація описів товарів через Delengine API.',
        'enabled' => env('MODULE_AI_CONTENT', false),
        'requires' => [],
    ],

    'homepage_builder' => [
        'name' => 'Конструктор головної',
        'description' => 'HomepageBuilder + MegaMenuEditor + HomepageModule + MegaMenuBuilder service.',
        'enabled' => env('MODULE_HOMEPAGE_BUILDER', true),
        'requires' => [],
    ],

    'theme_settings' => [
        'name' => 'Theme + Visual Settings',
        'description' => 'ThemeSettings + GazuVisualSettings + HeaderService + theme:set command.',
        'enabled' => env('MODULE_THEME_SETTINGS', true),
        'requires' => [],
    ],

    'cache_manager' => [
        'name' => 'Управління кешем',
        'description' => 'CacheManagement + CacheSettings Filament Pages + 2 Services.',
        'enabled' => env('MODULE_CACHE_MANAGER', true),
        'requires' => [],
    ],

    // Key must match the module directory/manifest name ('integrations') so the
    // ModuleManager waterfall actually controls it. Legacy MODULE_INTEGRATIONS_PANEL
    // env still honoured as a fallback for existing deploys.
    'integrations' => [
        'name' => 'Інтеграції (загальна панель)',
        'description' => 'IntegrationsPage + IntegrationConfigPage — список 3rd-party інтеграцій.',
        'enabled' => env('MODULE_INTEGRATIONS', env('MODULE_INTEGRATIONS_PANEL', true)),
        'requires' => [],
    ],

    'fiscal_checkbox' => [
        'name' => 'Фіскалізація Checkbox',
        'description' => 'Український фіскальний оператор Checkbox + open/close shift commands.',
        'enabled' => env('MODULE_FISCAL_CHECKBOX', false),
        'requires' => [],
    ],

    'image_optimization' => [
        'name' => 'Оптимізація зображень',
        'description' => 'TinyPng API + Media model + images:optimize command.',
        'enabled' => env('MODULE_IMAGE_OPTIMIZATION', false),
        'requires' => [],
    ],

    'batch_editor' => [
        'name' => 'Batch editor',
        'description' => 'Excel-стиль масове редагування товарів + CsvImportService.',
        'enabled' => env('MODULE_BATCH_EDITOR', true),
        'requires' => [],
    ],

    'currency' => [
        'name' => 'Курси валют',
        'description' => 'CurrencyService + UpdateCurrencyRates cron command.',
        'enabled' => env('MODULE_CURRENCY', true),
        'requires' => [],
    ],

    'error_pages' => [
        'name' => 'Налаштування 404/500',
        'description' => 'Error404Settings Filament Page.',
        'enabled' => env('MODULE_ERROR_PAGES', true),
        'requires' => [],
    ],

    'telegram_notify' => [
        'name' => 'Telegram сповіщення',
        'description' => 'TelegramService + Telegram/ namespace + telegram:test command.',
        'enabled' => env('MODULE_TELEGRAM_NOTIFY', true),
        'requires' => [],
    ],

    'layout_builder' => [
        'name' => 'Конструктор зон layout',
        'description' => 'OpenCart-стиль layout positions: admin призначає блоки (HTML/банер/featured) у іменовані зони storefront (layout.home.top, layout.home.bottom, product.sidebar) через Hooks API.',
        'enabled' => env('MODULE_LAYOUT_BUILDER', true),
        'requires' => [],
    ],

    // Преміум (платний) модуль. Для клієнтів, що НЕ придбали — вимкнути через
    // MODULE_MULTILANG=false у .env їхнього деплою (або тумблером в адмінці).
    // Коли вимкнено: сайт працює лише дефолтною мовою, перемикач прихований,
    // translatable-поля в адмінці показують одну мову.
    'multilang' => [
        'name' => 'Мультимовність (Преміум)',
        'description' => 'Багатомовний storefront: перемикач мов у шапці + локалізація контенту + керування перекладами в адмінці. Платний модуль.',
        'enabled' => env('MODULE_MULTILANG', true),
        'requires' => [],
        'premium' => true,
        'price' => 1500,
        'currency' => 'UAH',
    ],

];
