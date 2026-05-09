<?php

namespace Tests\Feature\Inventory;

use App\Models\Inventory;
use App\Models\MerchantWarehouse;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\Warehouse\InventoryService;
use App\Services\Warehouse\OrderFulfillmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderFulfillmentServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderFulfillmentService $svc;

    private InventoryService $inv;

    private MerchantWarehouse $warehouse;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->svc = app(OrderFulfillmentService::class);
        $this->inv = app(InventoryService::class);
        // Reuse the MAIN-01 created by seed migration so MerchantWarehouse::default()
        // and $this->warehouse always point to the same record.
        $this->warehouse = MerchantWarehouse::default()
            ?? MerchantWarehouse::factory()->default()->create();
        $this->product = Product::factory()->create();
        $this->inv->add($this->product, $this->warehouse, 50);
    }

    public function test_ship_order_decrements_inventory(): void
    {
        $order = $this->makeOrder([$this->product->id => 3]);

        $result = $this->svc->shipOrder($order);

        $this->assertSame(1, $result['shipped']);
        $this->assertSame(0, $result['skipped']);

        $inv = Inventory::forProduct($this->product->id)->forWarehouse($this->warehouse->id)->first();
        $this->assertSame(47, $inv->quantity);
    }

    public function test_ship_order_logs_ship_movement_referencing_order(): void
    {
        $order = $this->makeOrder([$this->product->id => 2]);
        $this->svc->shipOrder($order);

        $movement = StockMovement::query()
            ->where('reference_type', $order->getMorphClass())
            ->where('reference_id', $order->id)
            ->where('type', StockMovement::TYPE_SHIP)
            ->first();

        $this->assertNotNull($movement);
        $this->assertSame(-2, $movement->quantity);
    }

    public function test_ship_order_is_idempotent_on_retry(): void
    {
        $order = $this->makeOrder([$this->product->id => 2]);
        $this->svc->shipOrder($order);
        $result = $this->svc->shipOrder($order);

        $this->assertSame(0, $result['shipped']);
        $this->assertSame(0, $result['skipped']);
        $this->assertSame('already_shipped', $result['reason']);

        $inv = Inventory::forProduct($this->product->id)->forWarehouse($this->warehouse->id)->first();
        $this->assertSame(48, $inv->quantity); // not double-decremented
    }

    public function test_ship_order_sets_fulfillment_status_shipped(): void
    {
        $order = $this->makeOrder([$this->product->id => 1]);
        $this->svc->shipOrder($order);

        $this->assertSame('shipped', $order->refresh()->fulfillment_status);
    }

    public function test_ship_order_falls_back_to_default_warehouse(): void
    {
        // Order without explicit warehouse_id
        $order = $this->makeOrder([$this->product->id => 1], explicitWarehouse: false);

        // Sanity: setUp's warehouse IS the default.
        $default = MerchantWarehouse::default();
        $this->assertNotNull($default, 'No default warehouse found.');
        $this->assertSame(
            $this->warehouse->id,
            $default->id,
            sprintf('Default mismatch: expected w#%d, got w#%d', $this->warehouse->id, $default->id),
        );

        $result = $this->svc->shipOrder($order);

        $this->assertSame(1, $result['shipped'], 'errors='.json_encode($result));
        $inv = Inventory::forProduct($this->product->id)->forWarehouse($this->warehouse->id)->first();
        $this->assertSame(49, $inv->quantity);
    }

    public function test_ship_order_skips_item_when_inventory_missing(): void
    {
        // Product with no inventory anywhere
        $other = Product::factory()->create();
        $order = $this->makeOrder([$other->id => 1]);

        $result = $this->svc->shipOrder($order);

        $this->assertSame(0, $result['shipped']);
        $this->assertSame(1, $result['skipped']);
    }

    /**
     * Helper: build an order with the given product=>qty pairs.
     */
    private function makeOrder(array $items, bool $explicitWarehouse = true): Order
    {
        $order = Order::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'phone' => '+380987029924',
            'total' => 1000,
            'shipping_cost' => 50,
            'warehouse_id' => $explicitWarehouse ? $this->warehouse->id : null,
            'status' => 'new',
            'payment_status' => 'pending',
            'shipping_provider' => 'novaposhta',
            'shipping_method' => 'warehouse',
        ]);

        foreach ($items as $productId => $qty) {
            OrderProduct::create([
                'order_id' => $order->id,
                'product_id' => $productId,
                'warehouse_id' => $explicitWarehouse ? $this->warehouse->id : null,
                'title' => 'test',
                'slug' => 'test-'.$productId,
                'price' => 100,
                'quantity' => $qty,
            ]);
        }

        return $order->fresh(['orderProducts.product', 'warehouse']);
    }
}
