<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\NpShipmentResource;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\UpShipmentResource;
use App\Models\NpShipment;
use App\Models\UpShipment;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function shouldAutoSave(): bool
    {
        return true;
    }

    protected function getAutoSaveInterval(): int
    {
        return 2000; // Автозбереження кожні 2 секунди
    }

    protected function getHeaderActions(): array
    {
        return [
            // NP — create TTN (visible only when provider=novaposhta + no active shipment)
            Actions\Action::make('create_np_ttn')
                ->label('Створити ТТН (НП)')
                ->icon('heroicon-o-document-plus')
                ->color('success')
                ->url(fn () => NpShipmentResource::getUrl('create', ['order_id' => $this->record->id]))
                ->visible(fn () => ($this->record->shipping_provider ?? null) === 'novaposhta'
                    && ! NpShipment::forOrder($this->record->id)
                        ->whereNotIn('status', [NpShipment::STATUS_RETURNED])
                        ->exists()),

            Actions\Action::make('view_np_ttn')
                ->label(function () {
                    $sh = NpShipment::forOrder($this->record->id)->latest()->first();

                    return $sh?->ttn ? "ТТН НП: {$sh->ttn}" : 'Переглянути ТТН НП';
                })
                ->icon('heroicon-o-document-text')
                ->color('info')
                ->url(fn () => optional(NpShipment::forOrder($this->record->id)->latest()->first(),
                    fn ($sh) => NpShipmentResource::getUrl('edit', ['record' => $sh->id])))
                ->extraAttributes(function () {
                    $sh = NpShipment::forOrder($this->record->id)->latest()->first();

                    return $sh?->ttn
                        ? ['x-on:click.prevent.stop' => "navigator.clipboard.writeText('{$sh->ttn}'); \$tooltip('ТТН скопійовано!')"]
                        : [];
                })
                ->visible(fn () => NpShipment::forOrder($this->record->id)->exists()),

            // UP — create TTN record (visible only when provider=ukrposhta + no active record)
            Actions\Action::make('create_up_ttn')
                ->label('Створити запис УП')
                ->icon('heroicon-o-envelope')
                ->color('warning')
                ->url(fn () => UpShipmentResource::getUrl('create', ['order_id' => $this->record->id]))
                ->visible(fn () => ($this->record->shipping_provider ?? null) === 'ukrposhta'
                    && ! UpShipment::forOrder($this->record->id)
                        ->whereNotIn('status', [UpShipment::STATUS_RETURNED])
                        ->exists()),

            Actions\Action::make('view_up_ttn')
                ->label(function () {
                    $sh = UpShipment::forOrder($this->record->id)->latest()->first();

                    return $sh?->ttn ? "ТТН УП: {$sh->ttn}" : 'Переглянути запис УП';
                })
                ->icon('heroicon-o-document-text')
                ->color('info')
                ->url(fn () => optional(UpShipment::forOrder($this->record->id)->latest()->first(),
                    fn ($sh) => UpShipmentResource::getUrl('edit', ['record' => $sh->id])))
                ->extraAttributes(function () {
                    $sh = UpShipment::forOrder($this->record->id)->latest()->first();

                    return $sh?->ttn
                        ? ['x-on:click.prevent.stop' => "navigator.clipboard.writeText('{$sh->ttn}'); \$tooltip('ТТН скопійовано!')"]
                        : [];
                })
                ->visible(fn () => UpShipment::forOrder($this->record->id)->exists()),

            Actions\DeleteAction::make(),
        ];
    }

    public function copyValue()
    {
        // This method is called by suffixActions to copy field values
        $this->js('console.log("Copy action triggered")');
    }

    public function copyManagerName()
    {
        $user = \App\Models\User::find($this->data['user_id'] ?? null);
        $name = $user?->name ?? '';
        $this->js("navigator.clipboard.writeText('$name')");
    }

    public function copyProviderName()
    {
        $provider = \App\Models\ShippingProvider::where('code', $this->data['shipping_provider'] ?? '')->first();
        $name = $provider?->name ?? '';
        $this->js("navigator.clipboard.writeText('$name')");
    }

    public function copyMethodName()
    {
        $method = \App\Models\ShippingMethod::where('method_code', $this->data['shipping_method'] ?? '')->first();
        $name = $method?->name ?? '';
        $this->js("navigator.clipboard.writeText('$name')");
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        \Log::info('=== mutateFormDataBeforeFill called ===');
        \Log::info('Initial data keys: '.implode(', ', array_keys($data)));

        // Розбити name на first_name та last_name якщо вони порожні
        if (empty($data['first_name']) && empty($data['last_name']) && ! empty($data['name'])) {
            $nameParts = explode(' ', trim($data['name']), 2);
            $data['first_name'] = $nameParts[0] ?? '';
            $data['last_name'] = $nameParts[1] ?? '';
        }

        // Fallback значення для порожніх імен
        if (empty($data['first_name'])) {
            $data['first_name'] = 'Не вказано';
        }
        if (empty($data['last_name'])) {
            $data['last_name'] = 'Не вказано';
        }

        // Розпакувати JSON дані доставки у відповідні поля
        $shippingData = null;
        if (! empty($data['shipping_data'])) {
            // Перевіряємо чи це вже масив чи JSON строка
            if (is_array($data['shipping_data'])) {
                $shippingData = $data['shipping_data'];
            } else {
                $shippingData = json_decode($data['shipping_data'], true);
            }
            \Log::info('Shipping data from DB: '.json_encode($shippingData));

            if (is_array($shippingData)) {
                // Для відділень - завантажувати завжди якщо є ref
                if (isset($shippingData['city_ref'])) {
                    // Завантажувати ref міста незалежно від наявності назви
                    $data['np_city'] = $shippingData['city_ref'];

                    $cityName = $shippingData['city'] ?? $shippingData['city_name'] ?? 'no name';
                    \Log::info('Set np_city to: '.$shippingData['city_ref'].' - '.$cityName);
                }

                // Завантажувати відділення якщо воно відповідає місту
                if (isset($shippingData['warehouse_ref'])
                    && isset($data['np_city'])
                    && $data['np_city'] === ($shippingData['city_ref'] ?? null)) {
                    $data['np_warehouse'] = $shippingData['warehouse_ref'];

                    \Log::info('Set np_warehouse to: '.$shippingData['warehouse_ref']);
                }

                // Для поштоматів - підтримка різних форматів ключів
                $postomatCityRef = $shippingData['postomat_city_ref'] ?? $shippingData['city_ref'] ?? null;
                if ($postomatCityRef) {
                    $data['np_postomat_city'] = $postomatCityRef;
                }

                // Завантажувати поштомат
                $postomatRef = $shippingData['postomat_ref'] ?? null;
                if ($postomatRef && isset($data['np_postomat_city'])) {
                    $data['np_postomat'] = $postomatRef;
                }

                // Для кур'єра - підтримка різних форматів ключів
                $courierCityRef = $shippingData['courier_city_ref'] ?? $shippingData['city_ref'] ?? null;
                if ($courierCityRef) {
                    $data['np_courier_city'] = $courierCityRef;
                }
                if (isset($shippingData['street'])) {
                    $data['np_courier_street'] = $shippingData['street'];
                }
                if (isset($shippingData['building'])) {
                    $data['np_courier_building'] = $shippingData['building'];
                }
                if (isset($shippingData['apartment'])) {
                    $data['np_courier_apartment'] = $shippingData['apartment'];
                }

                // Для УкрПошти
                if (isset($shippingData['city_id'])) {
                    $data['ukr_city_id'] = $shippingData['city_id'];
                    // For courier, also set courier city
                    $data['ukr_courier_city_id'] = $shippingData['city_id'];
                }
                if (isset($shippingData['branch_id'])) {
                    $data['ukr_branch_id'] = $shippingData['branch_id'];
                }
                // УкрПошта кур'єр - додаткові поля
                if (isset($shippingData['street'])) {
                    $data['ukr_courier_street'] = $shippingData['street'];
                }
                if (isset($shippingData['building'])) {
                    $data['ukr_courier_building'] = $shippingData['building'];
                }
                if (isset($shippingData['apartment'])) {
                    $data['ukr_courier_apartment'] = $shippingData['apartment'];
                }
            }
        }

        // Для Rozetka Delivery
        if (is_array($shippingData) && isset($data['shipping_provider']) && $data['shipping_provider'] === 'rozetka') {
            if (isset($shippingData['city_id'])) {
                $data['rozetka_city_id'] = $shippingData['city_id'];
                $data['rozetka_courier_city_id'] = $shippingData['city_id'];
            }
            if (isset($shippingData['pickup_point_id'])) {
                $data['rozetka_pickup_point_id'] = $shippingData['pickup_point_id'];
            }
            // Rozetka кур'єр - додаткові поля
            if (isset($shippingData['street'])) {
                $data['rozetka_courier_street'] = $shippingData['street'];
            }
            if (isset($shippingData['building'])) {
                $data['rozetka_courier_building'] = $shippingData['building'];
            }
            if (isset($shippingData['apartment'])) {
                $data['rozetka_courier_apartment'] = $shippingData['apartment'];
            }
        }

        \Log::info('Final data keys: '.implode(', ', array_keys($data)));
        \Log::info('np_city value: '.($data['np_city'] ?? 'NOT SET'));
        \Log::info('np_warehouse value: '.($data['np_warehouse'] ?? 'NOT SET'));

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
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

        // Детальне логування для відлагодження
        \Log::info('=== mutateFormDataBeforeSave DEBUG ===');
        \Log::info('All data keys: '.implode(', ', array_keys($data)));
        \Log::info('np_city: '.($data['np_city'] ?? 'NOT SET'));
        \Log::info('np_warehouse: '.($data['np_warehouse'] ?? 'NOT SET'));
        \Log::info('shipping_data exists: '.(isset($data['shipping_data']) ? 'YES' : 'NO'));

        // Зібрати дані доставки у JSON
        $shippingData = [];

        // Якщо ці поля не присутні, але є shipping_data, значить це другий прохід
        if (! isset($data['np_city']) && ! isset($data['np_warehouse']) && isset($data['shipping_data'])) {
            \Log::info('Second pass detected - existing shipping_data: '.$data['shipping_data']);

            return $data;
        }

        if (isset($data['shipping_provider']) && $data['shipping_provider'] === 'novaposhta') {
            switch ($data['shipping_method'] ?? '') {
                case 'warehouse':
                    if (! empty($data['np_city'])) {
                        $shippingData['city_ref'] = $data['np_city'];

                        // Отримати назву міста ТІЛЬКИ через API
                        if (! empty($data['np_city'])) {
                            try {
                                $cityName = collect(app(\App\Services\NovaPoshtaApiService::class)->getCities($data['np_city'])['data'] ?? [])
                                    ->firstWhere('Ref', $data['np_city'])['Description'] ?? null;
                                if ($cityName) {
                                    $shippingData['city'] = $cityName;
                                } else {
                                    \Log::warning('City not found via API, ref: '.$data['np_city'].' - saving ref only');
                                }
                            } catch (\Throwable $e) {
                                \Log::error('Nova Poshta API error: '.$e->getMessage());
                            }
                        }
                    }

                    // Обробка відділення
                    if (! empty($data['np_warehouse'])) {
                        $shippingData['warehouse_ref'] = $data['np_warehouse'];

                        // Спробувати отримати назву відділення
                        if (! empty($data['np_city'])) {
                            try {
                                $warehouse = collect(app(\App\Services\NovaPoshtaApiService::class)->getWarehouses($data['np_city'], '', 500)['data'] ?? [])
                                    ->firstWhere('Ref', $data['np_warehouse']);
                                if ($warehouse) {
                                    $shippingData['warehouse'] = '№'.($warehouse['Number'] ?? '').' - '.($warehouse['Description'] ?? '');
                                }
                            } catch (\Throwable $e) {
                                \Log::warning('Nova Poshta API error, saving warehouse ref without name: '.$data['np_warehouse']);
                            }
                        }
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
                                $data['shipping_city'] = $cityName;
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

                        // Отримати назву міста для кур'єра (робочий сервіс)
                        try {
                            $cityName = collect(app(\App\Services\NovaPoshtaApiService::class)->getCities($data['np_courier_city'])['data'] ?? [])
                                ->firstWhere('Ref', $data['np_courier_city'])['Description'] ?? null;
                            if ($cityName) {
                                $shippingData['city'] = $cityName;
                                $data['shipping_city'] = $cityName;
                            }
                        } catch (\Throwable $e) {
                            // Ignore API errors
                        }
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

        // УкрПошта
        if (isset($data['shipping_provider']) && $data['shipping_provider'] === 'ukrposhta') {
            switch ($data['shipping_method'] ?? '') {
                case 'branch':
                    if (! empty($data['ukr_city_id'])) {
                        $shippingData['city_id'] = $data['ukr_city_id'];
                        // Get city name from API
                        try {
                            $provider = new \App\Services\Shipping\UkrPoshtaProvider;
                            $cities = $provider->getCities();
                            $city = $cities->firstWhere('id', $data['ukr_city_id']);
                            if ($city) {
                                $shippingData['city_name'] = $city['name'];
                                $data['shipping_city'] = $city['name'];
                            }
                        } catch (\Exception $e) {
                            // Ignore API errors
                        }
                    }

                    if (! empty($data['ukr_branch_id'])) {
                        $shippingData['branch_id'] = $data['ukr_branch_id'];
                        // Get branch name from API
                        if (! empty($data['ukr_city_id'])) {
                            try {
                                $provider = new \App\Services\Shipping\UkrPoshtaProvider;
                                $warehouses = $provider->getWarehouses($data['ukr_city_id']);
                                $branch = $warehouses->firstWhere('id', $data['ukr_branch_id']);
                                if ($branch) {
                                    $shippingData['branch_name'] = $branch['name'];
                                    $data['shipping_post_office'] = $branch['name'];
                                }
                            } catch (\Exception $e) {
                                // Ignore API errors
                            }
                        }
                    }
                    break;

                case 'courier':
                    if (! empty($data['ukr_courier_city_id'])) {
                        $shippingData['city_id'] = $data['ukr_courier_city_id'];
                        // Get city name from API
                        try {
                            $provider = new \App\Services\Shipping\UkrPoshtaProvider;
                            $cities = $provider->getCities();
                            $city = $cities->firstWhere('id', $data['ukr_courier_city_id']);
                            if ($city) {
                                $shippingData['city_name'] = $city['name'];
                                $data['shipping_city'] = $city['name'];
                            }
                        } catch (\Exception $e) {
                            // Ignore API errors
                        }
                    }
                    if (! empty($data['ukr_courier_street'])) {
                        $shippingData['street'] = $data['ukr_courier_street'];
                    }
                    if (! empty($data['ukr_courier_building'])) {
                        $shippingData['building'] = $data['ukr_courier_building'];
                    }
                    if (! empty($data['ukr_courier_apartment'])) {
                        $shippingData['apartment'] = $data['ukr_courier_apartment'];
                    }
                    // Сформувати повну адресу
                    $address = trim(($data['ukr_courier_street'] ?? '').' '.($data['ukr_courier_building'] ?? ''));
                    if (! empty($data['ukr_courier_apartment'])) {
                        $address .= ', кв. '.$data['ukr_courier_apartment'];
                    }
                    if ($address) {
                        $data['shipping_address'] = $address;
                    }
                    break;
            }
        }

        // Rozetka Delivery
        if (isset($data['shipping_provider']) && $data['shipping_provider'] === 'rozetka') {
            switch ($data['shipping_method'] ?? '') {
                case 'pickup_point':
                    if (! empty($data['rozetka_city_id'])) {
                        $shippingData['city_id'] = $data['rozetka_city_id'];
                        // Get city name from API
                        try {
                            $provider = new \App\Services\Shipping\RozetkaDeliveryProvider;
                            $cities = $provider->getCities();
                            $city = $cities->firstWhere('id', $data['rozetka_city_id']);
                            if ($city) {
                                $shippingData['city_name'] = $city['name'];
                                $data['shipping_city'] = $city['name'];
                            }
                        } catch (\Exception $e) {
                            // Ignore API errors
                        }
                    }

                    if (! empty($data['rozetka_pickup_point_id'])) {
                        $shippingData['pickup_point_id'] = $data['rozetka_pickup_point_id'];
                        // Get pickup point name from API
                        if (! empty($data['rozetka_city_id'])) {
                            try {
                                $provider = new \App\Services\Shipping\RozetkaDeliveryProvider;
                                $pickupPoints = $provider->getPickupPoints((int) $data['rozetka_city_id']);
                                $point = $pickupPoints->firstWhere('id', $data['rozetka_pickup_point_id']);
                                if ($point) {
                                    $shippingData['pickup_point_name'] = $point['name'];
                                    $data['shipping_post_office'] = $point['name'].' - '.$point['address'];
                                }
                            } catch (\Exception $e) {
                                // Ignore API errors
                            }
                        }
                    }
                    break;

                case 'courier':
                    if (! empty($data['rozetka_courier_city_id'])) {
                        $shippingData['city_id'] = $data['rozetka_courier_city_id'];
                        // Get city name from API
                        try {
                            $provider = new \App\Services\Shipping\RozetkaDeliveryProvider;
                            $cities = $provider->getCities();
                            $city = $cities->firstWhere('id', $data['rozetka_courier_city_id']);
                            if ($city) {
                                $shippingData['city_name'] = $city['name'];
                                $data['shipping_city'] = $city['name'];
                            }
                        } catch (\Exception $e) {
                            // Ignore API errors
                        }
                    }
                    if (! empty($data['rozetka_courier_street'])) {
                        $shippingData['street'] = $data['rozetka_courier_street'];
                    }
                    if (! empty($data['rozetka_courier_building'])) {
                        $shippingData['building'] = $data['rozetka_courier_building'];
                    }
                    if (! empty($data['rozetka_courier_apartment'])) {
                        $shippingData['apartment'] = $data['rozetka_courier_apartment'];
                    }
                    // Сформувати повну адресу
                    $address = trim(($data['rozetka_courier_street'] ?? '').' '.($data['rozetka_courier_building'] ?? ''));
                    if (! empty($data['rozetka_courier_apartment'])) {
                        $address .= ', кв. '.$data['rozetka_courier_apartment'];
                    }
                    if ($address) {
                        $data['shipping_address'] = $address;
                    }
                    break;
            }
        }

        $data['shipping_data'] = json_encode($shippingData);

        // Логування результату
        \Log::info('mutateFormDataBeforeSave - shipping_data result:', [
            'shipping_data' => $shippingData,
            'json' => $data['shipping_data'],
        ]);

        // Прибрати тимчасові поля
        unset($data['np_city'], $data['np_warehouse'], $data['np_postomat_city'], $data['np_postomat'],
            $data['np_courier_city'], $data['np_courier_street'], $data['np_courier_building'], $data['np_courier_apartment'],
            $data['ukr_city_id'], $data['ukr_branch_id'], $data['ukr_courier_city_id'], $data['ukr_courier_street'],
            $data['ukr_courier_building'], $data['ukr_courier_apartment'],
            $data['rozetka_city_id'], $data['rozetka_pickup_point_id'], $data['rozetka_courier_city_id'],
            $data['rozetka_courier_street'], $data['rozetka_courier_building'], $data['rozetka_courier_apartment']);

        return $data;
    }
}
