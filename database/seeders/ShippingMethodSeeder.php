<?php

namespace Database\Seeders;

use App\Models\ShippingMethod;
use App\Models\ShippingProvider;
use Illuminate\Database\Seeder;

class ShippingMethodSeeder extends Seeder
{
    public function run(): void
    {
        $providers = ShippingProvider::all();

        if ($providers->isEmpty()) {
            $this->command->warn('No shipping providers found. Run ShippingSeeder first.');

            return;
        }

        foreach ($providers as $provider) {
            // Create different shipping methods for each provider
            if ($provider->code === 'novaposhta') {
                ShippingMethod::create([
                    'provider_id' => $provider->id,
                    'name' => 'Доставка у відділення',
                    'method_code' => 'warehouse',
                    'description' => 'Доставка у відділення Нової Пошти',
                    'base_cost' => 50,
                    'is_active' => true,
                    'sort_order' => 1,
                ]);

                ShippingMethod::create([
                    'provider_id' => $provider->id,
                    'name' => 'Кур\'єрська доставка',
                    'method_code' => 'courier',
                    'description' => 'Доставка кур\'єром додому',
                    'base_cost' => 80,
                    'is_active' => true,
                    'sort_order' => 2,
                ]);
            }

            if ($provider->code === 'ukrposhta') {
                ShippingMethod::create([
                    'provider_id' => $provider->id,
                    'name' => 'Поштомат',
                    'method_code' => 'postomat',
                    'description' => 'Доставка у поштомат Укрпошти',
                    'base_cost' => 45,
                    'is_active' => true,
                    'sort_order' => 3,
                ]);
            }
        }

        $this->command->info('Shipping methods seeded: '.ShippingMethod::count());
    }
}
