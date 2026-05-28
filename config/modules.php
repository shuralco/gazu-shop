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

];
