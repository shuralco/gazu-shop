<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Rozetka Delivery Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Rozetka Delivery API integration
    |
    */

    'api_url' => env('ROZETKA_API_URL', 'https://rz-delivery-octopus.rozetka.ua/api/'),

    'api_key' => env('ROZETKA_API_KEY'),

    'username' => env('ROZETKA_USERNAME'),

    'password' => env('ROZETKA_PASSWORD'),

    'sandbox' => env('ROZETKA_SANDBOX', true),

    'merchant_id' => env('ROZETKA_MERCHANT_ID'),

    'secret_key' => env('ROZETKA_SECRET_KEY'),

    'payment' => [
        'currency' => 'UAH',
        'language' => 'uk',
        'success_url' => env('APP_URL').'/payment/rozetka/success',
        'fail_url' => env('APP_URL').'/payment/rozetka/fail',
        'callback_url' => env('APP_URL').'/payment/rozetka/callback',
    ],

    'delivery' => [
        'default_weight' => 0.5, // kg
        'default_dimensions' => [
            'length' => 20, // cm
            'width' => 15,  // cm
            'height' => 10, // cm
        ],
        'max_weight' => 25.0, // kg
        'estimated_delivery_days' => 1,
        'working_hours' => '09:00-21:00',
        'support_phone' => '+380800303344',
    ],

    'logging' => [
        'enabled' => env('ROZETKA_LOGGING', true),
        'channel' => env('LOG_CHANNEL', 'stack'),
    ],
];
