<?php

/**
 * Preset: General-purpose e-commerce
 *
 * Apply: php artisan preset:apply general-shop
 *
 * Minimal feature set — just enough to run a generic online store. Add
 * features incrementally via `php artisan module:enable {name}`.
 */

return [
    'label' => 'General Shop',
    'description' => 'Базовий e-commerce без auto/beauty специфіки. Мінімум модулів — додавай за потребою.',
    'theme' => 'gazu',
    'modules_on' => [
        'novaposhta',
        'ukrposhta',
        'reviews',
        'comparison',
        'coupons',
    ],
    'modules_off' => [
        'gazu_garage',
        'auto_parts_seed',
        'wholesale',
        'multi_warehouse',
        'rozetka_delivery',
        'meest_express',
        'quick_fill',
        'loyalty',
        'feed_export',
    ],
    'display_settings' => [
        'show_car_selector' => false,
        'show_oem_search' => false,
        'hero_template' => 'product-grid',
    ],
];
