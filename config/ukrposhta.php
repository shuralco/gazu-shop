<?php

return [
    /*
    |--------------------------------------------------------------------------
    | УкрПошта API Configuration
    |--------------------------------------------------------------------------
    |
    | Налаштування для інтеграції з API УкрПошти
    | Документація: https://ukrposhta.ua/api
    |
    */

    // Bearer токен для eCom API
    'bearer_token' => env('UKRPOSHTA_BEARER_TOKEN', '5a2c62b3-c867-358a-821e-dd2e7ba007aa'),

    // Counterparty токен
    'counterparty_token' => env('UKRPOSHTA_COUNTERPARTY_TOKEN', 'c7d523be-3c70-495a-a6ba-de4ff682751c'),

    // API ключ для StatusTracking
    'api_key' => env('UKRPOSHTA_API_KEY', 'f8bd626c-9d62-3243-9eea-1dbfc667e327'),

    // Базовий URL API
    'api_url' => env('UKRPOSHTA_API_URL', 'https://www.ukrposhta.ua/ecom/0.0.1/'),

    // API v1 URL для деяких методів
    'api_v1_url' => env('UKRPOSHTA_API_V1_URL', 'https://api.ukrposhta.ua/v1/'),

    // Режим тестування
    'sandbox' => env('UKRPOSHTA_SANDBOX', true),

    // Таймаут запитів (секунди)
    'timeout' => 30,

    // Кількість повторних спроб
    'retry_attempts' => 2,

    // Затримка між спробами (мс)
    'retry_delay' => 1000,

    /*
    |--------------------------------------------------------------------------
    | Дані відправника
    |--------------------------------------------------------------------------
    */

    'sender' => [
        'name' => env('UKRPOSHTA_SENDER_NAME', 'SimpleShop'),
        'phone' => env('UKRPOSHTA_SENDER_PHONE', '+380123456789'),
        'address' => env('UKRPOSHTA_SENDER_ADDRESS', 'вул. Хрещатик, 1, Київ'),
        'postcode' => env('UKRPOSHTA_SENDER_POSTCODE', '01001'),
        'region_id' => env('UKRPOSHTA_SENDER_REGION_ID', '80000000000'), // Київ
        'district_id' => env('UKRPOSHTA_SENDER_DISTRICT_ID', '80381000000'), // Київ
        'city_id' => env('UKRPOSHTA_SENDER_CITY_ID', '80000000001'), // Київ
    ],

    /*
    |--------------------------------------------------------------------------
    | Налаштування доставки
    |--------------------------------------------------------------------------
    */

    'delivery' => [
        // Базова вартість доставки (грн)
        'base_cost' => 45.0,

        // Вартість за кілограм (грн)
        'per_kg_cost' => 8.0,

        // Максимальна вага посилки (кг)
        'max_weight' => 30.0,

        // Мінімальна вага (кг)
        'min_weight' => 0.1,

        // Безкоштовна доставка від суми (грн)
        'free_shipping_threshold' => 1000.0,
    ],

    /*
    |--------------------------------------------------------------------------
    | Кешування
    |--------------------------------------------------------------------------
    */

    'cache' => [
        // Час кешування міст (секунди)
        'cities_ttl' => 3600, // 1 година

        // Час кешування відділень (секунди)
        'branches_ttl' => 1800, // 30 хвилин

        // Час кешування тарифів (секунди)
        'rates_ttl' => 600, // 10 хвилин
    ],
];
