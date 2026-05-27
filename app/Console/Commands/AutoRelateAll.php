<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Bulk-run the "auto-relate similar" algorithm for every product that has
 * specifications + category_id + zero related links. Same scoring as
 * RelatedProductsRelationManager::autoRelatedBySpecs (so admin button and CLI
 * stay in sync).
 *
 * Usage:
 *   php artisan products:auto-relate
 *   php artisan products:auto-relate --limit=50 --per-product=10 --force
 */
class AutoRelateAll extends Command
{
    protected $signature = 'products:auto-relate
        {--limit=0 : Max number of products to process (0 = all)}
        {--per-product=12 : Max related links per product}
        {--force : Overwrite even if related links already exist}';

    protected $description = 'Авто-зв\'язує товари за спільними характеристиками (variants picker)';

    public function handle(): int
    {
        $perProduct = (int) $this->option('per-product');
        $limit = (int) $this->option('limit');
        $force = (bool) $this->option('force');

        $query = Product::query()
            ->whereNotNull('category_id')
            ->whereNotNull('specifications')
            ->where('is_active', true);

        if ($limit > 0) $query->limit($limit);

        $total = (clone $query)->count();
        $this->info("Обробляю {$total} товарів (per-product={$perProduct}, force=".($force ? 'true' : 'false').")");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $stats = ['skipped' => 0, 'processed' => 0, 'attached' => 0];

        $query->chunk(100, function ($products) use ($bar, $perProduct, $force, &$stats) {
            foreach ($products as $owner) {
                $bar->advance();

                $specs = is_array($owner->specifications)
                    ? $owner->specifications
                    : (json_decode((string) $owner->specifications, true) ?: []);
                if (empty($specs)) { $stats['skipped']++; continue; }

                $existing = DB::table('related_products')
                    ->where('product_id', $owner->id)
                    ->where('type', 'related')
                    ->pluck('related_product_id')
                    ->all();

                if (! empty($existing) && ! $force) { $stats['skipped']++; continue; }

                $candidates = Product::query()
                    ->where('category_id', $owner->category_id)
                    ->where('id', '!=', $owner->id)
                    ->whereNotNull('specifications')
                    ->where('is_active', true)
                    ->limit(200)
                    ->get(['id', 'specifications']);

                $scored = [];
                foreach ($candidates as $c) {
                    $cs = is_array($c->specifications)
                        ? $c->specifications
                        : (json_decode((string) $c->specifications, true) ?: []);
                    if (empty($cs)) continue;

                    $common = 0; $diff = 0;
                    foreach ($specs as $k => $v) {
                        if (! isset($cs[$k])) continue;
                        if ((string) $cs[$k] === (string) $v) $common++;
                        else $diff++;
                    }
                    if ($common >= 1 && $diff >= 1 && $diff <= 3) {
                        $scored[] = ['id' => $c->id, 'score' => $common - $diff * 0.5];
                    }
                }
                usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);
                $picks = array_slice($scored, 0, $perProduct);

                foreach ($picks as $pick) {
                    if (in_array($pick['id'], $existing, true)) continue;
                    DB::table('related_products')->insertOrIgnore([
                        'product_id' => $owner->id,
                        'related_product_id' => $pick['id'],
                        'type' => 'related',
                        'sort_order' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $stats['attached']++;
                }
                $stats['processed']++;
            }
        });

        $bar->finish();
        $this->newLine(2);
        $this->info("✓ Готово: оброблено {$stats['processed']} | пропущено {$stats['skipped']} | прив'язано {$stats['attached']} зв'язків");

        return self::SUCCESS;
    }
}
