<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Filter;
use App\Models\FilterGroup;
use App\Models\Product;
use App\Services\Gazu\CatalogQuery;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Фільтрація каталогу по характеристиках (filter_groups → filters → filter_products).
 * Контракт: OR всередині групи, AND між групами.
 */
class CatalogFilterTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function group(string $title): FilterGroup
    {
        return FilterGroup::create(['title' => $title, 'is_active' => true, 'sort_order' => 0]);
    }

    private function filter(FilterGroup $g, string $title): Filter
    {
        return Filter::create(['title' => $title, 'filter_group_id' => $g->id, 'is_active' => true, 'sort_order' => 0]);
    }

    private function tag(Product $p, Filter $f): void
    {
        DB::table('filter_products')->insert([
            'product_id' => $p->id,
            'filter_id' => $f->id,
            'filter_group_id' => $f->filter_group_id,
        ]);
    }

    private function query(string $uri): CatalogQuery
    {
        return new CatalogQuery(Request::create($uri));
    }

    /** @return array{Category, array<string,Product>, array<string,Filter>} */
    private function seedCatalog(): array
    {
        $cat = Category::create(['title' => 'Оливи', 'slug' => 'olyvy', 'is_active' => true]);

        $visc = $this->group('В\'язкість');
        $type = $this->group('Тип');

        $f = [
            '5w30' => $this->filter($visc, '5W-30'),
            '5w40' => $this->filter($visc, '5W-40'),
            'synt' => $this->filter($type, 'Синтетика'),
            'mine' => $this->filter($type, 'Мінеральна'),
        ];

        $p = [
            'a' => Product::factory()->create(['title' => 'Олива A', 'category_id' => $cat->id, 'is_active' => true]),
            'b' => Product::factory()->create(['title' => 'Олива B', 'category_id' => $cat->id, 'is_active' => true]),
            'c' => Product::factory()->create(['title' => 'Олива C', 'category_id' => $cat->id, 'is_active' => true]),
        ];

        // A: 5W-30 + Синтетика | B: 5W-40 + Синтетика | C: 5W-30 + Мінеральна
        $this->tag($p['a'], $f['5w30']);
        $this->tag($p['a'], $f['synt']);
        $this->tag($p['b'], $f['5w40']);
        $this->tag($p['b'], $f['synt']);
        $this->tag($p['c'], $f['5w30']);
        $this->tag($p['c'], $f['mine']);

        return [$cat, $p, $f];
    }

    public function test_single_filter_narrows_catalog(): void
    {
        [$cat, $p, $f] = $this->seedCatalog();

        $ids = $this->query('/catalog?filter[]='.$f['5w30']->id)->paginate($cat)->pluck('id')->all();

        sort($ids);
        $this->assertEquals(collect([$p['a']->id, $p['c']->id])->sort()->values()->all(), $ids, '5W-30 → лише A і C');
    }

    public function test_two_filters_in_same_group_are_or(): void
    {
        [$cat, $p, $f] = $this->seedCatalog();

        $ids = $this->query('/catalog?filter[]='.$f['5w30']->id.'&filter[]='.$f['5w40']->id)->paginate($cat)->pluck('id')->all();

        $this->assertCount(3, $ids, 'обидві в\'язкості в одній групі → OR, усі 3 товари');
    }

    public function test_filters_across_groups_are_and(): void
    {
        [$cat, $p, $f] = $this->seedCatalog();

        $ids = $this->query('/catalog?filter[]='.$f['5w30']->id.'&filter[]='.$f['synt']->id)->paginate($cat)->pluck('id')->all();

        $this->assertEquals([$p['a']->id], $ids, '5W-30 І Синтетика → лише A');
    }

    public function test_contradictory_filters_return_nothing(): void
    {
        [$cat, , $f] = $this->seedCatalog();

        $ids = $this->query('/catalog?filter[]='.$f['5w40']->id.'&filter[]='.$f['mine']->id)->paginate($cat)->pluck('id')->all();

        $this->assertCount(0, $ids, '5W-40 І Мінеральна → жодного товару');
    }

    public function test_available_filters_expose_groups_with_counts(): void
    {
        [$cat, , $f] = $this->seedCatalog();

        $groups = $this->query('/catalog')->availableFilters($cat);

        $this->assertCount(2, $groups, 'дві групи характеристик');

        $visc = $groups->firstWhere('title', 'В\'язкість');
        $this->assertNotNull($visc);
        $counts = collect($visc->items)->pluck('count', 'title');
        $this->assertEquals(2, $counts['5W-30'], '5W-30 у двох товарах');
        $this->assertEquals(1, $counts['5W-40'], '5W-40 в одному');
    }

    public function test_counts_of_selected_group_ignore_its_own_selection(): void
    {
        [$cat, , $f] = $this->seedCatalog();

        // Обрано 5W-30. Усередині «В'язкість» 5W-40 має лишитись видимим
        // (інакше користувач не зможе переключитись), а «Тип» — звузитись.
        $groups = $this->query('/catalog?filter[]='.$f['5w30']->id)->availableFilters($cat);

        $visc = collect($groups)->firstWhere('title', 'В\'язкість');
        $this->assertEquals(1, collect($visc->items)->pluck('count', 'title')['5W-40'], '5W-40 не занулюється власним вибором групи');

        $type = collect($groups)->firstWhere('title', 'Тип');
        $typeCounts = collect($type->items)->pluck('count', 'title');
        $this->assertEquals(1, $typeCounts['Синтетика'], 'Тип звужується вибором 5W-30');
        $this->assertEquals(1, $typeCounts['Мінеральна']);
    }

    public function test_category_pinned_groups_restrict_the_panel(): void
    {
        [$cat, , $f] = $this->seedCatalog();

        $viscId = $f['5w30']->filter_group_id;
        DB::table('category_filters')->insert(['category_id' => $cat->id, 'filter_group_id' => $viscId]);

        $groups = $this->query('/catalog')->availableFilters($cat);

        $this->assertCount(1, $groups, 'прив\'язана лише одна група → показуємо лише її');
        $this->assertEquals('В\'язкість', $groups->first()->title);
    }
}
