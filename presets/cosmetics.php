<?php

/**
 * Preset: Cosmetics / Beauty shop
 *
 * Apply: php artisan preset:apply cosmetics
 *
 * Activates a typical cosmetics/beauty business model: loyalty + reviews +
 * coupons (high-conversion levers), no auto-parts/garage clutter.
 */

return [
    'label' => 'Cosmetics / Beauty Shop',
    'description' => 'Магазин косметики/краси: лояльність, відгуки, купони, без авто-фішок.',
    'theme' => 'gazu', // fallback to gazu until a dedicated "cosmetics" theme exists
    'modules_on' => [
        'novaposhta',
        'ukrposhta',
        'loyalty',
        'reviews',
        'comparison',
        'coupons',
        'feed_export',
    ],
    'modules_off' => [
        'gazu_garage',
        'auto_parts_seed',
        'wholesale',
        'multi_warehouse',
        'rozetka_delivery',
        'meest_express',
        'quick_fill',
    ],
    'display_settings' => [
        'shop_brand_name' => 'Beauty',
        'show_car_selector' => false,
        'show_oem_search' => false,
        'hero_template' => 'product-grid',
    ],
];
