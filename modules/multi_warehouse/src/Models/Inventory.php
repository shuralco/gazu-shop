<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Per-warehouse inventory row. quantity = physical stock,
 * reserved_quantity = locked by checkout reservations.
 *
 * Invariants (enforced by InventoryService):
 *   1) quantity >= 0
 *   2) reserved_quantity >= 0
 *   3) reserved_quantity <= quantity
 */
class Inventory extends Model
{
    protected $table = 'inventory';

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'quantity',
        'reserved_quantity',
        'price',
        'compare_at_price',
        'reorder_point',
        'reorder_quantity',
        'last_counted_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'reserved_quantity' => 'integer',
        'price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'reorder_point' => 'integer',
        'reorder_quantity' => 'integer',
        'last_counted_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(MerchantWarehouse::class, 'warehouse_id');
    }

    /**
     * Computed: physically present minus reserved.
     */
    protected function availableQuantity(): Attribute
    {
        return Attribute::get(fn () => max(0, $this->quantity - $this->reserved_quantity));
    }

    /**
     * Effective price for this (product, warehouse): override if set, otherwise base product price.
     */
    protected function effectivePrice(): Attribute
    {
        return Attribute::get(fn () => $this->price !== null ? (float) $this->price : (float) ($this->product->price ?? 0));
    }

    public function scopeForProduct(Builder $q, int $productId): Builder
    {
        return $q->where('product_id', $productId);
    }

    public function scopeForWarehouse(Builder $q, int $warehouseId): Builder
    {
        return $q->where('warehouse_id', $warehouseId);
    }

    public function scopeLowStock(Builder $q): Builder
    {
        return $q->whereColumn('quantity', '<=', 'reorder_point')
            ->whereNotNull('reorder_point');
    }
}
