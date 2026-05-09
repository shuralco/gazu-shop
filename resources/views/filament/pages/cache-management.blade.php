<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Статистика кешу -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @php $stats = $this->getCacheStats(); @endphp
            
            <x-filament::section>
                <x-slot name="heading">
                    📦 Application Cache
                </x-slot>
                
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span>Файлів кешу:</span>
                        <span class="font-bold">{{ $stats['cache_files'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Розмір:</span>
                        <span class="font-bold">{{ $stats['cache_size'] }}</span>
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">
                    👁️ View Cache
                </x-slot>
                
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span>Compiled views:</span>
                        <span class="font-bold">{{ $stats['view_cache_files'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Розмір:</span>
                        <span class="font-bold">{{ $stats['view_cache_size'] }}</span>
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">
                    ⚙️ Config & Routes
                </x-slot>
                
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span>Config cached:</span>
                        <span class="font-bold {{ $stats['config_cached'] ? 'text-green-600' : 'text-red-600' }}">
                            {{ $stats['config_cached'] ? '✅ Так' : '❌ Ні' }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span>Routes cached:</span>
                        <span class="font-bold {{ $stats['routes_cached'] ? 'text-green-600' : 'text-red-600' }}">
                            {{ $stats['routes_cached'] ? '✅ Так' : '❌ Ні' }}
                        </span>
                    </div>
                </div>
            </x-filament::section>
        </div>

        <!-- Опції керування кешем -->
        <x-filament::section>
            <x-slot name="heading">
                🧹 Додаткові опції очищення
            </x-slot>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold">Селективне очищення</h3>
                    
                    <div class="space-y-2">
                        <button 
                            wire:click="clearSpecificCache('home_page_data')"
                            class="w-full text-left px-4 py-2 bg-blue-50 hover:bg-blue-100 rounded border border-blue-200"
                        >
                            🏠 Головна сторінка
                        </button>
                        
                        <button 
                            wire:click="clearSpecificCache('category_*')"
                            class="w-full text-left px-4 py-2 bg-green-50 hover:bg-green-100 rounded border border-green-200"
                        >
                            📂 Всі категорії
                        </button>
                        
                        <button 
                            wire:click="clearSpecificCache('hit_products_*')"
                            class="w-full text-left px-4 py-2 bg-red-50 hover:bg-red-100 rounded border border-red-200"
                        >
                            ⭐ Хіт товари
                        </button>
                        
                        <button 
                            wire:click="clearSpecificCache('new_products_*')"
                            class="w-full text-left px-4 py-2 bg-yellow-50 hover:bg-yellow-100 rounded border border-yellow-200"
                        >
                            🆕 Нові товари
                        </button>
                    </div>
                </div>

                <div class="space-y-4">
                    <h3 class="text-lg font-semibold">Системні операції</h3>
                    
                    <div class="space-y-2">
                        <button 
                            wire:click="clearOpcache"
                            class="w-full text-left px-4 py-2 bg-purple-50 hover:bg-purple-100 rounded border border-purple-200"
                        >
                            🚀 Очистити OPcache
                        </button>
                        
                        <button 
                            wire:click="clearSessions"
                            class="w-full text-left px-4 py-2 bg-gray-50 hover:bg-gray-100 rounded border border-gray-200"
                        >
                            👥 Очистити сесії
                        </button>
                        
                        <button 
                            wire:click="optimizeForProduction"
                            class="w-full text-left px-4 py-2 bg-indigo-50 hover:bg-indigo-100 rounded border border-indigo-200"
                        >
                            ⚡ Оптимізувати для продакшн
                        </button>
                    </div>
                </div>
            </div>
        </x-filament::section>

        <!-- Логи очищення -->
        <x-filament::section>
            <x-slot name="heading">
                📋 Останні дії з кешем
            </x-slot>
            
            <div class="bg-gray-50 p-4 rounded-lg font-mono text-sm max-h-64 overflow-y-auto">
                @if(session('cache_logs'))
                    @foreach(session('cache_logs', []) as $log)
                        <div class="mb-1">
                            <span class="text-gray-500">{{ $log['time'] }}</span>
                            <span class="text-blue-600">{{ $log['action'] }}</span>
                            <span>{{ $log['details'] }}</span>
                        </div>
                    @endforeach
                @else
                    <div class="text-gray-500">Немає записів про очищення кешу</div>
                @endif
            </div>
        </x-filament::section>

        <!-- Поради з оптимізації -->
        <x-filament::section>
            <x-slot name="heading">
                💡 Поради з оптимізації кешу
            </x-slot>
            
            <div class="space-y-3 text-sm">
                <div class="p-3 bg-green-50 border border-green-200 rounded">
                    <strong>✅ Для розробки:</strong> Очищуйте кеш після змін в моделях, контролерах або views
                </div>
                <div class="p-3 bg-blue-50 border border-blue-200 rounded">
                    <strong>🚀 Для продакшн:</strong> Використовуйте "Оптимізувати для продакшн" для максимальної швидкості
                </div>
                <div class="p-3 bg-yellow-50 border border-yellow-200 rounded">
                    <strong>⚡ Прогрів кешу:</strong> Після очищення обов'язково прогрійте критичні дані
                </div>
                <div class="p-3 bg-red-50 border border-red-200 rounded">
                    <strong>🔥 Повне очищення:</strong> Використовуйте тільки при серйозних проблемах
                </div>
            </div>
        </x-filament::section>
    </div>

    @script
    <script>
        // Auto-refresh статистики кожні 30 секунд
        setInterval(() => {
            $wire.$refresh();
        }, 30000);
    </script>
    @endscript
</x-filament-panels::page>
