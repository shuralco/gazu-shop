<?php

namespace Tests\Feature\Inventory;

use App\Models\Inventory;
use App\Models\InventoryTransfer;
use App\Models\InventoryTransferItem;
use App\Models\MerchantWarehouse;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\Warehouse\InventoryService;
use App\Services\Warehouse\TransferService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use RuntimeException;
use Tests\TestCase;

/**
 * State machine + inventory invariants for inter-warehouse transfers.
 * Service: app/Services/Warehouse/TransferService.
 */
class TransferServiceTest extends TestCase
{
    use RefreshDatabase;

    private TransferService $svc;

    private InventoryService $inv;

    private MerchantWarehouse $from;

    private MerchantWarehouse $to;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->svc = app(TransferService::class);
        $this->inv = app(InventoryService::class);
        $this->from = MerchantWarehouse::factory()->default()->create(['code' => 'FROM-1']);
        $this->to = MerchantWarehouse::factory()->create(['code' => 'TO-1']);
        $this->product = Product::factory()->create();

        // Seed source warehouse with stock.
        $this->inv->add($this->product, $this->from, 100);
    }

    public function test_create_draft_assigns_unique_code_and_draft_status(): void
    {
        $t = $this->svc->createDraft($this->from, $this->to);

        $this->assertSame(InventoryTransfer::STATUS_DRAFT, $t->status);
        $this->assertMatchesRegularExpression('/^TRF-\d{4}-\d{6}$/', $t->code);
    }

    public function test_create_draft_rejects_same_warehouse(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->svc->createDraft($this->from, $this->from);
    }

    public function test_add_item_only_when_editable(): void
    {
        $t = $this->svc->createDraft($this->from, $this->to);
        $this->svc->addItem($t, $this->product, 5);

        $t->refresh();
        $this->assertCount(1, $t->items);
        $this->assertSame(5, $t->items->first()->quantity);
    }

    public function test_add_item_rejects_zero_or_negative(): void
    {
        $t = $this->svc->createDraft($this->from, $this->to);
        $this->expectException(InvalidArgumentException::class);
        $this->svc->addItem($t, $this->product, 0);
    }

    public function test_add_item_idempotent_per_product(): void
    {
        $t = $this->svc->createDraft($this->from, $this->to);
        $this->svc->addItem($t, $this->product, 3);
        $this->svc->addItem($t, $this->product, 7); // overrides

        $this->assertSame(1, InventoryTransferItem::where('transfer_id', $t->id)->count());
        $this->assertSame(7, InventoryTransferItem::where('transfer_id', $t->id)->value('quantity'));
    }

    public function test_ship_decrements_source_inventory(): void
    {
        $t = $this->svc->createDraft($this->from, $this->to);
        $this->svc->addItem($t, $this->product, 5);
        $this->svc->ship($t);

        $t->refresh();
        $this->assertSame(InventoryTransfer::STATUS_SENT, $t->status);
        $this->assertNotNull($t->shipped_at);

        $sourceInv = Inventory::query()
            ->forProduct($this->product->id)
            ->forWarehouse($this->from->id)
            ->first();
        $this->assertSame(95, $sourceInv->quantity); // 100 − 5
    }

    public function test_ship_creates_transfer_out_movement(): void
    {
        $t = $this->svc->createDraft($this->from, $this->to);
        $this->svc->addItem($t, $this->product, 5);
        $this->svc->ship($t);

        $movement = StockMovement::query()
            ->where('reference_type', $t->getMorphClass())
            ->where('reference_id', $t->id)
            ->where('type', StockMovement::TYPE_TRANSFER_OUT)
            ->first();

        $this->assertNotNull($movement);
        $this->assertSame(-5, $movement->quantity);
        $this->assertSame($this->from->id, $movement->warehouse_id);
    }

    public function test_ship_rejects_empty_transfer(): void
    {
        $t = $this->svc->createDraft($this->from, $this->to);
        $this->expectException(RuntimeException::class);
        $this->svc->ship($t);
    }

    public function test_ship_rejects_non_draft(): void
    {
        $t = $this->svc->createDraft($this->from, $this->to);
        $this->svc->addItem($t, $this->product, 5);
        $this->svc->ship($t);

        $this->expectException(RuntimeException::class);
        $this->svc->ship($t->refresh());
    }

    public function test_receive_increments_destination_inventory(): void
    {
        $t = $this->svc->createDraft($this->from, $this->to);
        $this->svc->addItem($t, $this->product, 5);
        $this->svc->ship($t);
        $this->svc->receive($t->refresh());

        $t->refresh();
        $this->assertSame(InventoryTransfer::STATUS_RECEIVED, $t->status);

        $destInv = Inventory::query()
            ->forProduct($this->product->id)
            ->forWarehouse($this->to->id)
            ->first();
        $this->assertSame(5, $destInv->quantity);
    }

    public function test_receive_creates_transfer_in_movement(): void
    {
        $t = $this->svc->createDraft($this->from, $this->to);
        $this->svc->addItem($t, $this->product, 4);
        $this->svc->ship($t);
        $this->svc->receive($t->refresh());

        $movement = StockMovement::query()
            ->where('reference_type', $t->getMorphClass())
            ->where('reference_id', $t->id)
            ->where('type', StockMovement::TYPE_TRANSFER_IN)
            ->first();

        $this->assertNotNull($movement);
        $this->assertSame(4, $movement->quantity);
        $this->assertSame($this->to->id, $movement->warehouse_id);
    }

    public function test_receive_rejects_non_sent(): void
    {
        $t = $this->svc->createDraft($this->from, $this->to);
        $this->svc->addItem($t, $this->product, 1);

        $this->expectException(RuntimeException::class);
        $this->svc->receive($t);
    }

    public function test_cancel_from_draft_no_inventory_effect(): void
    {
        $t = $this->svc->createDraft($this->from, $this->to);
        $this->svc->addItem($t, $this->product, 5);

        $sourceBefore = Inventory::forProduct($this->product->id)->forWarehouse($this->from->id)->value('quantity');
        $this->svc->cancel($t, reason: 'не потрібно');

        $sourceAfter = Inventory::forProduct($this->product->id)->forWarehouse($this->from->id)->value('quantity');
        $this->assertSame($sourceBefore, $sourceAfter);
        $this->assertSame(InventoryTransfer::STATUS_CANCELLED, $t->refresh()->status);
    }

    public function test_cancel_from_sent_returns_goods_to_source(): void
    {
        $t = $this->svc->createDraft($this->from, $this->to);
        $this->svc->addItem($t, $this->product, 5);
        $this->svc->ship($t);
        // After ship: source = 95
        $this->assertSame(95, Inventory::forProduct($this->product->id)->forWarehouse($this->from->id)->value('quantity'));

        $this->svc->cancel($t->refresh(), reason: 'transit lost');

        // Source restored to 100
        $this->assertSame(100, Inventory::forProduct($this->product->id)->forWarehouse($this->from->id)->value('quantity'));
        $this->assertSame(InventoryTransfer::STATUS_CANCELLED, $t->refresh()->status);
    }

    public function test_cannot_cancel_received(): void
    {
        $t = $this->svc->createDraft($this->from, $this->to);
        $this->svc->addItem($t, $this->product, 1);
        $this->svc->ship($t);
        $this->svc->receive($t->refresh());

        $this->expectException(RuntimeException::class);
        $this->svc->cancel($t->refresh());
    }
}
