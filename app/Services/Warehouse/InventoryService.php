<?php

namespace App\Services\Warehouse;

use App\Models\Inventory;
use App\Models\MerchantWarehouse;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

/**
 * Authoritative entry point for ALL inventory mutations.
 *
 * Invariants enforced:
 *   1) Inventory.quantity >= 0
 *   2) Inventory.reserved_quantity >= 0 and <= quantity
 *   3) SUM(StockMovement.quantity for warehouse+product) == Inventory.quantity
 *
 * Concurrency: every mutation runs in DB::transaction with row-level
 * lock (lockForUpdate) to prevent race conditions on the inventory row.
 *
 * Logic doc: docs/INVENTORY-LOGIC.md
 */
class InventoryService
{
    /**
     * Add stock (income from supplier, manual adjust upward, etc.).
     */
    public function add(
        Product $product,
        MerchantWarehouse $warehouse,
        int $qty,
        string $type = StockMovement::TYPE_INCOME,
        ?Model $reference = null,
        ?int $userId = null,
        ?string $note = null,
    ): StockMovement {
        $this->assertPositive($qty);

        return DB::transaction(function () use ($product, $warehouse, $qty, $type, $reference, $userId, $note) {
            $row = $this->lockOrCreateInventory($product->id, $warehouse->id);
            $row->quantity += $qty;
            $row->save();

            return $this->logMovement($warehouse->id, $product->id, $type, $qty, 0, $reference, $userId, $note);
        });
    }

    /**
     * Remove physical stock (shipment, manual adjust downward, transfer-out).
     * Throws if would leave quantity < reserved (we never overdraw availability).
     */
    public function subtract(
        Product $product,
        MerchantWarehouse $warehouse,
        int $qty,
        string $type = StockMovement::TYPE_SHIP,
        ?Model $reference = null,
        ?int $userId = null,
        ?string $note = null,
    ): StockMovement {
        $this->assertPositive($qty);

        return DB::transaction(function () use ($product, $warehouse, $qty, $type, $reference, $userId, $note) {
            $row = $this->lockInventory($product->id, $warehouse->id);
            if ($row->quantity - $qty < $row->reserved_quantity) {
                throw new RuntimeException(sprintf(
                    'Cannot subtract %d from product %d at warehouse %d — would leave %d, but %d are reserved.',
                    $qty, $product->id, $warehouse->id, $row->quantity - $qty, $row->reserved_quantity,
                ));
            }
            $row->quantity -= $qty;
            $row->save();

            return $this->logMovement($warehouse->id, $product->id, $type, -$qty, 0, $reference, $userId, $note);
        });
    }

    /**
     * Reserve units for a checkout/order. Increments reserved_quantity
     * but leaves physical quantity untouched until ship().
     */
    public function reserve(
        Product $product,
        MerchantWarehouse $warehouse,
        int $qty,
        ?Model $reference = null,
        ?int $userId = null,
        ?string $note = null,
    ): StockMovement {
        $this->assertPositive($qty);

        return DB::transaction(function () use ($product, $warehouse, $qty, $reference, $userId, $note) {
            $row = $this->lockInventory($product->id, $warehouse->id);
            $available = $row->quantity - $row->reserved_quantity;
            if ($available < $qty) {
                throw new RuntimeException(sprintf(
                    'Cannot reserve %d of product %d at warehouse %d — only %d available.',
                    $qty, $product->id, $warehouse->id, $available,
                ));
            }
            $row->reserved_quantity += $qty;
            $row->save();

            return $this->logMovement($warehouse->id, $product->id, StockMovement::TYPE_RESERVE, 0, $qty, $reference, $userId, $note);
        });
    }

    /**
     * Release a previously placed reservation (cart abandoned, TTL expired,
     * order cancelled before shipment).
     */
    public function release(
        Product $product,
        MerchantWarehouse $warehouse,
        int $qty,
        ?Model $reference = null,
        ?int $userId = null,
        ?string $note = null,
    ): StockMovement {
        $this->assertPositive($qty);

        return DB::transaction(function () use ($product, $warehouse, $qty, $reference, $userId, $note) {
            $row = $this->lockInventory($product->id, $warehouse->id);
            $release = min($qty, $row->reserved_quantity);
            $row->reserved_quantity -= $release;
            $row->save();

            return $this->logMovement($warehouse->id, $product->id, StockMovement::TYPE_RELEASE, 0, -$release, $reference, $userId, $note);
        });
    }

