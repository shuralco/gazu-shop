<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyTransaction extends Model
{
    public const TYPE_EARNED = 'earned';
    public const TYPE_SPENT = 'spent';
    public const TYPE_EXPIRED = 'expired';
    public const TYPE_ADJUSTED = 'adjusted';
    public const TYPE_BIRTHDAY = 'birthday';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'order_id',
        'type',
        'points',
        'balance_after',
        'description',
        'expires_at',
        'created_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'points' => 'integer',
        'balance_after' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Таблиця має лише created_at (без updated_at), тож $timestamps вимкнено.
     * created_at заповнюємо самі при створенні: покладатися на DB-default
     * CURRENT_TIMESTAMP не можна — fix-міграція додає його лише для MySQL,
     * тож на sqlite/інших драйверах колонка лишається NOT NULL без default.
     */
    protected static function booted(): void
    {
        static::creating(function (self $transaction): void {
            if (empty($transaction->created_at)) {
                $transaction->created_at = now();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function scopeEarned(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_EARNED);
    }

    public function scopeNotExpired(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }
}
