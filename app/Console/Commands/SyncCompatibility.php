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
        $query->chunkById(200, function ($products) use (&$total, &$linked) {
            foreach ($products as $p) {
                CompatibilitySync::syncProduct($p);
                $total++;
                $linked += $p->compatibleEngines()->count();
            }
        });

        $this->info("[sync-compatibility] Оброблено товарів: {$total}; активних зв'язків двигунів: {$linked}.");

        return self::SUCCESS;
    }
}
