<?php

namespace Tests\Unit;

use App\Models\CustomerGroup;
use App\Models\Product;
use App\Models\ProductGroupPrice;
use App\Models\User;
use App\Services\PricingService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PricingServiceTest extends TestCase
{
    use LazilyRefreshDatabase;

    private PricingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PricingService::class);
    }

    public function test_regular_price_for_guest(): void
    {
        $product = Product::factory()->create(['price' => 100]);

        $price = $this->service->getProductPrice($product);

        $this->assertEquals(100, $price);
    }

    public function test_group_discount_applied(): void
    {
        $group = CustomerGroup::create(['name' => 'wholesale', 'display_name' => 'Оптовий', 'discount_percentage' => 10, 'is_active' => true]);
        $user = User::factory()->create(['customer_group_id' => $group->id]);
        $product = Product::factory()->create(['price' => 100]);

        $price = $this->service->getProductPrice($product, $user);

        $this->assertEquals(90, $price);
    }

    public function test_group_price_override(): void
    {
        $group = CustomerGroup::create(['name' => 'vip', 'display_name' => 'VIP', 'discount_percentage' => 10, 'is_active' => true]);
        $user = User::factory()->create(['customer_group_id' => $group->id]);
        $product = Product::factory()->create(['price' => 100]);
        ProductGroupPrice::create(['product_id' => $product->id, 'customer_group_id' => $group->id, 'price' => 75, 'min_quantity' => 1]);

        $price = $this->service->getProductPrice($product, $user);

        $this->assertEquals(75, $price);
    }
}
