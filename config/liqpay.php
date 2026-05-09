<?php

return [
    /*
    |--------------------------------------------------------------------------
    | LiqPay Configuration
    |--------------------------------------------------------------------------
    |
    | Конфігурація для LiqPay платіжної системи
    |
    */

    'public_key' => env('LIQPAY_PUBLIC_KEY', 'sandbox_i00000000'),
    'private_key' => env('LIQPAY_PRIVATE_KEY', 'sandbox_demoPrivateKey'),
    'sandbox_mode' => env('LIQPAY_SANDBOX_MODE', true),
    'currency' => env('LIQPAY_CURRENCY', 'UAH'),

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    */

    'api_url' => 'https://www.liqpay.ua/api/request',
    'checkout_url' => 'https://www.liqpay.ua/api/3/checkout',

    /*
    |--------------------------------------------------------------------------
    | Fee Configuration
    |--------------------------------------------------------------------------
    */

    'fee_percentage' => env('LIQPAY_FEE_PERCENTAGE', 2.5),
    'min_amount' => env('LIQPAY_MIN_AMOUNT', 1.0),
    'max_amount' => env('LIQPAY_MAX_AMOUNT', 50000.0),

    /*
    |--------------------------------------------------------------------------
    | Supported Features
    |--------------------------------------------------------------------------
    */

    'features' => [
        'Картки Visa/MasterCard',
        'ПриватБанк',
        'Apple Pay',
        'Google Pay',
        'Розстрочка',
    ],
];
