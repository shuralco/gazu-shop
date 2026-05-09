<?php

return [
    'default' => [
        'title_template' => '%s | %s',
        'title_separator' => ' | ',
        'site_name' => config('app.name', 'SimpleShop'),
        'description_max_length' => 160,
        'keywords_max_count' => 10,
        'robots' => 'index,follow',
    ],

    'meta' => [
        'charset' => 'UTF-8',
        'viewport' => 'width=device-width, initial-scale=1',
        'robots' => 'index,follow',
        'language' => 'uk',
        'author' => 'SimpleShop',
        'generator' => 'Laravel 12 + SimpleShop',
    ],

    'open_graph' => [
        'enabled' => true,
        'type' => 'website',
        'site_name' => config('app.name', 'SimpleShop'),
        'locale' => 'uk_UA',
        'image_width' => 1200,
        'image_height' => 630,
        'default_image' => '/assets/img/og-default.jpg',
    ],

    'twitter' => [
        'enabled' => true,
        'card' => 'summary_large_image',
        'site' => '@simpleshop_ua',
        'creator' => '@simpleshop_ua',
    ],

    'structured_data' => [
        'enabled' => true,
        'organization' => [
            'name' => config('app.name', 'SimpleShop'),
            'url' => config('app.url'),
            'logo' => config('app.url').'/assets/img/logo.png',
            'contact_point' => [
                'telephone' => '+380123456789',
                'contact_type' => 'customer service',
                'available_language' => ['Ukrainian', 'English'],
            ],
        ],
        'website' => [
            'name' => config('app.name', 'SimpleShop'),
            'url' => config('app.url'),
            'description' => 'Інтернет-магазин якісних товарів з доставкою по Україні',
            'potential_action' => [
                'search' => [
                    'target' => config('app.url').'/search?q={search_term_string}',
                    'query_input' => 'required name=search_term_string',
                ],
            ],
        ],
    ],

    'sitemap' => [
        'enabled' => true,
        'cache_duration' => 24 * 60, // 24 hours in minutes
        'default_priority' => 0.5,
        'default_changefreq' => 'monthly',
        'priorities' => [
            'home' => 1.0,
            'category' => 0.8,
            'product' => 0.7,
            'page' => 0.6,
            'blog' => 0.5,
        ],
        'changefreq' => [
            'home' => 'daily',
            'category' => 'weekly',
            'product' => 'daily',
            'page' => 'monthly',
            'blog' => 'weekly',
        ],
        'exclude_patterns' => [
            '/admin/*',
            '/login',
            '/register',
            '/cart',
            '/checkout',
            '/user/*',
            '/search',
        ],
    ],

    'canonical' => [
        'enabled' => true,
        'force_trailing_slash' => false,
        'force_lowercase' => true,
        'remove_query_params' => ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'gclid', 'fbclid'],
    ],

    'cache' => [
        'enabled' => true,
        'prefix' => 'seo_meta_',
        'ttl' => 60 * 60 * 24, // 24 hours
        'store' => null, // Use default cache store
    ],

    'generator' => [
        'auto_generate' => true,
        'languages' => ['uk', 'en'],
        'title_templates' => [
            'uk' => [
                'home' => '%s - Інтернет-магазин якісних товарів',
                'category' => '%s | %s',
                'product' => 'Купити %s за %s грн | %s',
                'page' => '%s | %s',
            ],
            'en' => [
                'home' => '%s - Online Store for Quality Products',
                'category' => '%s | %s',
                'product' => 'Buy %s for %s UAH | %s',
                'page' => '%s | %s',
            ],
        ],
        'description_templates' => [
            'uk' => [
                'home' => 'Великий вибір якісних товарів за найкращими цінами. Швидка доставка по Україні. Гарантія якості.',
                'category' => 'Великий вибір товарів у категорії %s. Швидка доставка по Україні. Гарантія якості.',
                'product' => 'Купити %s за найкращою ціною %s грн. %s. Швидка доставка.',
                'page' => '%s - корисна інформація від SimpleShop.',
            ],
            'en' => [
                'home' => 'Wide selection of quality products at the best prices. Fast delivery across Ukraine. Quality guarantee.',
                'category' => 'Wide selection of products in %s category. Fast delivery across Ukraine. Quality guarantee.',
                'product' => 'Buy %s at the best price %s UAH. %s. Fast delivery.',
                'page' => '%s - useful information from SimpleShop.',
            ],
        ],
    ],

    'validation' => [
        'title_min_length' => 10,
        'title_max_length' => 60,
        'description_min_length' => 50,
        'description_max_length' => 160,
        'keywords_max_count' => 10,
        'image_max_size' => 5120, // KB
        'image_formats' => ['jpg', 'jpeg', 'png', 'webp'],
    ],

    'analytics' => [
        'google_analytics' => env('GOOGLE_ANALYTICS_ID'),
        'google_tag_manager' => env('GOOGLE_TAG_MANAGER_ID'),
        'facebook_pixel' => env('FACEBOOK_PIXEL_ID'),
        'yandex_metrika' => env('YANDEX_METRIKA_ID'),
    ],
];
