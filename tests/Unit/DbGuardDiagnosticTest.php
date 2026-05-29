<?php

namespace Tests\Unit;

use Tests\TestCase;

/**
 * Діагностика ізоляції тестової БД. НЕ використовує RefreshDatabase —
 * нічого не мігрує і не стирає. Лише перевіряє яку БД бачить тест-середовище.
 */
class DbGuardDiagnosticTest extends TestCase
{
    public function test_uses_sqlite_memory(): void
    {
        $default = config('database.default');
        $database = config("database.connections.{$default}.database");

        fwrite(STDERR, "\n[DB-DIAG] default={$default} database={$database}\n");

        $this->assertSame('sqlite', $default, 'Тести МУСЯТЬ бути на sqlite, а не на реальній БД!');
        $this->assertSame(':memory:', $database);
    }
}
