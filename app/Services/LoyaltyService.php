<?php

namespace App\Services;

use App\Models\LoyaltyTier;
use App\Models\LoyaltyTransaction;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class LoyaltyService
{
    /**
     * Нарахувати бали за замовлення
     */
    public function awardPoints(User $user, Order $order): int
    {
        if (! $this->isEnabled()) {
            return 0;
        }

        $pointsPerUah = (int) shopSetting('loyalty_points_per_uah', 1);
        $basePoints = (int) floor($order->total * $pointsPerUah);

        $tier = $this->getUserTier($user);
        $multiplier = $tier ? (float) $tier->points_multiplier : 1.0;
        $points = (int) floor($basePoints * $multiplier);

        if ($points <= 0) {
            return 0;
        }

        $expirationMonths = (int) shopSetting('loyalty_points_expiration_months', 12);
        $newBalance = $user->loyalty_points + $points;

        LoyaltyTransaction::create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'type' => LoyaltyTransaction::TYPE_EARNED,
            'points' => $points,
            'balance_after' => $newBalance,
            'description' => "Нараховано за замовлення #{$order->id}",
            'expires_at' => now()->addMonths($expirationMonths),
        ]);

        $user->update([
            'loyalty_points' => $newBalance,
            'total_spent' => $user->total_spent + $order->total,
        ]);

        $this->recalculateTier($user);

        return $points;
    }

    /**
     * Списати бали для знижки
     */
    public function redeemPoints(User $user, int $points): float
    {
        if ($points <= 0 || $points > $user->loyalty_points) {
            return 0;
        }

        $rate = (int) shopSetting('loyalty_redemption_rate', 100);
        $discount = round($points / $rate, 2);
        $newBalance = $user->loyalty_points - $points;

        LoyaltyTransaction::create([
            'user_id' => $user->id,
            'type' => LoyaltyTransaction::TYPE_SPENT,
            'points' => -$points,
            'balance_after' => $newBalance,
            'description' => "Списано для знижки {$discount} грн",
        ]);

        $user->update(['loyalty_points' => $newBalance]);

        return $discount;
    }

    /**
     * Перерахувати рівень лояльності користувача
     */
    public function recalculateTier(User $user): string
    {
        $totalEarned = $user->loyaltyTransactions()
            ->where('type', LoyaltyTransaction::TYPE_EARNED)
            ->sum('points');

        $tier = LoyaltyTier::active()
            ->where('min_points', '<=', $totalEarned)
            ->orderByDesc('min_points')
            ->first();

        $tierName = $tier ? $tier->name : 'bronze';

        if ($user->loyalty_tier !== $tierName) {
            $user->update(['loyalty_tier' => $tierName]);
            Cache::forget("user_tier_{$user->id}");
        }

        return $tierName;
    }

    /**
     * Списати прострочені бали
     */
    public function expireOldPoints(): int
    {
        $expired = 0;

        $transactions = LoyaltyTransaction::where('type', LoyaltyTransaction::TYPE_EARNED)
            ->where('expires_at', '<=', now())
            ->where('points', '>', 0)
            ->get();

        foreach ($transactions as $transaction) {
            $user = $transaction->user;
            $pointsToExpire = min($transaction->points, $user->loyalty_points);

            if ($pointsToExpire <= 0) {
                continue;
            }

            $newBalance = $user->loyalty_points - $pointsToExpire;

            LoyaltyTransaction::create([
                'user_id' => $user->id,
                'type' => LoyaltyTransaction::TYPE_EXPIRED,
                'points' => -$pointsToExpire,
                'balance_after' => $newBalance,
                'description' => 'Термін дії балів закінчився',
            ]);

            $user->update(['loyalty_points' => $newBalance]);
            // Позначити оригінал як оброблений
            $transaction->update(['points' => 0]);
            $expired += $pointsToExpire;
        }

        return $expired;
    }

    /**
     * Нарахувати бонус до дня народження
     */
    public function awardBirthdayBonus(User $user): int
    {
        if (! $this->isEnabled() || ! $user->birthdate) {
            return 0;
        }

        // Перевірити чи вже нараховано цього року
        $alreadyAwarded = $user->loyaltyTransactions()
            ->where('type', LoyaltyTransaction::TYPE_BIRTHDAY)
            ->whereYear('created_at', now()->year)
            ->exists();

        if ($alreadyAwarded) {
            return 0;
        }

        $bonusPoints = (int) shopSetting('loyalty_birthday_bonus_points', 100);
        $newBalance = $user->loyalty_points + $bonusPoints;

        LoyaltyTransaction::create([
            'user_id' => $user->id,
            'type' => LoyaltyTransaction::TYPE_BIRTHDAY,
            'points' => $bonusPoints,
            'balance_after' => $newBalance,
            'description' => 'Бонус до дня народження!',
            'expires_at' => now()->addMonths((int) shopSetting('loyalty_points_expiration_months', 12)),
        ]);

        $user->update(['loyalty_points' => $newBalance]);

        return $bonusPoints;
    }

    /**
     * Ручне коригування балів (адмін)
     */
    public function adjustPoints(User $user, int $points, string $description): void
    {
        $newBalance = max(0, $user->loyalty_points + $points);

        LoyaltyTransaction::create([
            'user_id' => $user->id,
            'type' => LoyaltyTransaction::TYPE_ADJUSTED,
            'points' => $points,
            'balance_after' => $newBalance,
            'description' => $description,
        ]);

        $user->update(['loyalty_points' => $newBalance]);
        $this->recalculateTier($user);
    }

    /**
     * Отримати поточний рівень користувача
     */
    public function getUserTier(User $user): ?LoyaltyTier
    {
        return Cache::remember("user_tier_{$user->id}", 3600, function () use ($user) {
            return LoyaltyTier::active()
                ->where('name', $user->loyalty_tier)
                ->first();
        });
    }

    /**
     * Отримати наступний рівень
     */
    public function getNextTier(User $user): ?LoyaltyTier
    {
        $currentTier = $this->getUserTier($user);

        if (! $currentTier) {
            return LoyaltyTier::active()->ordered()->first();
        }

        return LoyaltyTier::active()
            ->where('min_points', '>', $currentTier->min_points)
            ->orderBy('min_points')
            ->first();
    }

    /**
     * Отримати прогрес до наступного рівня (у відсотках)
     */
    public function getProgressToNextTier(User $user): float
    {
        $totalEarned = $user->loyaltyTransactions()
            ->where('type', LoyaltyTransaction::TYPE_EARNED)
            ->sum('points');

        $currentTier = $this->getUserTier($user);
        $nextTier = $this->getNextTier($user);

        if (! $nextTier) {
            return 100.0;
        }

        $currentMin = $currentTier ? $currentTier->min_points : 0;
        $nextMin = $nextTier->min_points;
        $range = $nextMin - $currentMin;

        if ($range <= 0) {
            return 100.0;
        }

        return min(100.0, round(($totalEarned - $currentMin) / $range * 100, 1));
    }

    /**
     * Розрахувати вартість балів у гривнях
     */
    public function getRedemptionValue(int $points): float
    {
        $rate = (int) shopSetting('loyalty_redemption_rate', 100);

        return round($points / $rate, 2);
    }

    /**
     * Перевірити чи програма лояльності увімкнена
     */
    private function isEnabled(): bool
    {
        return (bool) shopSetting('loyalty_enabled', true);
    }
}
