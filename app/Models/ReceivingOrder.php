<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReceivingOrder extends Model
{
    protected $fillable = [
        'code',
        'warehouse_id',
        'supplier_name',
        'invoice_number',
        'invoice_date',
        'status',
        'received_at',
        'cancelled_at',
        'created_by_user_id',
        'received_by_user_id',
        'note',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'received_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public const STATUS_DRAFT = 'draft';

    public const STATUS_RECEIVED = 'received';

    public const STATUS_CANCELLED = 'cancelled';

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(MerchantWarehouse::class, 'warehouse_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReceivingItem::class, 'receiving_order_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
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
            ->where('code', 'like', "RCV-{$year}-%")
            ->orderByDesc('id')
            ->value('code');
        $next = $last ? ((int) substr($last, -6)) + 1 : 1;

        return sprintf('RCV-%s-%06d', $year, $next);
    }
}
