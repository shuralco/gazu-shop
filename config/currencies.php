<?php

return [
    'default' => env('DEFAULT_CURRENCY', 'UAH'),
    'available' => [
        'UAH' => ['symbol' => '₴', 'name' => 'Гривня', 'code' => 'UAH', 'rate' => 1.0, 'position' => 'after', 'decimals' => 0],
        'USD' => ['symbol' => '$', 'name' => 'US Dollar', 'code' => 'USD', 'rate' => 0.024, 'position' => 'before', 'decimals' => 2],
        'EUR' => ['symbol' => '€', 'name' => 'Euro', 'code' => 'EUR', 'rate' => 0.022, 'position' => 'before', 'decimals' => 2],
    ],
];
