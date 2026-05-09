<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cache Optimization Configuration
    |--------------------------------------------------------------------------
    |
    | Automatic cache optimization based on environment and usage patterns
    |
    */

    'auto_optimize' => env('CACHE_AUTO_OPTIMIZE', true),

    'strategies' => [
        'development' => [
            'default' => 'file',
            'views' => true,
            'config' => false,
            'routes' => false,
            'events' => false,
        ],

        'production' => [
            'default' => 'database',
            'views' => true,
            'config' => true,
            'routes' => true,
            'events' => true,
        ],

        'mobile' => [
            'default' => 'database',
            'views' => true,
            'config' => true,
            'routes' => true,
            'events' => true,
            'aggressive' => true,
        ],
    ],

    'ttl' => [
        'default' => 3600, // 1 hour
        'long_term' => 86400, // 24 hours
        'short_term' => 300, // 5 minutes
    ],

    'tags' => [
        'products' => ['products', 'categories', 'filters'],
        'users' => ['users', 'orders'],
        'settings' => ['display_settings', 'seo'],
        'menu' => ['mega_menu', 'navigation'],
    ],
];
