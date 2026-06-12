<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Ліцензійний сервер Lionex (магазин розширень)
    |--------------------------------------------------------------------------
    | Звідки сторінка «Розширення» тягне каталог платних/преміум-модулів і
    | куди звертатиметься за купівлею. Зараз LicenseClient працює у стуб-режимі
    | (віддає 'catalog' нижче). Коли буде реальний сервер — заповни env, і
    | LicenseClient::catalog()/purchase() підуть по HTTP без змін UI.
    */
    'server_url' => env('MARKETPLACE_SERVER_URL', 'https://license.lionex.com.ua/api/v1'),

    // Ліцензійний ключ цього інстансу (видається Lionex). Поки порожній —
    // магазин показує стуб-каталог із позначкою «Незабаром».
    'license_key' => env('MARKETPLACE_LICENSE_KEY', ''),

    // HTTP timeout (сек) для звернень до ліцензійного сервера.
    'timeout' => (int) env('MARKETPLACE_TIMEOUT', 8),

    /*
    | Стуб-каталог доступних-для-встановлення розширень. Показується в секції
    | «Магазин розширень», поки не підключено реальний сервер. Поля картки:
    | key, name, description, category (= наша таксономія), price (₴/міс або
    | 'Безкоштовно'), icon (emoji), status ('available'|'soon').
    */
    'catalog' => [
        [
            'key' => 'prom_marketplace',
            'name' => 'Prom.ua маркетплейс',
            'description' => 'Двостороння синхронізація товарів, цін, залишків і замовлень з Prom.ua.',
            'category' => 'marketing',
            'price' => '299 ₴/міс',
            'icon' => '🛒',
            'status' => 'soon',
        ],
        [
            'key' => 'rozetka_marketplace',
            'name' => 'Rozetka маркетплейс',
            'description' => 'Вивантаження каталогу та прийом замовлень з Rozetka Marketplace.',
            'category' => 'marketing',
            'price' => '349 ₴/міс',
            'icon' => '🟢',
            'status' => 'soon',
        ],
        [
            'key' => 'binotel_telephony',
            'name' => 'Binotel / IP-телефонія',
            'description' => 'Спливаюча картка клієнта при дзвінку, історія викликів, запис розмов у CRM.',
            'category' => 'communication',
            'price' => '199 ₴/міс',
            'icon' => '📞',
            'status' => 'soon',
        ],
        [
            'key' => 'ai_descriptions_pro',
            'name' => 'AI-описи Pro',
            'description' => 'Масова генерація SEO-описів і характеристик товарів через LLM з власними промптами.',
            'category' => 'content',
            'price' => '249 ₴/міс',
            'icon' => '🤖',
            'status' => 'soon',
        ],
        [
            'key' => 'gdpr_consent',
            'name' => 'Згода на cookie (GDPR)',
            'description' => 'Банер згоди на cookie з категоріями та журналом згод для відповідності GDPR.',
            'category' => 'tools',
            'price' => 'Безкоштовно',
            'icon' => '🍪',
            'status' => 'soon',
        ],
    ],
];
