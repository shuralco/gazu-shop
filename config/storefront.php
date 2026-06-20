<?php

/**
 * Storefront cache/портативність — точки, що раніше були прибиті до GAZU.
 *
 * Новий шаблон/тема НЕ редагує core-файли кешу (GazuCacheProfile,
 * ResponseCacheObserver, EnsureWarm) — лише перевизначає цей config (або
 * відповідні ENV). Дефолти тут = поточна поведінка GAZU (behavior-preserving).
 *
 * @see app/Support/Cache/GazuCacheProfile.php
 * @see app/Observers/ResponseCacheObserver.php
 * @see app/Console/Commands/EnsureWarm.php
 */
return [

    /*
    | Префікси шляхів, які НІКОЛИ не кешуються повносторінковим ResponseCache
    | (user-specific / dynamic). Нова тема зі своїми slug'ами задає свій набір.
    */
    'excluded_cache_prefixes' => array_values(array_filter(array_map('trim', explode(',', (string) env(
        'STOREFRONT_EXCLUDED_PREFIXES',
        'admin,cart,checkout,account,login,register,logout,api,storage,livewire,sanctum,horizon,telescope,csrf-token,wishlist,obrane,kabinet,zamovlennya,garazh'
    ))))),

    /*
    | Ключові публічні сторінки, які guard (gazu:ensure-warm) пробує на «холод»
    | через loopback. Нова тема — свої ключові маршрути.
    */
    'warm_probe_paths' => array_values(array_filter(array_map('trim', explode(',', (string) env(
        'STOREFRONT_PROBE_PATHS',
        '/,/catalog'
    ))))),

    /*
    | DB-derived Cache::remember ключі, що стають stale при будь-якій зміні
    | storefront-моделі (мега-меню, статистика, featured-рядки головної…).
    | ResponseCacheObserver форгетить їх явно. Нова тема — свої ключі.
    |
    | АЛЬТЕРНАТИВА (рекомендовано для нових тем): тегувати свої derived-кеші
    | тегом `derived_cache_tag` нижче — тоді observer флашить їх БЕЗ списку.
    */
    'derived_cache_keys' => [
        'gazu_mega_carmakes',
        'gazu_shop_stats',
        'home:hero:makes',
        'home:new:8',
        'home:promo:8',
        'home:popular404',
        'cars:makes',
        'category_hierarchy',
        'mega_menu_structure',
        'display_settings_all',
    ],

    /*
    | Тег для storefront-derived кешів. Будь-який Cache::tags([цей_тег])->remember(...)
    | автоматично інвалідовується observer'ом — БЕЗ додавання у derived_cache_keys.
    | Це шлях, яким нова тема інтегрує кешування «з коробки».
    */
    'derived_cache_tag' => env('STOREFRONT_DERIVED_TAG', 'storefront'),

    /*
    | Тег fragment-кешу навігації/меню (відрендерений HTML нав-дерева).
    */
    'menu_cache_tag' => env('STOREFRONT_MENU_TAG', 'gazu-menu'),

    /*
    | Тег кешу каталогу (home-featured рядки + агрегати CatalogQuery). Флашиться
    | observer'ом разом із derived/menu — інакше лістинг лишався stale до TTL.
    */
    'catalog_cache_tag' => env('STOREFRONT_CATALOG_TAG', 'catalog'),
];
