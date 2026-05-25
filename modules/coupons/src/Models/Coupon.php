<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use HasFactory;

    const TYPE_PERCENTAGE = 'percentage';

    const TYPE_FIXED_AMOUNT = 'fixed_amount';

    const TYPE_FREE_SHIPPING = 'free_shipping';

    protected $fillable = [
        'code', 'type', 'value', 'minimum_amount', 'maximum_discount',
        'usage_limit', 'used_count', 'usage_limit_per_user', 'is_active',
        'valid_from', 'valid_until', 'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'value' => 'decimal:2',
        'minimum_amount' => 'decimal:2',
        'maximum_discount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
    ];

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Перевірити чи купон активний та в межах дат
     */
    public function isValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = Carbon::now();

        return $now->between($this->valid_from, $this->valid_until);
    }

    /**
     * Перевірити чи купон може бути використаний (ліміти)
     */
    public function canBeUsed(): bool
    {
        if (! $this->isValid()) {
            return false;
        }

        // Перевірка глобального ліміту
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Перевірити чи користувач може використати купон
     */
    public function canBeUsedByUser($userId = null, $userEmail = null): bool
    {
        if (! $this->canBeUsed()) {
            return false;
        }

        // Перевірка ліміту на користувача
        if ($this->usage_limit_per_user) {
            $userUsages = $this->usages()
                ->where(function ($query) use ($userId, $userEmail) {
                    if ($userId) {
                        $query->where('user_id', $userId);
                    }
                    if ($userEmail) {
                        $query->orWhere('user_email', $userEmail);
                    }
                })
                ->count();

            if ($userUsages >= $this->usage_limit_per_user) {
                return false;
            }
        }

        return true;
    }

    /**
     * Розрахувати суму знижки
     */
    public function calculateDiscount(float $orderTotal, float $shippingCost = 0): float
    {
        // Перевірка мінімальної суми
        if ($this->minimum_amount && $orderTotal < $this->minimum_amount) {
            return 0;
        }

        $discount = 0;

        switch ($this->type) {
            case self::TYPE_PERCENTAGE:
                $discount = ($orderTotal * $this->value) / 100;
                break;

            case self::TYPE_FIXED_AMOUNT:
                $discount = $this->value;
                break;

            case self::TYPE_FREE_SHIPPING:
                $discount = $shippingCost;
                break;
        }

        // Застосувати максимальний ліміт знижки
        if ($this->maximum_discount && $discount > $this->maximum_discount) {
            $discount = $this->maximum_discount;
        }

        // Знижка не може бути більше суми замовлення
        if ($discount > $orderTotal) {
            $discount = $orderTotal;
        }

        return round($discount, 2);
    }

    /**
     * Відмітити купон як використаний
     */
    public function markAsUsed($orderId, $userId = null, $userEmail = null, $discountAmount = 0): void
    {
        // Створити запис використання
        CouponUsage::create([
            'coupon_id' => $this->id,
            'order_id' => $orderId,
            'user_id' => $userId,
            'user_email' => $userEmail,
            'discount_amount' => $discountAmount,
            'used_at' => now(),
        ]);

        // Збільшити лічильник використань
        $this->increment('used_count');
    }

    /**
     * Scope для активних купонів
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('valid_from', '<=', now())
            ->where('valid_until', '>=', now());
    }

    /**
     * Scope для купонів за кодом
     */
    public function scopeByCode(Builder $query, string $code): Builder
    {
        return $query->where('code', Str::upper($code));
    }
}
