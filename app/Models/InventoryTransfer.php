<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Movement of stock between two own warehouses.
 * State machine + business rules in {@see App\Services\Warehouse\TransferService}.
 */
class InventoryTransfer extends Model
{
    protected $fillable = [
        'code',
        'from_warehouse_id',
        'to_warehouse_id',
        'status',
        'tracking_number',
        'carrier',
        'shipped_at',
        'received_at',
        'cancelled_at',
        'created_by_user_id',
        'shipped_by_user_id',
        'received_by_user_id',
        'note',
    ];

    protected $casts = [
        'shipped_at' => 'datetime',
        'received_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SENT = 'sent';

    public const STATUS_RECEIVED = 'received';

    public const STATUS_CANCELLED = 'cancelled';

    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(MerchantWarehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(MerchantWarehouse::class, 'to_warehouse_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InventoryTransferItem::class, 'transfer_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function shippedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shipped_by_user_id');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by_user_id');
    }

    public function scopeStatus(Builder $q, string $status): Builder
    {
        return $q->where('status', $status);
    }

    public function isEditable(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public static function nextCode(): string
    {
        $year = now()->format('Y');
        $last = static::query()
            ->where('code', 'like', "TRF-{$year}-%")
            ->orderByDesc('id')
            ->value('code');
        $next = $last ? ((int) substr($last, -6)) + 1 : 1;

        return sprintf('TRF-%s-%06d', $year, $next);
    }
}
