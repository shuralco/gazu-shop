<?php

namespace Tests\Feature\Inventory;

use App\Models\MerchantWarehouse;
use App\Services\Cart\ShippingCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 7 — per-warehouse shipping cost.
 *
 * Calculator groups cart lines by warehouse_id, applies each warehouse's
 * shipping_cost (waived above free_shipping_threshold).
 */
class ShippingCalculatorTest extends TestCase
{
    use RefreshDatabase;

    private ShippingCalculator $calc;

    private MerchantWarehouse $kyiv;

    private MerchantWarehouse $lviv;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calc = app(ShippingCalculator::class);
        $this->kyiv = MerchantWarehouse::factory()->create([
            'code' => 'KY-1', 'city' => 'Київ',
            'shipping_cost' => 60, 'free_shipping_threshold' => 2000,
        ]);
        $this->lviv = MerchantWarehouse::factory()->create([
            'code' => 'LV-1', 'city' => 'Львів',
            'shipping_cost' => 80, 'free_shipping_threshold' => null,
        ]);
    }

    public function test_single_warehouse_below_threshold_charges_shipping(): void
    {
        $cart = [
            '1_w'.$this->kyiv->id => ['price' => 500, 'quantity' => 2, 'warehouse_id' => $this->kyiv->id, 'title' => 'X'],
        ];

        $r = $this->calc->breakdown($cart);

        $this->assertSame(1000.0, $r['subtotal']);
        $this->assertSame(60.0, $r['shipping_total']);
        $this->assertSame(1060.0, $r['grand_total']);
        $this->assertFalse($r['groups'][0]['free']);
    }

    public function test_single_warehouse_at_threshold_is_free(): void
    {
        $cart = [
            '1_w'.$this->kyiv->id => ['price' => 1000, 'quantity' => 2, 'warehouse_id' => $this->kyiv->id, 'title' => 'X'],
        ];

        $r = $this->calc->breakdown($cart);

        $this->assertSame(2000.0, $r['subtotal']);
        $this->assertSame(0.0, $r['shipping_total']);
        $this->assertTrue($r['groups'][0]['free']);
    }

    public function test_warehouse_without_threshold_always_charges(): void
    {
        $cart = [
            '1_w'.$this->lviv->id => ['price' => 99999, 'quantity' => 1, 'warehouse_id' => $this->lviv->id, 'title' => 'X'],
        ];

        $r = $this->calc->breakdown($cart);

        $this->assertSame(80.0, $r['shipping_total']);
        $this->assertFalse($r['groups'][0]['free']);
    }

    public function test_multi_warehouse_breakdown_sums_per_group(): void
    {
        $cart = [
            '1_w'.$this->kyiv->id => ['price' => 500, 'quantity' => 1, 'warehouse_id' => $this->kyiv->id, 'title' => 'A'],
            '2_w'.$this->lviv->id => ['price' => 1500, 'quantity' => 1, 'warehouse_id' => $this->lviv->id, 'title' => 'B'],
        ];

        $r = $this->calc->breakdown($cart);

        $this->assertCount(2, $r['groups']);
        $this->assertSame(2000.0, $r['subtotal']);
        $this->assertSame(140.0, $r['shipping_total']); // 60 + 80, neither hits its threshold
        $this->assertSame(2140.0, $r['grand_total']);
    }

    public function test_one_warehouse_free_other_paid(): void
    {
        $cart = [
            '1_w'.$this->kyiv->id => ['price' => 1000, 'quantity' => 3, 'warehouse_id' => $this->kyiv->id, 'title' => 'A'],
            '2_w'.$this->lviv->id => ['price' => 200, 'quantity' => 1, 'warehouse_id' => $this->lviv->id, 'title' => 'B'],
        ];

        $r = $this->calc->breakdown($cart);

        $kyivGroup = collect($r['groups'])->firstWhere('warehouse.id', $this->kyiv->id);
        $lvivGroup = collect($r['groups'])->firstWhere('warehouse.id', $this->lviv->id);

        $this->assertTrue($kyivGroup['free'], 'Kyiv 3000 >= 2000 threshold → free');
        $this->assertFalse($lvivGroup['free']);
        $this->assertSame(80.0, $r['shipping_total']);
    }
}
