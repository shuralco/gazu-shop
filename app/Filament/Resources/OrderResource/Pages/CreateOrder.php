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
        // OrderResource має RelationManagers (товари, ТТН), які потребують вже
        // persisted-запис — тому форму «Створити» реалізовано як create-then-edit.
        // Побічний ефект: кожне відкриття плодило порожній draft. Тому спершу
        // ПЕРЕВИКОРИСТОВУЄМО останній кинутий порожній draft цього адміна
        // (pending, total=0, без товарів) — це обмежує засмічення одним записом.
        $order = \App\Models\Order::query()
            ->where('status', 'pending')
            ->where('total', 0)
            ->where(function ($w) {
                $w->whereNull('user_id')
                  ->orWhere('user_id', \Illuminate\Support\Facades\Auth::id());
            })
            ->whereDoesntHave('orderProducts')
            ->latest('id')
            ->first();

        if (! $order) {
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
        }

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

                        // Отримати назву міста та відділення (робочий сервіс)
                        try {
                            $np = app(\App\Services\NovaPoshtaApiService::class);
                            $cityName = collect($np->getCities($data['np_city'])['data'] ?? [])
                                ->firstWhere('Ref', $data['np_city'])['Description'] ?? null;
                            if ($cityName) {
                                $shippingData['city'] = $cityName;
                            }

                            // Отримати назву відділення
                            if (! empty($data['np_warehouse'])) {
                                $warehouse = collect($np->getWarehouses($data['np_city'], '', 500)['data'] ?? [])
                                    ->firstWhere('Ref', $data['np_warehouse']);
                                if ($warehouse) {
                                    $shippingData['warehouse'] = '№'.($warehouse['Number'] ?? '').' - '.($warehouse['Description'] ?? '');
                                }
                            }
                        } catch (\Throwable $e) {

                        }
                    }
                    if (! empty($data['np_warehouse'])) {
                        $shippingData['warehouse_ref'] = $data['np_warehouse'];
                    }
                    break;

                case 'postomat':
                    if (! empty($data['np_postomat_city'])) {
                        $shippingData['postomat_city_ref'] = $data['np_postomat_city'];

                        // Отримати назви міста та поштомату (робочий сервіс)
                        try {
                            $np = app(\App\Services\NovaPoshtaApiService::class);
                            $cityName = collect($np->getCities($data['np_postomat_city'])['data'] ?? [])
                                ->firstWhere('Ref', $data['np_postomat_city'])['Description'] ?? null;
                            if ($cityName) {
                                $shippingData['city'] = $cityName;
                            }

                            // Отримати назву поштомату
                            if (! empty($data['np_postomat'])) {
                                $postomat = collect($np->getWarehouses($data['np_postomat_city'], '', 500)['data'] ?? [])
                                    ->firstWhere('Ref', $data['np_postomat']);
                                if ($postomat) {
                                    $shippingData['postomat'] = $postomat['Description'] ?? '';
                                }
                            }
                        } catch (\Throwable $e) {

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
