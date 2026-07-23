<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\CarEngine;
use App\Models\CarMake;
use App\Models\CarModel;
use App\Models\Category;
use App\Models\Product;
use App\Services\Gazu\CatalogQuery;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Фасети лівої панелі (категорії, виробники) мусять рахуватись у ПОТОЧНОМУ
 * скоупі. Клієнт бачив «11 товарів» по VW ID4, а в категоріях — 40 і бренд BYD:
 *  1) лічильники категорій ігнорували підбір по авто;
 *  2) ключ кешу фасетів не містив make/model/engine, тож сторінка авто і
 *     загальний каталог ділили один запис кешу.
 */
class CatalogFacetScopeTest extends TestCase
{
    use LazilyRefreshDatabase;

    private Category $filters;
    private Category $oils;
    private CarEngine $vwEngine;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filters = Category::create(['title' => 'Фільтри', 'slug' => 'filtry', 'is_active' => true]);
        $this->oils = Category::create(['title' => 'Оливи', 'slug' => 'olyvy', 'is_active' => true]);

        $vw = CarMake::create(['slug' => 'vw', 'name' => 'Volkswagen', 'is_active' => true]);
        $id4 = CarModel::create(['make_id' => $vw->id, 'slug' => 'id4', 'name' => 'ID 4 Crozz', 'is_active' => true]);
        $this->vwEngine = CarEngine::create(['model_id' => $id4->id, 'code' => 'RWD', 'is_active' => true]);

        $byd = CarMake::create(['slug' => 'byd', 'name' => 'BYD', 'is_active' => true]);
        $han = CarModel::create(['make_id' => $byd->id, 'slug' => 'han', 'name' => 'Han', 'is_active' => true]);
        $bydEngine = CarEngine::create(['model_id' => $han->id, 'code' => 'EV', 'is_active' => true]);

        $brandVw = Brand::create(['name' => 'VW', 'slug' => 'vw-brand', 'is_active' => true]);
        $brandByd = Brand::create(['name' => 'BYD', 'slug' => 'byd-brand', 'is_active' => true]);

        // Один товар підходить до VW ID4, три — до BYD Han.
        $vwPart = Product::factory()->create(['is_active' => true, 'category_id' => $this->filters->id, 'brand_id' => $brandVw->id]);
        $vwPart->compatibleEngines()->attach($this->vwEngine->id);

        foreach (range(1, 3) as $i) {
            $p = Product::factory()->create([
                'is_active' => true,
                'category_id' => $i === 1 ? $this->filters->id : $this->oils->id,
                'brand_id' => $brandByd->id,
            ]);
            $p->compatibleEngines()->attach($bydEngine->id);
        }
    }

    private function query(string $uri): CatalogQuery
    {
        return new CatalogQuery(Request::create($uri));
    }

    public function test_baseline_whole_catalog_counts(): void
    {
        $cats = $this->query('/catalog')->availableCategories(null);

        $this->assertSame(4, $this->query('/catalog')->paginate(null)->total());
        $this->assertSame(2, $cats->firstWhere('slug', 'filtry')->products_count);
        $this->assertSame(2, $cats->firstWhere('slug', 'olyvy')->products_count);
    }

    public function test_category_counts_respect_vehicle_filter(): void
    {
        $uri = '/catalog?make=vw&model=id4&engine=rwd';

        $this->assertSame(1, $this->query($uri)->paginate(null)->total(), 'до VW ID4 підходить 1 товар');

        $cats = $this->query($uri)->availableCategories(null);

        $this->assertSame(1, $cats->firstWhere('slug', 'filtry')->products_count, 'категорія має рахуватись у скоупі авто');
        $this->assertNull($cats->firstWhere('slug', 'olyvy'), 'порожню категорію не показуємо');
    }

    public function test_brands_respect_vehicle_filter(): void
    {
        $brands = $this->query('/catalog?make=vw&model=id4&engine=rwd')->availableBrands(null);

        $labels = collect($brands)->pluck('label')->all();
        $this->assertNotContains('BYD', $labels, 'BYD не сумісний із VW ID4 — не має бути у виробниках');
    }

    public function test_facet_cache_is_not_shared_between_vehicles(): void
    {
        // Спершу прогріваємо кеш загальним каталогом, потім просимо сторінку авто.
        // До фіксу ключ був однаковий → сторінка авто отримувала чужі лічильники.
        $this->query('/catalog')->availableCategories(null);

        $cats = $this->query('/catalog?make=vw&model=id4&engine=rwd')->availableCategories(null);

        $this->assertSame(1, $cats->firstWhere('slug', 'filtry')->products_count, 'кеш загального каталогу не має протікати на сторінку авто');
    }

    public function test_different_vehicles_get_different_facets(): void
    {
        $vw = $this->query('/catalog?make=vw&model=id4&engine=rwd')->availableCategories(null);
        $byd = $this->query('/catalog?make=byd&model=han&engine=ev')->availableCategories(null);

        $this->assertSame(1, $vw->firstWhere('slug', 'filtry')->products_count);
        $this->assertSame(1, $byd->firstWhere('slug', 'filtry')->products_count);
        $this->assertSame(2, $byd->firstWhere('slug', 'olyvy')->products_count, 'BYD має свої лічильники, не VW-ські');
    }
}
