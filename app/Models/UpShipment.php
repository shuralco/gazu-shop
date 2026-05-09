<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UpShipment extends Model
{
    protected $table = 'up_shipments';

    public const STATUS_NEW = 'new';

    public const STATUS_SENT = 'sent';

    public const STATUS_IN_TRANSIT = 'in_transit';

    public const STATUS_ARRIVED = 'arrived';

    public const STATUS_DELIVERED = 'delivered';

    public const STATUS_RETURNED = 'returned';

    protected $fillable = [
        'order_id', 'ttn', 'status', 'up_status_text', 'up_status_code',
        'recipient_name', 'recipient_phone', 'recipient_email',
        'recipient_city_id', 'recipient_city_name',
        'recipient_branch_id', 'recipient_branch_address', 'recipient_postcode',
        'recipient_street', 'recipient_building', 'recipient_apartment',
        'service_type', 'weight', 'declared_value', 'cod_amount', 'shipping_cost',
        'description', 'shipped_at', 'delivered_at', 'last_tracked_at',
        'tracking_history',
    ];

    protected function casts(): array
    {
        return [
            'recipient_city_id' => 'integer',
            'recipient_branch_id' => 'integer',
            'weight' => 'decimal:3',
            'declared_value' => 'decimal:2',
            'cod_amount' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'last_tracked_at' => 'datetime',
            'tracking_history' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function scopeForOrder(Builder $query, int $orderId): Builder
    {
        return $query->where('order_id', $orderId);
    }

    public function scopeNeedsTracking(Builder $query): Builder
    {
        return $query->whereIn('status', [
            self::STATUS_SENT, self::STATUS_IN_TRANSIT, self::STATUS_ARRIVED,
        ])->whereNotNull('ttn');
    }

    public function getTrackingUrl(): ?string
    {
        return $this->ttn
            ? "https://track.ukrposhta.ua/tracking_UA.html?barcode={$this->ttn}"
            : null;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_NEW => 'Нова',
            self::STATUS_SENT => 'Відправлено',
            self::STATUS_IN_TRANSIT => 'В дорозі',
            self::STATUS_ARRIVED => 'Прибула',
            self::STATUS_DELIVERED => 'Доставлено',
            self::STATUS_RETURNED => 'Повернуто',
            default => ucfirst($this->status),
        };
    }
}
