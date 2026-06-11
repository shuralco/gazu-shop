<?php

namespace Tests\Feature\Shop;

use App\Models\Coupon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponTest extends TestCase
{
    use RefreshDatabase;

    private function coupon(array $attrs = []): Coupon
    {
        return Coupon::create(array_merge([
            'code' => 'SAVE10',
            'type' => Coupon::TYPE_PERCENTAGE,
            'value' => 10,
            'is_active' => true,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDay(),
        ], $attrs));
    }

    public function test_active_coupon_in_window_is_valid(): void
    {
        $this->assertTrue($this->coupon()->isValid());
    }

    public function test_inactive_coupon_is_invalid(): void
    {
        $this->assertFalse($this->coupon(['is_active' => false])->isValid());
    }

    public function test_expired_coupon_is_invalid(): void
    {
        $c = $this->coupon(['valid_from' => now()->subDays(5), 'valid_until' => now()->subDay()]);
        $this->assertFalse($c->isValid());
    }

    public function test_future_coupon_is_invalid(): void
    {
        $c = $this->coupon(['valid_from' => now()->addDay(), 'valid_until' => now()->addDays(5)]);
        $this->assertFalse($c->isValid());
    }

    public function test_usage_limit_blocks_when_exhausted(): void
    {
        $c = $this->coupon(['usage_limit' => 3, 'used_count' => 3]);
        $this->assertFalse($c->canBeUsed());

        $c->update(['used_count' => 2]);
        $this->assertTrue($c->fresh()->canBeUsed());
    }

    public function test_percentage_discount(): void
    {
        $this->assertSame(50.0, $this->coupon(['value' => 10])->calculateDiscount(500));
    }

    public function test_fixed_amount_discount(): void
    {
        $c = $this->coupon(['type' => Coupon::TYPE_FIXED_AMOUNT, 'value' => 150]);
        $this->assertSame(150.0, $c->calculateDiscount(500));
    }

    public function test_minimum_amount_not_met_gives_zero(): void
    {
        $c = $this->coupon(['value' => 10, 'minimum_amount' => 1000]);
        $this->assertSame(0.0, $c->calculateDiscount(500));
    }

    public function test_maximum_discount_caps_percentage(): void
    {
        $c = $this->coupon(['value' => 50, 'maximum_discount' => 100]);
        $this->assertSame(100.0, $c->calculateDiscount(1000)); // 50% = 500 → capped to 100
    }

    public function test_free_shipping_discount_equals_shipping_cost(): void
    {
        $c = $this->coupon(['type' => Coupon::TYPE_FREE_SHIPPING, 'value' => 0]);
        $this->assertSame(70.0, $c->calculateDiscount(500, 70));
    }

    public function test_mark_as_used_increments_and_logs(): void
    {
        $c = $this->coupon(['usage_limit' => 5]);
        $order = \App\Models\Order::create(['email' => 'x@example.com', 'total' => 100]);
        $c->markAsUsed(orderId: $order->id, userEmail: 'x@example.com', discountAmount: 50);

        $this->assertSame(1, $c->fresh()->used_count);
        $this->assertDatabaseHas('coupon_usages', ['coupon_id' => $c->id, 'order_id' => $order->id]);
    }

    public function test_by_code_scope_is_case_insensitive_lookup(): void
    {
        $this->coupon(['code' => 'WELCOME']);
        $this->assertNotNull(Coupon::byCode('WELCOME')->first());
    }
}
