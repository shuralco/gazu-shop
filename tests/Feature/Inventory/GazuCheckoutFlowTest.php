<?php

namespace Tests\Feature\Inventory;

use App\Helpers\Cart\Cart;
use App\Models\Inventory;
use App\Models\MerchantWarehouse;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * End-to-end GAZU checkout: cart → POST /checkout → order_products with
 * warehouse_id + reserved inventory + shipping_cost from per-warehouse
 * rates. Cancel → release. Out-of-stock → redirect with errors.
 *
 * Validates the full multi-warehouse fulfillment loop in one test class.
 */
class GazuCheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    private MerchantWarehouse $kyiv;

    private MerchantWarehouse $lviv;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        MerchantWarehouse::query()->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->kyiv = MerchantWarehouse::factory()->default()->create([
            'code' => 'KY-1', 'city' => 'Київ',
            'shipping_cost' => 60, 'free_shipping_threshold' => 2000,
        ]);
        $this->lviv = MerchantWarehouse::factory()->create([
            'code' => 'LV-1', 'city' => 'Львів', 'is_default' => false,
            'shipping_cost' => 80, 'free_shipping_threshold' => null,
        ]);

        $this->product = Product::factory()->create(['price' => 1000]);

        Inventory::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->kyiv->id,
            'quantity' => 10,
            'reserved_quantity' => 0,
            'price' => 1000,
        ]);
        Inventory::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->lviv->id,
            'quantity' => 5,
            'reserved_quantity' => 0,
            'price' => 900,
        ]);
    }

    public function test_checkout_creates_order_with_per_warehouse_lines_and_reservations(): void
    {
        // Use unique products per cart line so cart keys don't collide.
        $second = Product::factory()->create(['price' => 900]);
        Inventory::create([
            'product_id' => $second->id, 'warehouse_id' => $this->lviv->id,
            'quantity' => 5, 'reserved_quantity' => 0, 'price' => 900,
        ]);

        $cart = $this->cartWith([
            [$this->product->id, 2, $this->kyiv->id, 1000], // 2×1000 = 2000 → free shipping
            [$second->id,        1, $this->lviv->id, 900],  // 900 → +80 shipping
        ]);

        $response = $this->withSession(['cart' => $cart])
            ->post(route('gazu.checkout.store'), $this->validShippingPayload());

        $response->assertRedirect();
        $this->assertDatabaseCount('orders', 1);

        $order = Order::with(['orderProducts.warehouse'])->first();

        $this->assertSame(2, $order->orderProducts->count());
        // Lviv ships 80₴; Kyiv free above 2000
        $this->assertSame(80.0, (float) $order->shipping_cost);
        $this->assertSame(2980.0, (float) $order->total);
        // Effective subtotal == total − shipping. (orders.subtotal column may
        // not exist on every deploy; total + shipping is the reliable check.)
        $this->assertSame(2900.0, (float) $order->total - (float) $order->shipping_cost);

        $kyivLine = $order->orderProducts->firstWhere('warehouse_id', $this->kyiv->id);
        $lvivLine = $order->orderProducts->firstWhere('warehouse_id', $this->lviv->id);
        $this->assertNotNull($kyivLine);
        $this->assertNotNull($lvivLine);
        $this->assertSame(2, $kyivLine->quantity);
        $this->assertSame(1, $lvivLine->quantity);

        // Reservations bumped accordingly.
        $this->assertSame(2, Inventory::where('warehouse_id', $this->kyiv->id)->where('product_id', $this->product->id)->value('reserved_quantity'));
        $this->assertSame(1, Inventory::where('warehouse_id', $this->lviv->id)->where('product_id', $second->id)->value('reserved_quantity'));
    }

    public function test_cancelled_order_releases_reservations(): void
    {
        $cart = $this->cartWith([[$this->product->id, 1, $this->kyiv->id, 1000]]);
        $this->withSession(['cart' => $cart])
            ->post(route('gazu.checkout.store'), $this->validShippingPayload());

        $order = Order::first();
        $this->assertSame(1, Inventory::where('warehouse_id', $this->kyiv->id)->value('reserved_quantity'));

        $order->update(['status' => 'cancelled']);

        $this->assertSame(0, Inventory::where('warehouse_id', $this->kyiv->id)->value('reserved_quantity'));
    }

    public function test_shipped_order_decrements_physical_quantity(): void
    {
        $cart = $this->cartWith([[$this->product->id, 1, $this->kyiv->id, 1000]]);
        $this->withSession(['cart' => $cart])
            ->post(route('gazu.checkout.store'), $this->validShippingPayload());

        $order = Order::first();
        $kyivInv = Inventory::where('warehouse_id', $this->kyiv->id)->first();
        $this->assertSame(10, $kyivInv->quantity);
        $this->assertSame(1, $kyivInv->reserved_quantity);

        $order->update(['status' => 'shipped']);

        $kyivInv->refresh();
        $this->assertSame(9, $kyivInv->quantity, 'qty drops by 1 on ship');
        $this->assertSame(0, $kyivInv->reserved_quantity, 'reservation cleared on ship');
    }

    public function test_insufficient_stock_redirects_to_cart_with_error(): void
    {
        // Pre-reserve all 10 → only 0 available
        Inventory::where('warehouse_id', $this->kyiv->id)
            ->update(['reserved_quantity' => 10]);

        $cart = $this->cartWith([[$this->product->id, 1, $this->kyiv->id, 1000]]);

        $response = $this->withSession(['cart' => $cart])
            ->post(route('gazu.checkout.store'), $this->validShippingPayload());

        $response->assertRedirect(route('gazu.cart'));
        $response->assertSessionHasErrors(['stock']);
        // Order rolled back on reservation failure
        $this->assertDatabaseCount('orders', 0);
    }

    /**
     * Build a session-cart array (matches Cart::add2Cart shape) without
     * relying on the Cart helper, since session state set inside the test
     * setUp() doesn't survive into the http request.
     */
    private function cartWith(array $lines): array
    {
        $cart = [];
        foreach ($lines as [$productId, $qty, $whId, $price]) {
            $key = $productId.'_w'.$whId;
            $cart[$key] = [
                'title' => 'Test product',
                'slug' => 'test-product',
                'image' => null,
                'price' => $price,
                'quantity' => $qty,
                'variant_id' => null,
                'warehouse_id' => $whId,
            ];
        }
        return $cart;
    }

    private function validShippingPayload(): array
    {
        return [
            'first_name' => 'Іван',
            'last_name' => 'Тестовий',
            'phone' => '+380501234567',
            'email' => 'test@example.com',
            'shipping_method' => 'novaposhta',
            'shipping_city' => 'Київ',
            'shipping_warehouse' => 'Відділення №5',
            'payment_method' => 'card',
        ];
    }
}
