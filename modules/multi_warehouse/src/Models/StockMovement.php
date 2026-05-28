<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Append-only audit log of inventory changes.
 *
 * Invariant: SUM(stock_movements.quantity WHERE warehouse=W AND product=P)
 *            equals inventory.quantity for the same (W, P) pair.
 */
class StockMovement extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'warehouse_id',
        'product_id',
        'type',
        'quantity',
        'reserved_delta',
        'reference_type',
        'reference_id',
        'user_id',
        'note',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'reserved_delta' => 'integer',
    ];

    public const TYPE_INCOME = 'income';

    public const TYPE_RESERVE = 'reserve';

    public const TYPE_RELEASE = 'release';

    public const TYPE_SHIP = 'ship';

    public const TYPE_TRANSFER_OUT = 'transfer_out';

    public const TYPE_TRANSFER_IN = 'transfer_in';

    public const TYPE_ADJUSTMENT = 'adjustment';

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(MerchantWarehouse::class, 'warehouse_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
