<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Вичищає синтетичні демо-дані перед продакшн-запуском.
 *
 * Цільові маркери (безпечні — не зачіпають реальні дані):
 *   - замовлення з email demo+...@gazu.demo або maptest+...
 *
 * Запуск: php artisan db:scrub-demo            (інтерактивно, з підтвердженням)
 *         php artisan db:scrub-demo --force    (без запиту, для CI/деплою)
 *         php artisan db:scrub-demo --dry-run  (лише показати, що буде видалено)
 */
class ScrubDemoData extends Command
{
    protected $signature = 'db:scrub-demo {--force : Видалити без підтвердження} {--dry-run : Лише показати кількість, нічого не видаляти}';

    protected $description = 'Видаляє демо/тестові дані (демо-замовлення) перед запуском у прод';

    public function handle(): int
    {
        $orderQuery = Order::query()
            ->where('email', 'like', 'demo+%@gazu.demo')
            ->orWhere('email', 'like', 'maptest+%');

        $orderCount = (clone $orderQuery)->count();

        $this->info('Знайдено демо-даних:');
        $this->table(['Тип', 'Кількість'], [
            ['Демо-замовлення (demo+*@gazu.demo / maptest+*)', $orderCount],
        ]);

        if ($orderCount === 0) {
            $this->info('✓ Демо-даних не знайдено — нічого видаляти.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->warn('--dry-run: нічого не видалено.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm("Видалити {$orderCount} демо-замовлень? Це незворотньо.")) {
            $this->warn('Скасовано.');

            return self::SUCCESS;
        }

        $deleted = DB::transaction(fn () => (clone $orderQuery)->delete());

        $this->info("✓ Видалено демо-замовлень: {$deleted}");

        return self::SUCCESS;
    }
}
