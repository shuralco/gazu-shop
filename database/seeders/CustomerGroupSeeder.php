<?php

namespace Database\Seeders;

use App\Models\CustomerGroup;
use Illuminate\Database\Seeder;

class CustomerGroupSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            [
                'name' => 'retail',
                'display_name' => 'Роздрібний покупець',
                'discount_percentage' => 0,
                'min_order_amount' => 0,
                'payment_terms' => null,
                'is_default' => true,
                'is_active' => true,
                'sort_order' => 0,
            ],
            [
                'name' => 'wholesale',
                'display_name' => 'Оптовий покупець',
                'discount_percentage' => 5,
                'min_order_amount' => 5000,
                'payment_terms' => 'Оплата протягом 7 днів після отримання',
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'vip',
                'display_name' => 'VIP клієнт',
                'discount_percentage' => 10,
                'min_order_amount' => 0,
                'payment_terms' => null,
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'distributor',
                'display_name' => 'Дистриб\'ютор',
                'discount_percentage' => 15,
                'min_order_amount' => 10000,
                'payment_terms' => 'Оплата протягом 14 днів після отримання',
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($groups as $group) {
            CustomerGroup::updateOrCreate(
                ['name' => $group['name']],
                $group
            );
        }
    }
}
