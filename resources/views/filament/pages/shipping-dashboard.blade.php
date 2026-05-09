<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Quick Actions --}}
        <x-filament::section>
            <x-slot name="heading">
                Швидкі дії
            </x-slot>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <x-filament::button
                    href="{{ \App\Filament\Resources\ShippingProviderResource::getUrl('index') }}"
                    icon="heroicon-o-cog-6-tooth"
                    color="primary"
                    size="lg"
                    class="justify-start"
                >
                    Налаштування провайдерів
                </x-filament::button>
                
                <x-filament::button
                    href="{{ \App\Filament\Resources\ShippingWarehouseResource::getUrl('index') }}"
                    icon="heroicon-o-building-storefront"
                    color="success"
                    size="lg"
                    class="justify-start"
                >
                    Відділення та поштомати
                </x-filament::button>
                
                <x-filament::button
                    href="{{ \App\Filament\Resources\OrderResource::getUrl('index') }}"
                    icon="heroicon-o-shopping-cart"
                    color="warning"
                    size="lg"
                    class="justify-start"
                >
                    Замовлення НП
                </x-filament::button>
                
                <x-filament::button
                    href="{{ \App\Filament\Resources\ShippingMethodResource::getUrl('index') }}"
                    icon="heroicon-o-map"
                    color="info"
                    size="lg"
                    class="justify-start"
                >
                    Методи доставки
                </x-filament::button>
            </div>
        </x-filament::section>

        {{-- Status Overview --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Nova Poshta Status --}}
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center">
                        <x-heroicon-o-truck class="w-5 h-5 mr-2 text-blue-600"/>
                        Нова Пошта
                    </div>
                </x-slot>
                
                <div class="space-y-3">
                    @php
                        $npProvider = \App\Models\ShippingProvider::where('code', 'novaposhta')->first();
                        $apiKey = $npProvider?->configuration['api_key'] ?? null;
                        $isConfigured = !empty($apiKey);
                        
                        $isConnected = false;
                        if ($isConfigured) {
                            try {
                                $provider = new \App\Services\Shipping\NovaPoshtaProvider();
                                $testResult = $provider->testConnection();
                                $isConnected = $testResult;
                            } catch (\Exception $e) {
                                $isConnected = false;
                            }
                        }
                        
                        $warehouseCount = \App\Models\ShippingWarehouse::byProvider('novaposhta')->where('type', 'warehouse')->active()->count();
                        $postomatCount = \App\Models\ShippingWarehouse::byProvider('novaposhta')->where('type', 'postomat')->active()->count();
                    @endphp
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">API налаштування:</span>
                        @if($isConfigured)
                            <x-filament::badge color="success">Налаштовано</x-filament::badge>
                        @else
                            <x-filament::badge color="danger">Не налаштовано</x-filament::badge>
                        @endif
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Зʼєднання з API:</span>
                        @if($isConnected)
                            <x-filament::badge color="success">Підключено</x-filament::badge>
                        @else
                            <x-filament::badge color="danger">Не підключено</x-filament::badge>
                        @endif
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Відділення:</span>
                        <x-filament::badge color="primary">{{ $warehouseCount }}</x-filament::badge>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Поштомати:</span>
                        <x-filament::badge color="info">{{ $postomatCount }}</x-filament::badge>
                    </div>
                </div>
            </x-filament::section>
            
            {{-- System Health --}}
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center">
                        <x-heroicon-o-heart class="w-5 h-5 mr-2 text-green-600"/>
                        Стан системи
                    </div>
                </x-slot>
                
                <div class="space-y-3">
                    @php
                        $recentOrders = \App\Models\Order::where('created_at', '>=', now()->subDays(7))->count();
                        $shippingOrders = \App\Models\Order::whereNotNull('shipping_provider')->where('created_at', '>=', now()->subDays(7))->count();
                    @endphp
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Замовлення (7 днів):</span>
                        <x-filament::badge color="primary">{{ $recentOrders }}</x-filament::badge>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">З доставкою:</span>
                        <x-filament::badge color="success">{{ $shippingOrders }}</x-filament::badge>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Кеш API:</span>
                        <x-filament::badge color="info">Активний</x-filament::badge>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Останнє оновлення:</span>
                        <span class="text-xs text-gray-500">{{ now()->format('d.m.Y H:i') }}</span>
                    </div>
                </div>
            </x-filament::section>
        </div>
        
        {{-- Checkpoints List --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center">
                    <x-heroicon-o-clipboard-document-check class="w-5 h-5 mr-2 text-purple-600"/>
                    Список завдань налаштування
                </div>
            </x-slot>
            
            <div class="space-y-3">
                @php
                    $checkpoints = [
                        [
                            'title' => 'Налаштування API ключа Нової Пошти',
                            'completed' => $isConfigured,
                            'description' => 'API ключ: 737254fe131eca6c3ab91925ef9eff45',
                            'link' => $npProvider ? \App\Filament\Resources\ShippingProviderResource::getUrl('edit', ['record' => $npProvider->id]) : \App\Filament\Resources\ShippingProviderResource::getUrl('index')
                        ],
                        [
                            'title' => 'Тестування зʼєднання з API',
                            'completed' => $isConnected,
                            'description' => 'Перевірка працездатності API',
                            'link' => null
                        ],
                        [
                            'title' => 'Завантаження відділень та поштоматів',
                            'completed' => $warehouseCount > 0 || $postomatCount > 0,
                            'description' => "Завантажено {$warehouseCount} відділень та {$postomatCount} поштоматів",
                            'link' => \App\Filament\Resources\ShippingWarehouseResource::getUrl('index')
                        ],
                        [
                            'title' => 'Тестування процесу замовлення',
                            'completed' => $shippingOrders > 0,
                            'description' => "Оброблено {$shippingOrders} замовлень з доставкою",
                            'link' => \App\Filament\Resources\OrderResource::getUrl('index')
                        ],
                        [
                            'title' => 'Методи доставки налаштовано',
                            'completed' => \App\Models\ShippingMethod::count() > 0,
                            'description' => 'Активні методи (Нова Пошта, Укрпошта, Самовивіз тощо)',
                            'link' => \App\Filament\Resources\ShippingMethodResource::getUrl('index')
                        ]
                    ];
                @endphp
                
                @foreach($checkpoints as $checkpoint)
                    <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg border">
                        @if($checkpoint['completed'])
                            <x-heroicon-s-check-circle class="w-6 h-6 text-green-600 flex-shrink-0"/>
                        @else
                            <x-heroicon-s-x-circle class="w-6 h-6 text-red-600 flex-shrink-0"/>
                        @endif
                        
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">
                                {{ $checkpoint['title'] }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ $checkpoint['description'] }}
                            </p>
                        </div>
                        
                        @if($checkpoint['link'])
                            <x-filament::button
                                href="{{ $checkpoint['link'] }}"
                                size="sm"
                                color="gray"
                                outlined
                            >
                                Перейти
                            </x-filament::button>
                        @endif
                    </div>
                @endforeach
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>