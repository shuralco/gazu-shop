<?php

return [
    'enabled' => env('CHECKBOX_ENABLED', false),
    'api_url' => env('CHECKBOX_API_URL', 'https://api.checkbox.ua/api/v1'),
    'login' => env('CHECKBOX_LOGIN', ''),
    'password' => env('CHECKBOX_PASSWORD', ''),
    'license_key' => env('CHECKBOX_LICENSE_KEY', ''),
    'cashier_name' => env('CHECKBOX_CASHIER_NAME', 'Касир'),
    'tax_rate' => env('CHECKBOX_TAX_RATE', 20), // ПДВ 20%
];
