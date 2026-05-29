<?php

namespace Database\Seeders;

use App\Models\ShippingMethod;
use App\Models\ShippingProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Ідемпотентний засів базових методів доставки для GAZU.
 *
 * Засіває типові українські опції:
 *   - Нова Пошта — На відділення / Поштомат / Кур'єр
 *   - УкрПошта — На відділення
 *   - Самовивіз (Київ)
 *
 * Усі рядки створюються через updateOrCreate (ключ provider_id+method_code),
 * тож повторний запуск НЕ дублює дані, а лише оновлює дефолти.
 *
 * Примітка: у схемі shipping_methods немає полів free_from / min_amount,
 * тож умова "безкоштовно від 1500 грн" зберігається в additional_config
 * (поле free_shipping_threshold) для подальшої логіки checkout.
 */
class GazuShippingBaseSeeder extends Seeder
{
    public function run(): void
    {
        $hasSortOrder = Schema::hasColumn('shipping_methods', 'sort_order');

        // --- Провайдери (ідемпотентно по code) ---
        $novaPoshta = ShippingProvider::updateOrCreate(
            ['code' => 'novaposhta'],
            [
                'name' => 'Нова Пошта',
                'api_endpoint' => 'https://api.novaposhta.ua/v2.0/json/',
                'is_active' => true,
            ]
        );

        $ukrPoshta = ShippingProvider::updateOrCreate(
            ['code' => 'ukrposhta'],
            [
                'name' => 'УкрПошта',
                'api_endpoint' => 'https://www.ukrposhta.ua/ecom/0.0.1/',
                'is_active' => true,
            ]
        );

        $pickup = ShippingProvider::updateOrCreate(
            ['code' => 'pickup'],
            [
                'name' => 'Самовивіз',
                'api_endpoint' => null,
                'is_active' => true,
            ]
        );

        $freeFrom = ['free_shipping_threshold' => 1500];

        // --- Базові методи доставки ---
        $methods = [
            [
                'provider_id' => $novaPoshta->id,
                'method_code' => 'warehouse',
                'name' => 'Нова Пошта — відділення',
                'description' => 'Доставка до відділення Нової Пошти',
                'base_cost' => 60.00,
                'per_kg_cost' => 5.00,
                'estimated_days' => 1,
                'max_weight' => 30.00,
                'additional_config' => array_merge($freeFrom, ['service_type' => 'WarehouseWarehouse']),
                'sort_order' => 1,
            ],
            [
                'provider_id' => $novaPoshta->id,
                'method_code' => 'postomat',
                'name' => 'Нова Пошта — поштомат',
                'description' => 'Доставка в поштомат Нової Пошти',
                'base_cost' => 50.00,
                'per_kg_cost' => 4.00,
                'estimated_days' => 2,
                'max_weight' => 20.00,
                'additional_config' => array_merge($freeFrom, ['service_type' => 'WarehousePostomat']),
                'sort_order' => 2,
            ],
            [
                'provider_id' => $novaPoshta->id,
                'method_code' => 'courier',
                'name' => "Нова Пошта — кур'єр",
                'description' => "Адресна кур'єрська доставка Нової Пошти",
                'base_cost' => 90.00,
                'per_kg_cost' => 8.00,
                'estimated_days' => 2,
                'max_weight' => 30.00,
                'additional_config' => array_merge($freeFrom, ['service_type' => 'WarehouseDoors']),
                'sort_order' => 3,
            ],
            [
                'provider_id' => $ukrPoshta->id,
                'method_code' => 'branch',
                'name' => 'УкрПошта — відділення',
                'description' => 'Доставка до відділення УкрПошти',
                'base_cost' => 40.00,
                'per_kg_cost' => 3.00,
                'estimated_days' => 4,
                'max_weight' => 20.00,
                'additional_config' => $freeFrom,
                'sort_order' => 4,
            ],
            [
                'provider_id' => $pickup->id,
                'method_code' => 'pickup',
                'name' => 'Самовивіз (Київ)',
                'description' => 'Самовивіз з магазину в Києві',
                'base_cost' => 0.00,
                'per_kg_cost' => 0.00,
                'estimated_days' => 0,
                'max_weight' => null,
                'additional_config' => ['city' => 'Київ'],
                'sort_order' => 5,
            ],
        ];

        foreach ($methods as $data) {
            $key = [
                'provider_id' => $data['provider_id'],
                'method_code' => $data['method_code'],
            ];

            unset($data['provider_id'], $data['method_code']);

            if (! $hasSortOrder) {
                unset($data['sort_order']);
            }

            $data['is_active'] = true;

            ShippingMethod::updateOrCreate($key, $data);
        }

        if (isset($this->command)) {
            $this->command->info('GAZU базові методи доставки засіяно: '.ShippingMethod::count());
        }
    }
}
