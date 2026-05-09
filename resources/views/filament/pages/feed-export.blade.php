<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-5">
                <div class="text-xs text-gray-500 uppercase tracking-wider mb-1">Активних товарів</div>
                <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalProducts, 0, '.', ' ') }}</div>
            </div>
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-5">
                <div class="text-xs text-gray-500 uppercase tracking-wider mb-1">З фото</div>
                <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($withImage, 0, '.', ' ') }}</div>
                <div class="text-xs text-gray-400 mt-1">{{ $totalProducts > 0 ? round($withImage / $totalProducts * 100) : 0 }}% каталогу</div>
            </div>
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-5">
                <div class="text-xs text-gray-500 uppercase tracking-wider mb-1">У наявності</div>
                <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($inStock, 0, '.', ' ') }}</div>
                <div class="text-xs text-gray-400 mt-1">{{ $totalProducts > 0 ? round($inStock / $totalProducts * 100) : 0 }}% каталогу</div>
            </div>
        </div>

        {{-- Info --}}
        <div class="rounded-lg bg-blue-50 dark:bg-blue-900/20 p-4 border border-blue-200 dark:border-blue-800">
            <div class="flex gap-3">
                <x-filament::icon icon="heroicon-o-information-circle" class="h-5 w-5 text-blue-600 shrink-0 mt-0.5"/>
                <div class="text-sm text-blue-900 dark:text-blue-100 space-y-1">
                    <div><strong>Як це працює:</strong></div>
                    <ul class="list-disc list-inside text-xs space-y-0.5">
                        <li>Скопіюйте посилання на фід (наприклад <code>/feed/rozetka.xml</code>) і вставте у ЛК продавця маркетплейсу</li>
                        <li>Маркетплейс пуллить фід автоматично кожні 1–6 годин</li>
                        <li>Кеш фідів — 1 година. Натисніть <em>Перегенерувати</em> для миттєвого оновлення</li>
                        <li>У фід потрапляють лише <em>активні</em> товари. Налаштування фільтрів — у файлі <code>config/feed.php</code> (буде далі)</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Feeds grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($feeds as $f)
                <div class="bg-white dark:bg-gray-900 border-2 {{ $f['cached'] ? 'border-success-500' : 'border-gray-300 dark:border-gray-700' }} rounded-lg p-5 flex flex-col">
                    <div class="flex items-start justify-between mb-3 gap-2">
                        <div class="flex-1 min-w-0">
                            <h3 class="text-base font-bold flex items-center gap-2">
                                <span class="text-2xl">{{ $f['icon'] }}</span>
                                {{ $f['name'] }}
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $f['description'] }}</p>
                        </div>
                        @if($f['cached'])
                            <span class="px-2 py-0.5 text-xs font-bold bg-success-500 text-white rounded shrink-0">КЕШ</span>
                        @else
                            <span class="px-2 py-0.5 text-xs font-bold bg-gray-400 text-white rounded shrink-0">⌚</span>
                        @endif
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-800 rounded p-2 mb-3">
                        <div class="flex items-center gap-2 text-xs">
                            <input type="text" value="{{ $f['url'] }}" readonly
                                   class="flex-1 bg-transparent border-0 outline-none font-mono text-[11px] text-gray-700 dark:text-gray-300"
                                   onfocus="this.select()">
                            <button type="button"
                                    onclick="navigator.clipboard.writeText('{{ $f['url'] }}'); this.textContent='✓ скопійовано'; setTimeout(()=>this.textContent='Копіювати', 1500)"
                                    class="px-2 py-1 bg-primary-600 hover:bg-primary-700 text-white rounded text-[10px] font-medium whitespace-nowrap">
                                Копіювати
                            </button>
                        </div>
                    </div>

                    <div class="text-xs text-gray-500 mb-3">
                        @if($f['last_at'])
                            Останньо згенеровано: {{ $f['last_at'] }}
                        @else
                            Ще не генерувався
                        @endif
                    </div>

                    <div class="flex gap-2 mt-auto">
                        <a href="{{ $f['url'] }}" target="_blank"
                           class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-900 dark:text-white rounded text-sm font-medium no-underline">
                            <x-filament::icon icon="heroicon-o-arrow-top-right-on-square" class="h-4 w-4"/>
                            Відкрити XML
                        </a>
                        <button type="button"
                                wire:click="regenerate('{{ $f['type'] }}')"
                                wire:loading.attr="disabled"
                                wire:target="regenerate('{{ $f['type'] }}')"
                                class="px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded text-sm font-medium disabled:opacity-60">
                            <span wire:loading.remove wire:target="regenerate('{{ $f['type'] }}')">Регенерувати</span>
                            <span wire:loading wire:target="regenerate('{{ $f['type'] }}')">…</span>
                        </button>
                        @if($f['cached'])
                            <button type="button" wire:click="clearOne('{{ $f['type'] }}')"
                                    title="Очистити кеш"
                                    class="px-3 py-2 bg-danger-50 hover:bg-danger-100 dark:bg-danger-900/30 text-danger-700 rounded text-sm">
                                <x-filament::icon icon="heroicon-o-trash" class="h-4 w-4"/>
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Schedule info --}}
        <div class="rounded-lg bg-gray-50 dark:bg-gray-900/50 p-4 border border-gray-200 dark:border-gray-700">
            <div class="font-bold text-gray-900 dark:text-white mb-2">📅 Авто-оновлення фідів</div>
            <div class="text-xs text-gray-700 dark:text-gray-300 space-y-1">
                <p>Маркетплейси самі періодично завантажують фіди (Rozetka — кожні 4 год, Prom — кожні 2 год, OLX — за розкладом імпорту).</p>
                <p>Фід автоматично перегенерується після <code>php artisan cache:clear</code> або при першому зверненні після TTL (1 год).</p>
                <p>Для регулярного оновлення додайте у crontab:
                    <code class="block mt-1 p-2 bg-white dark:bg-gray-800 rounded">0 * * * * cd /var/www/html && php artisan cache:forget product_feed_rozetka product_feed_prom product_feed_olx</code>
                </p>
            </div>
        </div>
    </div>
</x-filament-panels::page>
