<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\Gazu\CompatibilitySync;
use Illuminate\Console\Command;

/**
 * Разовий бекфіл: синхронізує JSON `products.compatibility` → pivot
 * `product_compatibility` для ВСІХ товарів (щоб уже додана сумісність почала
 * впливати на фільтр підбору по авто). Далі синк відбувається авто при
 * збереженні товару (Product::saved).
 */
class SyncCompatibility extends Command
{
    protected $signature = 'gazu:sync-compatibility {--id= : Тільки один товар за id}';

    protected $description = 'Синк сумісності авто: products.compatibility (JSON) → product_compatibility (pivot)';

    public function handle(): int
    {
        $query = Product::query()->whereNotNull('compatibility');
        if ($id = $this->option('id')) {
            $query->where('id', (int) $id);
        }

        $total = 0;
        $linked = 0;
        $unresolved = [];
        $query->chunkById(200, function ($products) use (&$total, &$linked, &$unresolved) {
            foreach ($products as $p) {
                $n = CompatibilitySync::syncProduct($p);
                $total++;
                $linked += $n;
                // Товар має заповнену сумісність, але жоден рядок не розв'язався
                // у двигуни (модель/двигун відсутні в довіднику авто).
                if ($n === 0 && ! empty($p->compatibility)) {
                    $rows = collect(is_array($p->compatibility) ? $p->compatibility : [])
                        ->map(fn ($r) => is_array($r) ? trim(($r['make'] ?? '').' '.($r['model'] ?? '')) : '')
                        ->filter()->take(3)->implode('; ');
                    $unresolved[] = "#{$p->id} [{$rows}]";
                }
            }
        });

        $this->info("[sync-compatibility] Оброблено товарів: {$total}; активних зв'язків двигунів: {$linked}.");
        if ($unresolved) {
            $this->warn('[sync-compatibility] Не розв\'язано (модель/двигун відсутні в довіднику): '.count($unresolved));
            foreach (array_slice($unresolved, 0, 20) as $u) {
                $this->line('  ⚠ '.$u);
            }
        }

        return self::SUCCESS;
    }
}
