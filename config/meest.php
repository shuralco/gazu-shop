<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Meest Express Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Meest Express API integration
    |
    */

    'login' => env('MEEST_LOGIN'),

    'password' => env('MEEST_PASSWORD'),

    'sender_city_id' => env('MEEST_SENDER_CITY_ID', 1),

    'delivery' => [
        'default_weight' => 0.5, // kg
        'default_dimensions' => [
            'length' => 20, // cm
            'width' => 15,  // cm
            'height' => 10, // cm
        ],
        'max_weight' => 30.0, // kg
        'estimated_delivery_days' => 2,
        'working_hours' => '08:00-20:00',
        'support_phone' => '+380800508800',
    ],

    'logging' => [
        'enabled' => env('MEEST_LOGGING', true),
        'channel' => env('LOG_CHANNEL', 'stack'),
    ],
];
