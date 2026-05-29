<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * САПОБІЖНИК: жоден тест не сміє бігти проти не-sqlite (реальної) БД.
     *
     * Інцидент 2026-05-29: phpunit `<env>` без force="true" не перекрив
     * OS-env DB_CONNECTION=mysql (docker-compose), тести з RefreshDatabase
     * зробили migrate:fresh на РЕАЛЬНІЙ dev-БД gazu_shop і стерли дані.
     *
     * Друга лінія захисту: якщо default-конекшн НЕ sqlite і БД не виглядає
     * тестовою — негайно валимо прогін ДО того як RefreshDatabase щось дропне.
     */
    protected function setUp(): void
    {
        // Перевіряємо ДО parent::setUp() (там RefreshDatabase мігрує) і через
        // getenv() — бо контейнер Laravel ще не зібраний, config() недоступний.
        $conn = getenv('DB_CONNECTION') ?: ($_ENV['DB_CONNECTION'] ?? '');
        $db = getenv('DB_DATABASE') ?: ($_ENV['DB_DATABASE'] ?? '');

        $isSafe = $conn === 'sqlite'
            || $db === ':memory:'
            || str_ends_with((string) $db, '_testing')
            || str_contains((string) $db, 'test');

        if (! $isSafe) {
            fwrite(STDERR, "\n\n  ⛔ ABORT: тести на НЕ-тестовій БД ".
                "(DB_CONNECTION={$conn}, DB_DATABASE={$db}).\n".
                "  Це знищило б реальні дані. Перевір tests/bootstrap.php + force=\"true\" у phpunit.xml.\n\n");
            exit(1);
        }

        parent::setUp();
    }
}
