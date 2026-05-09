<?php

namespace App\Livewire;

use App\Models\Order;
use App\Services\Shipping\ShippingOrchestrator;
use Livewire\Component;

/**
 * Компонент для розрахунку доставки
 */
class ShippingCalculator extends Component
{
    // Дані адреси
    public string $city = '';

    public string $cityRef = '';

    public string $warehouse = '';

    public string $warehouseRef = '';

    public string $selectedProvider = 'novaposhta';

    // Дані замовлення
    public ?int $orderId = null;

    public float $totalWeight = 0;

    // Результати
    public array $cities = [];

    public array $warehouses = [];

    public array $shippingOptions = [];

    public ?array $selectedOption = null;

    // Стани
    public bool $isLoadingCities = false;

    public bool $isLoadingWarehouses = false;

    public bool $isCalculating = false;

    protected $rules = [
        'cityRef' => 'required',
        'warehouseRef' => 'required_if:selectedProvider,novaposhta',
    ];

    public function mount(?Order $order = null)
    {
        $this->orderId = $order?->id;
        $this->cities = [];
        $this->warehouses = [];
        $this->shippingOptions = [];

        if ($order) {
            $this->totalWeight = $this->calculateOrderWeight($order);
        }
    }

    /**
     * Пошук міст при зміні поля
     */
    public function updatedCity()
    {
        if (strlen($this->city) < 2) {
            $this->cities = [];

            return;
        }

        $this->searchCities();
    }

    /**
     * Обробка вибору міста
     */
    public function selectCity(string $cityRef, string $cityName)
    {
        $this->cityRef = $cityRef;
        $this->city = $cityName;
        $this->cities = [];

        // Очистити попередній вибір складу
        $this->warehouse = '';
        $this->warehouseRef = '';
        $this->warehouses = [];

        $this->loadWarehouses();
    }

    /**
     * Обробка вибору складу
     */
    public function selectWarehouse(string $warehouseRef, string $warehouseName)
    {
        $this->warehouseRef = $warehouseRef;
        $this->warehouse = $warehouseName;

        $this->calculateShipping();
    }

    /**
     * Зміна провайдера доставки
     */
    public function updatedSelectedProvider()
    {
        $this->resetData();
        $this->shippingOptions = [];
    }

    /**
     * Розрахувати доставку
     */
    public function calculateShipping()
    {
        if (! $this->cityRef) {
            return;
        }

        $this->isCalculating = true;

        try {
            $orchestrator = app(ShippingOrchestrator::class);

            $destination = [
                'city_ref' => $this->cityRef,
                'warehouse_ref' => $this->warehouseRef,
                'provider' => $this->selectedProvider,
            ];

            // Створюємо тестове замовлення якщо його немає
            $order = null;
            if ($this->orderId) {
                $order = Order::find($this->orderId);
            }

            if (! $order) {
                $order = new Order([
                    'total' => 1000, // Тестова сума
                ]);
            }

            $shippingOptions = $orchestrator->getAllShippingOptions($order, $destination);
            $this->shippingOptions = $shippingOptions->toArray();

            // Автоматично вибрати найкращий варіант
            if (! empty($this->shippingOptions)) {
                $this->selectedOption = $this->shippingOptions[0];
            }

        } catch (\Exception $e) {
            session()->flash('error', 'Помилка розрахунку доставки: '.$e->getMessage());
        } finally {
            $this->isCalculating = false;
        }
    }

    /**
     * Вибрати варіант доставки
     */
    public function selectShippingOption(int $index)
    {
        if (isset($this->shippingOptions[$index])) {
            $this->selectedOption = $this->shippingOptions[$index];

            // Відправити подію про вибір доставки
            $this->dispatch('shipping-selected', [
                'option' => $this->selectedOption,
                'cost' => $this->selectedOption['cost'],
            ]);
        }
    }

    /**
     * Пошук міст
     */
    protected function searchCities()
    {
        $this->isLoadingCities = true;

        try {
            $orchestrator = app(ShippingOrchestrator::class);
            $cities = $orchestrator->getCities($this->selectedProvider, $this->city);
            $this->cities = $cities->toArray();
        } catch (\Exception $e) {
            session()->flash('error', 'Помилка пошуку міст: '.$e->getMessage());
            $this->cities = [];
        } finally {
            $this->isLoadingCities = false;
        }
    }

    /**
     * Завантажити склади
     */
    protected function loadWarehouses()
    {
        if (! $this->cityRef) {
            return;
        }

        $this->isLoadingWarehouses = true;

        try {
            $orchestrator = app(ShippingOrchestrator::class);
            $warehouses = $orchestrator->getWarehouses($this->selectedProvider, $this->cityRef);
            $this->warehouses = $warehouses->toArray();
        } catch (\Exception $e) {
            session()->flash('error', 'Помилка завантаження складів: '.$e->getMessage());
            $this->warehouses = [];
        } finally {
            $this->isLoadingWarehouses = false;
        }
    }

    /**
     * Скинути дані
     */
    protected function resetData()
    {
        $this->city = '';
        $this->cityRef = '';
        $this->warehouse = '';
        $this->warehouseRef = '';
        $this->cities = [];
        $this->warehouses = [];
    }

    /**
     * Розрахувати вагу замовлення
     */
    protected function calculateOrderWeight(Order $order): float
    {
        $weight = 0.5; // Мінімальна вага

        if ($order->orderProducts) {
            $itemsCount = $order->orderProducts->sum('quantity');
            $weight += $itemsCount * 0.3; // 300г за товар
        }

        return round($weight, 2);
    }

    /**
     * Рендер компонента
     */
    public function render()
    {
        return view('livewire.shipping-calculator');
    }
}
