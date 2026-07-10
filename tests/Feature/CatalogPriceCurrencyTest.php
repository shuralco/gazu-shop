<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\Product;
use App\Services\Gazu\CatalogQuery;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * products.price лежить у валюті товару (price_currency), вітрина показує грн.
 * Фільтр «від/до», діапазон повзунка і сортування мусять рахувати в ГРН,
 * інакше товар за 458 ₴ ловиться діапазоном «10–11».
 */
class CatalogPriceCurrencyTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget('currencies:map');

        Currency::query()->update(['is_base' => false, 'is_active' => false]);
        Currency::updateOrCreate(['code' => 'UAH'], ['name' => 'Гривня', 'symbol' => '₴', 'rate' => 1, 'is_base' => true, 'is_active' => true, 'sort_order' => 0]);
        // 1 UAH = 0.025 EUR → товар за 10 EUR коштує 400 ₴.
        Currency::updateOrCreate(['code' => 'EUR'], ['name' => 'Євро', 'symbol' => '€', 'rate' => 0.025, 'is_base' => false, 'is_active' => true, 'sort_order' => 1]);
        Cache::forget('currencies:map');
    }

    private function query(string $uri): CatalogQuery
    {
        return new CatalogQuery(Request::create($uri));
    }

    private function product(string $title, float $price, string $currency): Product
    {
        return Product::factory()->create([
            'title' => $title, 'is_active' => true,
            'price' => $price, 'price_currency' => $currency,
        ]);
    }

    public function test_display_price_is_the_contract_we_filter_by(): void
    {
        $p = $this->product('Євровий', 10, 'EUR');

        $this->assertSame(400.0, (float) $p->display_price, 'перевірка вихідної умови: 10 EUR = 400 ₴');
    }

    public function test_price_filter_uses_hryvnia_not_raw_amount(): void
    {
        $eur = $this->product('Євровий', 10, 'EUR');   // 400 ₴
        $uah = $this->product('Гривневий', 400, 'UAH'); // 400 ₴

        $ids = $this->query('/catalog?min=300&max=500')->paginate(null)->pluck('id')->all();

        sort($ids);
        $this->assertEquals(collect([$eur->id, $uah->id])->sort()->values()->all(), $ids, 'обидва товари коштують 400 ₴');
    }

    public function test_raw_amount_no_longer_matches(): void
    {
        $this->product('Євровий', 10, 'EUR'); // 400 ₴, сира ціна 10

        $ids = $this->query('/catalog?min=9&max=11')->paginate(null)->pluck('id')->all();

        $this->assertCount(0, $ids, 'діапазон 9–11 ₴ не має ловити товар за 400 ₴');
    }

    public function test_price_range_is_reported_in_hryvnia(): void
    {
        $this->product('Євровий', 10, 'EUR');    // 400 ₴
        $this->product('Гривневий', 100, 'UAH'); // 100 ₴

        $range = $this->query('/catalog')->priceRange(null);

        $this->assertSame(100, $range['min']);
        $this->assertSame(400, $range['max']);
    }

    public function test_sorting_by_price_compares_hryvnia(): void
    {
        $cheapUah = $this->product('Дешевий грн', 200, 'UAH');  // 200 ₴
        $pricyEur = $this->product('Дорогий євро', 10, 'EUR');  // 400 ₴, сира ціна 10

        $asc = $this->query('/catalog?sort=price-asc')->paginate(null)->pluck('id')->all();
        $this->assertSame([$cheapUah->id, $pricyEur->id], $asc, 'сира ціна 10 < 200 не має робити євровий товар дешевшим');

        $desc = $this->query('/catalog?sort=price-desc')->paginate(null)->pluck('id')->all();
        $this->assertSame([$pricyEur->id, $cheapUah->id], $desc);
    }

    public function test_unknown_currency_falls_back_to_base(): void
    {
        // Валюта, якої немає в довіднику, не має обнуляти чи спотворювати ціну.
        $p = $this->product('Невідома валюта', 350, 'XXX');

        $ids = $this->query('/catalog?min=300&max=400')->paginate(null)->pluck('id')->all();

        $this->assertSame([$p->id], $ids);
    }
}
