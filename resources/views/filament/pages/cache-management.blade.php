<x-filament-panels::page>
    @php $stats = $this->getCacheStats(); @endphp

    <div class="space-y-6">
        {{-- Live system status --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            @php
                // color → Filament badge palette (green→success, red→danger, gray→gray)
                $cards = [
                    ['label' => 'Cache driver',       'value' => $stats['cache_driver']],
                    ['label' => 'Response store',     'value' => $stats['response_cache_driver']],
                    ['label' => 'Octane (Swoole)',    'value' => $stats['octane_active'] ? 'Активний' : 'Не доступний', 'color' => $stats['octane_active'] ? 'success' : 'danger'],
                    ['label' => 'OPcache',            'value' => $stats['opcache_enabled'] ? 'УВІМК' : 'ВИМК',        'color' => $stats['opcache_enabled'] ? 'success' : 'danger'],
                    ['label' => 'Config cached',      'value' => $stats['config_cached'] ? 'YES' : 'NO',              'color' => $stats['config_cached'] ? 'success' : 'gray'],
                    ['label' => 'Routes cached',      'value' => $stats['routes_cached'] ? 'YES' : 'NO',              'color' => $stats['routes_cached'] ? 'success' : 'gray'],
                ];
            @endphp
            @foreach ($cards as $c)
                <div class="bg-white dark:bg-gray-800 rounded-lg p-3 shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="text-[10px] uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ $c['label'] }}</div>
                    <div class="mt-1">
                        @if (isset($c['color']))
                            <x-filament::badge :color="$c['color']">{{ $c['value'] }}</x-filament::badge>
                        @else
                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $c['value'] }}</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Redis stats --}}
        <x-filament::section>
            <x-slot name="heading">Redis (cache storage)</x-slot>
            <x-slot name="description">Усі response cache і application cache зберігаються тут.</x-slot>

            @if (isset($stats['redis']['error']))
                <div class="text-red-600 text-sm">Помилка з'єднання: {{ $stats['redis']['error'] }}</div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                    <div>
                        <div class="text-gray-500">Використано пам'яті</div>
                        <div class="font-semibold text-base">{{ $stats['redis']['used_memory_human'] ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="text-gray-500">Всього keys</div>
                        <div class="font-semibold text-base">{{ number_format($stats['redis']['total_keys'] ?? 0, 0, '.', ' ') }}</div>
                    </div>
                    <div>
                        <div class="text-gray-500">App cache (на диску)</div>
                        <div class="font-semibold text-base">{{ $stats['cache_files'] }} файлів / {{ $stats['cache_size'] }}</div>
                    </div>
                    <div>
                        <div class="text-gray-500">Blade compiled</div>
                        <div class="font-semibold text-base">{{ $stats['view_cache_files'] }} файлів / {{ $stats['view_cache_size'] }}</div>
                    </div>
                </div>
            @endif
        </x-filament::section>

        {{-- Cache log --}}
        <x-filament::section>
            <x-slot name="heading">Журнал останніх дій (30)</x-slot>

            <div class="bg-gray-50 dark:bg-gray-900 p-3 rounded-lg font-mono text-xs max-h-72 overflow-y-auto border border-gray-100 dark:border-gray-700">
                @if (session('cache_logs'))
                    @foreach (session('cache_logs', []) as $log)
                        <div class="mb-1.5 flex items-start gap-2">
                            <span class="text-gray-400 shrink-0">[{{ $log['time'] }}]</span>
                            <span class="text-blue-600 dark:text-blue-400 font-semibold shrink-0">{{ $log['action'] }}</span>
                            <span class="text-gray-700 dark:text-gray-300">— {{ $log['details'] }}</span>
                        </div>
                    @endforeach
                @else
                    <div class="text-gray-400 italic">Поки що жодних дій. Використайте кнопки вгорі сторінки.</div>
                @endif
            </div>
        </x-filament::section>

        {{-- Help --}}
        <x-filament::section collapsible collapsed>
            <x-slot name="heading">Як працює cache stack</x-slot>

            <div class="text-sm text-gray-700 dark:text-gray-300 space-y-3">
                <p><strong>1. Laravel Octane (Swoole)</strong> — application boot tримається в RAM між запитами. Cold-start ~150ms → warm ~5ms.</p>
                <p><strong>2. Spatie ResponseCache</strong> — full HTML response storing in Redis tag <code class="text-xs bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded">gazu-response</code>. TTL 7 днів. Cache HIT ~2-8ms.</p>
                <p><strong>3. Cache::tags(...)</strong> — domain-scoped cache (products, categories, brands, blog, cars, settings, warehouses). Granular flush через «По домену» кнопку.</p>
                <p><strong>4. Eloquent observers</strong> — авто-flush при save/update/delete на: Product, Category, Brand, InfoPage, Page, DisplaySetting, MerchantWarehouse, Inventory.</p>
                <p><strong>5. Octane reload</strong> — graceful restart workers без downtime (для застосування змін у коді без full deploy).</p>
            </div>
        </x-filament::section>
    </div>

    @script
        <script>
            setInterval(() => { $wire.$refresh(); }, 30000);
        </script>
    @endscript
</x-filament-panels::page>
