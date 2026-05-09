<?php

namespace App\Livewire\Shipping;

use App\Models\Order;
use App\Services\Shipping\NovaPoshtaProvider;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;

class NovaPoshtaSelector extends Component
{
    // Delivery type: warehouse, courier, postomat
    public string $deliveryType = 'warehouse';

    // City search
    public string $citySearch = '';
    public string $cityRef = '';
    public string $cityName = '';
    public array $citySuggestions = [];
    public bool $cityLoading = false;

    // Warehouse / Postomat
    public string $warehouseRef = '';
    public string $warehouseName = '';
    public string $warehouseSearch = '';
    public array $warehouseSuggestions = [];
    public array $allWarehouses = [];
    public bool $warehouseLoading = false;

    // Courier address
    public string $streetSearch = '';
    public string $streetRef = '';
    public array $streetSuggestions = [];
    public bool $streetLoading = false;
    public string $building = '';
    public string $apartment = '';
    public ?int $floor = null;
    public bool $hasElevator = false;
    public ?string $preferredDate = null;
    public ?string $preferredTime = null;

    // Cost
    public ?float $shippingCost = null;
    public ?string $estimatedDelivery = null;

    /**
     * City search - debounced from wire:model.live.debounce.300ms
     */
    public function updatedCitySearch(): void
    {
        // Reset city selection when user types
        if ($this->cityRef) {
            $this->cityRef = '';
            $this->cityName = '';
            $this->resetWarehouseData();
            $this->resetCostData();
        }

        if (mb_strlen($this->citySearch) < 2) {
            $this->citySuggestions = [];
            return;
        }

        $this->cityLoading = true;

        try {
            $provider = new NovaPoshtaProvider;
            $cities = $provider->getCities($this->citySearch);

            $this->citySuggestions = $cities->map(function ($city) {
                return [
                    'Ref' => $city['ref'],
                    'Description' => $city['name'],
                    'AreaDescription' => $city['type'] ?? '',
                ];
            })->take(15)->toArray();
        } catch (\Exception $e) {
            Log::error('NP Selector - city search error: ' . $e->getMessage());
            $this->citySuggestions = [];
            $this->addError('citySearch', __('general.np_city_search_error'));
        } finally {
            $this->cityLoading = false;
        }
    }

    /**
     * Select a city by index (safe from XSS in blade interpolation)
     */
    public function selectCityByIndex(int $index): void
    {
        if (!isset($this->citySuggestions[$index])) {
            return;
        }

        $city = $this->citySuggestions[$index];
        $this->selectCity($city['Ref'], $city['Description']);
    }

    /**
     * Select a city from suggestions
     */
    public function selectCity(string $ref, string $name): void
    {
        $this->cityRef = $ref;
        $this->cityName = $name;
        $this->citySearch = $name;
        $this->citySuggestions = [];
        $this->resetErrorBag('citySearch');

        // Reset warehouse data and load new ones
        $this->resetWarehouseData();
        $this->resetCostData();

        if (in_array($this->deliveryType, ['warehouse', 'postomat'])) {
            $this->loadWarehouses();
        }
    }

    /**
     * Warehouse search - filter loaded warehouses
     */
    public function updatedWarehouseSearch(): void
    {
        if ($this->warehouseRef) {
            $this->warehouseRef = '';
            $this->warehouseName = '';
            $this->resetCostData();
        }

        $this->filterWarehouses();
    }

    /**
     * Select a warehouse by index (safe from XSS in blade interpolation)
     */
    public function selectWarehouseByIndex(int $index): void
    {
        if (!isset($this->warehouseSuggestions[$index])) {
            return;
        }

        $warehouse = $this->warehouseSuggestions[$index];
        $this->selectWarehouse($warehouse['ref'], $warehouse['display']);
    }

    /**
     * Select a warehouse by ref (used from map markers).
     */
    public function selectWarehouseByRef(string $ref): void
    {
        foreach ($this->allWarehouses as $w) {
            if ($w['ref'] === $ref) {
                $this->selectWarehouse($ref, $w['display']);
                return;
            }
        }
    }

    /**
     * Select a warehouse from suggestions
     */
    public function selectWarehouse(string $ref, string $name): void
    {
        $this->warehouseRef = $ref;
        $this->warehouseName = $name;
        $this->warehouseSearch = $name;
        $this->warehouseSuggestions = [];
        $this->resetErrorBag('warehouseSearch');

        $this->calculateCost();
    }

