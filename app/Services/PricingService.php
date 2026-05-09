<?php

namespace App\Services;

use App\Models\LoyaltyTier;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class PricingService
{
    /**
     * Отримати ціну товару для конкретного користувача
     */
    public function getProductPrice(Product $product, ?User $user = null): float
    {
        return $product->getPriceForUser($user);
    }

    /**
     * Отримати знижку групи клієнта
     */
    public function getGroupDiscount(?User $user): float
    {
        if (! $user || ! $user->customer_group_id) {
            return 0;
        }

        $group = $user->customerGroup;

        return $group && $group->is_active ? (float) $group->discount_percentage : 0;
    }

    /**
     * Застосувати знижку групи до суми
     */
    public function applyGroupDiscount(float $total, ?User $user = null): float
    {
        $discount = $this->getGroupDiscount($user);

        if ($discount <= 0) {
            return $total;
        }

        return round($total * (1 - $discount / 100), 2);
    }

    /**
     * Отримати найкращу знижку для користувача (з групи або рівня лояльності)
     */
    public function getBestDiscount(?User $user): float
    {
        if (! $user) {
            return 0;
        }

        $groupDiscount = $this->getGroupDiscount($user);

        $tierDiscount = 0;
        $tier = Cache::remember("user_tier_{$user->id}", 3600, function () use ($user) {
            return LoyaltyTier::where('name', $user->loyalty_tier)->first();
        });

        if ($tier) {
            $tierDiscount = (float) $tier->discount_percentage;
        }

        return max($groupDiscount, $tierDiscount);
    }
}
