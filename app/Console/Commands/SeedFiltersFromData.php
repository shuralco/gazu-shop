<?php

namespace App\Console\Commands;

use App\Models\Filter;
use App\Models\FilterGroup;
use App\Models\FilterLanding;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Наповнює filter_groups / filters / filter_products з реальних даних
 * каталогу (виробник, марки авто з compatibility, специфікації) і створює
 * демо filter-landing. Idempotent — можна ганяти повторно.
 *
 *   php artisan gazu:seed-filters
 */
class SeedFiltersFromData extends Command
{
    protected $signature = 'gazu:seed-filters {--no-landing : Не створювати демо-посадкову}';

    protected $description = 'Фільтри з реальних даних каталогу (виробник/марка авто/специфікації) + демо-посадкова';

    /** Ключі специфікацій, що стають фільтр-групами (=> sort_order). */
    private const SPEC_GROUPS = ['Положення' => 30, 'Тип' => 40, 'Цоколь' => 50, "В'язкість" => 60, 'Матеріал' => 70, 'Стандарт' => 80];

    public function handle(): int
    {
        $before = DB::table('filter_products')->count();

        $this->seedManufacturers();
        $this->seedCarMakes();
        $this->seedSpecGroups();

        if (! $this->option('no-landing')) {
            $this->seedDemoLanding();
        }

        $this->info(sprintf(
            '✓ groups=%d filters=%d links=%d (+%d)',
            FilterGroup::count(),
            Filter::count(),
            DB::table('filter_products')->count(),
            DB::table('filter_products')->count() - $before,
        ));

        return self::SUCCESS;
    }

    private function group(string $title, int $sort): FilterGroup
    {
        return FilterGroup::firstOrCreate(['title' => $title], ['sort_order' => $sort, 'is_active' => 1]);
    }

    private function filter(FilterGroup $g, string $title, int $sort): Filter
    {
        return Filter::firstOrCreate(
            ['title' => $title, 'filter_group_id' => $g->id],
            ['sort_order' => $sort, 'is_active' => 1, 'value' => $title],
        );
    }

    private function attach(Filter $f, array $productIds): void
    {
        foreach (array_chunk(array_values(array_unique($productIds)), 500) as $chunk) {
            DB::table('filter_products')->insertOrIgnore(array_map(
                fn ($pid) => ['filter_id' => $f->id, 'product_id' => $pid, 'filter_group_id' => $f->filter_group_id],
                $chunk,
            ));
        }
    }

    private function seedManufacturers(): void
    {
        $g = $this->group('Виробник', 10);
        $mans = Product::where('is_active', 1)->whereNotNull('manufacturer')
            ->selectRaw('manufacturer, count(*) c')->groupBy('manufacturer')
            ->havingRaw('count(*) >= 5')->orderByDesc('c')->limit(40)->pluck('c', 'manufacturer');

        $sort = 0;
        foreach ($mans as $name => $c) {
            $f = $this->filter($g, (string) $name, $sort += 10);
            $this->attach($f, Product::where('is_active', 1)->where('manufacturer', $name)->pluck('id')->all());
        }
    }

    private function seedCarMakes(): void
    {
        $g = $this->group('Марка авто', 20);
        $makeMap = [];
        Product::where('is_active', 1)->whereNotNull('compatibility')->select(['id', 'compatibility'])
            ->chunk(500, function ($rows) use (&$makeMap) {
                foreach ($rows as $p) {
                    $c = is_array($p->compatibility) ? $p->compatibility : (json_decode((string) $p->compatibility, true) ?: []);
                    foreach ((array) $c as $row) {
                        $mk = trim((string) ($row['make'] ?? ''));
                        if ($mk !== '') {
                            $makeMap[$mk][] = $p->id;
                        }
                    }
                }
            });

        ksort($makeMap);
        $sort = 0;
        foreach ($makeMap as $mk => $ids) {
            if (count(array_unique($ids)) < 3) {
                continue;
            }
            $this->attach($this->filter($g, $mk, $sort += 10), $ids);
        }
    }

    private function seedSpecGroups(): void
    {
        $specMap = [];
        Product::where('is_active', 1)->whereNotNull('specifications')->select(['id', 'specifications'])
            ->chunk(500, function ($rows) use (&$specMap) {
                foreach ($rows as $p) {
                    $s = is_array($p->specifications) ? $p->specifications : (json_decode((string) $p->specifications, true) ?: []);
                    foreach (self::SPEC_GROUPS as $key => $_) {
                        $val = trim((string) ($s[$key] ?? ''));
                        if ($val !== '') {
                            $specMap[$key][$val][] = $p->id;
                        }
                    }
                }
            });

        foreach (self::SPEC_GROUPS as $key => $gSort) {
            $vals = array_filter($specMap[$key] ?? [], fn ($ids) => count(array_unique($ids)) >= 2);
            if (count($vals) < 2) {
                continue; // група з одним значенням — шум, не фільтр
            }
            $g = $this->group($key, $gSort);
            ksort($vals);
            $sort = 0;
            foreach ($vals as $val => $ids) {
                $this->attach($this->filter($g, (string) $val, $sort += 10), $ids);
            }
        }
    }

    /** Демо-посадкова /lp/{category}-bosch: топ-виробник у його найбільшій категорії. */
    private function seedDemoLanding(): void
    {
        if (! class_exists(FilterLanding::class) || ! \Schema::hasTable('filter_landings')) {
            return;
        }

        $gMan = FilterGroup::where('title', 'Виробник')->first();
        $bosch = $gMan ? Filter::where('title', 'Bosch')->where('filter_group_id', $gMan->id)->first() : null;
        if (! $bosch) {
            return;
        }

        $catId = Product::where('is_active', 1)->where('manufacturer', 'Bosch')->whereNotNull('category_id')
            ->selectRaw('category_id, count(*) c')->groupBy('category_id')->orderByDesc('c')->value('category_id');
        $cat = \App\Models\Category::find($catId);
        $catTitle = is_array($cat?->title) ? ($cat->title['uk'] ?? 'Запчастини') : (string) ($cat?->title ?? 'Запчастини');

        FilterLanding::updateOrCreate(
            ['slug' => Str::slug($catTitle).'-bosch'],
            [
                'title' => $catTitle.' Bosch',
                'h1' => $catTitle.' Bosch — оригінальна якість',
                'meta_title' => $catTitle.' Bosch — купити з доставкою по Україні | GAZU',
                'meta_description' => 'Купити '.mb_strtolower($catTitle).' Bosch для китайських авто. Оригінальна продукція, наявність і ціни, доставка Новою Поштою, гарантія від GAZU.',
                'intro_html' => '<p>Добірка <strong>'.$catTitle.' Bosch</strong> — німецька якість для вашого авто. Усі позиції в наявності, з гарантією виробника.</p>',
                'outro_html' => '<p>Bosch — світовий лідер у виробництві автокомпонентів. Купуючи '.mb_strtolower($catTitle).' Bosch у GAZU, ви отримуєте оригінальну продукцію з офіційною гарантією та швидкою доставкою по Україні.</p>',
                'category_id' => $catId,
                'brand_id' => null,
                'filter_ids' => [$bosch->id],
                'is_active' => 1,
                'show_applied_filters' => 1,
                'sort_order' => 10,
            ],
        );

        $this->info('✓ Демо-посадкова: /lp/'.Str::slug($catTitle).'-bosch');
    }
}
