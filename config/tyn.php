<?php

return [
    // Вимикач: false або порожній ключ/секрет = перевірку пропущено.
    'enabled' => env('TYN_ENABLED', true),
    'site_key' => env('TYN_SITE_KEY'),
    'secret' => env('TYN_SECRET'),
    'base_url' => env('TYN_BASE_URL', 'https://c.tyn.com.ua'),
    // soft (типово): прийняти будь-яку спробу; enforce: лише correct=true.
    'mode' => env('TYN_MODE', 'soft'),
    // Тайм-аут запиту до сервісу, секунди.
    'timeout' => 2,
    // Тексти відмов (перевизначення).
    'messages' => [],
];
