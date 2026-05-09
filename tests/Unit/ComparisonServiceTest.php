<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Services\ComparisonService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ComparisonServiceTest extends TestCase
{
    use LazilyRefreshDatabase;

    private ComparisonService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ComparisonService::class);
    }

    public function test_add_product_to_comparison(): void
    {
        $product = Product::factory()->create();
        $result = $this->service->add($product->id);
        $this->assertTrue($result);
        $this->assertEquals(1, $this->service->getCount());
    }

    public function test_max_4_products(): void
    {
        $products = Product::factory()->count(5)->create();
        foreach ($products->take(4) as $p) {
            $this->service->add($p->id);
        }
        $result = $this->service->add($products->last()->id);
        $this->assertFalse($result);
        $this->assertEquals(4, $this->service->getCount());
    }

    public function test_remove_product(): void
    {
        $product = Product::factory()->create();
        $this->service->add($product->id);
        $this->service->remove($product->id);
        $this->assertEquals(0, $this->service->getCount());
    }

    public function test_clear_all(): void
    {
        Product::factory()->count(3)->create()->each(fn ($p) => $this->service->add($p->id));
        $this->service->clear();
        $this->assertEquals(0, $this->service->getCount());
    }
}
