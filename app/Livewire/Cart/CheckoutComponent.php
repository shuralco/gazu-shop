<?php

namespace App\Livewire\Cart;

use App\Mail\OrderClient;
use App\Mail\OrderManager;
use App\Models\Order;
use App\Services\LoyaltyService;
use App\Services\Shipping\ShippingOrchestrator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\On;
use Livewire\Component;

class CheckoutComponent extends Component
{
    // Основні дані
    public string $first_name = '';

    public string $last_name = '';

    public string $email = '';

    public string $phone = '';

    public string $note = '';

    // Юр. особа
    public bool $isCompany = false;

    public string $companyName = '';

    public string $edrpou = '';

    public string $contactPerson = '';

    // Вибір способу оплати
    public string $paymentMethod = '';

    public array $availablePaymentMethods = [];

    // Вибір способу доставки
    public string $shippingProvider = '';

    public string $shippingMethod = '';

    public array $availableProviders = [];

    public array $availableMethods = [];

    // Дані для Нової Пошти - відділення
    public string $npCity = '';

    public string $npCityRef = '';

    public array $npCities = [];

    public bool $npCitiesLoading = false;

    public string $npWarehouse = '';

    public string $npWarehouseRef = '';

    public array $npWarehouses = [];

    public array $npAllWarehouses = [];

    public bool $npWarehousesLoading = false;

    // Дані для Нової Пошти - кур'єр
    public string $npCourierCity = '';

    public string $npCourierCityRef = '';

    public array $npCourierCities = [];

    public string $npCourierStreet = '';

    public string $npCourierStreetRef = '';

    public string $npCourierBuilding = '';

    public string $npCourierApartment = '';

    // Дані для Нової Пошти - поштомат
    public string $npPostomatCity = '';

    public string $npPostomatCityRef = '';

    public array $npPostomatCities = [];

    public string $npPostomat = '';

    public string $npPostomatRef = '';

    public array $npPostomats = [];

    public array $npAllPostomats = [];

    // Дані для УкрПошти
    public string $ukrCity = '';

    public string $ukrCityId = '';

    public array $ukrCities = [];

    public bool $ukrCitiesLoading = false;

    public string $ukrBranch = '';

    public string $ukrBranchId = '';

    public array $ukrBranches = [];

    public array $ukrAllBranches = [];

    public bool $ukrBranchesLoading = false;

    // УкрПошта кур'єр
    public string $ukrCourierCity = '';

    public string $ukrCourierCityId = '';

    public array $ukrCourierCities = [];

    public string $ukrCourierStreet = '';

    public ?int $ukrCourierStreetId = null;

    public string $ukrCourierBuilding = '';

    public string $ukrCourierApartment = '';

    /**
     * Listener for UkrPoshtaSelector Livewire component.
     * Mirrors the inline searchUkr* methods so save() picks up the selection.
     */
    #[\Livewire\Attributes\On('ukrposhta-selected')]
    public function onUkrPoshtaSelected(array $payload): void
    {
        $method = $payload['method'] ?? 'branch';
        $this->shippingMethod = $method;

        if ($method === 'branch') {
            $this->ukrCityId = (string) ($payload['city_id'] ?? '');
            $this->ukrCity = (string) ($payload['city_name'] ?? '');
            $this->ukrBranchId = (string) ($payload['branch_id'] ?? '');
            $this->ukrBranch = (string) ($payload['branch_name'] ?? '');
        } else {
            $this->ukrCourierCityId = (string) ($payload['city_id'] ?? '');
            $this->ukrCourierCity = (string) ($payload['city_name'] ?? '');
            $this->ukrCourierStreet = (string) ($payload['street'] ?? '');
            $this->ukrCourierStreetId = isset($payload['street_id']) ? (int) $payload['street_id'] : null;
            $this->ukrCourierBuilding = (string) ($payload['building'] ?? '');
            $this->ukrCourierApartment = (string) ($payload['apartment'] ?? '');
        }
    }

    // Дані для Rozetka Delivery
    public string $rozetkaCity = '';

    public string $rozetkaCityId = '';

    public array $rozetkaCities = [];

    public bool $rozetkaCitiesLoading = false;

    public string $rozetkaPickupPoint = '';

    public string $rozetkaPickupPointId = '';

    public array $rozetkaPickupPoints = [];

    public bool $rozetkaPickupPointsLoading = false;

    // Rozetka кур'єр
    public string $rozetkaCourierCity = '';

    public string $rozetkaCourierCityId = '';

    public array $rozetkaCourierCities = [];

    public string $rozetkaCourierStreet = '';

    public string $rozetkaCourierBuilding = '';

    public string $rozetkaCourierApartment = '';

    // Дані для Meest Express
    public string $meestCity = '';

    public string $meestCityId = '';

    public array $meestCities = [];

    public bool $meestCitiesLoading = false;

    public string $meestBranch = '';

    public string $meestBranchId = '';

    public array $meestBranches = [];

    public bool $meestBranchesLoading = false;

    // Вартість доставки
    public float $shippingCost = 0;

    public bool $shippingCalculated = false;

    // Купони
    public array $appliedCoupon = [];

    public float $discountAmount = 0;

    // Збережені адреси та бонусні бали
    public array $savedAddresses = [];

    public ?int $selectedAddressId = null;

    public int $redeemPoints = 0;

    public float $loyaltyDiscount = 0;

    public int $availablePoints = 0;

    protected $listeners = [
        'coupon-applied' => 'handleCouponApplied',
        'coupon-removed' => 'handleCouponRemoved',
        'cart-updated' => '$refresh',
    ];

    /**
     * Handle Nova Poshta delivery data from NovaPoshtaSelector child component
     */
    #[On('np-delivery-selected')]
    public function handleNpDelivery(array $data): void
    {
        $this->shippingProvider = 'novaposhta';
        $this->shippingMethod = $data['method'] ?? 'warehouse';

        // Set NP-specific fields based on delivery method
        switch ($this->shippingMethod) {
            case 'warehouse':
                $this->npCityRef = $data['city_ref'] ?? '';
                $this->npCity = $data['city_name'] ?? '';
                $this->npWarehouseRef = $data['warehouse_ref'] ?? '';
                $this->npWarehouse = $data['warehouse_name'] ?? '';
                break;
            case 'postomat':
                $this->npPostomatCityRef = $data['city_ref'] ?? '';
                $this->npPostomatCity = $data['city_name'] ?? '';
                $this->npPostomatRef = $data['warehouse_ref'] ?? '';
                $this->npPostomat = $data['warehouse_name'] ?? '';
                break;
            case 'courier':
                $this->npCourierCityRef = $data['city_ref'] ?? '';
                $this->npCourierCity = $data['city_name'] ?? '';
                $this->npCourierStreet = $data['street'] ?? '';
                $this->npCourierStreetRef = $data['street_ref'] ?? '';
                $this->npCourierBuilding = $data['building'] ?? '';
                $this->npCourierApartment = $data['apartment'] ?? '';
                break;
        }

        // Update shipping cost (with free-shipping threshold + discount)
        $cost = (float) ($data['shipping_cost'] ?? 0);
        if ($cost > 0) {
            $this->shippingCost = $this->applyShippingPromos($cost);
            $this->shippingCalculated = true;
            $this->dispatch('shipping-cost-updated', cost: $this->shippingCost);
        }
    }

    /**
     * Apply free-shipping threshold and configured discounts to base cost.
     */
    protected function applyShippingPromos(float $cost): float
    {
        $threshold = (float) (\App\Models\DisplaySetting::get('free_shipping_threshold', 0) ?: 0);
        $cartTotal = \App\Helpers\Cart\Cart::getCartTotal();
        if ($threshold > 0 && $cartTotal >= $threshold) {
            return 0.0;
        }

        $percent = (float) (\App\Models\DisplaySetting::get('shipping_discount_percent', 0) ?: 0);
        $amount = (float) (\App\Models\DisplaySetting::get('shipping_discount_amount', 0) ?: 0);
        $cost = $cost * (1 - $percent / 100) - $amount;
        return max(0.0, round($cost, 2));
    }

    public function isFreeShipping(): bool
    {
        $threshold = (float) (\App\Models\DisplaySetting::get('free_shipping_threshold', 0) ?: 0);
        if ($threshold <= 0) {
            return false;
        }
        return \App\Helpers\Cart\Cart::getCartTotal() >= $threshold;
    }

    public function getFreeShippingThreshold(): float
    {
        return (float) (\App\Models\DisplaySetting::get('free_shipping_threshold', 0) ?: 0);
    }


    public function mount()
    {
        if (auth()->check()) {
            $userName = auth()->user()->name ?? '';
            $nameParts = explode(' ', $userName, 2);
            $this->first_name = $nameParts[0] ?? '';
            $this->last_name = $nameParts[1] ?? '';
            $this->email = auth()->user()->email ?? '';
            $this->phone = auth()->user()->phone ?? '';
        }

        // Відновити купон з сесії
        $sessionCoupon = session('applied_coupon');
        if ($sessionCoupon && is_array($sessionCoupon)) {
            $this->appliedCoupon = $sessionCoupon;
            $this->discountAmount = $sessionCoupon['discount'] ?? 0;
        }

        $this->loadAvailableProviders();
        $this->loadAvailablePaymentMethods();

        if (auth()->check()) {
            $this->savedAddresses = auth()->user()->addresses()->orderByDesc('is_default')->get()->toArray();
            $this->availablePoints = auth()->user()->loyalty_points;
        }
    }

