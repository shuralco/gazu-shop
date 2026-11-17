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
    // Події форм у сервіс (/v1/event); працюють лише коли є ключ і секрет.
    'events' => env('TYN_EVENTS', true),
    // Облік відвідувань вітрини (/v1/traffic); типово вимкнено.
    'traffic' => env('TYN_TRAFFIC', false),
    // Тексти відмов (перевизначення).
    'messages' => [],
];