    /**
     * When delivery type changes
     */
    public function updatedDeliveryType(): void
    {
        $this->resetWarehouseData();
        $this->resetCostData();
        $this->streetSearch = '';
        $this->streetRef = '';
        $this->building = '';
        $this->apartment = '';

        // If city is already selected, load warehouses for the new type
        if ($this->cityRef && in_array($this->deliveryType, ['warehouse', 'postomat'])) {
            $this->loadWarehouses();
        }

        $this->dispatchDeliveryData();
    }

    /**
     * When courier street changes, recalculate if complete
     */
    public function updatedStreetSearch(): void
    {
        $this->resetCostData();
        $this->streetRef = '';

        if (! $this->cityRef || mb_strlen($this->streetSearch) < 2) {
            $this->streetSuggestions = [];
            $this->tryCalculateCourierCost();

            return;
        }

        $this->streetLoading = true;

        try {
            $response = app(\App\Services\NovaPoshtaApiService::class)
                ->getStreets($this->cityRef, $this->streetSearch);

            $items = $response['data'] ?? [];
            $this->streetSuggestions = collect($items)
                ->take(15)
                ->map(fn ($s) => [
                    'ref' => $s['Ref'] ?? '',
                    'name' => trim(($s['StreetsType'] ?? '').' '.($s['Description'] ?? '')),
                ])
                ->values()
                ->toArray();
        } catch (\Throwable $e) {
            Log::error('NP Selector - street search error: '.$e->getMessage());
            $this->streetSuggestions = [];
        } finally {
            $this->streetLoading = false;
        }

        $this->tryCalculateCourierCost();
    }

    public function selectStreetByIndex(int $index): void
    {
        if (! isset($this->streetSuggestions[$index])) {
            return;
        }
        $s = $this->streetSuggestions[$index];
        $this->streetSearch = $s['name'];
        $this->streetRef = $s['ref'];
        $this->streetSuggestions = [];
        $this->tryCalculateCourierCost();
        $this->dispatchDeliveryData();
    }

    public function updatedBuilding(): void
    {
        $this->resetCostData();
        $this->tryCalculateCourierCost();
        $this->dispatchDeliveryData();
    }

    public function updatedApartment(): void
    {
        $this->tryCalculateCourierCost();
        $this->dispatchDeliveryData();
    }

    /**
     * Calculate shipping cost via NP API
     */
    public function calculateCost(): void
    {
        if (!$this->cityRef) {
            return;
        }

        try {
            $provider = new NovaPoshtaProvider;
            $cartTotal = \App\Helpers\Cart\Cart::getCartTotal();
            $weight = $this->calculateCartWeight();

            $serviceType = match ($this->deliveryType) {
                'warehouse', 'postomat' => 'WarehouseWarehouse',
                'courier' => 'WarehouseDoors',
                default => 'WarehouseWarehouse',
            };

            $tempOrder = new Order(['total' => $cartTotal]);
            $destination = ['city_ref' => $this->cityRef];

            $cost = $provider->calculateShippingCost($tempOrder, $destination);

            if ($cost > 0) {
                $this->shippingCost = $cost;
            } else {
                // Fallback: use base cost from DB method config
                $methodCode = match ($this->deliveryType) {
                    'warehouse' => 'warehouse',
                    'postomat' => 'postomat',
                    'courier' => 'courier',
                };

                $methodConfig = \App\Models\ShippingMethod::where('method_code', $methodCode)
                    ->whereHas('provider', fn ($q) => $q->where('code', 'novaposhta'))
                    ->first();

                if ($methodConfig) {
                    $baseCost = $methodConfig->base_cost ?? 65;
                    $perKgCost = $methodConfig->per_kg_cost ?? 5;
                    $this->shippingCost = round($baseCost + ($weight * $perKgCost), 2);
                } else {
                    // Hard fallback
                    $this->shippingCost = 65.0;
                }
            }

            $this->getEstimatedDelivery();
            $this->dispatchDeliveryData();

        } catch (\Exception $e) {
            Log::error('NP Selector - cost calculation error: ' . $e->getMessage());
            // Fallback to a sensible default
            $this->shippingCost = 65.0;
            $this->getEstimatedDelivery();
            $this->dispatchDeliveryData();
        }
    }