    public function handleCouponApplied($couponData)
    {
        $this->appliedCoupon = $couponData;
        $this->discountAmount = $couponData['discount'] ?? 0;
    }

    public function handleCouponRemoved()
    {
        $this->appliedCoupon = [];
        $this->discountAmount = 0;
    }

    public function selectAddress(int $addressId): void
    {
        $address = auth()->user()->addresses()->findOrFail($addressId);
        $this->first_name = $address->first_name;
        $this->last_name = $address->last_name;
        if ($address->phone) {
            $this->phone = $address->phone;
        }
        $this->selectedAddressId = $addressId;
    }

    public function updatedRedeemPoints($value): void
    {
        $this->redeemPoints = (int) min(max(0, (int) $value), $this->availablePoints);
        $this->loyaltyDiscount = app(LoyaltyService::class)->getRedemptionValue($this->redeemPoints);
    }

    public function increaseQuantity($productId)
    {
        $cart = \App\Helpers\Cart\Cart::getCart();
        if (isset($cart[$productId])) {
            $newQuantity = $cart[$productId]['quantity'] + 1;
            \App\Helpers\Cart\Cart::updateItemQuantity($productId, $newQuantity);
            $this->dispatch('cart-updated');
            
            $this->dispatch('show-notification',
                type: 'info',
                title: 'КІЛЬКІСТЬ ОНОВЛЕНО',
                message: "Товар '{$cart[$productId]['title']}': {$newQuantity} шт."
            );
        }
    }

    public function decreaseQuantity($productId)
    {
        $cart = \App\Helpers\Cart\Cart::getCart();
        if (isset($cart[$productId]) && $cart[$productId]['quantity'] > 1) {
            $newQuantity = $cart[$productId]['quantity'] - 1;
            \App\Helpers\Cart\Cart::updateItemQuantity($productId, $newQuantity);
            $this->dispatch('cart-updated');
            
            $this->dispatch('show-notification',
                type: 'info',
                title: 'КІЛЬКІСТЬ ОНОВЛЕНО',
                message: "Товар '{$cart[$productId]['title']}': {$newQuantity} шт."
            );
        }
    }

    public function removeFromCart($productId)
    {
        $cart = \App\Helpers\Cart\Cart::getCart();
        $productTitle = $cart[$productId]['title'] ?? 'Товар';
        
        if (\App\Helpers\Cart\Cart::removeProductFromCart($productId)) {
            $this->dispatch('cart-updated');
            
            $this->dispatch('show-notification',
                type: 'warning',
                title: 'ТОВАР ВИДАЛЕНО',
                message: "Товар '{$productTitle}' видалено з кошика"
            );
        }
    }

    public function handleShippingMethodChange()
    {
        // Скинути дані при зміні методу доставки
        $this->resetShippingData();
    }

    public function selectNpWarehouseByIndex($index)
    {
        if (isset($this->npWarehouses[$index])) {
            $warehouse = $this->npWarehouses[$index];
            $this->npWarehouseRef = $warehouse['ref'];
            $this->npWarehouse = '№'.$warehouse['number'].' - '.$warehouse['address'];
            $this->npWarehouses = [];
            $this->resetErrorBag('npWarehouse');
            $this->dispatch('citySelected');
            $this->calculateShipping();
        }
    }

    public function selectNpPostomatByIndex($index)
    {
        if (isset($this->npPostomats[$index])) {
            $postomat = $this->npPostomats[$index];
            $this->npPostomatRef = $postomat['ref'];

            // Формуємо назву поштомату залежно від доступних полів
            $number = $postomat['number'] ?? $postomat['siteKey'] ?? '';
            $address = $postomat['address'] ?? $postomat['description'] ?? '';

            $this->npPostomat = ($number ? '№'.$number.' - ' : '').$address;
            $this->npPostomats = [];
            $this->resetErrorBag('npPostomat');
            $this->dispatch('citySelected');
            $this->calculateShipping();
        }
    }

    public function selectUkrCityByIndex($index)
    {
        if (isset($this->ukrCities[$index])) {
            $city = $this->ukrCities[$index];
            $this->ukrCityId = $city['id'];
            $this->ukrCity = $city['name'];
            $this->ukrCities = [];
            $this->resetErrorBag('ukrCity');
            $this->dispatch('citySelected');
            $this->loadUkrBranches();
        }
    }

    public function selectUkrBranchByIndex($index)
    {
        if (isset($this->ukrBranches[$index])) {
            $branch = $this->ukrBranches[$index];
            $this->ukrBranchId = $branch['id'];
            $this->ukrBranch = ($branch['number'] ? $branch['number'].' - ' : '').$branch['address'];
            $this->ukrBranches = [];
            $this->resetErrorBag('ukrBranch');
            $this->dispatch('citySelected');
            $this->calculateShipping();
        }
    }

    public function selectNpCourierCityByIndex($index)
    {
        if (isset($this->npCourierCities[$index])) {
            $city = $this->npCourierCities[$index];
            $this->npCourierCityRef = $city['ref'];
            $this->npCourierCity = $city['name'];
            $this->npCourierCities = [];
            $this->dispatch('citySelected');

            // Автоматично розрахувати доставку якщо адреса заповнена
            if ($this->npCourierStreet && $this->npCourierBuilding) {
                $this->calculateShipping();
            }
        }
    }

    public function selectUkrCourierCityByIndex($index)
    {
        if (isset($this->ukrCourierCities[$index])) {
            $city = $this->ukrCourierCities[$index];
            $this->ukrCourierCityId = $city['id'];
            $this->ukrCourierCity = $city['name'];
            $this->ukrCourierCities = [];
            $this->dispatch('citySelected');
            // Автоматично розрахувати доставку якщо адреса заповнена
            if ($this->ukrCourierStreet && $this->ukrCourierBuilding) {
                $this->calculateShipping();
            }
        }
    }

    public function selectNpPostomatCityByIndex($index)
    {
        if (isset($this->npPostomatCities[$index])) {
            $city = $this->npPostomatCities[$index];
            $this->npPostomatCityRef = $city['ref'];
            $this->npPostomatCity = $city['name'];
            $this->npPostomatCities = [];
            $this->resetErrorBag('npPostomatCity');

            // Скинути попередній вибір поштомату
            $this->npPostomat = '';
            $this->npPostomatRef = '';

            $this->dispatch('citySelected');
            $this->loadNpPostomats();
            $this->calculateShipping();
        }
    }

    protected function resetShippingData()
    {
        // Скинути дані Нової Пошти
        $this->npCity = '';
        $this->npCityRef = '';
        $this->npCities = [];
        $this->npWarehouse = '';
        $this->npWarehouseRef = '';
        $this->npWarehouses = [];
        $this->npCourierCity = '';
        $this->npCourierCityRef = '';
        $this->npCourierCities = [];
        $this->npCourierStreet = '';
        $this->npCourierBuilding = '';
        $this->npCourierApartment = '';
        $this->npPostomatCity = '';
        $this->npPostomatCityRef = '';
        $this->npPostomatCities = [];
        $this->npPostomat = '';
        $this->npPostomatRef = '';
        $this->npPostomats = [];

        // Скинути дані УкрПошти
        $this->ukrCity = '';
        $this->ukrCityId = '';
        $this->ukrCities = [];
        $this->ukrBranch = '';
        $this->ukrBranchId = '';
        $this->ukrBranches = [];
        $this->ukrCourierCity = '';
        $this->ukrCourierCityId = '';
        $this->ukrCourierCities = [];
        $this->ukrCourierStreet = '';
        $this->ukrCourierBuilding = '';
        $this->ukrCourierApartment = '';

        // Скинути дані Rozetka
        $this->rozetkaCity = '';
        $this->rozetkaCityId = '';
        $this->rozetkaCities = [];
        $this->rozetkaPickupPoint = '';
        $this->rozetkaPickupPointId = '';
        $this->rozetkaPickupPoints = [];
        $this->rozetkaCourierCity = '';
        $this->rozetkaCourierCityId = '';
        $this->rozetkaCourierCities = [];
        $this->rozetkaCourierStreet = '';
        $this->rozetkaCourierBuilding = '';
        $this->rozetkaCourierApartment = '';

        // Скинути дані Meest Express
        $this->meestCity = '';
        $this->meestCityId = '';
        $this->meestCities = [];
        $this->meestBranch = '';
        $this->meestBranchId = '';
        $this->meestBranches = [];

        // Скинути розрахунок доставки
        $this->shippingCost = 0;
        $this->shippingCalculated = false;
    }

    /**
     * Завантажити доступні провайдери доставки
     */
    public function loadAvailableProviders()
    {
        $this->availableProviders = [
            'novaposhta' => 'Нова Пошта',
            'ukrposhta' => 'Укрпошта',
            'rozetka' => 'Rozetka Delivery',
            'meest' => 'Meest Express',
            'pickup' => 'Самовивіз',
        ];
    }

