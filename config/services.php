<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'liqpay' => [
        'public_key' => env('LIQPAY_PUBLIC_KEY'),
        'private_key' => env('LIQPAY_PRIVATE_KEY'),
        'sandbox' => env('LIQPAY_SANDBOX', true),
    ],

    'wayforpay' => [
        'merchant_account' => env('WAYFORPAY_MERCHANT_ACCOUNT'),
        'merchant_secret_key' => env('WAYFORPAY_MERCHANT_SECRET_KEY'),
        'merchant_domain' => env('WAYFORPAY_MERCHANT_DOMAIN', env('APP_URL')),
        'sandbox' => env('WAYFORPAY_SANDBOX', true),
    ],

    'monobank' => [
        'merchant_id' => env('MONOBANK_MERCHANT_ID'),
        'api_token' => env('MONOBANK_API_TOKEN'),
        'webhook_public_key' => env('MONOBANK_WEBHOOK_PUBLIC_KEY'),
        'sandbox' => env('MONOBANK_SANDBOX', true),
    ],

    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN', ''),
        'chat_id' => env('TELEGRAM_CHAT_ID', ''),
        'enabled' => env('TELEGRAM_ENABLED', false),
    ],

    'nova_poshta' => [
        'api_key' => env('NOVA_POSHTA_API_KEY', ''),
        'api_url' => env('NOVA_POSHTA_API_URL', 'https://api.novaposhta.ua/v2.0/json/'),
        'sender_ref' => env('NOVA_POSHTA_SENDER_REF', ''),
        'sender_city_ref' => env('NOVA_POSHTA_SENDER_CITY_REF', ''),
        'sender_warehouse_ref' => env('NOVA_POSHTA_SENDER_WAREHOUSE_REF', ''),
        'default_weight' => env('NOVA_POSHTA_DEFAULT_WEIGHT', 0.5),
        'default_cargo_type' => env('NOVA_POSHTA_DEFAULT_CARGO_TYPE', 'Parcel'),
    ],

];