    /**
     * Get estimated delivery date
     */
    public function getEstimatedDelivery(): void
    {
        $days = match ($this->deliveryType) {
            'warehouse' => 1,
            'postomat' => 1,
            'courier' => 2,
            default => 2,
        };

        // Skip weekends for estimation
        $date = now();
        $added = 0;
        while ($added < $days) {
            $date->addDay();
            if (!$date->isWeekend()) {
                $added++;
            }
        }

        $this->estimatedDelivery = $date->translatedFormat('d F (l)');
    }

    /**
     * Get formatted selected address string
     */
    public function getSelectedAddress(): string
    {
        if (!$this->cityName) {
            return '';
        }

        return match ($this->deliveryType) {
            'warehouse', 'postomat' => $this->cityName . ', ' . ($this->warehouseName ?: ''),
            'courier' => implode(', ', array_filter([
                $this->cityName,
                $this->streetSearch,
                $this->building ? __('general.building_label') . ' ' . $this->building : '',
                $this->apartment ? __('general.apartment_label') . ' ' . $this->apartment : '',
            ])),
            default => $this->cityName,
        };
    }

    /**
     * Get delivery data for order creation
     */
    public function getDeliveryData(): array
    {
        $data = [
            'provider' => 'novaposhta',
            'method' => $this->deliveryType,
            'city_ref' => $this->cityRef,
            'city_name' => $this->cityName,
            'shipping_cost' => $this->shippingCost ?? 0,
            'estimated_delivery' => $this->estimatedDelivery,
            'address' => $this->getSelectedAddress(),
        ];

        if (in_array($this->deliveryType, ['warehouse', 'postomat'])) {
            $data['warehouse_ref'] = $this->warehouseRef;
            $data['warehouse_name'] = $this->warehouseName;
        }

        if ($this->deliveryType === 'courier') {
            $data['street'] = $this->streetSearch;
            $data['street_ref'] = $this->streetRef;
            $data['building'] = $this->building;
            $data['apartment'] = $this->apartment;
            $data['floor'] = $this->floor;
            $data['has_elevator'] = $this->hasElevator;
        }

        if ($this->preferredDate) {
            $data['preferred_date'] = $this->preferredDate;
        }
        if ($this->preferredTime) {
            $data['preferred_time'] = $this->preferredTime;
        }

        return $data;
    }

    /**
     * Load warehouses for the selected city and delivery type
     */
    protected function loadWarehouses(): void
    {
        if (!$this->cityRef) {
            return;
        }

        $this->warehouseLoading = true;

        try {
            $provider = new NovaPoshtaProvider;
            $warehouses = $provider->getWarehouses($this->cityRef);

            // Filter by type
            if ($this->deliveryType === 'postomat') {
                $warehouses = $warehouses->filter(function ($warehouse) {
                    if (isset($warehouse['CategoryOfWarehouse']) && $warehouse['CategoryOfWarehouse'] === 'Postomat') {
                        return true;
                    }
                    $description = mb_strtolower($warehouse['description'] ?? '');
                    $address = mb_strtolower($warehouse['address'] ?? '');
                    return str_contains($description, 'поштомат')
                        || str_contains($address, 'поштомат')
                        || str_contains($description, 'poshtomat')
                        || str_contains($address, 'poshtomat');
                });
            } else {
                // warehouse type - exclude postomats
                $warehouses = $warehouses->filter(function ($warehouse) {
                    if (isset($warehouse['CategoryOfWarehouse']) && $warehouse['CategoryOfWarehouse'] === 'Postomat') {
                        return false;
                    }
                    $description = mb_strtolower($warehouse['description'] ?? '');
                    return !str_contains($description, 'поштомат')
                        && !str_contains($description, 'poshtomat');
                });
            }

            $this->allWarehouses = $warehouses->map(function ($warehouse) {
                $number = $warehouse['number'] ?? $warehouse['siteKey'] ?? '';
                $address = $warehouse['address'] ?? $warehouse['description'] ?? '';

                // Try to enrich with local DB data (coordinates, schedule, max weight)
                $local = \App\Models\NpWarehouse::where('ref', $warehouse['ref'])->first();

                return [
                    'ref' => $warehouse['ref'],
                    'number' => $number,
                    'address' => $address,
                    'display' => ($number ? '№' . $number . ' - ' : '') . $address,
                    'lat' => $local?->latitude ? (float) $local->latitude : null,
                    'lng' => $local?->longitude ? (float) $local->longitude : null,
                    'phone' => $local?->phone,
                    'max_weight' => $local?->total_max_weight,
                    'pos_terminal' => (bool) $local?->pos_terminal,
                ];
            })->sortBy('number')->values()->toArray();

            // Show first 20 by default
            $this->warehouseSuggestions = array_slice($this->allWarehouses, 0, 20);

            if (empty($this->allWarehouses)) {
                $this->addError('warehouseSearch', __('general.np_no_warehouses'));
            }
        } catch (\Exception $e) {
            Log::error('NP Selector - load warehouses error: ' . $e->getMessage());
            $this->allWarehouses = [];
            $this->warehouseSuggestions = [];
            $this->addError('warehouseSearch', __('general.np_load_error'));
        } finally {
            $this->warehouseLoading = false;
        }
    }