    /**
     * Завантажити доступні способи оплати
     */
    public function loadAvailablePaymentMethods()
    {
        // Отримуємо активні платіжні шлюзи з БД
        $paymentGateways = \App\Models\PaymentGatewaySettings::where('is_active', true)
            ->orderBy('name')
            ->get();

        $this->availablePaymentMethods = [];

        foreach ($paymentGateways as $gateway) {
            $this->availablePaymentMethods[$gateway->code] = [
                'name' => $gateway->name,
                'description' => $gateway->description,
                'fee' => $gateway->fee_percentage,
                'icon' => $this->getPaymentIcon($gateway->code),
            ];
        }

        // Якщо немає жодного методу, додаємо готівку за замовчуванням
        if (empty($this->availablePaymentMethods)) {
            $this->availablePaymentMethods['cash'] = [
                'name' => 'Готівка при отриманні',
                'description' => 'Оплата готівкою при отриманні товару',
                'fee' => 0,
                'icon' => 'fa-money-bill',
            ];
        }
    }

    /**
     * Отримати іконку для способу оплати
     */
    private function getPaymentIcon(string $code): string
    {
        return match ($code) {
            'liqpay' => 'fa-credit-card',
            'wayforpay' => 'fa-credit-card',
            'privat24' => 'fa-university',
            'bank_transfer' => 'fa-university',
            'cash' => 'fa-money-bill',
            'card' => 'fa-credit-card',
            'monobank' => 'fa-mobile',
            default => 'fa-wallet',
        };
    }

    /**
     * При зміні провайдера - завантажити методи
     */
    public function updatedShippingProvider($value)
    {
        $this->resetShippingData();
        $this->shippingMethod = '';
        $this->availableMethods = [];

        switch ($value) {
            case 'novaposhta':
                $this->availableMethods = [
                    'warehouse' => 'На відділення',
                    'courier' => 'Кур\'єром',
                    'postomat' => 'На поштомат',
                ];
                // Auto-select default method for NP selector component
                $this->shippingMethod = 'warehouse';
                break;
            case 'ukrposhta':
                $this->availableMethods = [
                    'branch' => 'На відділення УкрПошти',
                    'courier' => 'Кур\'єром УкрПошти',
                ];
                break;
            case 'rozetka':
                $this->availableMethods = [
                    'pickup_point' => 'Пункт видачі',
                    'courier' => 'Кур\'єрська доставка',
                ];
                break;
            case 'meest':
                $this->availableMethods = [
                    'branch' => 'На відділення Meest Express',
                ];
                break;
            case 'pickup':
                $this->availableMethods = [
                    'shop' => 'З магазину',
                ];
                $this->shippingMethod = 'shop'; // Автоматично вибираємо метод
                $this->shippingCost = 0;
                $this->shippingCalculated = true;
                break;
        }
    }

    /**
     * При зміні методу доставки
     */
    public function updatedShippingMethod($value)
    {
        // Зберегти обране місто якщо це Нова Пошта
        $savedCityRef = '';
        $savedCityName = '';

        if ($this->shippingProvider === 'novaposhta') {
            // Зберегти дані про місто з попереднього методу
            $savedCityRef = $this->npCityRef ?: $this->npCourierCityRef ?: $this->npPostomatCityRef;
            $savedCityName = $this->npCity ?: $this->npCourierCity ?: $this->npPostomatCity;
        }

        $this->resetShippingData();

        // Відновити місто для нового методу Нової Пошти
        if ($this->shippingProvider === 'novaposhta' && $savedCityRef && $savedCityName) {
            switch ($value) {
                case 'warehouse':
                    $this->npCityRef = $savedCityRef;
                    $this->npCity = $savedCityName;
                    break;
                case 'courier':
                    $this->npCourierCityRef = $savedCityRef;
                    $this->npCourierCity = $savedCityName;
                    break;
                case 'postomat':
                    $this->npPostomatCityRef = $savedCityRef;
                    $this->npPostomatCity = $savedCityName;
                    break;
            }
        }

        // Для самовивозу одразу встановлюємо нульову вартість
        if ($this->shippingProvider === 'pickup') {
            $this->shippingCost = 0;
            $this->shippingCalculated = true;
        }
    }

    /**
     * Пошук міст Нової Пошти (відділення)
     */
    public function searchNpCities()
    {
        if (strlen($this->npCity) < 2) {
            $this->npCities = [];
            $this->npCitiesLoading = false;

            return;
        }

        $this->npCitiesLoading = true;

        try {
            $provider = new \App\Services\Shipping\NovaPoshtaProvider;
            $cities = $provider->getCities($this->npCity);

            $this->npCities = $cities->map(function ($city) {
                return [
                    'ref' => $city['ref'],
                    'name' => $city['name'],
                    'type' => $city['type'] ?? 'місто',
                ];
            })->take(30)->toArray();
        } catch (\Exception $e) {
            Log::error('Error searching cities: '.$e->getMessage());
            $this->npCities = [];
            $this->addError('npCity', 'Помилка пошуку міст');
        } finally {
            $this->npCitiesLoading = false;
        }
    }

    /**
     * Вибір міста Нової Пошти (відділення)
     */
    public function selectNpCityByIndex($index)
    {
        if (isset($this->npCities[$index])) {
            $city = $this->npCities[$index];
            $this->npCityRef = $city['ref'];
            $this->npCity = $city['name'];
            $this->npCities = [];
            $this->resetErrorBag('npCity');
            $this->dispatch('citySelected');

            // Завантажити відділення автоматично
            $this->loadNpWarehouses();

            // Авто-розрахунок якщо є відділення
            if ($this->npWarehouseRef) {
                $this->calculateShipping();
            }
        }
    }

    public function selectNpCity($ref, $name)
    {
        $this->npCityRef = $ref;
        $this->npCity = $name;
        $this->npCities = [];
        $this->resetErrorBag('npCity');
        $this->dispatch('citySelected');

        // Завантажити відділення автоматично
        $this->loadNpWarehouses();
    }

    /**
     * Завантажити відділення Нової Пошти
     */
    public function loadNpWarehouses()
    {
        if (! $this->npCityRef) {
            $this->npWarehouses = [];

            return;
        }

        $this->npWarehousesLoading = true;
        $this->npWarehouse = '';
        $this->npWarehouseRef = '';

        try {
            $provider = new \App\Services\Shipping\NovaPoshtaProvider;
            $warehouses = $provider->getWarehouses($this->npCityRef);

            Log::info('LoadNpWarehouses', [
                'cityRef' => $this->npCityRef,
                'warehousesCount' => $warehouses->count(),
                'warehouses' => $warehouses->take(3)->toArray(),
            ]);

            $warehouseList = $warehouses->map(function ($warehouse) {
                return [
                    'ref' => $warehouse['ref'],
                    'number' => $warehouse['number'] ?? '',
                    'address' => $warehouse['address'] ?? $warehouse['description'] ?? '',
                    'schedule' => $warehouse['schedule'] ?? '',
                ];
            })->sortBy('number')->values()->toArray();

            // Зберігаємо повний список для пошуку
            $this->npAllWarehouses = $warehouseList;
            $this->npWarehouses = array_slice($warehouseList, 0, 20); // Показуємо перші 20 для початку

            if (empty($this->npAllWarehouses)) {
                $this->addError('npWarehouse', 'Відділення в цьому місті не знайдено');
            }
        } catch (\Exception $e) {
            Log::error('Error loading warehouses: '.$e->getMessage());
            $this->npWarehouses = [];
            $this->addError('npWarehouse', 'Помилка завантаження відділень');
        } finally {
            $this->npWarehousesLoading = false;
        }
    }

    /**
     * Пошук відділень
     */
    public function searchNpWarehouses()
    {
        if (! $this->npCityRef || empty($this->npAllWarehouses)) {
            $this->npWarehouses = [];

            return;
        }

        // Якщо пошук менше 2 символів, показуємо перші 20 відділень
        if (strlen($this->npWarehouse) < 2) {
            $this->npWarehouses = array_slice($this->npAllWarehouses, 0, 20);

            return;
        }

        // Фільтруємо відділення за пошуковим запитом
        $search = mb_strtolower($this->npWarehouse);

        $this->npWarehouses = collect($this->npAllWarehouses)->filter(function ($warehouse) use ($search) {
            return str_contains(mb_strtolower($warehouse['number']), $search) ||
                   str_contains(mb_strtolower($warehouse['address']), $search);
        })->take(30)->values()->toArray();
    }

    /**
     * Вибір відділення
     */
    public function selectNpWarehouse($ref, $display)
    {
        $this->npWarehouseRef = $ref;
        $this->npWarehouse = $display;
        $this->npWarehouses = []; // Закриваємо dropdown
        $this->resetErrorBag('npWarehouse');
        $this->dispatch('citySelected');
        $this->calculateShipping();
    }

    /**
     * При зміні тексту відділення (якщо редагують вибране)
     */
    public function updatedNpWarehouse($value)
    {
        if ($this->npWarehouseRef && $value !== $this->npWarehouse) {
            // Якщо змінили текст - скинути вибір і відкрити пошук
            $this->npWarehouseRef = '';
            $this->searchNpWarehouses();
        }
    }

