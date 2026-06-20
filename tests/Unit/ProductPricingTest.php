<?php

namespace Tests\Unit;

use App\Models\CustomerGroup;
use App\Models\Product;
use App\Models\ProductGroupPrice;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

/**
 * Персональні (гуртові) ціни групи на вітрині: effectivePriceForUser /
 * priceViewForUser. Рішення бізнесу: explicit гуртова ціна діє ВІД min_quantity
 * і ГОЛОВНІША за ціну складу; %-знижка групи — поверх ціни складу.
 */
class ProductPricingTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function wholesaleUser(float $discount = 0): User
    {
        $group = CustomerGroup::create([
            'name' => 'wholesale'.uniqid(),
            'display_name' => 'Оптовий',
            'discount_percentage' => $discount,
            'is_active' => true,
        ]);

        return User::factory()->create(['customer_group_id' => $group->id]);
    }

    public function test_guest_gets_regular_price(): void
    {
        $product = Product::factory()->create(['price' => 100]);

        $this->assertEquals(100, $product->effectivePriceForUser(null, 1));
        $view = $product->priceViewForUser(null, 1);
        $this->assertFalse($view['is_group']);
        $this->assertEquals(100, $view['price']);
    }

    public function test_group_discount_percentage(): void
    {
        $user = $this->wholesaleUser(10);
        $product = Product::factory()->create(['price' => 100]);

        $this->assertEquals(90, $product->effectivePriceForUser($user, 1));
        $this->assertTrue($product->priceViewForUser($user, 1)['is_group']);
    }

    public function test_explicit_group_price_applies_from_min_quantity_only(): void
    {
        $user = $this->wholesaleUser(0);
        $product = Product::factory()->create(['price' => 100]);
        ProductGroupPrice::create([
            'product_id' => $product->id,
            'customer_group_id' => $user->customer_group_id,
            'price' => 75,
            'min_quantity' => 10,
        ]);

        // Нижче порогу — звичайна ціна + підказка.
        $this->assertEquals(100, $product->effectivePriceForUser($user, 1));
        $view = $product->priceViewForUser($user, 1);
        $this->assertFalse($view['is_group']);
        $this->assertEquals(10, $view['group_from_qty']);
        $this->assertEquals(75, $view['group_from_price']);

        // Від порогу — гуртова ціна.
        $this->assertEquals(75, $product->effectivePriceForUser($user, 10));
        $this->assertTrue($product->priceViewForUser($user, 10)['is_group']);
    }

    public function test_group_price_overrides_warehouse_price(): void
    {
        $user = $this->wholesaleUser(0);
        $product = Product::factory()->create(['price' => 100]);
        ProductGroupPrice::create([
            'product_id' => $product->id,
            'customer_group_id' => $user->customer_group_id,
            'price' => 75,
            'min_quantity' => 1,
        ]);

        // baseUah=80 (ціна складу) — гуртова 75 головніша.
        $this->assertEquals(75, $product->effectivePriceForUser($user, 1, 80.0));
    }

    public function test_group_discount_applies_on_top_of_warehouse_price(): void
    {
        $user = $this->wholesaleUser(10);
        $product = Product::factory()->create(['price' => 100]);

        // Без explicit рядка: -10% від ціни складу 80 → 72.
        $this->assertEquals(72, $product->effectivePriceForUser($user, 1, 80.0));
    }

    public function test_below_threshold_falls_back_to_group_discount(): void
    {
        $user = $this->wholesaleUser(10);
        $product = Product::factory()->create(['price' => 100]);
        ProductGroupPrice::create([
            'product_id' => $product->id,
            'customer_group_id' => $user->customer_group_id,
            'price' => 75,
            'min_quantity' => 10,
        ]);

        // qty=1: explicit не діє → застосовується %-знижка групи (90).
        $this->assertEquals(90, $product->effectivePriceForUser($user, 1));
        // qty=10: explicit гуртова ціна 75.
        $this->assertEquals(75, $product->effectivePriceForUser($user, 10));
    }
}
