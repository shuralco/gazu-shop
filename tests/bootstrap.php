<?php

/**
 * Phpunit bootstrap — БУЛЕТПРУФ ізоляція тестової БД.
 *
 * Інцидент 2026-05-29: контейнер задає DB_CONNECTION=mysql / DB_DATABASE=gazu_shop
 * як OS-env (docker-compose). phpunit `<env>` навіть з force="true" НЕ перекрив їх
 * при запуску через `php artisan test`, тож тести з RefreshDatabase зробили
 * migrate:fresh на РЕАЛЬНІЙ dev-БД і стерли дані.
 *
 * Тут ми ПРИМУСОВО переписуємо env-змінні у PHP ДО того як Laravel їх прочитає —
 * на рівні putenv/$_ENV/$_SERVER. Це перекриває OS-env гарантовано, незалежно
 * від способу запуску (artisan test, vendor/bin/phpunit, IDE).
 */

$forced = [
    'APP_ENV'                => 'testing',
    'DB_CONNECTION'          => 'sqlite',
    'DB_DATABASE'            => ':memory:',
    'DB_HOST'                => '127.0.0.1',
    'CACHE_STORE'            => 'array',
    'CACHE_DRIVER'           => 'array',
    'SESSION_DRIVER'         => 'array',
    'QUEUE_CONNECTION'       => 'sync',
    'RESPONSE_CACHE_ENABLED' => 'false',
    'SCOUT_DRIVER'           => 'null',
    'REDIS_HOST'             => '127.0.0.1',
    'MAIL_MAILER'            => 'array',
    'TELESCOPE_ENABLED'      => 'false',
    'PULSE_ENABLED'          => 'false',
];

foreach ($forced as $key => $value) {
    putenv("{$key}={$value}");
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

// ОСТАННІЙ запобіжник: якщо щось досі вказує на не-тестову БД — НЕ стартуємо.
if (($_ENV['DB_CONNECTION'] ?? null) !== 'sqlite' || ($_ENV['DB_DATABASE'] ?? null) !== ':memory:') {
    fwrite(STDERR, "\n⛔ Test bootstrap: не вдалося форснути sqlite :memory: — abort, щоб не зачепити реальну БД.\n");
    exit(1);
}

require __DIR__.'/../vendor/autoload.php';