    /**
     * Пошук міст для кур'єра
     */
    public function searchNpCourierCities()
    {
        if (strlen($this->npCourierCity) < 2) {
            $this->npCourierCities = [];
            $this->npCitiesLoading = false;

            return;
        }

        $this->npCitiesLoading = true;

        try {
            $provider = new \App\Services\Shipping\NovaPoshtaProvider;
            $cities = $provider->getCities($this->npCourierCity);

            $this->npCourierCities = $cities->map(function ($city) {
                return [
                    'ref' => $city['ref'],
                    'name' => $city['name'],
                    'type' => $city['type'] ?? 'місто',
                ];
            })->take(30)->toArray();
        } catch (\Exception $e) {
            Log::error('Error searching courier cities: '.$e->getMessage());
            $this->npCourierCities = [];
        } finally {
            $this->npCitiesLoading = false;
        }
    }

    /**
     * Вибір міста для кур'єра
     */
    public function selectNpCourierCity($ref, $name)
    {
        $this->npCourierCityRef = $ref;
        $this->npCourierCity = $name;
        $this->npCourierCities = [];
    }

    /**
     * Пошук міст для поштоматів
     */
    public function searchNpPostomatCities()
    {
        if (strlen($this->npPostomatCity) < 2) {
            $this->npPostomatCities = [];
            $this->npCitiesLoading = false;

            return;
        }

        $this->npCitiesLoading = true;

        try {
            $provider = new \App\Services\Shipping\NovaPoshtaProvider;
            $cities = $provider->getCities($this->npPostomatCity);

            Log::info('SearchNpPostomatCities', [
                'query' => $this->npPostomatCity,
                'citiesCount' => $cities->count(),
                'cities' => $cities->take(3)->toArray(),
            ]);

            $this->npPostomatCities = $cities->map(function ($city) {
                return [
                    'ref' => $city['ref'],
                    'name' => $city['name'],
                    'type' => $city['type'] ?? 'місто',
                ];
            })->take(10)->toArray();
        } catch (\Exception $e) {
            Log::error('Error searching postomat cities: '.$e->getMessage());
            $this->npPostomatCities = [];
        } finally {
            $this->npCitiesLoading = false;
        }
    }

    /**
     * Вибір міста для поштоматів
     */
    public function selectNpPostomatCity($ref, $name)
    {
        $this->npPostomatCityRef = $ref;
        $this->npPostomatCity = $name;
        $this->npPostomatCities = [];
        $this->dispatch('citySelected');

        // Завантажити поштомати
        $this->loadNpPostomats();
    }

    /**
     * Завантажити поштомати
     */
    public function loadNpPostomats()
    {
        if (! $this->npPostomatCityRef) {
            $this->npPostomats = [];
            $this->npAllPostomats = [];

            return;
        }

        $this->npWarehousesLoading = true;

        try {
            $provider = new \App\Services\Shipping\NovaPoshtaProvider;
            $warehouses = $provider->getWarehouses($this->npPostomatCityRef);

            Log::info('LoadNpPostomats', [
                'cityRef' => $this->npPostomatCityRef,
                'warehousesCount' => $warehouses->count(),
                'warehouses' => $warehouses->take(3)->toArray(),
            ]);

            // Фільтруємо тільки поштомати (використовуємо mb_strtolower для українського тексту)
            $postomats = $warehouses->filter(function ($warehouse) {
                // Check CategoryOfWarehouse field from API
                if (isset($warehouse['CategoryOfWarehouse']) && $warehouse['CategoryOfWarehouse'] === 'Postomat') {
                    return true;
                }

                // Fallback to text search
                $description = mb_strtolower($warehouse['description'] ?? '');
                $address = mb_strtolower($warehouse['address'] ?? '');

                return str_contains($description, 'поштомат') ||
                       str_contains($address, 'поштомат') ||
                       str_contains($description, 'poshtomat') ||
                       str_contains($address, 'poshtomat');
            })->map(function ($postomat) {
                return [
                    'ref' => $postomat['ref'],
                    'number' => $postomat['number'] ?? $postomat['siteKey'] ?? '',
                    'address' => $postomat['address'] ?? $postomat['description'] ?? '',
                    'description' => $postomat['description'] ?? '',
                ];
            })->sortBy('number')->values()->toArray();

            // Зберігаємо повний список для пошуку
            $this->npAllPostomats = $postomats;

            // Якщо є пошуковий запит, фільтруємо, інакше показуємо перші 10
            if (strlen($this->npPostomat) >= 2) {
                $this->searchNpPostomats();
            } else {
                $this->npPostomats = array_slice($this->npAllPostomats, 0, 10);
            }

        } catch (\Exception $e) {
            Log::error('Error loading postomats: '.$e->getMessage());
            $this->npPostomats = [];
            $this->npAllPostomats = [];
        } finally {
            $this->npWarehousesLoading = false;
        }
    }

    /**
     * Пошук поштоматів
     */
    public function searchNpPostomats()
    {
        if (! $this->npPostomatCityRef || empty($this->npAllPostomats)) {
            $this->npPostomats = [];

            return;
        }

        // Якщо пошук менше 2 символів, показуємо всі поштомати
        if (strlen($this->npPostomat) < 2) {
            $this->npPostomats = array_slice($this->npAllPostomats, 0, 10);

            return;
        }

        // Фільтруємо завантажені поштомати за пошуковим запитом
        $search = mb_strtolower($this->npPostomat);

        $this->npPostomats = collect($this->npAllPostomats)->filter(function ($postomat) use ($search) {
            return str_contains(mb_strtolower($postomat['number']), $search) ||
                   str_contains(mb_strtolower($postomat['address']), $search);
        })->take(20)->values()->toArray();
    }

    /**
     * Вибір поштомату
     */
    public function selectNpPostomat($ref, $display)
    {
        $this->npPostomatRef = $ref;
        $this->npPostomat = $display;
        $this->npPostomats = []; // Закриваємо dropdown
        $this->resetErrorBag('npPostomat');

        // Розрахувати доставку
        $this->calculateShipping();
    }

    /**
     * При зміні тексту поштомату (якщо редагують вибраний)
     */
    public function updatedNpPostomat($value)
    {
        if ($this->npPostomatRef && $value !== $this->npPostomat) {
            // Якщо змінили текст - скинути вибір і відкрити пошук
            $this->npPostomatRef = '';
            $this->searchNpPostomats();
        }
    }

    /**
     * Пошук міст УкрПошти
     */
    public function searchUkrCities()
    {
        if (strlen($this->ukrCity) < 2) {
            $this->ukrCities = [];
            $this->ukrCitiesLoading = false;

            return;
        }

        $this->ukrCitiesLoading = true;

        try {
            $provider = new \App\Services\Shipping\UkrPoshtaProvider;
            $cities = $provider->getCities($this->ukrCity);

            $this->ukrCities = $cities->map(function ($city) {
                return [
                    'id' => $city['id'],
                    'name' => $city['name'],
                    'region' => $city['region'] ?? '',
                ];
            })->take(15)->toArray();
        } catch (\Exception $e) {
            Log::error('Error searching UkrPoshta cities: '.$e->getMessage());
            $this->ukrCities = [];
            $this->addError('ukrCity', 'Помилка пошуку міст УкрПошти');
        } finally {
            $this->ukrCitiesLoading = false;
        }
    }

    /**
     * Вибір міста УкрПошти
     */
    public function selectUkrCity($id, $name)
    {
        $this->ukrCityId = $id;
        $this->ukrCity = $name;
        $this->ukrCities = [];
        $this->resetErrorBag('ukrCity');

        // Завантажити відділення автоматично
        $this->loadUkrBranches();
    }

    /**
     * Завантажити відділення УкрПошти
     */
    public function loadUkrBranches()
    {
        if (! $this->ukrCityId) {
            $this->ukrBranches = [];

            return;
        }

        $this->ukrBranchesLoading = true;
        $this->ukrBranch = '';
        $this->ukrBranchId = '';

        try {
            $provider = new \App\Services\Shipping\UkrPoshtaProvider;
            $branches = $provider->getWarehouses($this->ukrCityId);

            Log::info('LoadUkrBranches', [
                'cityId' => $this->ukrCityId,
                'branchesCount' => $branches->count(),
                'branches' => $branches->take(3)->toArray(),
            ]);

            $branchList = $branches->map(function ($branch) {
                return [
                    'id' => $branch['id'],
                    'number' => $branch['number'] ?? '',
                    'name' => $branch['name'] ?? '',
                    'address' => $branch['address'] ?? $branch['description'] ?? '',
                    'phone' => $branch['phone'] ?? '',
                ];
            })->values()->toArray();

            // Зберігаємо повний список для пошуку
            $this->ukrAllBranches = $branchList;
            $this->ukrBranches = array_slice($branchList, 0, 10); // Показуємо перші 10

            if (empty($this->ukrAllBranches)) {
                $this->addError('ukrBranch', 'Відділення УкрПошти в цьому місті не знайдено');
            }
        } catch (\Exception $e) {
            Log::error('Error loading UkrPoshta branches: '.$e->getMessage());
            $this->ukrBranches = [];
            $this->addError('ukrBranch', 'Помилка завантаження відділень УкрПошти');
        } finally {
            $this->ukrBranchesLoading = false;
        }
    }

