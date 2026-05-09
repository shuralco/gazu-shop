<?php

return [
    /*
    |--------------------------------------------------------------------------
    | УкрПошта API Keys Backup - Всі доступні ключі
    |--------------------------------------------------------------------------
    |
    | Файл містить всі відомі API ключі для УкрПошти
    | Використовується для резервного копіювання та порівняння
    |
    */

    'existing_keys' => [
        // Поточні ключі в системі
        'ecom_bearer' => '5a2c62b3-c867-358a-821e-dd2e7ba007aa',
        'statustracking_api_key' => 'f8bd626c-9d62-3243-9eea-1dbfc667e327',
        'counterparty_token' => 'c7d523be-3c70-495a-a6ba-de4ff682751c',
        'delengine_api_key' => 'v4n208uaysugpqe6v3ijelusl601fduv',
    ],

    'new_keys_from_user' => [
        // Нові ключі надані користувачем
        'ecom_bearer' => '90c6198c-3718-3708-8354-f6be2a97bd76',
        'statustracking_bearer' => 'a644e8d9-844e-307c-a3f5-7d110c49b2ea',
        'user_token' => '72e36abe-d9dc-41dd-8a3d-cb0fe538f322',
    ],

    'test_results' => [
        'address_classifier' => [
            'status' => 'working',
            'method' => 'public_api_no_auth',
            'package' => 'kolirt/laravel-ukrposhta',
            'endpoint' => 'https://www.ukrposhta.ua/address-classifier/',
            'note' => 'Працює стабільно без Bearer токенів',
        ],
        'ecom_api' => [
            'status' => 'not_working',
            'existing_key' => '404_error',
            'new_key' => '404_error',
            'note' => 'Потребує правильні endpoints з офіційної документації',
        ],
        'statustracking_api' => [
            'status' => 'not_working',
            'new_key' => '404_error',
            'note' => 'Потребує правильні endpoints для StatusTracking',
        ],
    ],

    'recommendations' => [
        'address_classifier' => 'Залишити як є - працює стабільно',
        'ecom_api' => 'Потрібні правильні endpoints для тестування нових ключів',
        'statustracking' => 'Потрібні правильні endpoints для тестування',
        'priority' => 'Зосередитись на Address Classifier що працює, інші API - вторинні',
    ],

    'working_endpoints' => [
        'cities' => 'https://www.ukrposhta.ua/address-classifier/get_city_by_region_id_and_district_id_and_city_ua',
        'post_offices' => 'https://www.ukrposhta.ua/address-classifier/get_postoffices_by_postindex',
        'regions' => 'https://www.ukrposhta.ua/address-classifier/get_regions_by_region_ua',
        'districts' => 'https://www.ukrposhta.ua/address-classifier/get_districts_by_region_id_and_district_ua',
    ],

    'non_working_endpoints' => [
        'ecom_clients' => 'https://www.ukrposhta.ua/ecom/0.0.1/clients',
        'statustracking' => 'https://www.ukrposhta.ua/status-tracking/0.0.1/statuses',
        'dev_api' => 'https://dev.ukrposhta.ua/api/statuses',
        'api_v1' => 'https://www.ukrposhta.ua/api/v1/cities',
    ],
];
