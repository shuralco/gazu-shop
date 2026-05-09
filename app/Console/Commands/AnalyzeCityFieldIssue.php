<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\Shipping\NovaPoshtaProvider;
use Illuminate\Console\Command;

class AnalyzeCityFieldIssue extends Command
{
    protected $signature = 'debug:city-field {order_id=5}';

    protected $description = 'Аналіз проблеми з відображенням поля міста в адмін-панелі';

    public function handle()
    {
        $orderId = $this->argument('order_id');

        $this->info('=== АНАЛІЗ ПРОБЛЕМИ З ПОЛЕМ МІСТА ===');
        $this->info("Замовлення ID: {$orderId}");
        $this->newLine();

        // 1. Аналіз замовлення
        $order = Order::find($orderId);
        if (! $order) {
            $this->error("Замовлення #{$orderId} не знайдено!");

            return 1;
        }

        $this->info('1. Дані замовлення:');
        $this->line("   shipping_provider: {$order->shipping_provider}");
        $this->line("   shipping_method: {$order->shipping_method}");

        $shippingData = json_decode($order->shipping_data, true);
        $this->line('   shipping_data: '.json_encode($shippingData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // 2. Аналіз структури даних
        $this->newLine();
        $this->info('2. Аналіз структури shipping_data:');

        $cityRef = null;
        $cityName = null;

        if (isset($shippingData['city_ref'])) {
            $cityRef = $shippingData['city_ref'];
            $this->line("   ✅ city_ref присутній: {$cityRef}");
        } else {
            $this->error('   ❌ city_ref відсутній');
        }

        if (isset($shippingData['city'])) {
            $cityName = $shippingData['city'];
            $this->line("   ✅ city присутня: {$cityName}");
        } else {
            $this->error('   ❌ city відсутня - ОСЬ ПРОБЛЕМА!');
        }

        // 3. Тестування Nova Poshta API
        $this->newLine();
        $this->info('3. Тестування Nova Poshta API:');

        try {
            $provider = new NovaPoshtaProvider;

            // Тест з порожнім запитом
            $cities = $provider->getCities('');
            $this->line("   getCities(''): {$cities->count()} міст");

            // Тест з пошуком
            $cities = $provider->getCities('київ');
            $this->line("   getCities('київ'): {$cities->count()} міст");

            // Пошук нашого проблемного ref
            if ($cityRef) {
                $this->line("   Пошук ref: {$cityRef}");

                $searchTerms = ['київ', 'харків', 'одеса', 'дніпро', 'львів', 'кривий'];
                $found = false;

                foreach ($searchTerms as $term) {
                    $cities = $provider->getCities($term);
                    $city = $cities->firstWhere('ref', $cityRef);
                    if ($city) {
                        $this->line("   ✅ ЗНАЙДЕНО! Місто: {$city['name']} (пошук: '{$term}')");
                        $found = true;
                        break;
                    }
                }

                if (! $found) {
                    $this->error('   ❌ Місто з ref НЕ ЗНАЙДЕНО - застарілий або недійсний ref');
                }
            }

        } catch (\Exception $e) {
            $this->error("   Помилка API: {$e->getMessage()}");
        }

        // 4. Симуляція Filament options() методу
        $this->newLine();
        $this->info('4. Симуляція Filament options() методу:');

        if (isset($shippingData['city_ref']) && isset($shippingData['city'])) {
            $this->line("   ✅ options() поверне: [{$shippingData['city_ref']} => {$shippingData['city']}]");
            $this->line("   ✅ Поле покаже: {$shippingData['city']}");
        } elseif (isset($shippingData['city_ref'])) {
            $this->error('   ❌ options() поверне: [] (порожній масив)');
            $this->error("   ❌ Поле покаже: {$shippingData['city_ref']} (сирий ID)");
        } else {
            $this->error('   ❌ options() поверне: [] (немає даних)');
        }

        // 5. Рекомендації
        $this->newLine();
        $this->info('5. ВИСНОВКИ ТА РЕКОМЕНДАЦІЇ:');

        if (! isset($shippingData['city'])) {
            $this->error("   🔴 ПРОБЛЕМА: В shipping_data відсутнє поле 'city'");
            $this->line('   💡 РІШЕННЯ: Оновити shipping_data додавши назву міста');

            if ($cityRef && $found ?? false) {
                $this->line('   ✅ Ref дійсний - можна оновити автоматично');
            } else {
                $this->line('   ⚠️ Ref недійсний - потрібно ручне втручання');
            }
        } else {
            $this->line('   ✅ shipping_data містить і ref і назву міста');
            $this->line('   🤔 Проблема може бути в логіці Filament форми');
        }

        $this->newLine();
        $this->info('=== КІНЕЦЬ АНАЛІЗУ ===');

        return 0;
    }
}
