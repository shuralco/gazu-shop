<?php

namespace Tests\Feature\Inventory;

use App\Models\MerchantWarehouse;
use App\Models\NpShipment;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Services\Shipping\OrderShipmentSplitter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 5 — split TTN per warehouse.
 *
 * Splitter groups order_products by warehouse_id and creates one draft
 * NpShipment per group. Idempotent on re-run.
 */
class OrderShipmentSplitterTest extends TestCase
{
    use RefreshDatabase;

    private OrderShipmentSplitter $splitter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->splitter = app(OrderShipmentSplitter::class);
    }

    public function test_no_split_when_no_warehouse_id(): void
    {
        $order = $this->seedOrderWithLines([
            ['warehouse_id' => null, 'qty' => 1, 'price' => 100],
            ['warehouse_id' => null, 'qty' => 2, 'price' => 50],
        ]);

        $shipments = $this->splitter->splitNova($order);

        $this->assertCount(0, $shipments);
        $this->assertCount(0, $order->fresh()->relationLoaded('npShipments') ? $order->npShipments : NpShipment::where('order_id', $order->id)->get());
    }

    public function test_creates_one_shipment_per_distinct_warehouse(): void
    {
        $kyiv = MerchantWarehouse::factory()->create(['code' => 'KY']);
        $lviv = MerchantWarehouse::factory()->create(['code' => 'LV']);

        $order = $this->seedOrderWithLines([
            ['warehouse_id' => $kyiv->id, 'qty' => 1, 'price' => 100],
            ['warehouse_id' => $kyiv->id, 'qty' => 2, 'price' => 50],
            ['warehouse_id' => $lviv->id, 'qty' => 1, 'price' => 200],
        ]);

        $shipments = $this->splitter->splitNova($order);

        $this->assertCount(2, $shipments);
        $this->assertSame(
            [$kyiv->id, $lviv->id],
            $shipments->pluck('warehouse_id')->sort()->values()->all()
        );
    }

    public function test_split_is_idempotent(): void
    {
        $kyiv = MerchantWarehouse::factory()->create(['code' => 'KY']);

        $order = $this->seedOrderWithLines([
            ['warehouse_id' => $kyiv->id, 'qty' => 1, 'price' => 100],
        ]);

        $first = $this->splitter->splitNova($order);
        $second = $this->splitter->splitNova($order);

        $this->assertCount(1, $first);
        $this->assertCount(0, $second, 'Second run must skip existing shipments');
        $this->assertSame(1, NpShipment::where('order_id', $order->id)->count());
    }

    public function test_shipment_pin_to_warehouse_inherits_recipient_from_order(): void
    {
        $wh = MerchantWarehouse::factory()->create();

        $order = $this->seedOrderWithLines(
            [['warehouse_id' => $wh->id, 'qty' => 1, 'price' => 100]],
            [
                'first_name' => 'Іван',
                'last_name' => 'Тестовий',
                'phone' => '+380501234567',
                'shipping_city' => 'Київ',
                'shipping_warehouse' => 'Відділення №5',
            ],
        );

        $shipment = $this->splitter->splitNova($order)->first();

        $this->assertSame($wh->id, $shipment->warehouse_id);
        $this->assertStringContainsString('Іван', $shipment->recipient_name);
        $this->assertSame('+380501234567', $shipment->recipient_phone);
        $this->assertSame('Київ', $shipment->recipient_city_name);
        $this->assertSame('draft', $shipment->status);
    }

    private function seedOrderWithLines(array $lines, array $orderOverrides = []): Order
    {
        $order = Order::create(array_merge([
            'first_name' => 'T', 'last_name' => 'U',
            'phone' => '+1', 'email' => 'x@y.com',
            'shipping_method' => 'novaposhta',
            'payment_method' => 'liqpay', 'payment_status' => 'pending',
            'subtotal' => 1, 'total' => 1,
            'shipping_cost' => 0, 'discount_amount' => 0,
            'fulfillment_status' => 'pending', 'status' => 'pending',
        ], $orderOverrides));

        foreach ($lines as $line) {
            $product = Product::factory()->create();
            OrderProduct::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'warehouse_id' => $line['warehouse_id'],
                'title' => 'Test',
                'price' => $line['price'],
                'quantity' => $line['qty'],
                'slug' => 'x',
            ]);
        }

        return $order;
    }
}
