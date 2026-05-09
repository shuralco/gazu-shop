<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Delengine API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Delengine delivery services directory API
    |
    */

    'api_key' => env('DELENGINE_API_KEY', 'v4n208uaysugpqe6v3ijelusl601fduv'),

    'base_url' => env('DELENGINE_BASE_URL', 'https://api.delengine.com/v1.0'),

    /*
    |--------------------------------------------------------------------------
    | Company UUIDs for delivery services
    |--------------------------------------------------------------------------
    */

    'companies' => [
        'novaposhta' => '44666c78-5709-40d0-a2fd-96d16cb55388',
        'justin' => 'feb86445-3635-4416-bdca-54e4aabc921b',
        'delivery' => 'f1d02802-e49c-455a-80d5-5a7979d9e76c',
        'sat' => '5390ff28-9ebf-47b7-9d9d-7346804f1f84',
        'meest' => 'dd4108b2-9b11-4682-9c6b-10ed4313262b',
        'ukrposhta' => '64054fa0-8584-4492-8425-142156ce3110',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    */

    'cache_ttl' => 24, // hours
];
