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

    /** Стандартна (роздрібна) група — та, за якою рахується ціна для гостя. */
    private function retailGroup(float $markup = 0): CustomerGroup
    {
        return CustomerGroup::create([
            'name' => 'retail'.uniqid(),
            'display_name' => 'Роздріб',
            'discount_percentage' => 0,
            'markup_percentage' => $markup,
            'is_default' => true,
            'is_active' => true,
        ]);
    }

    /**
     * Ціна, вписана в «Гуртові ціни» для СТАНДАРТНОЇ групи, — це роздрібна ціна
     * сайту: її бачить гість. Регресія з проду: гість бачив базу × націнку
     * (45 454 грн) замість вписаних 650 USD (29 545 грн).
     */
    public function test_guest_sees_explicit_price_of_default_group(): void
    {
        $retail = $this->retailGroup();
        $product = Product::factory()->create(['price' => 500]);
        ProductGroupPrice::create([
            'product_id' => $product->id,
            'customer_group_id' => $retail->id,
            'price' => 650,
            'min_quantity' => 1,
        ]);
        \App\Support\PricingGroup::flush();

        $view = $product->priceViewForUser(null, 1);
        $this->assertEquals(650, $view['price']);
        // Це звичайна ціна, а не знижка → без перекреслення й бейджа.
        $this->assertEquals(650, $view['regular']);
        $this->assertFalse($view['is_group']);
    }

    /** Гуртова ціна нижча за роздрібну → бейдж і перекреслена роздрібна. */
    public function test_wholesale_price_is_compared_against_default_group_price(): void
    {
        $retail = $this->retailGroup();
        $product = Product::factory()->create(['price' => 500]);
        ProductGroupPrice::create([
            'product_id' => $product->id,
            'customer_group_id' => $retail->id,
            'price' => 650,
            'min_quantity' => 1,
        ]);

        $user = $this->wholesaleUser(0);
        ProductGroupPrice::create([
            'product_id' => $product->id,
            'customer_group_id' => $user->customer_group_id,
            'price' => 600,
            'min_quantity' => 1,
        ]);
        \App\Support\PricingGroup::flush();

        $view = $product->priceViewForUser($user->fresh(), 1);
        $this->assertEquals(600, $view['price']);
        $this->assertEquals(650, $view['regular']);
        $this->assertTrue($view['is_group']);
    }

    /** Явна ціна фінальна: %-знижка групи поверх неї НЕ накладається. */
    public function test_explicit_price_ignores_group_discount(): void
    {
        $this->retailGroup();
        $user = $this->wholesaleUser(20);
        $product = Product::factory()->create(['price' => 500]);
        ProductGroupPrice::create([
            'product_id' => $product->id,
            'customer_group_id' => $user->customer_group_id,
            'price' => 400,
            'min_quantity' => 1,
        ]);
        \App\Support\PricingGroup::flush();

        // 400, а не 400 − 20 % = 320.
        $this->assertEquals(400, $product->effectivePriceForUser($user->fresh(), 1));
    }

    /**
     * Акційна стара ціна лежить на тому ж рівні, що й база (до націнки), тож
     * мусить пройти ту саму націнку. Інакше при +100 % «було 600» виглядало б
     * дешевше за поточні 1000 і перекреслення зникало разом зі знижкою.
     */
    public function test_old_price_gets_same_markup_as_base(): void
    {
        $this->retailGroup(100);
        $product = Product::factory()->create(['price' => 500, 'old_price' => 600]);
        \App\Support\PricingGroup::flush();

        $view = $product->priceViewForUser(null, 1);
        $this->assertEquals(1000, $view['price']);
        $this->assertEquals(1200, $view['old']);
    }

    /** Без акційної ціни перекреслювати нічого — фальшивих знижок не малюємо. */
    public function test_no_old_price_without_promo(): void
    {
        $this->retailGroup(100);
        $product = Product::factory()->create(['price' => 500, 'old_price' => 0]);
        \App\Support\PricingGroup::flush();

        $this->assertNull($product->priceViewForUser(null, 1)['old']);
    }

    /** Явна ціна фінальна → перекреслюємо роздрібну ціну сайту, не базове «було». */
    public function test_explicit_price_strikes_retail_not_base_old_price(): void
    {
        $retail = $this->retailGroup(100);
        $product = Product::factory()->create(['price' => 500, 'old_price' => 600]);

        $user = $this->wholesaleUser(0);
        ProductGroupPrice::create([
            'product_id' => $product->id,
            'customer_group_id' => $user->customer_group_id,
            'price' => 700,
            'min_quantity' => 1,
        ]);
        \App\Support\PricingGroup::flush();

        $view = $product->priceViewForUser($user->fresh(), 1);
        $this->assertEquals(700, $view['price']);
        // Роздріб 1000 (500 × 2), а не базові 600 і не 1200.
        $this->assertEquals(1000, $view['old']);
    }
}
