<?php

namespace Tests\Feature\Inventory;

use App\Models\Inventory;
use App\Models\MerchantWarehouse;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\Warehouse\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use RuntimeException;
use Tests\TestCase;

/**
 * Covers invariants from docs/INVENTORY-LOGIC.md §2:
 *  1) quantity ≥ 0
 *  2) reserved_quantity ≥ 0
 *  3) reserved_quantity ≤ quantity
 *  4) available = quantity − reserved_quantity (accessor)
 *  5) SUM(stock_movements.quantity for warehouse+product) == inventory.quantity
 *  6) SUM(stock_movements.reserved_delta) == inventory.reserved_quantity
 */
class InventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    private InventoryService $svc;

    private MerchantWarehouse $warehouse;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->svc = app(InventoryService::class);
        $this->warehouse = MerchantWarehouse::factory()->default()->create();
        $this->product = Product::factory()->create();
    }

    // ─── add ────────────────────────────────────────────────────────────────

    public function test_add_creates_inventory_row_and_logs_movement(): void
    {
        $this->svc->add($this->product, $this->warehouse, 10);

        $inv = Inventory::query()->forProduct($this->product->id)->forWarehouse($this->warehouse->id)->first();
        $this->assertSame(10, $inv->quantity);
        $this->assertSame(0, $inv->reserved_quantity);

        $this->assertMovement(StockMovement::TYPE_INCOME, 10, 0);
        $this->assertSumInvariant();
    }

    public function test_add_increments_existing_quantity(): void
    {
        $this->svc->add($this->product, $this->warehouse, 5);
        $this->svc->add($this->product, $this->warehouse, 3);

        $inv = $this->inv();
        $this->assertSame(8, $inv->quantity);
        $this->assertSumInvariant();
    }

    public function test_add_rejects_zero_or_negative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->svc->add($this->product, $this->warehouse, 0);
    }

    // ─── subtract ───────────────────────────────────────────────────────────

    public function test_subtract_decrements_quantity(): void
    {
        $this->svc->add($this->product, $this->warehouse, 10);
        $this->svc->subtract($this->product, $this->warehouse, 3);

        $this->assertSame(7, $this->inv()->quantity);
        $this->assertSumInvariant();
    }

    public function test_subtract_throws_if_would_drop_below_reserved(): void
    {
        $this->svc->add($this->product, $this->warehouse, 10);
        $this->svc->reserve($this->product, $this->warehouse, 8);

        $this->expectException(RuntimeException::class);
        // Would leave 1 physical, but 8 are reserved.
        $this->svc->subtract($this->product, $this->warehouse, 9);
    }

    // ─── reserve / release ──────────────────────────────────────────────────

    public function test_reserve_increments_reserved_only(): void
    {
        $this->svc->add($this->product, $this->warehouse, 10);
        $this->svc->reserve($this->product, $this->warehouse, 4);

        $inv = $this->inv();
        $this->assertSame(10, $inv->quantity);
        $this->assertSame(4, $inv->reserved_quantity);
        $this->assertSame(6, $inv->available_quantity);
    }

    public function test_reserve_throws_when_insufficient_available(): void
    {
        $this->svc->add($this->product, $this->warehouse, 5);
        $this->svc->reserve($this->product, $this->warehouse, 4);

        $this->expectException(RuntimeException::class);
        $this->svc->reserve($this->product, $this->warehouse, 2); // only 1 available
    }

    public function test_release_decrements_reserved(): void
    {
        $this->svc->add($this->product, $this->warehouse, 10);
        $this->svc->reserve($this->product, $this->warehouse, 6);
        $this->svc->release($this->product, $this->warehouse, 4);

        $inv = $this->inv();
        $this->assertSame(2, $inv->reserved_quantity);
    }

    public function test_release_clamped_to_current_reserved(): void
    {
        $this->svc->add($this->product, $this->warehouse, 5);
        $this->svc->reserve($this->product, $this->warehouse, 2);
        $this->svc->release($this->product, $this->warehouse, 999); // larger than reserved

        $this->assertSame(0, $this->inv()->reserved_quantity);
    }

    // ─── ship (reserve → physical removal) ──────────────────────────────────

    public function test_ship_decrements_both_reserved_and_quantity(): void
    {
        $this->svc->add($this->product, $this->warehouse, 10);
        $this->svc->reserve($this->product, $this->warehouse, 3);
        $this->svc->ship($this->product, $this->warehouse, 3);

        $inv = $this->inv();
        $this->assertSame(7, $inv->quantity);
        $this->assertSame(0, $inv->reserved_quantity);
        $this->assertSumInvariant();
    }

    public function test_ship_throws_without_prior_reservation(): void
    {
        $this->svc->add($this->product, $this->warehouse, 10);

        $this->expectException(RuntimeException::class);
        $this->svc->ship($this->product, $this->warehouse, 1);
    }

    // ─── adjust (inventory audit) ───────────────────────────────────────────

    public function test_adjust_sets_absolute_quantity_and_logs_delta(): void
    {
        $this->svc->add($this->product, $this->warehouse, 10);
        $this->svc->adjust($this->product, $this->warehouse, 7, reason: 'test count');

        $inv = $this->inv();
        $this->assertSame(7, $inv->quantity);
        $this->assertNotNull($inv->last_counted_at);

        $this->assertMovement(StockMovement::TYPE_ADJUSTMENT, -3, 0);
        $this->assertSumInvariant();
    }

    public function test_adjust_rejects_negative_target(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->svc->adjust($this->product, $this->warehouse, -1);
    }

    public function test_adjust_throws_if_would_drop_below_reserved(): void
    {
        $this->svc->add($this->product, $this->warehouse, 10);
        $this->svc->reserve($this->product, $this->warehouse, 5);

        $this->expectException(RuntimeException::class);
        $this->svc->adjust($this->product, $this->warehouse, 4); // can't go below 5 reserved
    }

    // ─── transfer between warehouses ────────────────────────────────────────

    public function test_move_creates_paired_out_and_in_movements(): void
    {
        $this->svc->add($this->product, $this->warehouse, 10);
        $dest = MerchantWarehouse::factory()->create(['code' => 'DST-01']);

        $result = $this->svc->move($this->product, $this->warehouse, $dest, 4);

        $this->assertSame(6, $this->inv()->quantity);

        $destInv = Inventory::query()->forProduct($this->product->id)->forWarehouse($dest->id)->first();
        $this->assertSame(4, $destInv->quantity);

        $this->assertSame(StockMovement::TYPE_TRANSFER_OUT, $result['out']->type);
        $this->assertSame(StockMovement::TYPE_TRANSFER_IN, $result['in']->type);
        $this->assertSumInvariant();
    }

    public function test_move_rejects_same_warehouse(): void
    {
        $this->svc->add($this->product, $this->warehouse, 10);
        $this->expectException(InvalidArgumentException::class);
        $this->svc->move($this->product, $this->warehouse, $this->warehouse, 1);
    }

    // ─── full lifecycle round-trip ──────────────────────────────────────────

    public function test_full_order_lifecycle_preserves_invariants(): void
    {
        $this->svc->add($this->product, $this->warehouse, 100, type: 'income');     // income
        $this->svc->reserve($this->product, $this->warehouse, 5);                    // cart
        $this->svc->reserve($this->product, $this->warehouse, 3);                    // another cart
        $this->svc->release($this->product, $this->warehouse, 5);                    // first cart abandoned
        $this->svc->ship($this->product, $this->warehouse, 3);                       // second TTN created

        $inv = $this->inv();
        $this->assertSame(97, $inv->quantity);
        $this->assertSame(0, $inv->reserved_quantity);
        $this->assertSumInvariant();
    }

    // ─── helpers ────────────────────────────────────────────────────────────

    private function inv(): Inventory
    {
        return Inventory::query()
            ->forProduct($this->product->id)
            ->forWarehouse($this->warehouse->id)
            ->firstOrFail();
    }

    private function assertMovement(string $type, int $qty, int $reservedDelta): void
    {
        $m = StockMovement::query()
            ->where('warehouse_id', $this->warehouse->id)
            ->where('product_id', $this->product->id)
            ->latest('id')
            ->first();
        $this->assertSame($type, $m->type);
        $this->assertSame($qty, $m->quantity);
        $this->assertSame($reservedDelta, $m->reserved_delta);
    }

    /**
     * Invariant 5: SUM(movements.quantity) == inventory.quantity
     * Invariant 6: SUM(movements.reserved_delta) == inventory.reserved_quantity
     */
    private function assertSumInvariant(): void
    {
        $inv = $this->inv();
        $sumQty = (int) StockMovement::query()
            ->where('warehouse_id', $this->warehouse->id)
            ->where('product_id', $this->product->id)
            ->sum('quantity');
        $sumReserved = (int) StockMovement::query()
            ->where('warehouse_id', $this->warehouse->id)
            ->where('product_id', $this->product->id)
            ->sum('reserved_delta');
        $this->assertSame($inv->quantity, $sumQty, 'Invariant 5 violated: SUM(quantity) != inventory.quantity');
        $this->assertSame($inv->reserved_quantity, $sumReserved, 'Invariant 6 violated: SUM(reserved_delta) != inventory.reserved_quantity');
    }
}
