<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Stats --}}
        <div style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(290px,1fr))">
            <x-filament::section>
                <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Активних товарів</div>
                <div class="text-3xl font-bold text-gray-950 dark:text-white">{{ number_format($totalProducts, 0, '.', ' ') }}</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">З фото</div>
                <div class="text-3xl font-bold text-gray-950 dark:text-white">{{ number_format($withImage, 0, '.', ' ') }}</div>
                <div class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $totalProducts > 0 ? round($withImage / $totalProducts * 100) : 0 }}% каталогу</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">У наявності</div>
                <div class="text-3xl font-bold text-gray-950 dark:text-white">{{ number_format($inStock, 0, '.', ' ') }}</div>
                <div class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $totalProducts > 0 ? round($inStock / $totalProducts * 100) : 0 }}% каталогу</div>
            </x-filament::section>
        </div>

        {{-- Info --}}
        <x-filament::section icon="heroicon-o-information-circle" icon-color="info">
            <x-slot name="heading">Як це працює</x-slot>
            <ul class="list-disc list-inside text-sm text-gray-600 dark:text-gray-400 space-y-1">
                <li>Скопіюйте посилання на фід (наприклад <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">/feed/rozetka.xml</code>) і вставте у ЛК продавця маркетплейсу</li>
                <li>Маркетплейс пуллить фід автоматично кожні 1–6 годин</li>
                <li>Кеш фідів — 1 година. Натисніть <em>Перегенерувати</em> для миттєвого оновлення</li>
                <li>У фід потрапляють лише <em>активні</em> товари. Налаштування фільтрів — у файлі <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">config/feed.php</code> (буде далі)</li>
            </ul>
        </x-filament::section>

        {{-- Feeds grid --}}
        <div style="display:grid;gap:1.5rem;grid-template-columns:repeat(auto-fit,minmax(340px,1fr))">
            @foreach($feeds as $f)
                <x-filament::section>
                    <x-slot name="heading">
                        <span class="flex items-center gap-2">
                            <span class="text-2xl">{{ $f['icon'] }}</span>
                            {{ $f['name'] }}
                        </span>
                    </x-slot>
                    <x-slot name="description">{{ $f['description'] }}</x-slot>
                    <x-slot name="headerEnd">
                        @if($f['cached'])
                            <x-filament::badge color="success">КЕШ</x-filament::badge>
                        @else
                            <x-filament::badge color="gray">⌚</x-filament::badge>
                        @endif
                    </x-slot>

                    <div class="flex flex-col gap-3">
                        <div class="flex items-center gap-2 rounded-lg bg-gray-50 p-2 dark:bg-white/5">
                            <input type="text" value="{{ $f['url'] }}" readonly
                                   class="fi-input block w-full rounded-lg border-none bg-white px-3 py-1.5 font-mono text-xs text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 transition focus:ring-2 focus:ring-inset focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/10"
                                   style="flex:1 1 0%"
                                   onfocus="this.select()">
                            <x-filament::button
                                size="xs"
                                color="primary"
                                icon="heroicon-o-clipboard"
                                onclick="navigator.clipboard.writeText('{{ $f['url'] }}'); this.querySelector('.fi-btn-label').textContent='✓ скопійовано'; setTimeout(()=>this.querySelector('.fi-btn-label').textContent='Копіювати', 1500)"
                                class="whitespace-nowrap">
                                Копіювати
                            </x-filament::button>
                        </div>

                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            @if($f['last_at'])
                                Останньо згенеровано: {{ $f['last_at'] }}
                            @else
                                Ще не генерувався
                            @endif
                        </div>

                        <div class="flex gap-2">
                            <x-filament::button
                                tag="a"
                                href="{{ $f['url'] }}"
                                target="_blank"
                                color="gray"
                                icon="heroicon-o-arrow-top-right-on-square"
                                style="flex:1 1 0%">
                                Відкрити XML
                            </x-filament::button>
                            <x-filament::button
                                color="primary"
                                icon="heroicon-o-arrow-path"
                                wire:click="regenerate('{{ $f['type'] }}')"
                                wire:loading.attr="disabled"
                                wire:target="regenerate('{{ $f['type'] }}')">
                                <span wire:loading.remove wire:target="regenerate('{{ $f['type'] }}')">Регенерувати</span>
                                <span wire:loading wire:target="regenerate('{{ $f['type'] }}')">…</span>
                            </x-filament::button>
                            @if($f['cached'])
                                <x-filament::icon-button
                                    icon="heroicon-o-trash"
                                    wire:click="clearOne('{{ $f['type'] }}')"
                                    label="Очистити кеш"
                                    color="danger"/>
                            @endif
                        </div>
                    </div>
                </x-filament::section>
            @endforeach
        </div>

        {{-- Schedule info --}}
        <x-filament::section icon="heroicon-o-calendar-days" icon-color="gray">
            <x-slot name="heading">📅 Авто-оновлення фідів</x-slot>
            <div class="text-sm text-gray-600 dark:text-gray-400 space-y-2">
                <p>Маркетплейси самі періодично завантажують фіди (Rozetka — кожні 4 год, Prom — кожні 2 год, OLX — за розкладом імпорту).</p>
                <p>Фід автоматично перегенерується після <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">php artisan cache:clear</code> або при першому зверненні після TTL (1 год).</p>
                <p>Для регулярного оновлення додайте у crontab:</p>
                <code class="block rounded bg-gray-100 p-2 font-mono text-xs dark:bg-white/10">0 * * * * cd /var/www/html && php artisan cache:forget product_feed_rozetka product_feed_prom product_feed_olx</code>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
