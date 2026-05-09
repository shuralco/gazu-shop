<?php

namespace Database\Seeders;

use App\Models\LoyaltyTier;
use Illuminate\Database\Seeder;

class LoyaltyTierSeeder extends Seeder
{
    public function run(): void
    {
        $tiers = [
            [
                'name' => 'bronze',
                'display_name' => 'Бронзовий',
                'min_points' => 0,
                'points_multiplier' => 1.0,
                'discount_percentage' => 0,
                'color' => '#CD7F32',
                'sort_order' => 0,
                'is_active' => true,
            ],
            [
                'name' => 'silver',
                'display_name' => 'Срібний',
                'min_points' => 1000,
                'points_multiplier' => 1.5,
                'discount_percentage' => 3,
                'color' => '#C0C0C0',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'gold',
                'display_name' => 'Золотий',
                'min_points' => 5000,
                'points_multiplier' => 2.0,
                'discount_percentage' => 5,
                'color' => '#FFD700',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'platinum',
                'display_name' => 'Платиновий',
                'min_points' => 10000,
                'points_multiplier' => 3.0,
                'discount_percentage' => 10,
                'color' => '#E5E4E2',
                'sort_order' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($tiers as $tier) {
            LoyaltyTier::updateOrCreate(
                ['name' => $tier['name']],
                $tier
            );
        }
    }
}
