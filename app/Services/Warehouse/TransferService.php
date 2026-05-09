<?php

namespace App\Services\Warehouse;

use App\Models\InventoryTransfer;
use App\Models\InventoryTransferItem;
use App\Models\MerchantWarehouse;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

/**
 * State-machine wrapper around InventoryService::move() for inter-warehouse
 * transfers. Workflow: draft → sent → received (or cancelled at any stage).
 *
 * Inventory invariants are enforced inside InventoryService — see
 * docs/INVENTORY-LOGIC.md.
 */
class TransferService
{
    public function __construct(private InventoryService $inventory) {}

    public function createDraft(
        MerchantWarehouse $from,
        MerchantWarehouse $to,
        ?int $userId = null,
        ?string $note = null,
    ): InventoryTransfer {
        if ($from->id === $to->id) {
            throw new InvalidArgumentException('Source and destination must differ.');
        }

        return InventoryTransfer::create([
            'code' => InventoryTransfer::nextCode(),
            'from_warehouse_id' => $from->id,
            'to_warehouse_id' => $to->id,
            'status' => InventoryTransfer::STATUS_DRAFT,
            'created_by_user_id' => $userId,
            'note' => $note,
        ]);
    }

    public function addItem(InventoryTransfer $transfer, Product $product, int $qty, ?string $note = null): InventoryTransferItem
    {
        if (! $transfer->isEditable()) {
            throw new RuntimeException("Transfer {$transfer->code} is no longer editable (status: {$transfer->status}).");
        }
        if ($qty <= 0) {
            throw new InvalidArgumentException('Quantity must be positive.');
        }

        return InventoryTransferItem::updateOrCreate(
            ['transfer_id' => $transfer->id, 'product_id' => $product->id],
            ['quantity' => $qty, 'note' => $note],
        );
    }

    public function ship(InventoryTransfer $transfer, ?int $userId = null): InventoryTransfer
    {
        if ($transfer->status !== InventoryTransfer::STATUS_DRAFT) {
            throw new RuntimeException("Only draft transfers can be shipped (current: {$transfer->status}).");
        }
        if ($transfer->items()->count() === 0) {
            throw new RuntimeException("Cannot ship empty transfer {$transfer->code}.");
        }

        DB::transaction(function () use ($transfer, $userId) {
            $from = $transfer->fromWarehouse;
            foreach ($transfer->items as $item) {
                // transfer_out only — physical departure. transfer_in happens
                // on receive. We do NOT use InventoryService::move() because
                // we want the receiving side held in transit, not auto-added.
                $this->inventory->subtract(
                    $item->product,
                    $from,
                    $item->quantity,
                    type: StockMovement::TYPE_TRANSFER_OUT,
                    reference: $transfer,
                    userId: $userId,
                );
            }

            $transfer->update([
                'status' => InventoryTransfer::STATUS_SENT,
                'shipped_at' => now(),
                'shipped_by_user_id' => $userId,
            ]);
        });

        return $transfer->refresh();
    }

    public function receive(InventoryTransfer $transfer, ?int $userId = null): InventoryTransfer
    {
        if ($transfer->status !== InventoryTransfer::STATUS_SENT) {
            throw new RuntimeException("Only sent transfers can be received (current: {$transfer->status}).");
        }

        DB::transaction(function () use ($transfer, $userId) {
            $to = $transfer->toWarehouse;
            foreach ($transfer->items as $item) {
                $this->inventory->add(
                    $item->product,
                    $to,
                    $item->quantity,
                    type: StockMovement::TYPE_TRANSFER_IN,
                    reference: $transfer,
                    userId: $userId,
                );
            }

            $transfer->update([
                'status' => InventoryTransfer::STATUS_RECEIVED,
                'received_at' => now(),
                'received_by_user_id' => $userId,
            ]);
        });

        return $transfer->refresh();
    }

    public function cancel(InventoryTransfer $transfer, ?int $userId = null, ?string $reason = null): InventoryTransfer
    {
        if ($transfer->status === InventoryTransfer::STATUS_RECEIVED) {
            throw new RuntimeException('Cannot cancel a received transfer; create a reverse transfer instead.');
        }
        if ($transfer->status === InventoryTransfer::STATUS_CANCELLED) {
            return $transfer;
        }

        DB::transaction(function () use ($transfer, $userId, $reason) {
            // If already shipped — return goods to source warehouse via transfer_in.
            if ($transfer->status === InventoryTransfer::STATUS_SENT) {
                $from = $transfer->fromWarehouse;
                foreach ($transfer->items as $item) {
                    $this->inventory->add(
                        $item->product,
                        $from,
                        $item->quantity,
                        type: StockMovement::TYPE_TRANSFER_IN,
                        reference: $transfer,
                        userId: $userId,
                        note: 'Cancellation return: '.($reason ?? ''),
                    );
                }
            }

            $transfer->update([
                'status' => InventoryTransfer::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'note' => trim(($transfer->note ?? '')."\n[CANCELLED] ".($reason ?? '')),
            ]);
        });

        return $transfer->refresh();
    }
}
