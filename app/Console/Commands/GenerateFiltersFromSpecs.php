<?php

namespace App\Console\Commands;

use App\Models\Filter;
use App\Models\FilterGroup;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Парсить products.specifications JSON у структуровані FilterGroup + Filter
 * + filter_products pivot. Кожен унікальний spec-ключ → FilterGroup,
 * кожне значення → Filter, прив'язаний до товару.
 *
 * Запуск: php artisan filters:generate-from-specs
 */
class GenerateFiltersFromSpecs extends Command
{
    protected $signature = 'filters:generate-from-specs {--prune : видалити існуючі filters перед генерацією}';

    protected $description = 'Build filter_groups + filters + pivot from products.specifications JSON';

    public function handle(): int
    {
        if ($this->option('prune')) {
            $this->warn('Pruning старі фільтри…');
            DB::table('filter_products')->truncate();
            Filter::query()->delete();
            FilterGroup::query()->delete();
        }

        $products = Product::query()
            ->whereNotNull('specifications')
            ->where('specifications', '!=', '[]')
            ->where('specifications', '!=', '{}')
            ->get(['id', 'specifications']);

        if ($products->isEmpty()) {
            $this->warn('Нема товарів з заповненими specifications.');
            return self::SUCCESS;
        }

        $this->info("Обробляю {$products->count()} товарів…");
        $bar = $this->output->createProgressBar($products->count());

        $groupCache = [];
        $filterCache = [];
        $pivotRows = [];

        foreach ($products as $p) {
            $specs = is_array($p->specifications)
                ? $p->specifications
                : (json_decode((string) $p->specifications, true) ?: []);

            if (! is_array($specs)) continue;

            foreach ($specs as $key => $value) {
                $key = trim((string) $key);
                $value = is_array($value) ? implode(', ', $value) : trim((string) $value);
                if ($key === '' || $value === '') continue;

                // Skip noisy keys
                if (preg_match('/^(описание|description|notes?|примітк)/iu', $key)) continue;
                if (mb_strlen($value) > 100) continue;

                // FilterGroup
                $groupKey = mb_strtolower($key);
                if (! isset($groupCache[$groupKey])) {
                    $group = FilterGroup::firstOrCreate(
                        ['title' => $key],
                        ['is_active' => true, 'sort_order' => count($groupCache)]
                    );
                    $groupCache[$groupKey] = $group->id;
                }
                $groupId = $groupCache[$groupKey];

                // Filter (group_id + value)
                $filterKey = $groupId.'|'.mb_strtolower($value);
                if (! isset($filterCache[$filterKey])) {
                    $filter = Filter::firstOrCreate(
                        ['filter_group_id' => $groupId, 'title' => $value],
                        ['value' => $value, 'is_active' => true, 'sort_order' => 0]
                    );
                    $filterCache[$filterKey] = $filter->id;
                }
                $filterId = $filterCache[$filterKey];

                $pivotRows[] = [
                    'product_id' => $p->id,
                    'filter_id' => $filterId,
                    'filter_group_id' => $groupId,
                ];
            }

            $bar->advance();
        }
        $bar->finish();
        $this->newLine(2);

        // Batch insert pivot (idempotent — drop dupes first)
        if (! empty($pivotRows)) {
            $existing = DB::table('filter_products')->get(['product_id', 'filter_id']);
            $existingKeys = $existing->map(fn ($r) => $r->product_id.'|'.$r->filter_id)->flip();

            $newRows = array_values(array_filter(
                $pivotRows,
                fn ($r) => ! isset($existingKeys[$r['product_id'].'|'.$r['filter_id']])
            ));

            // Insert in chunks (10k rows per query — MySQL ok)
            foreach (array_chunk($newRows, 5000) as $chunk) {
                DB::table('filter_products')->insert($chunk);
            }
        }

        $this->info('Готово:');
        $this->table(['Метрика', 'К-сть'], [
            ['FilterGroups', count($groupCache)],
            ['Filters', count($filterCache)],
            ['Pivot rows', count($pivotRows)],
        ]);

        return self::SUCCESS;
    }
}
