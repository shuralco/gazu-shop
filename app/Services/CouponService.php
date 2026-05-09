<?php

namespace App\Services;

use App\Models\Coupon;
use Illuminate\Support\Str;

class CouponService
{
    /**
     * Перевірити та застосувати купон
     */
    public function applyCoupon(
        string $code,
        float $orderTotal,
        float $shippingCost = 0,
        $userId = null,
        ?string $userEmail = null
    ): array {
        $code = Str::upper(Str::trim($code));

        // Знайти купон
        $coupon = Coupon::byCode($code)->active()->first();

        if (! $coupon) {
            return [
                'success' => false,
                'message' => 'Купон не знайдено або він неактивний',
                'discount' => 0,
            ];
        }

        // Перевірити чи може бути використаний користувачем
        if (! $coupon->canBeUsedByUser($userId, $userEmail)) {
            return [
                'success' => false,
                'message' => 'Ви вже використали цей купон максимальну кількість разів',
                'discount' => 0,
            ];
        }

        // Розрахувати знижку
        $discountAmount = $coupon->calculateDiscount($orderTotal, $shippingCost);

        if ($discountAmount <= 0) {
            $minAmount = $coupon->minimum_amount ? formatPrice($coupon->minimum_amount) : null;
            $message = $minAmount
                ? "Мінімальна сума замовлення для цього купону: {$minAmount}"
                : 'Купон не може бути застосований до цього замовлення';

            return [
                'success' => false,
                'message' => $message,
                'discount' => 0,
            ];
        }

        return [
            'success' => true,
            'message' => $this->getDiscountMessage($coupon, $discountAmount),
            'discount' => $discountAmount,
            'coupon' => $coupon,
        ];
    }

    /**
     * Відмітити купон як використаний при створенні замовлення
     */
    public function markCouponAsUsed(
        Coupon $coupon,
        int $orderId,
        float $discountAmount,
        $userId = null,
        ?string $userEmail = null
    ): void {
        $coupon->markAsUsed($orderId, $userId, $userEmail, $discountAmount);
    }

    /**
     * Отримати повідомлення про знижку
     */
    private function getDiscountMessage(Coupon $coupon, float $discountAmount): string
    {
        $discount = formatPrice($discountAmount);

        return match ($coupon->type) {
            Coupon::TYPE_PERCENTAGE => "Знижка {$coupon->value}%: -{$discount}",
            Coupon::TYPE_FIXED_AMOUNT => "Фіксована знижка: -{$discount}",
            Coupon::TYPE_FREE_SHIPPING => "Безкоштовна доставка: -{$discount}",
            default => "Знижка: -{$discount}"
        };
    }

    /**
     * Валідувати код купону (для форм)
     */
    public function validateCouponCode(string $code): array
    {
        $code = Str::upper(Str::trim($code));

        if (empty($code)) {
            return [
                'valid' => false,
                'message' => 'Введіть код купону',
            ];
        }

        if (Str::length($code) < 3) {
            return [
                'valid' => false,
                'message' => 'Код купону має містити мінімум 3 символи',
            ];
        }

        $coupon = Coupon::byCode($code)->first();

        if (! $coupon) {
            return [
                'valid' => false,
                'message' => 'Купон з таким кодом не існує',
            ];
        }

        if (! $coupon->isValid()) {
            return [
                'valid' => false,
                'message' => 'Купон неактивний або термін дії закінчився',
            ];
        }

        return [
            'valid' => true,
            'message' => 'Купон знайдено',
            'coupon' => $coupon,
        ];
    }

    /**
     * Отримати список активних купонів для адмін-панелі
     */
    public function getActiveCoupons()
    {
        return Coupon::active()
            ->orderBy('valid_until', 'asc')
            ->get();
    }

    /**
     * Статистика використання купону
     */
    public function getCouponStats(Coupon $coupon): array
    {
        $totalUsages = $coupon->used_count;
        $totalDiscount = $coupon->usages()->sum('discount_amount');
        $uniqueUsers = $coupon->usages()
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count();

        return [
            'total_usages' => $totalUsages,
            'total_discount_given' => $totalDiscount,
            'unique_users' => $uniqueUsers,
            'remaining_uses' => $coupon->usage_limit ? ($coupon->usage_limit - $totalUsages) : null,
        ];
    }
}
