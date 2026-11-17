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
    // Серверні події (/v1/track: order, order_status, gate, identify лише з uid, catalog); типово вимкнено.
    'track' => env('TYN_TRACK', false),
    // Браузерний збирач https://<base>/w/t.js на сторінках вітрини; типово вимкнено.
    'collector' => env('TYN_COLLECTOR', false),
    // Заслін на вхід в адмінку: off (типово) | all. Лише для НЕзалогіненого, fail-open.
    'gate_admin' => env('TYN_GATE_ADMIN', 'off'),
    // Тексти відмов (перевизначення).
    'messages' => [],
    // Заслін на весь сайт: off (типово) | observe (лише позначає «було б показано») | suspicious | all.
    'gate' => env('TYN_GATE', 'off'),
    // Додаткові винятки заслону: шаблони шляхів з * (без початкової скісної), через кому в TYN_GATE_SKIP.
    'gate_skip' => array_values(array_filter(array_map('trim', explode(',', (string) env('TYN_GATE_SKIP', ''))))),
    // Додаткові боти (підрядки User-Agent), яким заслін не показується; TYN_GATE_BOTS через кому.
    'gate_bots' => array_values(array_filter(array_map('trim', explode(',', (string) env('TYN_GATE_BOTS', ''))))),
    // Скільки годин діє позначка проходу (1..720).
    'gate_ttl_hours' => (int) env('TYN_GATE_TTL_HOURS', 12),
    // Прив'язка позначки до підмережі клієнта (на мобільних мережах може заважати).
    'gate_bind' => env('TYN_GATE_BIND', false),
];
