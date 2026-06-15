<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'first_name', 'last_name', 'middle_name', 'name', 'email', 'locale', 'phone', 'note', 'total', 'status', 'paid_at',
        'shipping_cost', 'shipping_provider', 'shipping_method', 'shipping_data',
        'shipping_city', 'shipping_city_ref', 'shipping_warehouse', 'shipping_warehouse_ref',
        'shipping_warehouse_type', 'shipping_postcode',
        'shipping_address', 'shipping_post_office', 'shipping_post_office_ref',
        'warehouse_id', 'fulfillment_status',
        'coupon_id', 'coupon_code', 'discount_amount',
        'payment_method', 'payment_status', 'payment_transaction_id',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'total' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'shipping_data' => 'array',
        'discount_amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Каскад при видаленні: order_products.order_id має FK RESTRICT (без
     * cascadeOnDelete), тож пряме видалення замовлення з позиціями падало на
     * FK → 500 в адмінці. Чистимо позиції перед видаленням замовлення.
     * (np_shipments каскадить на рівні БД, інші діти — теж.)
     */
    protected static function booted(): void
    {
        static::deleting(function (Order $order) {
            $order->orderProducts()->delete();
        });
    }

    public function orderProducts(): HasMany
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(MerchantWarehouse::class, 'warehouse_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function getLatestPayment(): ?Payment
    {
        return $this->payments()->latest()->first();
    }

    public function hasSuccessfulPayment(): bool
    {
        return $this->payments()->where('status', 'success')->exists();
    }

    /**
     * Відправлення для цього замовлення
     */
    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    /**
     * Відправлення Нової Пошти (ТТН)
     */
    public function npShipments(): HasMany
    {
        return $this->hasMany(NpShipment::class);
    }

    /**
     * Останнє ТТН Нової Пошти
     */
    public function latestNpShipment()
    {
        return $this->npShipments()->latest()->limit(1);
    }

    /**
     * Останнє відправлення
     */
    public function latestShipment(): HasMany
    {
        return $this->shipments()->latest();
    }

    /**
     * Перевірити чи замовлення має активне відправлення
     */
    public function hasActiveShipment(): bool
    {
        return $this->shipments()->whereNotIn('status', [
            Shipment::STATUS_DELIVERED,
            Shipment::STATUS_FAILED,
            Shipment::STATUS_RETURNED,
        ])->exists();
    }

    /**
     * Перевірити чи замовлення доставлено
     */
    public function isDelivered(): bool
    {
        return $this->shipments()->where('status', Shipment::STATUS_DELIVERED)->exists();
    }

    /**
     * Отримати номер відстеження
     */
    public function getTrackingNumber(): ?string
    {
        $shipment = $this->latestShipment()->first();

        return $shipment?->tracking_number;
    }

    /**
     * Отримати статус доставки
     */
    public function getShippingStatus(): ?string
    {
        $shipment = $this->latestShipment()->first();

        return $shipment?->status;
    }

    /**
     * Розрахувати загальну вагу замовлення
     */
    public function calculateTotalWeight(): float
    {
        $weight = 0.5; // Мінімальна вага упаковки

        $itemsCount = $this->orderProducts()->sum('quantity');
        $weight += $itemsCount * 0.3; // 300г за товар

        return round($weight, 2);
    }
}
