<?php

/**
 * Preset: Auto-Parts shop (GAZU profile)
 *
 * Apply: php artisan preset:apply auto-parts
 *
 * Activates the auto-parts business model: car-make/model selectors, garage
 * (UserCar), wholesale prices for service stations, full NP + UP integration,
 * product feeds for marketplaces.
 */

return [
    'label' => 'Auto-Parts Shop',
    'description' => 'Магазин автозапчастин (як GAZU): car-make/model selectors, гараж, опт для СТО, ТТН.',
    'theme' => 'gazu',
    'modules_on' => [
        'novaposhta',
        'ukrposhta',
        'multi_warehouse',
        'gazu_garage',
        'wholesale',
        'reviews',
        'comparison',
        'coupons',
        'loyalty',
        'feed_export',
        'quick_fill',
        'auto_parts_seed',
    ],
    'modules_off' => [
        'rozetka_delivery',
        'meest_express',
    ],
    'display_settings' => [
        'shop_brand_name' => 'GAZU',
        'show_car_selector' => true,
        'show_oem_search' => true,
        'hero_template' => 'car-selector',
    ],
];