    /**
     * Пошук відділень УкрПошти
     */
    public function searchUkrBranches()
    {
        if (! $this->ukrCityId || empty($this->ukrAllBranches)) {
            $this->ukrBranches = [];

            return;
        }

        // Якщо пошук менше 2 символів, показуємо перші 10 відділень
        if (strlen($this->ukrBranch) < 2) {
            $this->ukrBranches = array_slice($this->ukrAllBranches, 0, 10);

            return;
        }

        // Фільтруємо відділення за пошуковим запитом
        $search = strtolower($this->ukrBranch);

        $this->ukrBranches = collect($this->ukrAllBranches)->filter(function ($branch) use ($search) {
            return str_contains(strtolower($branch['number']), $search) ||
                   str_contains(strtolower($branch['address']), $search) ||
                   str_contains(strtolower($branch['name']), $search);
        })->take(10)->values()->toArray();
    }

    /**
     * Вибір відділення УкрПошти
     */
    public function selectUkrBranch($id, $display)
    {
        $this->ukrBranchId = $id;
        $this->ukrBranch = $display;
        $this->ukrBranches = []; // Закриваємо dropdown
        $this->resetErrorBag('ukrBranch');
        $this->calculateShipping();
    }

    /**
     * При зміні тексту відділення УкрПошти
     */
    public function updatedUkrBranch($value)
    {
        if ($this->ukrBranchId && $value !== $this->ukrBranch) {
            // Якщо змінили текст - скинути вибір і відкрити пошук
            $this->ukrBranchId = '';
            $this->searchUkrBranches();
        }
    }

    /**
     * Пошук міст УкрПошти для кур'єра
     */
    public function searchUkrCourierCities()
    {
        if (strlen($this->ukrCourierCity) < 2) {
            $this->ukrCourierCities = [];
            $this->ukrCitiesLoading = false;

            return;
        }

        $this->ukrCitiesLoading = true;

        try {
            $provider = new \App\Services\Shipping\UkrPoshtaProvider;
            $cities = $provider->getCities($this->ukrCourierCity);

            $this->ukrCourierCities = $cities->map(function ($city) {
                return [
                    'id' => $city['id'],
                    'name' => $city['name'],
                    'region' => $city['region'] ?? '',
                ];
            })->take(10)->toArray();
        } catch (\Exception $e) {
            Log::error('Error searching UkrPoshta courier cities: '.$e->getMessage());
            $this->ukrCourierCities = [];
        } finally {
            $this->ukrCitiesLoading = false;
        }
    }

    /**
     * Вибір міста УкрПошти для кур'єра
     */
    public function selectUkrCourierCity($id, $name)
    {
        $this->ukrCourierCityId = $id;
        $this->ukrCourierCity = $name;
        $this->ukrCourierCities = [];
        $this->dispatch('citySelected');
        // Автоматично розрахувати доставку якщо адреса заповнена
        if ($this->ukrCourierStreet && $this->ukrCourierBuilding) {
            $this->calculateShipping();
        }
    }

    /**
     * Автоматичний розрахунок при введенні вулиці для Нової Пошти
     */
    public function updatedNpCourierStreet()
    {
        if ($this->npCourierCityRef && $this->npCourierStreet && $this->npCourierBuilding) {
            $this->calculateShipping();
        }
    }

    /**
     * Автоматичний розрахунок при введенні будинку для Нової Пошти
     */
    public function updatedNpCourierBuilding()
    {
        if ($this->npCourierCityRef && $this->npCourierStreet && $this->npCourierBuilding) {
            $this->calculateShipping();
        }
    }

    /**
     * Автоматичний розрахунок при введенні вулиці для УкрПошти
     */
    public function updatedUkrCourierStreet()
    {
        if ($this->ukrCourierCityId && $this->ukrCourierStreet && $this->ukrCourierBuilding) {
            $this->calculateShipping();
        }
    }

    /**
     * Автоматичний розрахунок при введенні будинку для УкрПошти
     */
    public function updatedUkrCourierBuilding()
    {
        if ($this->ukrCourierCityId && $this->ukrCourierStreet && $this->ukrCourierBuilding) {
            $this->calculateShipping();
        }
    }

    /**
     * Пошук міст Rozetka Delivery
     */
    public function searchRozetkaCities()
    {
        if (strlen($this->rozetkaCity) < 2) {
            $this->rozetkaCities = [];
            $this->rozetkaCitiesLoading = false;

            return;
        }

        $this->rozetkaCitiesLoading = true;

        try {
            $provider = new \App\Services\Shipping\RozetkaDeliveryProvider;
            $cities = $provider->getCities($this->rozetkaCity);

            $this->rozetkaCities = $cities->map(function ($city) {
                return [
                    'id' => $city['id'],
                    'name' => $city['name'],
                ];
            })->take(30)->toArray();
        } catch (\Exception $e) {
            Log::error('Error searching Rozetka cities: '.$e->getMessage());
            $this->rozetkaCities = [];
            $this->addError('rozetkaCity', 'Помилка пошуку міст');
        }

        $this->rozetkaCitiesLoading = false;
    }

    /**
     * Вибрати місто Rozetka за індексом
     */
    public function selectRozetkaCityByIndex($index)
    {
        if (isset($this->rozetkaCities[$index])) {
            $city = $this->rozetkaCities[$index];
            $this->rozetkaCityId = $city['id'];
            $this->rozetkaCity = $city['name'];
            $this->rozetkaCities = [];
            $this->resetErrorBag('rozetkaCity');
            $this->dispatch('citySelected');

            // Завантажити пункти видачі для pickup_point методу
            if ($this->shippingMethod === 'pickup_point') {
                $this->loadRozetkaPickupPoints();
            }
        }
    }

    /**
     * Вибрати місто Rozetka напряму
     */
    public function selectRozetkaCity($id, $name)
    {
        $this->rozetkaCityId = $id;
        $this->rozetkaCity = $name;
        $this->rozetkaCities = [];
        $this->resetErrorBag('rozetkaCity');

        // Завантажити пункти видачі для pickup_point методу
        if ($this->shippingMethod === 'pickup_point') {
            $this->loadRozetkaPickupPoints();
        }
    }

    /**
     * Завантажити пункти видачі Rozetka
     */
    public function loadRozetkaPickupPoints()
    {
        if (! $this->rozetkaCityId) {
            $this->rozetkaPickupPoints = [];

            return;
        }

        $this->rozetkaPickupPointsLoading = true;
        $this->rozetkaPickupPoint = '';
        $this->rozetkaPickupPointId = '';

        try {
            $provider = new \App\Services\Shipping\RozetkaDeliveryProvider;
            $pickupPoints = $provider->getPickupPoints((int) $this->rozetkaCityId);

            $this->rozetkaPickupPoints = $pickupPoints->map(function ($point) {
                return [
                    'id' => $point['id'],
                    'name' => $point['name'],
                    'address' => $point['address'],
                ];
            })->toArray();
        } catch (\Exception $e) {
            Log::error('Error loading Rozetka pickup points: '.$e->getMessage());
            $this->rozetkaPickupPoints = [];
            $this->addError('rozetkaPickupPoint', 'Помилка завантаження пунктів видачі');
        }

        $this->rozetkaPickupPointsLoading = false;
    }

    /**
     * Вибрати пункт видачі Rozetka за індексом
     */
    public function selectRozetkaPickupPointByIndex($index)
    {
        if (isset($this->rozetkaPickupPoints[$index])) {
            $point = $this->rozetkaPickupPoints[$index];
            $this->rozetkaPickupPointId = $point['id'];
            $this->rozetkaPickupPoint = $point['name'].' - '.$point['address'];
            $this->rozetkaPickupPoints = [];
            $this->resetErrorBag('rozetkaPickupPoint');
            $this->dispatch('citySelected');
            $this->calculateShipping();
        }
    }

    /**
     * Вибрати пункт видачі Rozetka напряму
     */
    public function selectRozetkaPickupPoint($id, $display)
    {
        $this->rozetkaPickupPointId = $id;
        $this->rozetkaPickupPoint = $display;
        $this->rozetkaPickupPoints = [];
        $this->resetErrorBag('rozetkaPickupPoint');
        $this->calculateShipping();
    }

    /**
     * Пошук міст Rozetka для кур'єра
     */
    public function searchRozetkaCourierCities()
    {
        if (strlen($this->rozetkaCourierCity) < 2) {
            $this->rozetkaCourierCities = [];

            return;
        }

        try {
            $provider = new \App\Services\Shipping\RozetkaDeliveryProvider;
            $cities = $provider->getCities($this->rozetkaCourierCity);

            $this->rozetkaCourierCities = $cities->map(function ($city) {
                return [
                    'id' => $city['id'],
                    'name' => $city['name'],
                ];
            })->take(20)->toArray();
        } catch (\Exception $e) {
            Log::error('Error searching Rozetka courier cities: '.$e->getMessage());
            $this->rozetkaCourierCities = [];
        }
    }

