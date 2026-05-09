<?php

namespace Database\Seeders;

use App\Models\PaymentGatewaySettings;
use App\Models\ShippingMethod;
use App\Models\ShippingProvider;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use Illuminate\Database\Seeder;

class ShippingSeeder extends Seeder
{
    /**
     * Відновити оригінальні дані провайдерів доставки з резервної копії
     */
    public function run(): void
    {
        // Створити провайдерів доставки - точно як в оригіналі
        $novaPoshta = ShippingProvider::updateOrCreate(
            ['code' => 'novaposhta'],
            [
                'name' => 'Нова Пошта',
                'code' => 'novaposhta',
                'api_endpoint' => 'https://api.novaposhta.ua/v2.0/json/',
                'is_active' => true,
                'configuration' => [
                    'api_key' => env('NOVAPOSHTA_API_KEY'),
                    'sandbox' => env('NOVAPOSHTA_SANDBOX', true),
                    'language' => env('NOVAPOSHTA_LANGUAGE', 'ua'),
                    'sender_city_ref' => env('NOVAPOSHTA_SENDER_CITY_REF', '8d5a980d-391c-11dd-90d9-001a92567626'),
                    'sender_warehouse_ref' => env('NOVAPOSHTA_SENDER_WAREHOUSE_REF'),
                    'sender_contact_ref' => env('NOVAPOSHTA_SENDER_CONTACT_REF'),
                    'sender_phone' => env('NOVAPOSHTA_SENDER_PHONE'),
                    'sender_address' => env('NOVAPOSHTA_SENDER_ADDRESS'),
                ],
            ]);

        $ukrPoshta = ShippingProvider::updateOrCreate(
            ['code' => 'ukrposhta'],
            [
                'name' => 'УкрПошта',
                'code' => 'ukrposhta',
                'api_endpoint' => 'https://www.ukrposhta.ua/ecom/0.0.1/',
                'is_active' => true,
                'configuration' => [
                    'bearer_token' => '5a2c62b3-c867-358a-821e-dd2e7ba007aa', // EXISTING Bearer Token (Set 2 - CORRECT)
                    'counterparty_token' => 'c7d523be-3c70-495a-a6ba-de4ff682751c', // EXISTING Counterparty Token
                    'api_key' => 'f8bd626c-9d62-3243-9eea-1dbfc667e327', // EXISTING API Key (Set 2 - CORRECT)
                    'delengine_api_key' => 'v4n208uaysugpqe6v3ijelusl601fduv', // DelEngine API for real cities/offices
                    'sandbox' => false, // Production режим
                    'sender_region_id' => '80000000000', // Київ
                    'sender_city_id' => '80000000001', // Київ
                    'has_tracking' => true,
                    'requires_passport' => false,
                    'max_weight' => 30,
                    'max_dimensions' => [100, 100, 100],
                    'estimated_delivery_days' => [3, 5],
                    'working_hours' => '08:00-18:00',
                    'contact_phone' => '+380800500440',
                    'supports_cod' => true, // підтримка накладного платежу
                ],
            ]);

        $rozetka = ShippingProvider::updateOrCreate(
            ['code' => 'rozetka'],
            [
                'name' => 'Rozetka Delivery',
                'code' => 'rozetka',
                'api_endpoint' => 'https://rz-delivery.rozetka.ua/api/',
                'is_active' => true,
                'configuration' => [
                    'api_key' => env('ROZETKA_API_KEY'),
                    'merchant_id' => env('ROZETKA_MERCHANT_ID'),
                    'secret_key' => env('ROZETKA_SECRET_KEY'),
                    'sandbox' => env('ROZETKA_SANDBOX', true),
                    'max_weight' => 25,
                    'max_dimensions' => [100, 100, 100],
                    'estimated_delivery_days' => 1,
                    'working_hours' => '09:00-21:00',
                    'contact_phone' => '+380800303344',
                    'supports_cod' => true,
                ],
            ]);

        // Створити методи доставки - точно як в оригіналі
        $npWarehouseToWarehouse = ShippingMethod::create([
            'name' => 'На відділення',
            'provider_id' => $novaPoshta->id,
            'method_code' => 'warehouse',
            'description' => 'Доставка до відділення Нової Пошти',
            'base_cost' => 50.00,
            'per_kg_cost' => 5.00,
            'estimated_days' => 1,
            'max_weight' => 30.00,
            'additional_config' => [
                'service_type' => 'WarehouseWarehouse',
                'requires_warehouse_selection' => true,
            ],
            'is_active' => true,
        ]);

        $npWarehouseToDoors = ShippingMethod::create([
            'name' => 'Курʼєром',
            'provider_id' => $novaPoshta->id,
            'method_code' => 'courier',
            'description' => 'Доставка кур\'єром до адреси від відділення',
            'base_cost' => 80.00,
            'per_kg_cost' => 8.00,
            'estimated_days' => 2,
            'max_weight' => 30.00,
            'additional_config' => [
                'service_type' => 'WarehouseDoors',
                'requires_address' => true,
            ],
            'is_active' => true,
        ]);

        $npPostomat = ShippingMethod::create([
            'name' => 'Поштомат',
            'provider_id' => $novaPoshta->id,
            'method_code' => 'postomat',
            'description' => 'Доставка в поштомат',
            'base_cost' => 45.00,
            'per_kg_cost' => 4.00,
            'estimated_days' => 2,
            'max_weight' => 30.00,
            'additional_config' => [
                'service_type' => 'Postomat',
                'requires_postomat_selection' => true,
            ],
            'is_active' => true,
        ]);

        // Створити провайдер самовивозу
        $pickup = ShippingProvider::updateOrCreate(
            ['code' => 'pickup'],
            [
                'name' => 'Самовивіз',
                'code' => 'pickup',
                'api_endpoint' => null,
                'is_active' => true,
                'configuration' => [],
            ]
        );

        // Створити метод самовивозу
        ShippingMethod::create([
            'name' => 'Самовивіз з магазину',
            'provider_id' => $pickup->id,
            'method_code' => 'pickup',
            'description' => 'Самовивіз з магазину',
            'base_cost' => 0.00,
            'per_kg_cost' => 0.00,
            'estimated_days' => 0,
            'max_weight' => null,
            'additional_config' => null,
            'is_active' => true,
        ]);

        // Створити методи для УкрПошти
        $upBranch = ShippingMethod::create([
            'name' => 'Відділення',
            'provider_id' => $ukrPoshta->id,
            'method_code' => 'branch',
            'description' => 'Доставка УкрПоштою до відділення',
            'base_cost' => 30.00,
            'per_kg_cost' => 3.00,
            'estimated_days' => 5,
            'max_weight' => 20.00,
            'additional_config' => null,
            'is_active' => true,
        ]);

        $upCourier = ShippingMethod::create([
            'name' => 'Кур\'єр',
            'provider_id' => $ukrPoshta->id,
            'method_code' => 'courier',
            'description' => 'Кур\'єрська доставка УкрПошти',
            'base_cost' => 60.00,
            'per_kg_cost' => 6.00,
            'estimated_days' => 3,
            'max_weight' => 10.00,
            'additional_config' => null,
            'is_active' => true,
        ]);

        // Створити методи для Rozetka Delivery
        $rozetkaStandard = ShippingMethod::create([
            'name' => 'Пункт видачі',
            'provider_id' => $rozetka->id,
            'method_code' => 'pickup_point',
            'description' => 'Доставка до пункту видачі Rozetka',
            'base_cost' => 40.00,
            'per_kg_cost' => 4.00,
            'estimated_days' => 1,
            'max_weight' => 25.00,
            'additional_config' => null,
            'is_active' => true,
        ]);

        $rozetkaCourier = ShippingMethod::create([
            'name' => 'Кур\'єрська доставка',
            'provider_id' => $rozetka->id,
            'method_code' => 'courier',
            'description' => 'Доставка кур\'єром Rozetka',
            'base_cost' => 70.00,
            'per_kg_cost' => 7.00,
            'estimated_days' => 1,
            'max_weight' => 25.00,
            'additional_config' => null,
            'is_active' => true,
        ]);

        // Створити зони доставки
        $ukraineZone = ShippingZone::create([
            'name' => 'Україна',
            'country_code' => 'UA',
            'regions' => [
                'Київська область',
                'Харківська область',
                'Дніпропетровська область',
                'Львівська область',
                'Одеська область',
                'Запорізька область',
                'Полтавська область',
                'Вінницька область',
                'Черкаська область',
                'Сумська область',
                'Житомирська область',
                'Чернігівська область',
                'Рівненська область',
                'Хмельницька область',
                'Івано-Франківська область',
                'Тернопільська область',
                'Волинська область',
                'Закарпатська область',
                'Чернівецька область',
                'Кіровоградська область',
                'Миколаївська область',
                'Херсонська область',
                'Донецька область',
                'Луганська область',
            ],
            'is_active' => true,
        ]);

        // Створити базові тарифи для активних методів Нової Пошти
        foreach ([$npWarehouseToWarehouse, $npWarehouseToDoors, $npPostomat] as $method) {
            ShippingRate::create([
                'method_id' => $method->id,
                'zone_id' => $ukraineZone->id,
                'weight_min' => 0.1,
                'weight_max' => 30.0,
                'base_cost' => $method->base_cost,
                'per_kg_cost' => $method->per_kg_cost,
                'delivery_days' => $method->estimated_days,
            ]);
        }

        // Тарифи для УкрПошти (неактивні поки)
        foreach ([$upBranch, $upCourier] as $method) {
            ShippingRate::create([
                'method_id' => $method->id,
                'zone_id' => $ukraineZone->id,
                'weight_min' => 0.1,
                'weight_max' => $method->max_weight,
                'base_cost' => $method->base_cost,
                'per_kg_cost' => $method->per_kg_cost,
                'delivery_days' => $method->estimated_days,
            ]);
        }

        // Тарифи для Rozetka (неактивні поки)
        foreach ([$rozetkaStandard, $rozetkaCourier] as $method) {
            ShippingRate::create([
                'method_id' => $method->id,
                'zone_id' => $ukraineZone->id,
                'weight_min' => 0.1,
                'weight_max' => $method->max_weight,
                'base_cost' => $method->base_cost,
                'per_kg_cost' => $method->per_kg_cost,
                'delivery_days' => $method->estimated_days,
            ]);
        }

        // Створити способи оплати
        PaymentGatewaySettings::updateOrCreate(
            ['code' => 'cash'],
            [
                'name' => 'Готівка при отриманні',
                'code' => 'cash',
                'is_active' => true,
                'configuration' => [],
                'fee_percentage' => 0.00,
                'min_amount' => 0.00,
                'max_amount' => 50000.00,
                'currency' => 'UAH',
                'description' => 'Оплата готівкою при отриманні товару',
            ]
        );

        PaymentGatewaySettings::updateOrCreate(
            ['code' => 'card'],
            [
                'name' => 'Картою при отриманні',
                'code' => 'card',
                'is_active' => true,
                'configuration' => [],
                'fee_percentage' => 0.00,
                'min_amount' => 0.00,
                'max_amount' => 50000.00,
                'currency' => 'UAH',
                'description' => 'Оплата картою при отриманні товару',
            ]
        );

        PaymentGatewaySettings::updateOrCreate(
            ['code' => 'liqpay'],
            [
                'name' => 'LiqPay (онлайн)',
                'code' => 'liqpay',
                'is_active' => true,
                'configuration' => [
                    'public_key' => env('LIQPAY_PUBLIC_KEY'),
                    'private_key' => env('LIQPAY_PRIVATE_KEY'),
                ],
                'fee_percentage' => 2.75,
                'min_amount' => 1.00,
                'max_amount' => 100000.00,
                'currency' => 'UAH',
                'description' => 'Онлайн оплата через LiqPay',
            ]
        );

        PaymentGatewaySettings::updateOrCreate(
            ['code' => 'wayforpay'],
            [
                'name' => 'WayForPay (онлайн)',
                'code' => 'wayforpay',
                'is_active' => true,
                'configuration' => [
                    'merchant_account' => env('WAYFORPAY_MERCHANT_ACCOUNT'),
                    'merchant_secret' => env('WAYFORPAY_SECRET_KEY'),
                ],
                'fee_percentage' => 2.95,
                'min_amount' => 1.00,
                'max_amount' => 100000.00,
                'currency' => 'UAH',
                'description' => 'Онлайн оплата через WayForPay',
            ]
        );

        PaymentGatewaySettings::updateOrCreate(
            ['code' => 'privat24'],
            [
                'name' => 'Приват24',
                'code' => 'privat24',
                'is_active' => true,
                'configuration' => [
                    'merchant_id' => env('PRIVAT24_MERCHANT_ID'),
                    'merchant_password' => env('PRIVAT24_MERCHANT_PASSWORD'),
                ],
                'fee_percentage' => 2.50,
                'min_amount' => 1.00,
                'max_amount' => 100000.00,
                'currency' => 'UAH',
                'description' => 'Онлайн оплата через Приват24',
            ]
        );

        PaymentGatewaySettings::updateOrCreate(
            ['code' => 'bank_transfer'],
            [
                'name' => 'Банківський переказ',
                'code' => 'bank_transfer',
                'is_active' => true,
                'configuration' => [],
                'fee_percentage' => 0.00,
                'min_amount' => 100.00,
                'max_amount' => 1000000.00,
                'currency' => 'UAH',
                'description' => 'Оплата банківським переказом',
            ]
        );

        $this->command->info('Original shipping data restored successfully!');
    }
}
