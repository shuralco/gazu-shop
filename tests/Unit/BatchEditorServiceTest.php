<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\CustomerGroup;
use App\Services\BatchEditorService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class BatchEditorServiceTest extends TestCase
{
    use LazilyRefreshDatabase;

    private BatchEditorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(BatchEditorService::class);
    }

    public function test_batch_update_price_set(): void
    {
        $products = Product::factory()->count(3)->create(['price' => 100]);
        $ids = $products->pluck('id')->toArray();
        $this->service->batchUpdatePrice($ids, 'set', 200);
        $this->assertEquals(200, Product::find($ids[0])->price);
    }

    public function test_batch_update_price_increase_percent(): void
    {
        $products = Product::factory()->count(2)->create(['price' => 100]);
        $ids = $products->pluck('id')->toArray();
        $this->service->batchUpdatePrice($ids, 'increase_percent', 10);
        $this->assertEquals(110, Product::find($ids[0])->price);
    }

    public function test_batch_set_sale(): void
    {
        $product = Product::factory()->create(['price' => 1000, 'old_price' => 0]);
        $this->service->batchSetSale([$product->id], 'percent', 20);
        $product->refresh();
        $this->assertEquals(1000, $product->old_price);
        $this->assertEquals(800, $product->price);
    }

    public function test_search_replace(): void
    {
        $product = Product::factory()->create(['title' => 'Test Product Alpha']);
        $result = $this->service->searchReplace([$product->id], 'title', 'Alpha', 'Beta');
        $this->assertEquals(1, $result['count']);
        $this->assertEquals('Test Product Beta', Product::find($product->id)->title);
    }

    public function test_duplicate_products(): void
    {
        $product = Product::factory()->create(['title' => 'Original']);
        $count = $this->service->duplicateProducts([$product->id]);
        $this->assertEquals(1, $count);
        $this->assertEquals(2, Product::count());
        $this->assertDatabaseHas('products', ['title' => '(Копія) Original']);
    }
}
