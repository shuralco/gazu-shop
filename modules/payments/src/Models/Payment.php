<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'order_id',
        'gateway',
        'external_id',
        'status',
        'amount',
        'currency',
        'fee_amount',
        'metadata',
        'webhook_received_at',
        'processed_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'amount' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'webhook_received_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(PaymentLog::class);
    }

    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    public function isPending(): bool
    {
        return in_array($this->status, ['pending', 'processing']);
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function canBeRefunded(): bool
    {
        return $this->status === 'success' && $this->amount > 0;
    }
}
