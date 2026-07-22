<?php

namespace Tests\Feature;

use App\Models\CarEngine;
use App\Models\CarMake;
use App\Models\CarModel;
use App\Models\Product;
use App\Services\Gazu\CatalogQuery;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Pretty-URL /zapchastyny/{make}/{model}/{engine}: коди двигунів містять пробіли
 * («RWD 100 kWh 2021-») і давали 404. У URL іде slug, бекенд резолвить у code.
 */
class CatalogEngineSlugTest extends TestCase
{
    use LazilyRefreshDatabase;

    /** @return array{CarModel, CarEngine} */
    private function vehicle(string $engineCode): array
    {
        $mk = CarMake::create(['slug' => 'zeekr', 'name' => 'ZEEKR', 'is_active' => true]);
        $md = CarModel::create(['make_id' => $mk->id, 'slug' => '001', 'name' => '001', 'is_active' => true]);
        $eng = CarEngine::create(['model_id' => $md->id, 'code' => $engineCode, 'label' => 'Long Range', 'is_active' => true]);

        return [$md, $eng];
    }

    private function productFor(CarEngine $eng): Product
    {
        $p = Product::factory()->create(['is_active' => true]);
        $p->compatibleEngines()->attach($eng->id);

        return $p;
    }

    private function ids(string $uri): array
    {
        return (new CatalogQuery(Request::create($uri)))->paginate(null)->pluck('id')->all();
    }

    public function test_url_slug_strips_spaces_and_slashes(): void
    {
        $this->assertSame('rwd-100-kwh-2021', CarEngine::urlSlug('RWD 100 kWh 2021-'));
        $this->assertSame('awd-100-kwh', CarEngine::urlSlug('AWD  100 kWh'));      // подвійний пробіл
        $this->assertSame('007-7gt', CarEngine::urlSlug('007 / 7GT'));             // слеш
        $this->assertSame('rwd-116-kwh', CarEngine::urlSlug(' RWD  116 kWh'));     // провідний пробіл
        $this->assertSame('', CarEngine::urlSlug(null));
    }

    public function test_slug_in_url_resolves_to_engine_and_filters(): void
    {
        [$model, $eng] = $this->vehicle('RWD 100 kWh 2021-');
        $wanted = $this->productFor($eng);

        $other = CarEngine::create(['model_id' => $model->id, 'code' => 'AWD 140 kWh', 'is_active' => true]);
        $this->productFor($other);

        $ids = $this->ids('/catalog?make=zeekr&model=001&engine=rwd-100-kwh-2021');

        $this->assertSame([$wanted->id], $ids, 'slug має відфільтрувати саме товар цього двигуна');
    }

    public function test_raw_code_still_works(): void
    {
        [, $eng] = $this->vehicle('RWD 100 kWh 2021-');
        $wanted = $this->productFor($eng);

        // Старе посилання із сирим кодом (URL-декодованим) не має ламатись.
        $ids = $this->ids('/catalog?make=zeekr&model=001&engine='.rawurlencode('RWD 100 kWh 2021-'));

        $this->assertSame([$wanted->id], $ids);
    }

    public function test_unknown_engine_slug_yields_empty_not_error(): void
    {
        [, $eng] = $this->vehicle('RWD 100 kWh 2021-');
        $this->productFor($eng);

        $ids = $this->ids('/catalog?make=zeekr&model=001&engine=nonexistent-motor');

        $this->assertSame([], $ids);
    }

    public function test_pretty_route_accepts_slug_but_not_raw_code(): void
    {
        [, $eng] = $this->vehicle('RWD 100 kWh 2021-');
        $this->productFor($eng);

        // slug — валідний сегмент, сторінка відкривається.
        $this->get('/zapchastyny/zeekr/001/rwd-100-kwh-2021')->assertOk();

        // сирий code із пробілами не матчить route-констрейнт → 404 (тому й slug).
        $this->get('/zapchastyny/zeekr/001/'.rawurlencode('RWD 100 kWh 2021-'))->assertNotFound();
    }
}
