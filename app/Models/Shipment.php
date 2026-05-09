<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'method_id',
        'tracking_number',
        'provider_reference',
        'status',
        'sender_address',
        'recipient_address',
        'weight',
        'dimensions',
        'declared_value',
        'shipping_cost',
        'additional_data',
        'shipped_at',
        'delivered_at',
    ];

    protected $casts = [
        'sender_address' => 'array',
        'recipient_address' => 'array',
        'dimensions' => 'array',
        'additional_data' => 'array',
        'weight' => 'decimal:2',
        'declared_value' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    /**
     * Доступні статуси відправлення
     */
    public const STATUS_PENDING = 'pending';

    public const STATUS_CREATED = 'created';

    public const STATUS_PICKED_UP = 'picked_up';

    public const STATUS_IN_TRANSIT = 'in_transit';

    public const STATUS_OUT_FOR_DELIVERY = 'out_for_delivery';

    public const STATUS_DELIVERED = 'delivered';

    public const STATUS_FAILED = 'failed';

    public const STATUS_RETURNED = 'returned';

    /**
     * Замовлення
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Метод доставки
     */
    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class, 'method_id');
    }

    /**
     * Оновлення відстеження
     */
    public function trackingUpdates(): HasMany
    {
        return $this->hasMany(TrackingUpdate::class)->orderBy('event_time', 'desc');
    }

    /**
     * Останнє оновлення статусу
     */
    public function latestUpdate(): HasMany
    {
        return $this->trackingUpdates()->latest('event_time')->limit(1);
    }

    /**
     * Оновити статус відправлення
     */
    public function updateStatus(string $status, array $data = []): void
    {
        $this->update(['status' => $status]);

        // Створити запис про оновлення
        $this->trackingUpdates()->create([
            'status' => $status,
            'description' => $data['description'] ?? null,
            'location' => $data['location'] ?? null,
            'event_time' => $data['event_time'] ?? now(),
            'raw_data' => $data,
        ]);

        // Оновити час доставки
        if ($status === self::STATUS_DELIVERED) {
            $this->update(['delivered_at' => $data['event_time'] ?? now()]);
        }
    }

    /**
     * Перевірити чи відправлення доставлено
     */
    public function isDelivered(): bool
    {
        return $this->status === self::STATUS_DELIVERED;
    }

    /**
     * Перевірити чи відправлення в дорозі
     */
    public function isInTransit(): bool
    {
        return in_array($this->status, [
            self::STATUS_PICKED_UP,
            self::STATUS_IN_TRANSIT,
            self::STATUS_OUT_FOR_DELIVERY,
        ]);
    }

    /**
     * Отримати повну адресу отримувача
     */
    public function getFormattedRecipientAddress(): string
    {
        $address = $this->recipient_address;

        return implode(', ', [
            $address['city'] ?? '',
            $address['street'] ?? '',
            $address['building'] ?? '',
        ]);
    }

    /**
     * Scope для статусу
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope для відправлень у дорозі
     */
    public function scopeInTransit(Builder $query): Builder
    {
        return $query->whereIn('status', [
            self::STATUS_PICKED_UP,
            self::STATUS_IN_TRANSIT,
            self::STATUS_OUT_FOR_DELIVERY,
        ]);
    }

    /**
     * Scope для доставлених відправлень
     */
    public function scopeDelivered(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DELIVERED);
    }
}
