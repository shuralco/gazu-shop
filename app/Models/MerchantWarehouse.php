<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Own warehouse of the shop. Holds inventory and ships orders.
 * Distinct from {@see ShippingWarehouse} (NP/UP carrier branch cache).
 */
class MerchantWarehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'country',
        'region',
        'city',
        'postcode',
        'address',
        'latitude',
        'longitude',
        'phone',
        'email',
        'manager_user_id',
        'working_hours',
        'delivery_eta',
        'is_active',
        'is_default',
        'pickup_supported',
        'sort_order',
        'np_sender_ref',
        'np_sender_city_ref',
        'np_sender_warehouse_ref',
        'np_contact_person_ref',
        'np_sender_phone',
        'up_sender_uuid',
        'up_sender_address_uuid',
        'up_counterparty_token',
        'up_ecom_bearer',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'working_hours' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'pickup_supported' => 'boolean',
        'sort_order' => 'integer',
    ];

    public const TYPE_OWN = 'own';

    public const TYPE_DROP_SHIP = 'drop_ship';

    public const TYPE_VIRTUAL = 'virtual';

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_user_id');
    }

    public function inventory(): HasMany
    {
        return $this->hasMany(Inventory::class, 'warehouse_id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'warehouse_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'warehouse_id');
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function scopeOfType(Builder $q, string $type): Builder
    {
        return $q->where('type', $type);
    }

    public static function default(): ?self
    {
        return static::query()->where('is_default', true)->where('is_active', true)->first()
            ?? static::query()->where('is_active', true)->orderBy('sort_order')->orderBy('id')->first();
    }
}