    /**
     * Convert an active reservation into a shipment: decrement BOTH
     * reserved_quantity and physical quantity by the same amount.
     */
    public function ship(
        Product $product,
        MerchantWarehouse $warehouse,
        int $qty,
        ?Model $reference = null,
        ?int $userId = null,
        ?string $note = null,
    ): StockMovement {
        $this->assertPositive($qty);

        return DB::transaction(function () use ($product, $warehouse, $qty, $reference, $userId, $note) {
            $row = $this->lockInventory($product->id, $warehouse->id);
            if ($row->reserved_quantity < $qty || $row->quantity < $qty) {
                throw new RuntimeException(sprintf(
                    'Cannot ship %d of product %d at warehouse %d — reserved=%d, physical=%d.',
                    $qty, $product->id, $warehouse->id, $row->reserved_quantity, $row->quantity,
                ));
            }
            $row->reserved_quantity -= $qty;
            $row->quantity -= $qty;
            $row->save();

            return $this->logMovement($warehouse->id, $product->id, StockMovement::TYPE_SHIP, -$qty, -$qty, $reference, $userId, $note);
        });
    }

    /**
     * Set absolute physical quantity (inventory audit / count).
     * Records a signed adjustment movement.
     */
    public function adjust(
        Product $product,
        MerchantWarehouse $warehouse,
        int $newQuantity,
        ?int $userId = null,
        ?string $reason = null,
    ): StockMovement {
        if ($newQuantity < 0) {
            throw new InvalidArgumentException('newQuantity cannot be negative.');
        }

        return DB::transaction(function () use ($product, $warehouse, $newQuantity, $userId, $reason) {
            $row = $this->lockOrCreateInventory($product->id, $warehouse->id);
            if ($newQuantity < $row->reserved_quantity) {
                throw new RuntimeException(sprintf(
                    'Cannot set qty to %d for product %d at warehouse %d — %d are reserved.',
                    $newQuantity, $product->id, $warehouse->id, $row->reserved_quantity,
                ));
            }
            $delta = $newQuantity - $row->quantity;
            $row->quantity = $newQuantity;
            $row->last_counted_at = now();
            $row->save();

            return $this->logMovement(
                $warehouse->id, $product->id, StockMovement::TYPE_ADJUSTMENT,
                $delta, 0, null, $userId, $reason,
            );
        });
    }

    /**
     * Move qty between two warehouses (records paired transfer_out / transfer_in).
     * Reserved state is NOT preserved — caller must release before transferring.
     */
    public function move(
        Product $product,
        MerchantWarehouse $from,
        MerchantWarehouse $to,
        int $qty,
        ?Model $reference = null,
        ?int $userId = null,
        ?string $note = null,
    ): array {
        $this->assertPositive($qty);
        if ($from->id === $to->id) {
            throw new InvalidArgumentException('Source and destination warehouse must differ.');
        }

        return DB::transaction(function () use ($product, $from, $to, $qty, $reference, $userId, $note) {
            $out = $this->subtract($product, $from, $qty, StockMovement::TYPE_TRANSFER_OUT, $reference, $userId, $note);
            $in = $this->add($product, $to, $qty, StockMovement::TYPE_TRANSFER_IN, $reference, $userId, $note);

            return ['out' => $out, 'in' => $in];
        });
    }

    /**
     * Read current inventory row (or null if no rows for this product+warehouse).
     */
    public function get(int $productId, int $warehouseId): ?Inventory
    {
        return Inventory::query()
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();
    }

    // ----------------------------------------------------------------------

    private function lockInventory(int $productId, int $warehouseId): Inventory
    {
        $row = Inventory::query()
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->lockForUpdate()
            ->first();

        if (! $row) {
            throw new RuntimeException("Inventory row missing for product {$productId} at warehouse {$warehouseId}.");
        }

        return $row;
    }

    private function lockOrCreateInventory(int $productId, int $warehouseId): Inventory
    {
        $row = Inventory::query()
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->lockForUpdate()
            ->first();

        if (! $row) {
            $row = Inventory::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'quantity' => 0,
                'reserved_quantity' => 0,
            ]);
            // Refresh under the lock so subsequent updates respect serialization.
            $row = Inventory::query()->whereKey($row->id)->lockForUpdate()->first();
        }

        return $row;
    }

    private function logMovement(
        int $warehouseId,
        int $productId,
        string $type,
        int $quantity,
        int $reservedDelta,
        ?Model $reference,
        ?int $userId,
        ?string $note,
    ): StockMovement {
        return StockMovement::create([
            'warehouse_id' => $warehouseId,
            'product_id' => $productId,
            'type' => $type,
            'quantity' => $quantity,
            'reserved_delta' => $reservedDelta,
            'reference_type' => $reference ? $reference->getMorphClass() : null,
            'reference_id' => $reference?->getKey(),
            'user_id' => $userId,
            'note' => $note,
        ]);
    }

    private function assertPositive(int $qty): void
    {
        if ($qty <= 0) {
            throw new InvalidArgumentException('Quantity must be positive.');
        }
    }
}
