<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Auto Vite Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration enables automatic detection and handling of 
    | development vs production asset serving for both local and mobile access.
    |
    */

    'auto_detect' => env('VITE_AUTO_DETECT', true),
    
    'local_ip' => env('LOCAL_IP', '192.168.0.123'),
    
    'ports' => [
        'app' => env('APP_PORT', 8003),
        'vite' => env('VITE_PORT', 5173),
    ],
    
    'force_production_assets' => env('FORCE_PRODUCTION_ASSETS', false),
    
    'mobile_patterns' => [
        'Mobile', 'Android', 'iPhone', 'iPad', 'BlackBerry',
        'Windows Phone', 'Opera Mini', 'IEMobile', 'webOS',
    ],
];