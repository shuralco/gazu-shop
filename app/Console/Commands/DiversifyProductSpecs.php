<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;

/**
 * Seed data ships with identical specifications across products in the same
 * category. That makes variant-picker useless — there is no value to pick from.
 *
 * This command randomly assigns realistic values from a fixed pool per category
 * type, so we get true variants (different Висота / Різьба / Обʼєм) within a
 * category. Run after seeding, BEFORE products:auto-relate.
 *
 *   php artisan products:diversify-specs --dry-run
 *   php artisan products:diversify-specs
 */
class DiversifyProductSpecs extends Command
{
    protected $signature = 'products:diversify-specs {--dry-run}';
    protected $description = 'Розкидає specs значення у тестових товарах для появи variants';

    private const POOLS = [
        // Filters
        'філь' => [
            'Висота' => ['65 мм', '75 мм', '85 мм', '95 мм', '120 мм'],
            'Різьба' => ['M20×1.5', 'M22×1.5', '3/4-16 UNF', 'M27×2.0'],
            'Тип' => ['Накручуваний', 'Картриджний', 'Інлайн'],
        ],
        // Oils
        'олив' => [
            'Об\'єм' => ['1 л', '4 л', '5 л', '20 л'],
            'В\'язкість' => ['5W-30', '5W-40', '0W-20', '10W-40'],
            'Стандарт' => ['ACEA C3', 'API SN', 'API SP'],
        ],
        // Brake pads / discs
        'гальм' => [
            'Положення' => ['Передні', 'Задні'],
            'Діаметр' => ['256 мм', '276 мм', '296 мм', '316 мм'],
            'Товщина' => ['22 мм', '25 мм', '28 мм'],
        ],
        // Shock absorbers / suspension
        'аморт' => [
            'Положення' => ['Передній', 'Задній'],
            'Тип' => ['Газовий', 'Масляний', 'Газомасляний'],
        ],
        // Spark plugs
        'свіч' => [
            'Зазор' => ['0.7 мм', '0.8 мм', '0.9 мм', '1.0 мм', '1.1 мм'],
            'Тип' => ['Іридієва', 'Платинова', 'Нікелева'],
        ],
        // Bulbs
        'лампа' => [
            'Цоколь' => ['H4', 'H7', 'H11', 'HB3', 'HB4'],
            'Потужність' => ['55W', '60W', '65W', '100W'],
            'Колір' => ['Білий', 'Жовтий', 'Холодний білий'],
        ],
        // Default / generic
        '*' => [
            'Розмір' => ['S', 'M', 'L', 'XL'],
        ],
    ];

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $touched = 0;
        $skipped = 0;
        $changesByCategory = [];

        // Group products per category to deterministically assign value index per product.
        Category::query()
            ->whereNotNull('title')
            ->chunk(50, function ($cats) use (&$touched, &$skipped, &$changesByCategory, $dry) {
                foreach ($cats as $cat) {
                    $title = is_array($cat->title) ? mb_strtolower($cat->title['uk'] ?? '') : mb_strtolower((string) $cat->title);
                    $pool = $this->matchPool($title);
                    if (! $pool) continue;

                    $prods = Product::query()
                        ->where('category_id', $cat->id)
                        ->whereNotNull('specifications')
                        ->get(['id', 'specifications']);

                    foreach ($prods as $i => $p) {
                        $specs = is_array($p->specifications)
                            ? $p->specifications
                            : (json_decode((string) $p->specifications, true) ?: []);

                        $newSpecs = $specs;
                        $changed = false;
                        foreach ($pool as $key => $values) {
                            // Spread products evenly across the value pool by index.
                            $newValue = $values[$i % count($values)];
                            if (($newSpecs[$key] ?? null) !== $newValue) {
                                $newSpecs[$key] = $newValue;
                                $changed = true;
                            }
                        }
                        if (! $changed) { $skipped++; continue; }

                        if (! $dry) {
                            $p->specifications = $newSpecs;
                            $p->save();
                        }
                        $touched++;
                        $changesByCategory[$cat->id] = ($changesByCategory[$cat->id] ?? 0) + 1;
                    }
                }
            });

        $this->info(($dry ? '[DRY] ' : '').'Оновлено товарів: '.$touched.', пропущено: '.$skipped);
        $this->table(['category_id', 'кількість оновлених'], collect($changesByCategory)
            ->sortDesc()
            ->take(15)
            ->map(fn ($c, $id) => [$id, $c])->values()->all());
        return self::SUCCESS;
    }

    private function matchPool(string $title): ?array
    {
        foreach (self::POOLS as $needle => $pool) {
            if ($needle === '*') continue;
            if (str_contains($title, $needle)) return $pool;
        }
        return null;
    }
}
