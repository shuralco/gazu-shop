<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\Shipping\NovaPoshtaProvider;
use Illuminate\Console\Command;

class UpdateCityNamesInOrders extends Command
{
    protected $signature = 'orders:update-city-names';

    protected $description = 'Оновити назви міст у замовленнях з Nova Poshta API';

    public function handle()
    {
        $this->info('Початок оновлення назв міст у замовленнях...');

        $orders = Order::whereNotNull('shipping_data')->get();
        $provider = new NovaPoshtaProvider;
        $updated = 0;

        foreach ($orders as $order) {
            $shippingData = json_decode($order->shipping_data, true);
            $needsUpdate = false;

            // Оновити назву для відділення
            if (isset($shippingData['city_ref']) && ! isset($shippingData['city'])) {
                $cityName = $this->findCityName($provider, $shippingData['city_ref']);
                if ($cityName) {
                    $shippingData['city'] = $cityName;
                    $needsUpdate = true;
                }
            }

            // Оновити назву для поштомату
            if (isset($shippingData['postomat_city_ref']) && ! isset($shippingData['city'])) {
                $cityName = $this->findCityName($provider, $shippingData['postomat_city_ref']);
                if ($cityName) {
                    $shippingData['city'] = $cityName;
                    $needsUpdate = true;
                }
            }

            // Оновити назву для кур'єра
            if (isset($shippingData['courier_city_ref']) && ! isset($shippingData['city'])) {
                $cityName = $this->findCityName($provider, $shippingData['courier_city_ref']);
                if ($cityName) {
                    $shippingData['city'] = $cityName;
                    $needsUpdate = true;
                }
            }

            if ($needsUpdate) {
                $order->update(['shipping_data' => json_encode($shippingData)]);
                $updated++;
                $this->line("Оновлено замовлення #{$order->id}");
            }
        }

        $this->info("Оновлено {$updated} замовлень.");
    }

    private function findCityName(NovaPoshtaProvider $provider, string $cityRef): ?string
    {
        // Список найпоширеніших міст України для пошуку
        $searchTerms = [
            'київ', 'харків', 'одеса', 'дніпро', 'львів', 'запоріжжя', 'кривий', 'миколаїв',
            'вінниця', 'херсон', 'полтава', 'чернігів', 'черкаси', 'житомир', 'суми', 'рівне',
            'івано', 'ужгород', 'тернопіль', 'хмельн', 'чернівці', 'луцьк', 'кропивн', 'біла',
            'бровари', 'ірпінь', 'буча', 'васильків', 'фастів', 'обухів', 'переяслав',
            'а', 'б', 'в', 'г', 'д', 'е', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п',
            'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ю', 'я',
        ];

        foreach ($searchTerms as $term) {
            try {
                $cities = $provider->getCities($term);
                $city = $cities->firstWhere('ref', $cityRef);
                if ($city) {
                    return $city['name'];
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return null;
    }
}
