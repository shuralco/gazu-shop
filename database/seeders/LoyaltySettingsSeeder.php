<?php

namespace Database\Seeders;

use App\Models\ShopSettings;
use Illuminate\Database\Seeder;

class LoyaltySettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'loyalty_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'loyalty',
                'description' => 'Увімкнути програму лояльності',
                'is_public' => false,
            ],
            [
                'key' => 'loyalty_points_per_uah',
                'value' => '1',
                'type' => 'integer',
                'group' => 'loyalty',
                'description' => 'Кількість балів за 1 грн витрат',
                'is_public' => false,
            ],
            [
                'key' => 'loyalty_redemption_rate',
                'value' => '100',
                'type' => 'integer',
                'group' => 'loyalty',
                'description' => 'Кількість балів для списання 1 грн',
                'is_public' => false,
            ],
            [
                'key' => 'loyalty_points_expiration_months',
                'value' => '12',
                'type' => 'integer',
                'group' => 'loyalty',
                'description' => 'Термін дії балів (місяці)',
                'is_public' => false,
            ],
            [
                'key' => 'loyalty_birthday_bonus_points',
                'value' => '100',
                'type' => 'integer',
                'group' => 'loyalty',
                'description' => 'Бонусні бали на день народження',
                'is_public' => false,
            ],
        ];

        foreach ($settings as $setting) {
            ShopSettings::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