    /**
     * Вибрати місто для кур'єра Rozetka за індексом
     */
    public function selectRozetkaCourierCityByIndex($index)
    {
        if (isset($this->rozetkaCourierCities[$index])) {
            $city = $this->rozetkaCourierCities[$index];
            $this->rozetkaCourierCityId = $city['id'];
            $this->rozetkaCourierCity = $city['name'];
            $this->rozetkaCourierCities = [];
            $this->dispatch('citySelected');

            // Автоматично розрахувати доставку якщо адреса заповнена
            if ($this->rozetkaCourierStreet && $this->rozetkaCourierBuilding) {
                $this->calculateShipping();
            }
        }
    }

    /**
     * Автоматичний розрахунок при введенні вулиці для Rozetka
     */
    public function updatedRozetkaCourierStreet()
    {
        if ($this->rozetkaCourierCityId && $this->rozetkaCourierStreet && $this->rozetkaCourierBuilding) {
            $this->calculateShipping();
        }
    }

    /**
     * Автоматичний розрахунок при введенні будинку для Rozetka
     */
    public function updatedRozetkaCourierBuilding()
    {
        if ($this->rozetkaCourierCityId && $this->rozetkaCourierStreet && $this->rozetkaCourierBuilding) {
            $this->calculateShipping();
        }
    }

    /**
     * Розрахувати вартість доставки
     */
    public function calculateShipping()
    {
        // Валідація даних доставки
        if (! $this->validateShippingData()) {
            $this->addError('shipping', 'Заповніть всі поля доставки');

            return;
        }

        $this->resetErrorBag('shipping');

        try {
            // Підготувати дані для розрахунку
            $destination = $this->prepareDestinationData();
            $weight = $this->calculateCartWeight();

            // Використати базовий тариф з БД якщо API не працює
            $methodConfig = \App\Models\ShippingMethod::where('method_code', $this->shippingMethod)
                ->whereHas('provider', fn ($q) => $q->where('code', $this->shippingProvider))
                ->first();

            if ($methodConfig) {
                $baseCost = $methodConfig->base_cost ?? 50;
                $perKgCost = $methodConfig->per_kg_cost ?? 5;
                $calculatedCost = $baseCost + ($weight * $perKgCost);

                $this->shippingCost = $this->applyShippingPromos(round($calculatedCost, 2));
                $this->shippingCalculated = true;
                $this->dispatch('shipping-cost-updated', cost: $this->shippingCost);

                // Показати успішне повідомлення
                $providerName = $this->availableProviders[$this->shippingProvider] ?? $this->shippingProvider;
                $this->js("toastr.success('Вартість доставки {$providerName}: {$this->shippingCost} грн')");

                return;
            }

            // Спробувати розрахунок через провайдера
            if ($this->shippingProvider === 'novaposhta') {
                $provider = new \App\Services\Shipping\NovaPoshtaProvider;
                // Nova Poshta API розрахунок (якщо потрібно)
            } elseif ($this->shippingProvider === 'ukrposhta') {
                $provider = new \App\Services\Shipping\UkrPoshtaProvider;
                // Створимо тимчасове замовлення для розрахунку
                $tempOrder = new \App\Models\Order(['total' => \App\Helpers\Cart\Cart::getCartTotal()]);
                $cost = $provider->calculateShippingCost($tempOrder, $destination);

                if ($cost > 0) {
                    $this->shippingCost = $this->applyShippingPromos($cost);
                    $this->shippingCalculated = true;
                    $this->dispatch('shipping-cost-updated', cost: $this->shippingCost);
                    $this->js("toastr.success('Вартість доставки УкрПошта: {$cost} грн')");

                    return;
                }
            } elseif ($this->shippingProvider === 'rozetka') {
                $provider = new \App\Services\Shipping\RozetkaDeliveryProvider;
                // Створимо тимчасове замовлення для розрахунку
                $tempOrder = new \App\Models\Order(['total' => \App\Helpers\Cart\Cart::getCartTotal()]);
                $cost = $provider->calculateShippingCost($tempOrder, $destination);

                if ($cost > 0) {
                    $this->shippingCost = $this->applyShippingPromos($cost);
                    $this->shippingCalculated = true;
                    $this->dispatch('shipping-cost-updated', cost: $this->shippingCost);
                    $this->js("toastr.success('Вартість доставки Rozetka: {$cost} грн')");

                    return;
                }
            } elseif ($this->shippingProvider === 'meest') {
                $provider = new \App\Services\Shipping\MeestExpressProvider;
                // Створимо тимчасове замовлення для розрахунку
                $tempOrder = new \App\Models\Order(['total' => \App\Helpers\Cart\Cart::getCartTotal()]);
                $cost = $provider->calculateShippingCost($tempOrder, $destination);

                if ($cost > 0) {
                    $this->shippingCost = $this->applyShippingPromos($cost);
                    $this->shippingCalculated = true;
                    $this->dispatch('shipping-cost-updated', cost: $this->shippingCost);
                    $this->js("toastr.success('Вартість доставки Meest Express: {$cost} грн')");

                    return;
                }
            }

            // Fallback до фіксованої вартості
            $cost = 50;
            $this->shippingCost = $this->applyShippingPromos($cost);
            $this->shippingCalculated = true;
            $this->dispatch('shipping-cost-updated', cost: $this->shippingCost);

            // Показати успішне повідомлення
            $this->js("toastr.success('Вартість доставки розрахована: {$cost} грн')");

        } catch (\Exception $e) {
            Log::error('Error calculating shipping: '.$e->getMessage());
            $this->addError('shipping', 'Помилка розрахунку доставки: '.$e->getMessage());
        }
    }

    /**
     * Валідація даних доставки
     */
    protected function validateShippingData(): bool
    {
        if (! $this->shippingProvider || ! $this->shippingMethod) {
            return false;
        }

        if ($this->shippingProvider === 'novaposhta') {
            switch ($this->shippingMethod) {
                case 'warehouse':
                    return $this->npCityRef && $this->npWarehouseRef;
                case 'courier':
                    return $this->npCourierCityRef && $this->npCourierStreet && $this->npCourierBuilding;
                case 'postomat':
                    return $this->npPostomatCityRef && $this->npPostomatRef;
            }
        }

        if ($this->shippingProvider === 'ukrposhta') {
            switch ($this->shippingMethod) {
                case 'branch':
                    return $this->ukrCityId && $this->ukrBranchId;
                case 'courier':
                    return $this->ukrCourierCityId && $this->ukrCourierStreet && $this->ukrCourierBuilding;
            }
        }

        if ($this->shippingProvider === 'rozetka') {
            switch ($this->shippingMethod) {
                case 'pickup_point':
                    return $this->rozetkaCityId && $this->rozetkaPickupPointId;
                case 'courier':
                    return $this->rozetkaCourierCityId && $this->rozetkaCourierStreet && $this->rozetkaCourierBuilding;
            }
        }

        if ($this->shippingProvider === 'meest') {
            switch ($this->shippingMethod) {
                case 'branch':
                    return $this->meestCityId && $this->meestBranchId;
            }
        }

        if ($this->shippingProvider === 'pickup') {
            return true;
        }

        return false;
    }

    /**
     * Підготувати дані призначення
     */
    protected function prepareDestinationData(): array
    {
        $data = [
            'provider' => $this->shippingProvider,
            'method' => $this->shippingMethod,
        ];

        if ($this->shippingProvider === 'novaposhta') {
            switch ($this->shippingMethod) {
                case 'warehouse':
                    $data['city_ref'] = $this->npCityRef;
                    $data['warehouse_ref'] = $this->npWarehouseRef;
                    $data['city_name'] = $this->resolveCityName($this->npCity, $this->npCityRef);
                    $data['warehouse_number'] = $this->npWarehouse;
                    break;
                case 'courier':
                    $data['city_ref'] = $this->npCourierCityRef;
                    $data['city_name'] = $this->resolveCityName($this->npCourierCity, $this->npCourierCityRef);
                    $data['street'] = $this->npCourierStreet;
                    $data['street_ref'] = $this->npCourierStreetRef;
                    $data['building'] = $this->npCourierBuilding;
                    $data['apartment'] = $this->npCourierApartment;
                    break;
                case 'postomat':
                    $data['city_ref'] = $this->npPostomatCityRef;
                    $data['postomat_ref'] = $this->npPostomatRef;
                    $data['city_name'] = $this->resolveCityName($this->npPostomatCity, $this->npPostomatCityRef);
                    $data['postomat_number'] = $this->npPostomat;
                    break;
            }
        }

        if ($this->shippingProvider === 'ukrposhta') {
            switch ($this->shippingMethod) {
                case 'branch':
                    $data['city_id'] = $this->ukrCityId;
                    $data['branch_id'] = $this->ukrBranchId;
                    $data['city_name'] = $this->ukrCity;
                    $data['branch_name'] = $this->ukrBranch;
                    break;
                case 'courier':
                    $data['city_id'] = $this->ukrCourierCityId;
                    $data['city_name'] = $this->ukrCourierCity;
                    $data['street'] = $this->ukrCourierStreet;
                    $data['street_id'] = $this->ukrCourierStreetId;
                    $data['building'] = $this->ukrCourierBuilding;
                    $data['apartment'] = $this->ukrCourierApartment;
                    break;
            }
        }

        if ($this->shippingProvider === 'rozetka') {
            switch ($this->shippingMethod) {
                case 'pickup_point':
                    $data['city_id'] = $this->rozetkaCityId;
                    $data['pickup_point_id'] = $this->rozetkaPickupPointId;
                    $data['city_name'] = $this->rozetkaCity;
                    $data['pickup_point_name'] = $this->rozetkaPickupPoint;
                    break;
                case 'courier':
                    $data['city_id'] = $this->rozetkaCourierCityId;
                    $data['city_name'] = $this->rozetkaCourierCity;
                    $data['street'] = $this->rozetkaCourierStreet;
                    $data['building'] = $this->rozetkaCourierBuilding;
                    $data['apartment'] = $this->rozetkaCourierApartment;
                    break;
            }
        }

        if ($this->shippingProvider === 'meest') {
            switch ($this->shippingMethod) {
                case 'branch':
                    $data['city_id'] = $this->meestCityId;
                    $data['branch_id'] = $this->meestBranchId;
                    $data['city_name'] = $this->meestCity;
                    $data['branch_name'] = $this->meestBranch;
                    break;
            }
        }

        return $data;
    }

