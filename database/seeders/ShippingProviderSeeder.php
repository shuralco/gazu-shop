<?php

namespace Database\Seeders;

use App\Models\ShippingMethod;
use App\Models\ShippingProvider;
use Illuminate\Database\Seeder;

class ShippingProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Нова Пошта
        $novaposhta = ShippingProvider::updateOrCreate(
            ['code' => 'novaposhta'],
            [
                'name' => 'Нова Пошта',
                'api_endpoint' => 'https://api.novaposhta.ua/v2.0/json/',
                'is_active' => true,
                'configuration' => [
                    'api_key' => '737254fe131eca6c3ab91925ef9eff45',
                    'sandbox' => false,
                    'sender_city_ref' => '8d5a980d-391c-11dd-90d9-001a92567626', // Київ
                    'sender_phone' => '+380000000000',
                ],
            ]
        );

        // Методи доставки для Нової Пошти
        ShippingMethod::updateOrCreate(
            ['provider_id' => $novaposhta->id, 'method_code' => 'warehouse'],
            [
                'name' => 'На відділення',
                'description' => 'Доставка на відділення Нової Пошти',
                'base_cost' => 50,
                'per_kg_cost' => 5,
                'estimated_days' => 2,
                'is_active' => true,
            ]
        );

        ShippingMethod::updateOrCreate(
            ['provider_id' => $novaposhta->id, 'method_code' => 'courier'],
            [
                'name' => 'Кур\'єром',
                'description' => 'Доставка кур\'єром за адресою',
                'base_cost' => 80,
                'per_kg_cost' => 8,
                'estimated_days' => 3,
                'is_active' => true,
            ]
        );

        ShippingMethod::updateOrCreate(
            ['provider_id' => $novaposhta->id, 'method_code' => 'postomat'],
            [
                'name' => 'На поштомат',
                'description' => 'Доставка на поштомат Нової Пошти',
                'base_cost' => 45,
                'per_kg_cost' => 4,
                'estimated_days' => 2,
                'is_active' => true,
            ]
        );

        // УкрПошта
        $ukrposhta = ShippingProvider::updateOrCreate(
            ['code' => 'ukrposhta'],
            [
                'name' => 'Укрпошта',
                'api_endpoint' => 'https://api.ukrposhta.ua/ecom/0.0.1/',
                'is_active' => true,
                'configuration' => [
                    'token' => '',
                    'counterparty' => '',
                    'sandbox' => true,
                ],
            ]
        );

        ShippingMethod::updateOrCreate(
            ['provider_id' => $ukrposhta->id, 'method_code' => 'branch'],
            [
                'name' => 'На відділення',
                'description' => 'Доставка на відділення Укрпошти',
                'base_cost' => 40,
                'per_kg_cost' => 3,
                'estimated_days' => 4,
                'is_active' => true,
            ]
        );

        ShippingMethod::updateOrCreate(
            ['provider_id' => $ukrposhta->id, 'method_code' => 'courier'],
            [
                'name' => 'Кур\'єром',
                'description' => 'Доставка кур\'єром Укрпошти',
                'base_cost' => 70,
                'per_kg_cost' => 6,
                'estimated_days' => 5,
                'is_active' => true,
            ]
        );

        $this->command->info('Shipping providers and methods seeded successfully!');
    }
}
