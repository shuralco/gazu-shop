<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NpShipment extends Model
{
    protected $table = 'np_shipments';

    public const STATUS_NEW = 'new';

    public const STATUS_CREATED = 'created';

    public const STATUS_SENT = 'sent';

    public const STATUS_DELIVERED = 'delivered';

    public const STATUS_RETURNED = 'returned';

    /**
     * Маппінг NP StatusCode → внутрішній статус
     */
    public const NP_STATUS_MAP = [
        '1' => self::STATUS_CREATED,   // Відправник створив
        '2' => self::STATUS_CREATED,   // Видалено
        '3' => self::STATUS_CREATED,   // Номер не знайдено
        '4' => self::STATUS_SENT,      // Відправлення в дорозі
        '5' => self::STATUS_SENT,      // Відправлення прямує у місто
        '6' => self::STATUS_SENT,      // У місті
        '7' => self::STATUS_SENT,      // Прибув у відділення
        '8' => self::STATUS_SENT,      // Прибув у відділення
        '9' => self::STATUS_DELIVERED, // Отримано
        '10' => self::STATUS_DELIVERED, // Отримано (з наложкою)
        '11' => self::STATUS_DELIVERED, // Отримано
        '14' => self::STATUS_RETURNED, // Відмова отримувача
        '102' => self::STATUS_RETURNED, // Повернення
        '103' => self::STATUS_RETURNED, // Повернення
        '104' => self::STATUS_RETURNED, // Змінено адресу
        '108' => self::STATUS_RETURNED, // Повернення — повернуто
    ];

    protected $fillable = [
        'order_id', 'ref', 'ttn', 'status', 'np_status', 'np_status_code',
        // Sender
        'sender_ref', 'sender_city_ref', 'sender_warehouse_ref', 'sender_address',
        'sender_contact_ref', 'sender_contact_name', 'sender_phone',
        // Recipient
        'recipient_name', 'recipient_phone', 'recipient_contact_name', 'recipient_email',
        'recipient_edrpou', 'recipient_company_name',
        'recipient_city_ref', 'recipient_city_name',
        'recipient_warehouse_ref', 'recipient_warehouse_name',
        'recipient_address', 'recipient_street', 'recipient_house', 'recipient_flat',
        'recipient_floor', 'recipient_has_elevator',
        // Delivery preferences
        'preferred_delivery_date', 'preferred_delivery_time_from', 'preferred_delivery_time_to',
        // Cargo
        'service_type', 'cargo_type', 'weight', 'volume', 'volume_weight',
        'seats_amount', 'parcels',
        // Payment
        'cost', 'shipping_cost', 'declared_cost',
        'payer_type', 'payment_method', 'cod_amount',
        'payment_control', 'backward_delivery_type', 'backward_delivery_amount', 'backward_delivery_payer',
        // Options
        'avia_delivery', 'packing_number', 'description', 'additional_information',
        'estimated_delivery_date', 'actual_shipping_date', 'recipient_date',
        // Tracking + admin
        'tracking_history', 'last_tracked_at',
        'print_url', 'printed_at', 'registry_ref',
    ];

    protected $casts = [
        'weight' => 'decimal:3',
        'volume' => 'decimal:4',
        'volume_weight' => 'decimal:3',
        'cost' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'declared_cost' => 'decimal:2',
        'cod_amount' => 'decimal:2',
        'backward_delivery_amount' => 'decimal:2',
        'recipient_floor' => 'integer',
        'recipient_has_elevator' => 'boolean',
        'payment_control' => 'boolean',
        'avia_delivery' => 'boolean',
        'parcels' => 'array',
        'tracking_history' => 'array',
        'last_tracked_at' => 'datetime',
        'printed_at' => 'datetime',
        'preferred_delivery_date' => 'date',
        'actual_shipping_date' => 'date',
        'recipient_date' => 'date',
    ];

    /**
     * Замовлення
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * URL відстеження на сайті НП
     */
    public function getTrackingUrl(): ?string
    {
        if (! $this->ttn) {
            return null;
        }

        return "https://novaposhta.ua/tracking/?cargo_number={$this->ttn}";
    }

    /**
     * Чи роздруковано ТТН
     */
    public function isPrinted(): bool
    {
        return ! empty($this->print_url);
    }

    /**
     * Чи можна редагувати ТТН (тільки до відправки)
     */
    public function canEdit(): bool
    {
        return in_array($this->status, [self::STATUS_NEW, self::STATUS_CREATED]);
    }

    /**
     * Чи можна видалити ТТН (тільки до відправки)
     */
    public function canDelete(): bool
    {
        return in_array($this->status, [self::STATUS_NEW, self::STATUS_CREATED]);
    }

    /**
     * Чи потрібно відстежувати (не фінальний статус)
     */
    public function needsTracking(): bool
    {
        return ! in_array($this->status, [self::STATUS_DELIVERED, self::STATUS_RETURNED]);
    }

    /**
     * Scope: активні (не фінальний статус)
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('status', [self::STATUS_DELIVERED, self::STATUS_RETURNED]);
    }

    /**
     * Scope: за статусом
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: для замовлення
     */
    public function scopeForOrder(Builder $query, int $orderId): Builder
    {
        return $query->where('order_id', $orderId);
    }

    /**
     * Scope: ТТН що потребують відстеження
     */
    public function scopeNeedsTracking(Builder $query): Builder
    {
        return $query->whereNotIn('status', [self::STATUS_NEW, self::STATUS_DELIVERED, self::STATUS_RETURNED])
            ->whereNotNull('ttn');
    }

    /**
     * Визначити внутрішній статус за StatusCode від НП
     */
    public static function resolveStatusFromCode(?string $code): string
    {
        if ($code === null) {
            return self::STATUS_NEW;
        }

        return self::NP_STATUS_MAP[$code] ?? self::STATUS_SENT;
    }
}