    /**
     * Розрахувати вагу кошика
     */
    protected function calculateCartWeight(): float
    {
        $weight = 0;
        $cart = \App\Helpers\Cart\Cart::getCart();

        foreach ($cart as $item) {
            // Припускаємо 0.5 кг за одиницю якщо вага не вказана
            $itemWeight = $item['weight'] ?? 0.5;
            $weight += $itemWeight * $item['quantity'];
        }

        return $weight ?: 1; // Мінімум 1 кг
    }

    /**
     * Розв'язати назву міста з ref якщо назва відсутня
     */
    protected function resolveCityName(string $cityName, string $cityRef): string
    {
        // Якщо назва міста вже є, повертаємо її
        if (! empty($cityName)) {
            return $cityName;
        }

        // Якщо немає ref, повертаємо порожню строку
        if (empty($cityRef)) {
            return '';
        }

        try {
            // Спробуємо отримати назву міста через API Nova Poshty
            $provider = new \App\Services\Shipping\NovaPoshtaProvider;
            $city = $provider->getCityByRef($cityRef);

            if ($city && isset($city['name'])) {
                Log::info('Resolved missing city name', [
                    'city_ref' => $cityRef,
                    'city_name' => $city['name'],
                ]);

                return $city['name'];
            }
        } catch (\Exception $e) {
            Log::error('Failed to resolve city name from ref', [
                'city_ref' => $cityRef,
                'error' => $e->getMessage(),
            ]);
        }

        // Якщо не вдалося розв'язати, повертаємо повідомлення про помилку
        return "Місто (ref: {$cityRef})";
    }

    /**
     * Валідація повноти даних доставки перед збереженням
     */
    protected function validateShippingDataCompleteness(): bool
    {
        if ($this->shippingProvider === 'pickup') {
            return true;
        }

        if ($this->shippingProvider === 'novaposhta') {
            switch ($this->shippingMethod) {
                case 'warehouse':
                    // Перевіряємо що є і ref і назва міста (або можливість її отримати)
                    if ($this->npCityRef && ($this->npCity || $this->canResolveCityName($this->npCityRef))) {
                        return $this->npWarehouseRef !== '';
                    }
                    break;
                case 'courier':
                    if ($this->npCourierCityRef && ($this->npCourierCity || $this->canResolveCityName($this->npCourierCityRef))) {
                        return $this->npCourierStreet && $this->npCourierBuilding;
                    }
                    break;
                case 'postomat':
                    if ($this->npPostomatCityRef && ($this->npPostomatCity || $this->canResolveCityName($this->npPostomatCityRef))) {
                        return $this->npPostomatRef !== '';
                    }
                    break;
            }
        }

        if ($this->shippingProvider === 'ukrposhta') {
            switch ($this->shippingMethod) {
                case 'branch':
                    return $this->ukrCityId && $this->ukrCity && $this->ukrBranchId;
                case 'courier':
                    return $this->ukrCourierCityId && $this->ukrCourierCity && $this->ukrCourierStreet && $this->ukrCourierBuilding;
            }
        }

        if ($this->shippingProvider === 'rozetka') {
            switch ($this->shippingMethod) {
                case 'pickup_point':
                    return $this->rozetkaCityId && $this->rozetkaCity && $this->rozetkaPickupPointId;
                case 'courier':
                    return $this->rozetkaCourierCityId && $this->rozetkaCourierCity && $this->rozetkaCourierStreet && $this->rozetkaCourierBuilding;
            }
        }

        if ($this->shippingProvider === 'meest') {
            switch ($this->shippingMethod) {
                case 'branch':
                    return $this->meestCityId && $this->meestCity && $this->meestBranchId;
            }
        }

        return false;
    }

