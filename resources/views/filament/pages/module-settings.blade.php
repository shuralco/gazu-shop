<x-filament-panels::page>
    <div class="space-y-6">
        <div class="rounded-lg bg-blue-50 dark:bg-blue-900/20 p-4 border border-blue-200 dark:border-blue-800">
            <div class="flex gap-3">
                <x-filament::icon icon="heroicon-o-information-circle" class="h-5 w-5 text-blue-600 shrink-0 mt-0.5" />
                <div class="text-sm text-blue-900 dark:text-blue-100">
                    <strong>Модулі</strong> — це опціональні фічі магазину.
                    Вимкнення модуля приховує його у sidebar, на frontend, відключає cron-jobs та routes.
                    <strong>Дані залишаються</strong> у БД — re-enable повертає функціонал миттєво.
                    <br><br>
                    Зміни записуються у <code class="px-1 bg-blue-100 dark:bg-blue-800 rounded">.env</code> як <code class="px-1 bg-blue-100 dark:bg-blue-800 rounded">MODULE_*</code>. Опис архітектури: <code class="px-1 bg-blue-100 dark:bg-blue-800 rounded">docs/MULTI-CLIENT-ARCHITECTURE.md</code>.
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($this->getModules() as $m)
                <div class="border-2 {{ $m['enabled'] ? 'border-success-500' : 'border-gray-300 dark:border-gray-700' }} rounded-lg p-5 bg-white dark:bg-gray-900 transition-all hover:shadow-md flex flex-col">
                    <div class="flex items-start justify-between mb-2 gap-2">
                        <div class="flex-1 min-w-0">
                            <h3 class="text-base font-bold leading-tight">{{ $m['name'] }}</h3>
                            <p class="text-xs text-gray-500 mt-0.5 font-mono">{{ $m['key'] }}</p>
                        </div>
                        @if($m['enabled'])
                            <span class="px-2 py-0.5 text-xs font-bold bg-success-500 text-white rounded shrink-0">УВІМК</span>
                        @else
                            <span class="px-2 py-0.5 text-xs font-bold bg-gray-400 text-white rounded shrink-0">ВИМК</span>
                        @endif
                    </div>

                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3 flex-grow">
                        {{ $m['description'] }}
                    </p>

                    @if(! empty($m['requires']))
                        <div class="text-xs mb-2">
                            <span class="text-gray-500">Потребує:</span>
                            @foreach($m['requires'] as $req)
                                <span class="inline-block px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 rounded font-mono ml-1">{{ $req }}</span>
                            @endforeach
                        </div>
                    @endif

                    @if(! empty($m['dependents']))
                        <div class="text-xs mb-3">
                            <span class="text-orange-600">Залежать від нього:</span>
                            @foreach($m['dependents'] as $dep)
                                <span class="inline-block px-1.5 py-0.5 bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200 rounded font-mono ml-1">{{ $dep }}</span>
                            @endforeach
                        </div>
                    @endif

                    @if($m['enabled'])
                        <button
                            type="button"
                            wire:click="toggleModule('{{ $m['key'] }}', false)"
                            wire:confirm="Точно вимкнути модуль «{{ $m['name'] }}»? Дані залишаться, але адмін-сторінки і функціонал зникнуть."
                            class="w-full px-3 py-2 bg-danger-600 hover:bg-danger-700 text-white rounded font-medium text-sm transition-colors"
                        >
                            Вимкнути
                        </button>
                    @else
                        <button
                            type="button"
                            wire:click="toggleModule('{{ $m['key'] }}', true)"
                            class="w-full px-3 py-2 bg-success-600 hover:bg-success-700 text-white rounded font-medium text-sm transition-colors"
                        >
                            Увімкнути
                        </button>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="rounded-lg bg-gray-50 dark:bg-gray-900 p-4 border border-gray-200 dark:border-gray-700">
            <h4 class="font-bold mb-2">CLI еквівалент</h4>
            <pre class="text-xs bg-white dark:bg-gray-800 p-3 rounded overflow-x-auto"><code>php artisan module:list
php artisan module:enable loyalty coupons
php artisan module:disable rozetka_delivery</code></pre>
        </div>
    </div>
</x-filament-panels::page>
