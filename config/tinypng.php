<?php

return [
    'enabled' => env('TINYPNG_ENABLED', true),
    'api_key' => env('TINYPNG_API_KEY', ''),
    'max_width' => env('TINYPNG_MAX_WIDTH', 1920),
    'quality' => env('TINYPNG_QUALITY', 80),
    'convert_to_webp' => env('TINYPNG_CONVERT_WEBP', true),
];