    /**
     * Filter warehouses based on search text
     */
    protected function filterWarehouses(): void
    {
        if (empty($this->allWarehouses)) {
            $this->warehouseSuggestions = [];
            return;
        }

        if (mb_strlen($this->warehouseSearch) < 2) {
            $this->warehouseSuggestions = array_slice($this->allWarehouses, 0, 20);
            return;
        }

        $search = mb_strtolower($this->warehouseSearch);

        $this->warehouseSuggestions = collect($this->allWarehouses)->filter(function ($warehouse) use ($search) {
            return str_contains(mb_strtolower($warehouse['number']), $search)
                || str_contains(mb_strtolower($warehouse['address']), $search);
        })->take(30)->values()->toArray();
    }

    /**
     * Try to calculate courier cost if address is complete
     */
    protected function tryCalculateCourierCost(): void
    {
        if ($this->cityRef && $this->streetSearch && $this->building) {
            $this->calculateCost();
        }
    }

    /**
     * Dispatch delivery data to parent checkout component
     */
    protected function dispatchDeliveryData(): void
    {
        $payload = $this->getDeliveryData();
        $this->dispatch('np-delivery-selected', data: $payload);
        // Mirror selection in session for reload-prefill (one slot per provider).
        session(['np_last_delivery' => $payload]);
    }

    public function mount(): void
    {
        $last = session('np_last_delivery');
        if (! is_array($last)) {
            return;
        }

        $this->deliveryType = $last['method'] ?? $this->deliveryType;
        $this->cityRef = $last['city_ref'] ?? '';
        $this->cityName = $last['city_name'] ?? '';
        $this->citySearch = $this->cityName;

        if (in_array($this->deliveryType, ['warehouse', 'postomat']) && ! empty($last['warehouse_ref'])) {
            $this->warehouseRef = $last['warehouse_ref'];
            $this->warehouseName = $last['warehouse_name'] ?? '';
            $this->warehouseSearch = $this->warehouseName;
            if ($this->cityRef) {
                $this->loadWarehouses();
            }
        }

        if ($this->deliveryType === 'courier') {
            $this->streetSearch = $last['street'] ?? '';
            $this->streetRef = $last['street_ref'] ?? '';
            $this->building = (string) ($last['building'] ?? '');
            $this->apartment = (string) ($last['apartment'] ?? '');
            $this->floor = isset($last['floor']) ? (int) $last['floor'] : null;
            $this->hasElevator = (bool) ($last['has_elevator'] ?? false);
        }
    }

    /**
     * Reset warehouse-related data
     */
    protected function resetWarehouseData(): void
    {
        $this->warehouseRef = '';
        $this->warehouseName = '';
        $this->warehouseSearch = '';
        $this->warehouseSuggestions = [];
        $this->allWarehouses = [];
    }

    /**
     * Reset cost data
     */
    protected function resetCostData(): void
    {
        $this->shippingCost = null;
        $this->estimatedDelivery = null;
    }

    /**
     * Calculate total cart weight
     */
    protected function calculateCartWeight(): float
    {
        $weight = 0;
        $cart = \App\Helpers\Cart\Cart::getCart();

        foreach ($cart as $item) {
            $itemWeight = $item['weight'] ?? 0.5;
            $weight += $itemWeight * $item['quantity'];
        }

        return $weight ?: 1;
    }

    public function render()
    {
        return view('livewire.shipping.nova-poshta-selector');
    }
}
