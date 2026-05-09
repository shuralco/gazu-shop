<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\Shipping\NovaPoshtaProvider;
use Illuminate\Console\Command;

class CleanInvalidCityRefs extends Command
{
    protected $signature = 'orders:clean-invalid-cities';

    protected $description = 'Очистити недійсні city_ref з shipping_data замовлень';

    public function handle()
    {
        $this->info('Початок очищення недійсних city_ref...');

        $orders = Order::whereNotNull('shipping_data')->get();
        $provider = new NovaPoshtaProvider;
        $cleaned = 0;

        foreach ($orders as $order) {
            $shippingData = json_decode($order->shipping_data, true);
            $needsUpdate = false;

            // Перевірити та очистити warehouse city_ref
            if (isset($shippingData['city_ref'])) {
                if (! $this->isValidCityRef($provider, $shippingData['city_ref'])) {
                    $this->line("Очищення недійсного city_ref в замовленні #{$order->id}: {$shippingData['city_ref']}");
                    unset($shippingData['city_ref']);
                    unset($shippingData['city']);
                    unset($shippingData['warehouse_ref']);
                    unset($shippingData['warehouse']);
                    $needsUpdate = true;
                }
            }

            // Перевірити та очистити postomat city_ref
            if (isset($shippingData['postomat_city_ref'])) {
                if (! $this->isValidCityRef($provider, $shippingData['postomat_city_ref'])) {
                    $this->line("Очищення недійсного postomat_city_ref в замовленні #{$order->id}: {$shippingData['postomat_city_ref']}");
                    unset($shippingData['postomat_city_ref']);
                    unset($shippingData['postomat_ref']);
                    unset($shippingData['postomat']);
                    $needsUpdate = true;
                }
            }

            // Перевірити та очистити courier city_ref
            if (isset($shippingData['courier_city_ref'])) {
                if (! $this->isValidCityRef($provider, $shippingData['courier_city_ref'])) {
                    $this->line("Очищення недійсного courier_city_ref в замовленні #{$order->id}: {$shippingData['courier_city_ref']}");
                    unset($shippingData['courier_city_ref']);
                    $needsUpdate = true;
                }
            }

            if ($needsUpdate) {
                $order->update(['shipping_data' => json_encode($shippingData)]);
                $cleaned++;
            }
        }

        $this->info("Очищено {$cleaned} замовлень з недійсними city_ref.");
        $this->line('Тепер ці замовлення будуть показувати порожні поля міст для коректного вибору.');
    }

    private function isValidCityRef(NovaPoshtaProvider $provider, string $cityRef): bool
    {
        $searchTerms = ['київ', 'харків', 'одеса', 'дніпро', 'львів', 'кривий'];

        foreach ($searchTerms as $term) {
            try {
                $cities = $provider->getCities($term);
                if ($cities->firstWhere('ref', $cityRef)) {
                    return true;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return false;
    }
}
