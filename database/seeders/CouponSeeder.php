<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            [
                'code' => 'WELCOME10',
                'type' => 'percentage',
                'value' => 10.00,
                'minimum_amount' => 500.00,
                'maximum_discount' => 200.00,
                'usage_limit' => 100,
                'usage_limit_per_user' => 1,
                'is_active' => true,
                'valid_from' => Carbon::now(),
                'valid_until' => Carbon::now()->addMonth(),
                'description' => 'Знижка 10% для нових клієнтів',
            ],
            [
                'code' => 'SUMMER50',
                'type' => 'fixed_amount',
                'value' => 50.00,
                'minimum_amount' => 300.00,
                'maximum_discount' => null,
                'usage_limit' => 50,
                'usage_limit_per_user' => 2,
                'is_active' => true,
                'valid_from' => Carbon::now(),
                'valid_until' => Carbon::now()->addWeeks(2),
                'description' => 'Літня знижка 50 грн',
            ],
            [
                'code' => 'FREESHIP',
                'type' => 'free_shipping',
                'value' => 0.00,
                'minimum_amount' => 1000.00,
                'maximum_discount' => null,
                'usage_limit' => null,
                'usage_limit_per_user' => null,
                'is_active' => true,
                'valid_from' => Carbon::now(),
                'valid_until' => Carbon::now()->addMonth(),
                'description' => 'Безкоштовна доставка при замовленні від 1000 грн',
            ],
            [
                'code' => 'VIP25',
                'type' => 'percentage',
                'value' => 25.00,
                'minimum_amount' => 2000.00,
                'maximum_discount' => 500.00,
                'usage_limit' => 20,
                'usage_limit_per_user' => 1,
                'is_active' => true,
                'valid_from' => Carbon::now(),
                'valid_until' => Carbon::now()->addMonths(3),
                'description' => 'VIP знижка 25% для великих замовлень',
            ],
            [
                'code' => 'EXPIRED',
                'type' => 'percentage',
                'value' => 15.00,
                'minimum_amount' => 100.00,
                'maximum_discount' => null,
                'usage_limit' => 10,
                'usage_limit_per_user' => 1,
                'is_active' => false,
                'valid_from' => Carbon::now()->subWeek(),
                'valid_until' => Carbon::now()->subDay(),
                'description' => 'Прострочений купон для тестування',
            ],
        ];

        foreach ($coupons as $coupon) {
            Coupon::create($coupon);
        }
    }
}
