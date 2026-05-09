<?php

namespace App\Services\Warehouse;

use App\Models\MerchantWarehouse;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Shrinks inventory when an order is physically shipped (TTN created).
 *
 * Idempotent — checks stock_movements for existing 'ship' rows tied to the
 * order before doing anything, so a TTN retry won't decrement twice.
 *
 * Inventory rules:
 *   - Decrement uses InventoryService::subtract(type=ship). We do NOT use
 *     ::ship() because that requires a prior reserve, which is Phase 2.
 *   - If a product is out of stock at the order's warehouse, we log a
 *     warning and continue (don't fail the TTN — the goods left the
 *     warehouse already in the real world; admin should reconcile).
 *   - Order's warehouse resolves via order.warehouse → fallback default.
 */
class OrderFulfillmentService
{
    public function __construct(private InventoryService $inventory) {}

    /**
     * Ship every order_product against the order's assigned warehouse.
     *
     * @return array{shipped:int, skipped:int}
     */
    public function shipOrder(Order $order, ?int $userId = null): array
    {
        $warehouse = $order->warehouse ?? MerchantWarehouse::default();
        if (! $warehouse) {
            Log::warning('[fulfillment] order has no warehouse and no default exists', [
                'order_id' => $order->id,
            ]);

            return ['shipped' => 0, 'skipped' => 0];
        }

        // Idempotency guard — check both fulfillment_status and movements.
        if ($order->fulfillment_status === 'shipped') {
            return ['shipped' => 0, 'skipped' => 0, 'reason' => 'already_shipped'];
        }
        $alreadyShipped = StockMovement::query()
            ->where('reference_type', $order->getMorphClass())
            ->where('reference_id', $order->id)
            ->where('type', StockMovement::TYPE_SHIP)
            ->exists();
        if ($alreadyShipped) {
            return ['shipped' => 0, 'skipped' => 0, 'reason' => 'already_shipped'];
        }

        $shipped = 0;
        $skipped = 0;
        $errors = [];

        DB::transaction(function () use ($order, $warehouse, $userId, &$shipped, &$skipped, &$errors) {
            $items = $order->orderProducts()->with('product')->get();
            foreach ($items as $op) {
                $result = $this->shipItem($op, $warehouse, $order, $userId);
                if ($result === 'shipped') {
                    $shipped++;
                } else {
                    $skipped++;
                    $errors[] = $result;
                }
            }

            $order->update(['fulfillment_status' => 'shipped']);
        });

        return ['shipped' => $shipped, 'skipped' => $skipped, 'errors' => $errors];
    }

    private function shipItem(OrderProduct $op, MerchantWarehouse $warehouse, Order $order, ?int $userId): string
    {
        if (! $op->product || $op->quantity <= 0) {
            return 'skipped:no_product_or_qty';
        }

        $itemWarehouse = $op->warehouse ?? $warehouse;

        try {
            $this->inventory->subtract(
                product: $op->product,
                warehouse: $itemWarehouse,
                qty: (int) $op->quantity,
                type: StockMovement::TYPE_SHIP,
                reference: $order,
                userId: $userId,
                note: "Order #{$order->id} order_product #{$op->id}",
            );

            return 'shipped';
        } catch (\Throwable $e) {
            Log::warning('[fulfillment] could not ship item', [
                'order_id' => $order->id,
                'order_product_id' => $op->id,
                'product_id' => $op->product_id,
                'warehouse_id' => $itemWarehouse->id,
                'error' => $e->getMessage(),
            ]);

            return 'skipped:'.$e->getMessage();
        }
    }
}
