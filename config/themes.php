<?php

/**
 * Theme registry. The "default" key is the fallback when nothing else is set
 * (no DisplaySetting row, no THEME env var). Full list of installed themes is
 * discovered automatically from themes/*\/theme.json — this file just sets
 * the boot-time default.
 *
 * @see themes/README.md
 */

return [
    'default' => env('THEME', 'gazu'),
];
