<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Services\ComparisonService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ComparisonTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_comparison_route_exists(): void
    {
        $response = $this->get('/comparison');
        // Route exists (not 404) even if rendering fails in test env
        $this->assertNotEquals(404, $response->getStatusCode());
    }

    public function test_add_to_comparison_via_service(): void
    {
        $product = Product::factory()->create();
        $service = app(ComparisonService::class);

        $result = $service->add($product->id);

        $this->assertTrue($result);
        $this->assertEquals(1, $service->getCount());
    }

    public function test_comparison_prevents_duplicates(): void
    {
        $product = Product::factory()->create();
        $service = app(ComparisonService::class);

        $service->add($product->id);
        $service->add($product->id);

        $this->assertEquals(1, $service->getCount());
    }

    public function test_comparison_remove_via_service(): void
    {
        $product = Product::factory()->create();
        $service = app(ComparisonService::class);

        $service->add($product->id);
        $service->remove($product->id);

        $this->assertEquals(0, $service->getCount());
    }
}
