<?php

namespace App\Console\Commands;

use App\Models\Filter;
use App\Models\FilterGroup;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Парсить products.specifications JSON у структуровані FilterGroup + Filter
 * + filter_products pivot. Кожен придатний spec-ключ → FilterGroup,
 * кожне значення → Filter, прив'язаний до товару.
 *
 * Ключі-ідентифікатори (крос-код, артикул, OEM) НЕ стають фільтрами: у них
 * майже в кожного товару своє значення, тож фільтр з них марний.
 *
 * Запуск: php artisan filters:generate-from-specs [--dry-run]
 */
class GenerateFiltersFromSpecs extends Command
{
    protected $signature = 'filters:generate-from-specs
        {--prune : видалити існуючі filters перед генерацією}
        {--dry-run : лише показати, що буде створено}';

    protected $description = 'Build filter_groups + filters + pivot from products.specifications JSON';

    /** Мінімум товарів із ключем, щоб він став фільтром. */
    private const MIN_PRODUCTS = 2;

    /** Від якої кількості значень вмикається перевірка на «майже унікальні». */
    private const CARDINALITY_FLOOR = 5;

    /** Частка distinct-значень від к-сті товарів, вище якої ключ — ідентифікатор. */
    private const CARDINALITY_RATIO = 0.7;

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($this->option('prune') && ! $dryRun) {
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

        // Фаза 1 — зібрати. Ключ → [productId => значення]. Рішення про придатність
        // приймається лише коли видно ВЕСЬ каталог: по одному товару не зрозуміти,
        // ключ це «Тип матеріалу» чи «Артикул».
        $collected = [];
        $titles = [];
        foreach ($products as $p) {
            $specs = is_array($p->specifications)
                ? $p->specifications
                : (json_decode((string) $p->specifications, true) ?: []);

            if (! is_array($specs)) {
                continue;
            }

            foreach ($specs as $key => $value) {
                $key = trim((string) $key);
                $value = is_array($value) ? implode(', ', $value) : trim((string) $value);
                if ($key === '' || $value === '' || mb_strlen($value) > 100) {
                    continue;
                }
                if ($this->isNoisyKey($key)) {
                    continue;
                }

                $norm = mb_strtolower($key);
                $titles[$norm] ??= $key;
                $collected[$norm][$p->id] = $value;
            }
        }

        // Фаза 2 — відсіяти ключі-ідентифікатори.
        $accepted = [];
        $rejected = [];
        foreach ($collected as $norm => $byProduct) {
            $reason = $this->rejectionReason($byProduct);
            if ($reason === null) {
                $accepted[$norm] = $byProduct;
            } else {
                $rejected[] = [$titles[$norm], count($byProduct), count(array_unique($byProduct)), $reason];
            }
        }

        if ($rejected) {
            $this->newLine();
            $this->warn('Пропущено ключів: '.count($rejected));
            $this->table(['Ключ', 'Товарів', 'Значень', 'Причина'], $rejected);
        }

        if (! $accepted) {
            $this->warn('Жоден ключ не придатний для фільтра.');

            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->info('Буде створено (dry-run):');
            $this->table(
                ['Група', 'Значень', 'Товарів'],
                array_map(
                    fn ($norm) => [$titles[$norm], count(array_unique($accepted[$norm])), count($accepted[$norm])],
                    array_keys($accepted)
                )
            );

            return self::SUCCESS;
        }

        $groupCache = [];
        $filterCache = [];
        $pivotRows = [];

        foreach ($accepted as $norm => $byProduct) {
            $group = FilterGroup::firstOrCreate(
                ['title' => $titles[$norm]],
                ['is_active' => true, 'sort_order' => count($groupCache)]
            );
            $groupCache[$norm] = $group->id;

            foreach ($byProduct as $productId => $value) {
                $filterKey = $group->id.'|'.mb_strtolower($value);
                if (! isset($filterCache[$filterKey])) {
                    $filter = Filter::firstOrCreate(
                        ['filter_group_id' => $group->id, 'title' => $value],
                        ['value' => $value, 'is_active' => true, 'sort_order' => 0]
                    );
                    $filterCache[$filterKey] = $filter->id;
                }

                $pivotRows[] = [
                    'product_id' => $productId,
                    'filter_id' => $filterCache[$filterKey],
                    'filter_group_id' => $group->id,
                ];
            }
        }

        if ($pivotRows) {
            $existingKeys = DB::table('filter_products')
                ->get(['product_id', 'filter_id'])
                ->map(fn ($r) => $r->product_id.'|'.$r->filter_id)
                ->flip();

            $newRows = array_values(array_filter(
                $pivotRows,
                fn ($r) => ! isset($existingKeys[$r['product_id'].'|'.$r['filter_id']])
            ));

            foreach (array_chunk($newRows, 5000) as $chunk) {
                DB::table('filter_products')->insert($chunk);
            }
        }

        Filter::flushCatalogCache();

        $this->info('Готово:');
        $this->table(['Метрика', 'К-сть'], [
            ['FilterGroups', count($groupCache)],
            ['Filters', count($filterCache)],
            ['Pivot rows', count($pivotRows)],
        ]);

        return self::SUCCESS;
    }

    private function isNoisyKey(string $key): bool
    {
        return (bool) preg_match('/^(описание|description|notes?|примітк)/iu', $key)
            || (bool) preg_match('/(крос.?код|артикул|sku|oem|штрих|barcode|код товару)/iu', $key);
    }

    /**
     * Чому ключ не годиться у фільтри. null — годиться.
     *
     * @param  array<int,string>  $byProduct
     */
    private function rejectionReason(array $byProduct): ?string
    {
        $products = count($byProduct);
        if ($products < self::MIN_PRODUCTS) {
            return 'лише 1 товар';
        }

        $distinct = count(array_unique($byProduct));
        if ($distinct < 2) {
            return 'одне значення на всіх';
        }

        // Майже в кожного товару своє значення → це ідентифікатор, не характеристика.
        if ($distinct >= self::CARDINALITY_FLOOR && $distinct / $products > self::CARDINALITY_RATIO) {
            return sprintf('майже унікальні (%d значень на %d товарів)', $distinct, $products);
        }

        return null;
    }
}
