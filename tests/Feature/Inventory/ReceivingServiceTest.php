<?php

namespace Tests\Feature\Inventory;

use App\Models\Inventory;
use App\Models\MerchantWarehouse;
use App\Models\Product;
use App\Models\ReceivingOrder;
use App\Models\StockMovement;
use App\Services\Warehouse\ReceivingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use RuntimeException;
use Tests\TestCase;

class ReceivingServiceTest extends TestCase
{
    use RefreshDatabase;

    private ReceivingService $svc;

    private MerchantWarehouse $warehouse;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->svc = app(ReceivingService::class);
        $this->warehouse = MerchantWarehouse::factory()->default()->create();
        $this->product = Product::factory()->create();
    }

    public function test_create_draft_assigns_code_and_draft_status(): void
    {
        $r = $this->svc->createDraft($this->warehouse, supplierName: 'ТОВ Постачальник');

        $this->assertSame(ReceivingOrder::STATUS_DRAFT, $r->status);
        $this->assertMatchesRegularExpression('/^RCV-\d{4}-\d{6}$/', $r->code);
        $this->assertSame('ТОВ Постачальник', $r->supplier_name);
    }

    public function test_add_item_only_when_editable(): void
    {
        $r = $this->svc->createDraft($this->warehouse);
        $item = $this->svc->addItem($r, $this->product, 10, costPrice: 50.0);

        $this->assertSame(10, $item->quantity);
        $this->assertSame('50.00', (string) $item->cost_price);
    }

    public function test_add_item_rejects_zero(): void
    {
        $r = $this->svc->createDraft($this->warehouse);
        $this->expectException(InvalidArgumentException::class);
        $this->svc->addItem($r, $this->product, 0);
    }

    public function test_receive_increments_inventory(): void
    {
        $r = $this->svc->createDraft($this->warehouse);
        $this->svc->addItem($r, $this->product, 25);
        $this->svc->receive($r);

        $r->refresh();
        $this->assertSame(ReceivingOrder::STATUS_RECEIVED, $r->status);
        $this->assertNotNull($r->received_at);

        $inv = Inventory::query()
            ->forProduct($this->product->id)
            ->forWarehouse($this->warehouse->id)
            ->first();
        $this->assertSame(25, $inv->quantity);
    }

    public function test_receive_creates_income_movement(): void
    {
        $r = $this->svc->createDraft($this->warehouse);
        $this->svc->addItem($r, $this->product, 25);
        $this->svc->receive($r);

        $movement = StockMovement::query()
            ->where('reference_type', $r->getMorphClass())
            ->where('reference_id', $r->id)
            ->where('type', StockMovement::TYPE_INCOME)
            ->first();

        $this->assertNotNull($movement);
        $this->assertSame(25, $movement->quantity);
    }

    public function test_receive_rejects_empty_order(): void
    {
        $r = $this->svc->createDraft($this->warehouse);
        $this->expectException(RuntimeException::class);
        $this->svc->receive($r);
    }

    public function test_cannot_receive_already_received(): void
    {
        $r = $this->svc->createDraft($this->warehouse);
        $this->svc->addItem($r, $this->product, 1);
        $this->svc->receive($r);

        $this->expectException(RuntimeException::class);
        $this->svc->receive($r->refresh());
    }

    public function test_cancel_from_draft_no_inventory_effect(): void
    {
        $r = $this->svc->createDraft($this->warehouse);
        $this->svc->addItem($r, $this->product, 10);
        $this->svc->cancel($r, reason: 'duplicate');

        $invCount = Inventory::query()
            ->forProduct($this->product->id)
            ->forWarehouse($this->warehouse->id)
            ->count();
        $this->assertSame(0, $invCount);
        $this->assertSame(ReceivingOrder::STATUS_CANCELLED, $r->refresh()->status);
    }

    public function test_cannot_cancel_already_received(): void
    {
        $r = $this->svc->createDraft($this->warehouse);
        $this->svc->addItem($r, $this->product, 1);
        $this->svc->receive($r);

        $this->expectException(RuntimeException::class);
        $this->svc->cancel($r->refresh());
    }
}
