<?php

namespace App\Console\Commands;

use App\Models\Filter;
use App\Models\FilterGroup;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Витягує характеристики з назв товарів у таксономію фільтрів.
 *
 * Потрібно, бо каталог наповнювали без поля «Характеристики»: параметри є, але
 * лише всередині назви («…вугільний…», «…3.5л…», «…КОПІЯ»). Правила навмисно
 * консервативні — краще не розпізнати, ніж проставити товару чужу характеристику.
 *
 * Запуск: php artisan gazu:filters-from-titles --dry-run
 */
class FiltersFromTitles extends Command
{
    protected $signature = 'gazu:filters-from-titles
        {--dry-run : показати, що буде проставлено, і нічого не писати}';

    protected $description = 'Розпізнати характеристики в назвах товарів і записати їх у фільтри';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $products = Product::query()->where('is_active', true)->get(['id', 'title']);
        if ($products->isEmpty()) {
            $this->warn('Нема активних товарів.');

            return self::SUCCESS;
        }

        /** @var array<string, array<int, string>> $matches група => [productId => значення] */
        $matches = [];
        $preview = [];

        foreach ($products as $p) {
            $title = (string) $p->title;
            foreach ($this->detect($title) as $group => $value) {
                $matches[$group][$p->id] = $value;
                $preview[] = [mb_substr($title, 0, 46), $group, $value];
            }
        }

        if (! $matches) {
            $this->warn('У назвах нічого не розпізнано.');

            return self::SUCCESS;
        }

        $this->table(['Товар', 'Група', 'Значення'], $preview);

        $summary = [];
        foreach ($matches as $group => $byProduct) {
            $summary[] = [$group, count(array_unique($byProduct)), count($byProduct)];
        }
        $this->table(['Група', 'Значень', 'Товарів'], $summary);

        if ($dryRun) {
            $this->info('dry-run — нічого не записано.');

            return self::SUCCESS;
        }

        $created = 0;
        foreach ($matches as $groupTitle => $byProduct) {
            $group = FilterGroup::firstOrCreate(
                ['title' => $groupTitle],
                ['is_active' => true, 'sort_order' => 0]
            );

            foreach ($byProduct as $productId => $value) {
                $filter = Filter::firstOrCreate(
                    ['filter_group_id' => $group->id, 'title' => $value],
                    ['value' => $value, 'is_active' => true, 'sort_order' => 0]
                );

                $exists = DB::table('filter_products')
                    ->where('product_id', $productId)
                    ->where('filter_id', $filter->id)
                    ->exists();

                if (! $exists) {
                    DB::table('filter_products')->insert([
                        'product_id' => $productId,
                        'filter_id' => $filter->id,
                        'filter_group_id' => $group->id,
                    ]);
                    $created++;
                }
            }
        }

        Filter::flushCatalogCache();
        $this->info("Готово. Нових звʼязків товар↔характеристика: {$created}.");

        return self::SUCCESS;
    }

    /**
     * Розпізнані характеристики назви. Ключ — назва групи, значення — значення фільтра.
     *
     * @return array<string, string>
     */
    public function detect(string $title): array
    {
        $out = [];

        // Оригінал / копія. «КОПІЯ» пишуть капсом, «Оригінал» — з великої.
        if (preg_match('/\bкопія\b/iu', $title)) {
            $out['Тип запчастини'] = 'Копія';
        } elseif (preg_match('/\bоригінал\b/iu', $title)) {
            $out['Тип запчастини'] = 'Оригінал';
        }

        // Обʼєм: «946мл», «3.5л», «1л». Кома і крапка як роздільник.
        if (preg_match('/(\d+(?:[.,]\d+)?)\s*(мл|л)\b/iu', $title, $m)) {
            $number = rtrim(rtrim(str_replace(',', '.', $m[1]), '0'), '.');
            $unit = mb_strtolower($m[2]) === 'мл' ? 'мл' : 'л';
            $out['Обʼєм'] = "{$number} {$unit}";
        }

        // Місце встановлення — лише однозначні формулювання.
        if (preg_match('/\bв салон[іеа]\b|\bсалон[ау]\b/iu', $title)) {
            $out['Місце встановлення'] = 'Салон';
        } elseif (preg_match('/під капотом/iu', $title)) {
            $out['Місце встановлення'] = 'Під капотом';
        }

        return $out;
    }
}
