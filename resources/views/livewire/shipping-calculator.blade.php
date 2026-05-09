<div class="shipping-calculator">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-shipping-fast me-2"></i>
                Розрахунок доставки
            </h5>
        </div>
        <div class="card-body">
            {{-- Вибір провайдера --}}
            <div class="row mb-3">
                <div class="col-12">
                    <label class="form-label">Служба доставки</label>
                    <select wire:model.live="selectedProvider" class="form-select">
                        <option value="novaposhta">🚚 Нова Пошта</option>
                        <option value="ukrposhta" disabled>📮 Укрпошта (незабаром)</option>
                        <option value="rozetka" disabled>🛍️ Rozetka Delivery (незабаром)</option>
                    </select>
                </div>
            </div>

            {{-- Пошук міста --}}
            <div class="row mb-3">
                <div class="col-12">
                    <label class="form-label">
                        Місто доставки
                        @if($isLoadingCities)
                            <div class="spinner-border spinner-border-sm ms-2" role="status">
                                <span class="visually-hidden">Завантаження...</span>
                            </div>
                        @endif
                    </label>
                    <input 
                        type="text" 
                        wire:model.live.debounce.500ms="city" 
                        class="form-control" 
                        placeholder="Почніть вводити назву міста..."
                        autocomplete="off"
                    >
                    
                    {{-- Список міст --}}
                    @if($cities->isNotEmpty())
                        <div class="list-group mt-2 position-absolute w-100" style="z-index: 1000; max-height: 200px; overflow-y: auto;">
                            @foreach($cities as $cityOption)
                                <button 
                                    type="button"
                                    wire:click="selectCity('{{ $cityOption['ref'] }}', '{{ $cityOption['name'] }}')"
                                    class="list-group-item list-group-item-action"
                                >
                                    <strong>{{ $cityOption['name'] }}</strong>
                                    @if(isset($cityOption['type']))
                                        <small class="text-muted">({{ $cityOption['type'] }})</small>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Вибір відділення (тільки для Нової Пошти) --}}
            @if($selectedProvider === 'novaposhta' && $cityRef)
                <div class="row mb-3">
                    <div class="col-12">
                        <label class="form-label">
                            Відділення Нової Пошти
                            @if($isLoadingWarehouses)
                                <div class="spinner-border spinner-border-sm ms-2" role="status">
                                    <span class="visually-hidden">Завантаження...</span>
                                </div>
                            @endif
                        </label>
                        
                        @if($warehouses->isNotEmpty())
                            <select wire:model.live="warehouseRef" class="form-select">
                                <option value="">Оберіть відділення</option>
                                @foreach($warehouses as $warehouseOption)
                                    <option value="{{ $warehouseOption['ref'] }}">
                                        №{{ $warehouseOption['number'] }} - {{ $warehouseOption['address'] }}
                                    </option>
                                @endforeach
                            </select>
                        @else
                            <div class="alert alert-warning">
                                @if($isLoadingWarehouses)
                                    Завантаження відділень...
                                @else
                                    Відділення не знайдено для цього міста
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Результати розрахунку --}}
            @if($shippingOptions->isNotEmpty())
                <div class="row">
                    <div class="col-12">
                        <label class="form-label">Варіанти доставки</label>
                        
                        @foreach($shippingOptions as $index => $option)
                            <div class="card mb-2 {{ $selectedOption && $selectedOption['method_code'] === $option['method_code'] ? 'border-primary' : '' }}">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">
                                                <span class="badge bg-info me-2">{{ $option['provider_name'] }}</span>
                                                {{ $option['method_name'] }}
                                            </h6>
                                            <small class="text-muted">{{ $option['description'] }}</small>
                                            @if($option['estimated_days'])
                                                <div class="text-muted">
                                                    <i class="fas fa-clock me-1"></i>
                                                    {{ $option['estimated_days'] }} {{ $option['estimated_days'] == 1 ? 'день' : 'дні' }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="text-end">
                                            <div class="h5 mb-1 text-primary">{{ formatPrice($option['cost']) }}</div>
                                            <button 
                                                type="button"
                                                wire:click="selectShippingOption({{ $index }})"
                                                class="btn {{ $selectedOption && $selectedOption['method_code'] === $option['method_code'] ? 'btn-primary' : 'btn-outline-primary' }} btn-sm"
                                            >
                                                {{ $selectedOption && $selectedOption['method_code'] === $option['method_code'] ? 'Обрано' : 'Обрати' }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Стан розрахунку --}}
            @if($isCalculating)
                <div class="text-center py-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Розраховуємо вартість доставки...</span>
                    </div>
                    <div class="mt-2">Розраховуємо вартість доставки...</div>
                </div>
            @endif

            {{-- Інформація про вагу --}}
            @if($order && $totalWeight > 0)
                <div class="alert alert-info mt-3">
                    <small>
                        <i class="fas fa-weight me-1"></i>
                        Загальна вага: {{ $totalWeight }} кг
                        @if($order->orderProducts)
                            ({{ $order->orderProducts->sum('quantity') }} товарів)
                        @endif
                    </small>
                </div>
            @endif

            {{-- Обране рішення --}}
            @if($selectedOption)
                <div class="alert alert-success mt-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Обрано:</strong> {{ $selectedOption['method_name'] }}
                            <br>
                            <small>{{ $selectedOption['provider_name'] }} - {{ $selectedOption['description'] }}</small>
                        </div>
                        <div class="text-end">
                            <div class="h5 mb-0 text-success">{{ formatPrice($selectedOption['cost']) }}</div>
                            @if($selectedOption['estimated_days'])
                                <small class="text-muted">{{ $selectedOption['estimated_days'] }} дн.</small>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Модальне вікно помилок --}}
    @if(session()->has('error'))
        <div class="alert alert-danger mt-3">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ session('error') }}
        </div>
    @endif
</div>

@push('scripts')
<script>
    // Слухач події вибору доставки
    document.addEventListener('livewire:init', () => {
        if (typeof Livewire !== 'undefined' && Livewire.on) {
            Livewire.on('shipping-selected', (data) => {
                console.log('Shipping selected:', data);
                
                // Можна додати додаткову логіку обробки вибору доставки
                // Наприклад, оновлення загальної суми замовлення
            });
        }
    });
</script>
@endpush

@push('styles')
<style>
    .shipping-calculator .list-group {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border-radius: 0.375rem;
    }
    
    .shipping-calculator .card {
        transition: all 0.3s ease;
    }
    
    .shipping-calculator .card:hover {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }
    
    .shipping-calculator .border-primary {
        border-width: 2px !important;
    }
</style>
@endpush