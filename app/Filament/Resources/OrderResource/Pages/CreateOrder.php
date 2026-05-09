<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    public function mount(): void
    {
        parent::mount();

        // Автоматично створюємо порожнє замовлення та перенаправляємо на редагування
        $this->autoCreateAndRedirect();
    }

    protected function autoCreateAndRedirect(): void
    {
        // Створюємо порожнє замовлення з мінімальними даними
        $order = \App\Models\Order::create([
            'first_name' => 'Новий',
            'last_name' => 'Клієнт',
            'email' => 'temp@example.com',
            'phone' => '',
            'status' => 'pending',
            'total' => 0,
            'shipping_cost' => 0,
            'shipping_data' => json_encode([]),
        ]);

        // Перенаправляємо на редагування
        $this->redirect($this->getResource()::getUrl('edit', ['record' => $order]));
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Middle name (по батькові) залишається як є, необов'язкове поле

        // Встановити shipping_cost якщо не встановлено
        if (! isset($data['shipping_cost']) || $data['shipping_cost'] === null) {
            $data['shipping_cost'] = 0;
        }

        // Встановити phone якщо не встановлено
        if (! isset($data['phone']) || $data['phone'] === null) {
            $data['phone'] = '';
        }

        // Встановити email якщо не встановлено
        if (! isset($data['email']) || $data['email'] === null || $data['email'] === '') {
            $data['email'] = 'guest@example.com';
        }

        // Зібрати дані доставки у JSON
        $shippingData = [];

        if (isset($data['shipping_provider']) && $data['shipping_provider'] === 'novaposhta') {
            switch ($data['shipping_method'] ?? '') {
                case 'warehouse':
                    if (! empty($data['np_city'])) {
                        $shippingData['city_ref'] = $data['np_city'];

                        // Отримати назву міста та відділення
                        try {
                            $provider = new \App\Services\Shipping\NovaPoshtaProvider;
                            $cities = $provider->getCities('');
                            $city = $cities->firstWhere('ref', $data['np_city']);
                            if ($city) {
                                $shippingData['city'] = $city['name'];
                            }

                            // Отримати назву відділення
                            if (! empty($data['np_warehouse'])) {
                                $warehouses = $provider->getWarehouses($data['np_city']);
                                $warehouse = $warehouses->firstWhere('ref', $data['np_warehouse']);
                                if ($warehouse) {
                                    $shippingData['warehouse'] = "№{$warehouse['number']} - {$warehouse['description']}";
                                }
                            }
                        } catch (\Exception $e) {

                        }
                    }
                    if (! empty($data['np_warehouse'])) {
                        $shippingData['warehouse_ref'] = $data['np_warehouse'];
                    }
                    break;

                case 'postomat':
                    if (! empty($data['np_postomat_city'])) {
                        $shippingData['postomat_city_ref'] = $data['np_postomat_city'];

                        // Отримати назви міста та поштомату
                        try {
                            $provider = new \App\Services\Shipping\NovaPoshtaProvider;
                            $cities = $provider->getCities('');
                            $city = $cities->firstWhere('ref', $data['np_postomat_city']);
                            if ($city) {
                                $shippingData['city'] = $city['name'];
                            }

                            // Отримати назву поштомату
                            if (! empty($data['np_postomat'])) {
                                $warehouses = $provider->getWarehouses($data['np_postomat_city']);
                                $postomat = $warehouses->firstWhere('ref', $data['np_postomat']);
                                if ($postomat) {
                                    $shippingData['postomat'] = $postomat['description'];
                                }
                            }
                        } catch (\Exception $e) {

                        }
                    }
                    if (! empty($data['np_postomat'])) {
                        $shippingData['postomat_ref'] = $data['np_postomat'];
                    }
                    break;

                case 'courier':
                    if (! empty($data['np_courier_city'])) {
                        $shippingData['courier_city_ref'] = $data['np_courier_city'];
                    }
                    if (! empty($data['np_courier_street'])) {
                        $shippingData['street'] = $data['np_courier_street'];
                    }
                    if (! empty($data['np_courier_building'])) {
                        $shippingData['building'] = $data['np_courier_building'];
                    }
                    if (! empty($data['np_courier_apartment'])) {
                        $shippingData['apartment'] = $data['np_courier_apartment'];
                    }
                    break;
            }
        }

        $data['shipping_data'] = json_encode($shippingData);

        // Прибрати тимчасові поля
        unset($data['np_city'], $data['np_warehouse'], $data['np_postomat_city'], $data['np_postomat'],
            $data['np_courier_city'], $data['np_courier_street'], $data['np_courier_building'], $data['np_courier_apartment']);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