    /**
     * Перевірити чи можливо отримати назву міста за ref
     */
    protected function canResolveCityName(string $cityRef): bool
    {
        if (empty($cityRef)) {
            return false;
        }

        try {
            $provider = new \App\Services\Shipping\NovaPoshtaProvider;
            $city = $provider->getCityByRef($cityRef);

            return $city && isset($city['name']);
        } catch (\Exception $e) {
            Log::warning('Cannot verify city ref resolution', [
                'city_ref' => $cityRef,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Зберегти замовлення
     */
    public function saveOrder()
    {
        Log::info('SaveOrder called', [
            'shippingProvider' => $this->shippingProvider,
            'shippingMethod' => $this->shippingMethod,
            'shippingCalculated' => $this->shippingCalculated,
            'shippingCost' => $this->shippingCost,
        ]);

        // Перевірка мінімальної суми замовлення
        $cartTotal = \App\Helpers\Cart\Cart::getCartTotal();
        $minAmount = minOrderAmount();

        if ($cartTotal < $minAmount) {
            $this->addError('cart_total', 'Мінімальна сума замовлення: '.formatPrice($minAmount));

            return;
        }

        // Очистка телефону від форматування перед валідацією
        $cleanPhone = preg_replace('/[^0-9+]/', '', $this->phone);
        $this->phone = $cleanPhone;

        // Динамічна валідація телефону (український формат)
        $phoneRule = isPhoneRequired() ? 'required|regex:/^\+38[0-9]{10}$/' : 'nullable|regex:/^\+38[0-9]{10}$/';

        // Валідація основних полів
        $this->validate([
            'first_name' => 'required|min:2',
            'last_name' => 'required|min:2',
            'email' => 'nullable|email',
            'phone' => $phoneRule,
            'shippingProvider' => 'required',
            'shippingMethod' => 'required',
            'paymentMethod' => 'required',
        ], [
            'first_name.required' => 'Вкажіть ваше ім\'я',
            'first_name.min' => 'Ім\'я має бути не менше 2 символів',
            'last_name.required' => 'Вкажіть ваше прізвище',
            'last_name.min' => 'Прізвище має бути не менше 2 символів',
            'email.email' => 'Невірний формат email',
            'phone.required' => 'Вкажіть телефон',
            'phone.regex' => 'Невірний формат телефону. Використовуйте формат +38 (0XX) XXX-XX-XX',
            'shippingProvider.required' => 'Оберіть спосіб доставки',
            'shippingMethod.required' => 'Оберіть метод доставки',
            'paymentMethod.required' => 'Оберіть спосіб оплати',
        ]);

        // Перевірка що доставка розрахована
        if (! $this->shippingCalculated && $this->shippingProvider !== 'pickup') {
            Log::error('Shipping not calculated', [
                'provider' => $this->shippingProvider,
                'method' => $this->shippingMethod,
                'calculated' => $this->shippingCalculated,
            ]);
            $this->addError('shipping', 'Спочатку розрахуйте вартість доставки');
            $this->js("toastr.error('Оберіть місто та відділення для розрахунку доставки')");

            return;
        }

        // Валідація даних доставки перед збереженням
        if (! $this->validateShippingDataCompleteness()) {
            $this->addError('shipping', 'Не всі дані доставки заповнені коректно');
            $this->js("toastr.error('Перевірте дані доставки')");

            return;
        }

        // Rate limiting
        $key = 'checkout.'.(auth()->id() ?: request()->ip());

        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($key);
            $this->addError('name', 'Забагато спроб. Спробуйте через '.$seconds.' секунд.');

            return;
        }

        \Illuminate\Support\Facades\RateLimiter::hit($key, 60);

        // Перевірка безкоштовної доставки
        $cartTotal = \App\Helpers\Cart\Cart::getCartTotal();
        $freeShippingThreshold = freeShippingThreshold();

        if ($cartTotal >= $freeShippingThreshold && $this->shippingProvider !== 'pickup') {
            $this->shippingCost = 0;
        }

        // Застосувати знижку якщо є
        $discountAmount = $this->discountAmount;
        $totalWithDiscount = max(0, $cartTotal - $discountAmount - $this->loyaltyDiscount);
        $totalWithShipping = $totalWithDiscount + $this->shippingCost;

        $shippingData = $this->prepareDestinationData();

        // Append legal-entity fields if company
        if ($this->isCompany) {
            $shippingData['is_company'] = true;
            $shippingData['company_name'] = $this->companyName;
            $shippingData['edrpou'] = $this->edrpou;
            $shippingData['contact_person'] = $this->contactPerson;
        }

        $orderData = [
            'user_id' => auth()->id(),
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'locale' => app()->getLocale(),
            'phone' => $this->phone,
            'note' => $this->note,
            'total' => $totalWithShipping,
            'shipping_cost' => $this->shippingCost,
            'shipping_provider' => $this->shippingProvider,
            'shipping_method' => $this->shippingMethod,
            'shipping_data' => json_encode($shippingData),
            'shipping_city' => $shippingData['city_name'] ?? null,
            'shipping_post_office' => $shippingData['branch_name'] ?? $shippingData['warehouse_number'] ?? $shippingData['postomat_number'] ?? null,
            'payment_method' => $this->paymentMethod,
            'payment_status' => 'pending',
            'coupon_id' => $this->appliedCoupon['coupon_id'] ?? null,
            'coupon_code' => $this->appliedCoupon['code'] ?? null,
            'discount_amount' => $discountAmount,
        ];

        try {
            $order = \Illuminate\Support\Facades\DB::transaction(function () use ($orderData) {
                $order = Order::create($orderData);

                // Додати товари до замовлення
                $cart = \App\Helpers\Cart\Cart::getCart();
                $order_products = [];

                foreach ($cart as $product_id => $product) {
                    $order_products[] = [
                        'product_id' => $product_id,
                        'title' => $product['title'],
                        'price' => $product['price'],
                        'quantity' => $product['quantity'],
                        'slug' => $product['slug'],
                        'image' => $product['image'] ?: 'default-product.jpg',
                    ];
                }

                $order->orderProducts()->createMany($order_products);

                // Створити відправлення
                if ($this->shippingProvider !== 'pickup') {
                    $this->createShipment($order);
                }

                return $order;
            });

            // Відправити email
            try {
                Mail::to($this->email)->send(new OrderClient(
                    $order->orderProducts->toArray(),
                    $cartTotal,
                    $order->id,
                    $this->note
                ));
                Mail::to(shopEmail())->send(new OrderManager($order->id));
            } catch (\Exception $e) {
                Log::error('Email sending error: '.$e->getMessage());
            }

            // Loyalty: redeem and award points
            if (auth()->check()) {
                $loyaltyService = app(LoyaltyService::class);
                if ($this->redeemPoints > 0) {
                    $loyaltyService->redeemPoints(auth()->user(), $this->redeemPoints);
                }
                $loyaltyService->awardPoints(auth()->user(), $order);
                auth()->user()->increment('total_spent', $order->total);
            }

            // Очистити кошик
            \App\Helpers\Cart\Cart::clearCart();
            $this->dispatch('cart-updated');

            // Очистити rate limiter
            \Illuminate\Support\Facades\RateLimiter::clear($key);

            // Показати сповіщення про успішне оформлення замовлення
            $this->dispatch('show-notification',
                type: 'success',
                title: 'ЗАМОВЛЕННЯ ОФОРМЛЕНО',
                message: "Ваше замовлення #{$order->id} успішно створено і буде відправлено протягом 24 годин.",
                action: 'ПЕРЕГЛЯНУТИ ЗАМОВЛЕННЯ',
                actionUrl: locale_route('orders-show', ['id' => $order->id])
            );

            // Якщо вибрано онлайн оплату - перенаправляємо на оплату
            if (in_array($this->paymentMethod, ['liqpay', 'wayforpay', 'privat24', 'monobank'])) {
                return redirect()->to(locale_route('orders.payment', ['order' => $order->id]));
            }

            // Для інших методів (готівка, банківський переказ) - на success сторінку
            return redirect()->to(locale_route('orders.success', ['order' => $order->id]));

        } catch (\Exception $e) {
            Log::error('Order creation error: '.$e->getMessage());

            // Показати сповіщення про помилку
            $this->dispatch('show-notification',
                type: 'error',
                title: 'ПОМИЛКА ОФОРМЛЕННЯ',
                message: 'Виникла помилка при створенні замовлення. Спробуйте ще раз або зверніться до підтримки.',
                action: 'СПРОБУВАТИ ЗНОВУ'
            );
        }
    }

    /**
     * Створити відправлення
     */
    protected function createShipment(Order $order)
    {
        // Skip auto-creation if sender is not fully configured
        // (TTN can still be created manually in admin panel)
        if ($this->shippingProvider === 'novaposhta') {
            $provider = \App\Models\ShippingProvider::where('code', 'novaposhta')->first();
            $cfg = $provider->configuration ?? [];
            $required = ['sender_ref', 'sender_contact_ref', 'sender_warehouse_ref', 'sender_phone'];
            foreach ($required as $key) {
                if (empty($cfg[$key])) {
                    Log::info("Skipping TTN auto-create for order {$order->id}: shipping_providers.novaposhta.configuration.{$key} not set. Configure in /admin/shipping-providers to enable automatic TTN creation.");
                    return;
                }
            }
        }

        try {
            $orchestrator = app(ShippingOrchestrator::class);

            $shippingData = array_merge($this->prepareDestinationData(), [
                'recipient_name' => $this->first_name.' '.$this->last_name,
                'recipient_phone' => $this->phone,
                'cost' => $this->shippingCost,
                'provider_code' => $this->shippingProvider,
                'method_code' => $this->shippingMethod,
            ]);

            $orchestrator->createShipment($order, $shippingData);

        } catch (\Exception $e) {
            Log::warning('Failed to create shipment for order '.$order->id.': '.$e->getMessage());
        }
    }

    /**
     * Отримати загальну суму з доставкою
     */
    public function getTotalWithShipping(): float
    {
        $cartTotal = \App\Helpers\Cart\Cart::getCartTotal();
        $totalWithDiscount = max(0, $cartTotal - $this->discountAmount - $this->loyaltyDiscount);

        return $totalWithDiscount + $this->shippingCost;
    }

    /**
     * Оновити кількість товару в кошику
     */
    public function updateItemQuantity($productId, $newQuantity)
    {
        if ($newQuantity < 1) {
            $this->removeFromCart($productId);

            return;
        }

        \App\Helpers\Cart\Cart::updateItemQuantity($productId, $newQuantity);
        $this->dispatch('cart-updated');
    }

    /**
     * Пошук міст Meest Express
     */
    public function searchMeestCities()
    {
        if (strlen($this->meestCity) < 2) {
            $this->meestCities = [];

            return;
        }

        $this->meestCitiesLoading = true;

        try {
            $provider = new \App\Services\Shipping\MeestExpressProvider;
            $cities = $provider->getCities($this->meestCity);

            $this->meestCities = $cities->map(function ($city) {
                return [
                    'id' => $city['id'],
                    'name' => $city['name'],
                ];
            })->take(20)->toArray();
        } catch (\Exception $e) {
            Log::error('Error searching Meest cities: '.$e->getMessage());
            $this->meestCities = [];
        }

        $this->meestCitiesLoading = false;
    }

    /**
     * Вибрати місто Meest Express за індексом
     */
    public function selectMeestCityByIndex($index)
    {
        if (isset($this->meestCities[$index])) {
            $city = $this->meestCities[$index];
            $this->meestCityId = $city['id'];
            $this->meestCity = $city['name'];
            $this->meestCities = [];
            $this->resetErrorBag('meestCity');
            $this->dispatch('citySelected');
            $this->loadMeestBranches();
        }
    }

    /**
     * Завантажити відділення Meest Express
     */
    public function loadMeestBranches()
    {
        if (! $this->meestCityId) {
            $this->meestBranches = [];

            return;
        }

        $this->meestBranchesLoading = true;
        $this->meestBranch = '';
        $this->meestBranchId = '';

        try {
            $provider = new \App\Services\Shipping\MeestExpressProvider;
            $branches = $provider->getBranches($this->meestCityId);

            $this->meestBranches = $branches->map(function ($branch) {
                return [
                    'id' => $branch['id'],
                    'name' => $branch['name'],
                    'address' => $branch['address'],
                ];
            })->toArray();
        } catch (\Exception $e) {
            Log::error('Error loading Meest branches: '.$e->getMessage());
            $this->meestBranches = [];
        }

        $this->meestBranchesLoading = false;
    }

    /**
     * Вибрати відділення Meest Express за індексом
     */
    public function selectMeestBranchByIndex($index)
    {
        if (isset($this->meestBranches[$index])) {
            $branch = $this->meestBranches[$index];
            $this->meestBranchId = $branch['id'];
            $this->meestBranch = $branch['name'].' - '.$branch['address'];
            $this->meestBranches = [];
            $this->resetErrorBag('meestBranch');
            $this->dispatch('citySelected');
            $this->calculateShipping();
        }
    }

    public function render()
    {
        return view('livewire.cart.checkout-component', [
            'title' => 'Оформлення замовлення',
            'cartTotal' => \App\Helpers\Cart\Cart::getCartTotal(),
            'totalWithShipping' => $this->getTotalWithShipping(),
        ])->layout('components.layouts.app');
    }
}
