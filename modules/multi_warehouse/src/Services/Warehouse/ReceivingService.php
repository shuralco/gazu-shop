<?php

namespace App\Services\Warehouse;

use App\Models\MerchantWarehouse;
use App\Models\Product;
use App\Models\ReceivingItem;
use App\Models\ReceivingOrder;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

/**
 * Workflow for inbound goods from suppliers.
 * Lifecycle: draft → received → (cancelled is also possible from draft).
 *
 * Receiving directly increments warehouse inventory via
 * InventoryService::add() with type=income.
 */
class ReceivingService
{
    public function __construct(private InventoryService $inventory) {}

    public function createDraft(
        MerchantWarehouse $warehouse,
        ?string $supplierName = null,
        ?string $invoiceNumber = null,
        ?\DateTimeInterface $invoiceDate = null,
        ?int $userId = null,
        ?string $note = null,
    ): ReceivingOrder {
        return ReceivingOrder::create([
            'code' => ReceivingOrder::nextCode(),
            'warehouse_id' => $warehouse->id,
            'supplier_name' => $supplierName,
            'invoice_number' => $invoiceNumber,
            'invoice_date' => $invoiceDate?->format('Y-m-d'),
            'status' => ReceivingOrder::STATUS_DRAFT,
            'created_by_user_id' => $userId,
            'note' => $note,
        ]);
    }

    public function addItem(
        ReceivingOrder $order,
        Product $product,
        int $qty,
        ?float $costPrice = null,
        ?string $note = null,
    ): ReceivingItem {
        if (! $order->isEditable()) {
            throw new RuntimeException("Receiving order {$order->code} is not editable (status: {$order->status}).");
        }
        if ($qty <= 0) {
            throw new InvalidArgumentException('Quantity must be positive.');
        }

        return ReceivingItem::create([
            'receiving_order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => $qty,
            'cost_price' => $costPrice,
            'note' => $note,
        ]);
    }

    public function receive(ReceivingOrder $order, ?int $userId = null): ReceivingOrder
    {
        if ($order->status !== ReceivingOrder::STATUS_DRAFT) {
            throw new RuntimeException("Only draft receiving orders can be received (current: {$order->status}).");
        }
        if ($order->items()->count() === 0) {
            throw new RuntimeException("Cannot receive empty order {$order->code}.");
        }

        DB::transaction(function () use ($order, $userId) {
            $warehouse = $order->warehouse;
            foreach ($order->items as $item) {
                $this->inventory->add(
                    $item->product,
                    $warehouse,
                    $item->quantity,
                    type: StockMovement::TYPE_INCOME,
                    reference: $order,
                    userId: $userId,
                    note: $item->note,
                );
            }

            $order->update([
                'status' => ReceivingOrder::STATUS_RECEIVED,
                'received_at' => now(),
                'received_by_user_id' => $userId,
            ]);
        });

        return $order->refresh();
    }

    public function cancel(ReceivingOrder $order, ?string $reason = null): ReceivingOrder
    {
        if ($order->status === ReceivingOrder::STATUS_RECEIVED) {
            throw new RuntimeException('Cannot cancel an already-received order; create a reverse adjustment instead.');
        }
        if ($order->status === ReceivingOrder::STATUS_CANCELLED) {
            return $order;
        }

        $order->update([
            'status' => ReceivingOrder::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'note' => trim(($order->note ?? '')."\n[CANCELLED] ".($reason ?? '')),
        ]);

        return $order->refresh();
    }
}
