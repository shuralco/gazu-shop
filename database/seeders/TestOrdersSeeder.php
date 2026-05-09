<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\ShippingProvider;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TestOrdersSeeder extends Seeder
{
    public function run()
    {
        // Список міст для тестування
        $cities = [
            // Великі міста
            ['name' => 'Київ', 'ref' => 'db5c88d0-391c-11dd-90d9-001a92567626'],
            ['name' => 'Харків', 'ref' => 'db5c88e0-391c-11dd-90d9-001a92567626'],
            ['name' => 'Одеса', 'ref' => 'db5c88f0-391c-11dd-90d9-001a92567626'],
            ['name' => 'Дніпро', 'ref' => 'db5c8900-391c-11dd-90d9-001a92567626'],
            ['name' => 'Донецьк', 'ref' => 'db5c8904-391c-11dd-90d9-001a92567626'],
            ['name' => 'Запоріжжя', 'ref' => 'db5c8892-391c-11dd-90d9-001a92567626'],
            ['name' => 'Львів', 'ref' => 'db5c88de-391c-11dd-90d9-001a92567626'],
            ['name' => 'Кривий Ріг', 'ref' => 'db5c88c6-391c-11dd-90d9-001a92567626'],
            ['name' => 'Миколаїв', 'ref' => 'db5c88d8-391c-11dd-90d9-001a92567626'],
            ['name' => 'Маріуполь', 'ref' => 'db5c8914-391c-11dd-90d9-001a92567626'],

            // Середні міста
            ['name' => 'Луцьк', 'ref' => 'db5c88ac-391c-11dd-90d9-001a92567626'],
            ['name' => 'Вінниця', 'ref' => 'db5c8896-391c-11dd-90d9-001a92567626'],
            ['name' => 'Херсон', 'ref' => 'db5c8902-391c-11dd-90d9-001a92567626'],
            ['name' => 'Полтава', 'ref' => 'db5c88e4-391c-11dd-90d9-001a92567626'],
            ['name' => 'Чернігів', 'ref' => 'db5c88c0-391c-11dd-90d9-001a92567626'],
            ['name' => 'Черкаси', 'ref' => 'db5c88c2-391c-11dd-90d9-001a92567626'],
            ['name' => 'Житомир', 'ref' => 'db5c88d4-391c-11dd-90d9-001a92567626'],
            ['name' => 'Суми', 'ref' => 'db5c88cc-391c-11dd-90d9-001a92567626'],
            ['name' => 'Хмельницький', 'ref' => 'db5c88e8-391c-11dd-90d9-001a92567626'],
            ['name' => 'Рівне', 'ref' => 'db5c88c8-391c-11dd-90d9-001a92567626'],
        ];

        // Отримаємо користувачів та продукти
        $users = User::all();
        if ($users->isEmpty()) {
            // Створимо тестових користувачів якщо їх немає
            $users = collect();
            for ($i = 1; $i <= 5; $i++) {
                $users->push(User::create([
                    'name' => "Тестовий Користувач $i",
                    'email' => "test$i@example.com",
                    'password' => bcrypt('password'),
                    'phone' => '+38099'.rand(1000000, 9999999),
                ]));
            }
        }

        $products = Product::where('is_active', true)->get();
        if ($products->isEmpty()) {
            $this->command->error('Немає активних продуктів. Спочатку запустіть ProductSeeder.');

            return;
        }

        // Отримаємо провайдерів доставки
        $novaPoshtaProvider = ShippingProvider::where('code', 'novaposhta')->first();
        $ukrPoshtaProvider = ShippingProvider::where('code', 'ukrposhta')->first();

        // Типи доставки
        $deliveryTypes = [
            'branch' => 'На відділення',
            'postomat' => 'Поштомат',
            'courier' => "Кур'єрська доставка",
        ];

        // Методи оплати
        $paymentMethods = [
            'cash' => 'Готівка при отриманні',
            'card' => 'Оплата карткою',
            'online' => 'Онлайн оплата',
        ];

        // Статуси замовлень
        $statuses = ['new', 'processing', 'shipped', 'delivered', 'cancelled'];

        $this->command->info('Створюємо 40 тестових замовлень...');

        for ($i = 1; $i <= 40; $i++) {
            // Випадковий користувач
            $user = $users->random();

            // Випадкове місто
            $city = $cities[array_rand($cities)];

            // Випадковий провайдер (70% Нова Пошта, 30% УкрПошта)
            $useNovaPoshta = rand(1, 100) <= 70;
            $provider = $useNovaPoshta ? $novaPoshtaProvider : $ukrPoshtaProvider;

            // Випадковий тип доставки
            $deliveryType = array_rand($deliveryTypes);

            // Випадковий метод оплати
            $paymentMethod = array_rand($paymentMethods);

            // Випадковий статус
            $status = $statuses[array_rand($statuses)];

            // Випадкова кількість товарів (1-5)
            $itemsCount = rand(1, 5);
            $selectedProducts = $products->random($itemsCount);

            // Розрахунок суми
            $subtotal = 0;
            $orderProducts = [];

            foreach ($selectedProducts as $product) {
                $quantity = rand(1, 3);
                $price = $product->price;
                $subtotal += $price * $quantity;

                $orderProducts[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $price,
                ];
            }

            // Вартість доставки
            $shippingCost = $useNovaPoshta ? rand(45, 85) : rand(50, 95);
            $total = $subtotal + $shippingCost;

            // Створюємо замовлення
            $order = Order::create([
                'user_id' => $user->id,
                'first_name' => explode(' ', $user->name)[0] ?? 'Тестовий',
                'last_name' => explode(' ', $user->name)[1] ?? 'Користувач',
                'middle_name' => '',
                'email' => $user->email,
                'phone' => $user->phone ?? '+38099'.rand(1000000, 9999999),
                'note' => 'Тестове замовлення #'.$i.' для міста '.$city['name'],
                'payment_method' => $paymentMethod,
                'shipping_cost' => $shippingCost,
                'total' => $total,
                'status' => $status === 'new',
                'shipping_provider' => $provider->code,
                'shipping_method' => $deliveryType,
                'shipping_data' => [
                    'provider' => $provider->code,
                    'city_ref' => $useNovaPoshta ? $city['ref'] : null,
                    'city_id' => ! $useNovaPoshta ? rand(1000, 9999) : null,
                    'warehouse_ref' => $useNovaPoshta ? Str::uuid() : null,
                    'branch_id' => ! $useNovaPoshta ? rand(10000, 99999) : null,
                    'delivery_type' => $deliveryType,
                    'city_name' => $city['name'],
                    'warehouse_number' => 'Відділення №'.rand(1, 200),
                ],
                'created_at' => now()->subDays(rand(0, 30)),
                'updated_at' => now()->subDays(rand(0, 30)),
            ]);

            // Додаємо товари до замовлення
            foreach ($orderProducts as $productData) {
                $product = Product::find($productData['product_id']);
                OrderProduct::create([
                    'order_id' => $order->id,
                    'product_id' => $productData['product_id'],
                    'title' => $product->name ?? 'Товар #'.$productData['product_id'],
                    'price' => $productData['price'],
                    'quantity' => $productData['quantity'],
                    'image' => $product->image ?? '',
                    'slug' => ($product ? $product->getLocalizedSlug('uk') : '') ?: '',
                ]);
            }

            $this->command->info("Створено замовлення #{$i}: ID {$order->id} для міста {$city['name']} ({$provider->name})");
        }

        $this->command->info('✅ Успішно створено 40 тестових замовлень!');

        // Виводимо статистику
        $this->command->table(
            ['Метрика', 'Значення'],
            [
                ['Всього замовлень', Order::count()],
                ['Нова Пошта', Order::where('shipping_provider', 'novaposhta')->count()],
                ['УкрПошта', Order::where('shipping_provider', 'ukrposhta')->count()],
                ['Нові замовлення', Order::where('status', 'new')->count()],
                ['В обробці', Order::where('status', 'processing')->count()],
                ['Відправлені', Order::where('status', 'shipped')->count()],
                ['Доставлені', Order::where('status', 'delivered')->count()],
                ['Скасовані', Order::where('status', 'cancelled')->count()],
            ]
        );
    }
}
