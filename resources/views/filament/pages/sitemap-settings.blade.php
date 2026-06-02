<x-filament-panels::page>
    @php
        $stats = $this->getStats();
    @endphp

    <div class="space-y-6">
        {{-- Google 2025 Trends Info --}}
        <x-filament::section icon="heroicon-o-sparkles" icon-color="info">
            <x-slot name="heading">🚀 Google 2025 SEO Оптимізація</x-slot>
            <x-slot name="description">Налаштування відповідно до найновіших рекомендацій Google для підвищення ранжування</x-slot>

            <div class="flex flex-wrap items-center justify-between gap-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">URL в sitemap</p>
                <p class="text-2xl font-bold text-gray-950 dark:text-white">{{ $stats['total_urls'] }}</p>
            </div>
        </x-filament::section>

        {{-- Quick Stats --}}
        <div style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fill,minmax(290px,1fr))">
            <x-filament::section>
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Категорії</p>
                        <p class="text-2xl font-bold text-gray-950 dark:text-white">{{ $stats['categories'] }}</p>
                    </div>
                    <x-heroicon-o-tag class="w-8 h-8 text-primary-500"/>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Товари</p>
                        <p class="text-2xl font-bold text-gray-950 dark:text-white">{{ $stats['products'] }}</p>
                    </div>
                    <x-heroicon-o-cube class="w-8 h-8 text-success-500"/>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Розмір кешу</p>
                        <p class="text-lg font-bold text-gray-950 dark:text-white">{{ $stats['cache_size'] }}</p>
                    </div>
                    <x-heroicon-o-server class="w-8 h-8 text-gray-500"/>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Останнє оновлення</p>
                        <p class="text-sm font-medium text-gray-950 dark:text-white">{{ $stats['last_generated'] }}</p>
                    </div>
                    <x-heroicon-o-clock class="w-8 h-8 text-warning-500"/>
                </div>
            </x-filament::section>
        </div>

        {{-- Google 2025 Features --}}
        <x-filament::section icon="heroicon-o-sparkles" icon-color="warning">
            <x-slot name="heading">Тренди Google 2025</x-slot>

            <div style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(340px,1fr))">
                <div class="rounded-lg p-4 ring-1 ring-inset ring-gray-950/10 dark:ring-white/10">
                    <h4 class="mb-2 font-medium text-gray-950 dark:text-white">📱 Mobile-First Indexing</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Google пріоритизує мобільну версію сайту для індексації та ранжування</p>
                </div>
                <div class="rounded-lg p-4 ring-1 ring-inset ring-gray-950/10 dark:ring-white/10">
                    <h4 class="mb-2 font-medium text-gray-950 dark:text-white">⚡ Core Web Vitals</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Метрики швидкості та користувацького досвіду впливають на позиції в пошуку</p>
                </div>
                <div class="rounded-lg p-4 ring-1 ring-inset ring-gray-950/10 dark:ring-white/10">
                    <h4 class="mb-2 font-medium text-gray-950 dark:text-white">🤖 AI Content Detection</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Google рекомендує позначати контент, створений штучним інтелектом</p>
                </div>
                <div class="rounded-lg p-4 ring-1 ring-inset ring-gray-950/10 dark:ring-white/10">
                    <h4 class="mb-2 font-medium text-gray-950 dark:text-white">🛒 E-commerce Priority</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Підвищені пріоритети для товарів та категорій онлайн-магазинів</p>
                </div>
            </div>
        </x-filament::section>

        {{-- Sitemap Links --}}
        <x-filament::section icon="heroicon-o-link">
            <x-slot name="heading">Швидкі посилання</x-slot>

            <div style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fill,minmax(240px,1fr))">
                <a href="/sitemap.xml" target="_blank"
                   class="flex items-center gap-3 rounded-lg p-3 ring-1 ring-inset ring-gray-950/10 transition hover:bg-gray-50 dark:ring-white/10 dark:hover:bg-white/5">
                    <x-heroicon-o-map class="w-5 h-5 text-primary-500"/>
                    <div>
                        <p class="font-medium text-gray-950 dark:text-white">Основний Sitemap</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">sitemap.xml</p>
                    </div>
                </a>

                <a href="/sitemap-categories.xml" target="_blank"
                   class="flex items-center gap-3 rounded-lg p-3 ring-1 ring-inset ring-gray-950/10 transition hover:bg-gray-50 dark:ring-white/10 dark:hover:bg-white/5">
                    <x-heroicon-o-tag class="w-5 h-5 text-success-500"/>
                    <div>
                        <p class="font-medium text-gray-950 dark:text-white">Категорії</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">sitemap-categories.xml</p>
                    </div>
                </a>

                <a href="/sitemap-products.xml" target="_blank"
                   class="flex items-center gap-3 rounded-lg p-3 ring-1 ring-inset ring-gray-950/10 transition hover:bg-gray-50 dark:ring-white/10 dark:hover:bg-white/5">
                    <x-heroicon-o-cube class="w-5 h-5 text-gray-500"/>
                    <div>
                        <p class="font-medium text-gray-950 dark:text-white">Товари</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">sitemap-products.xml</p>
                    </div>
                </a>

                <a href="/robots.txt" target="_blank"
                   class="flex items-center gap-3 rounded-lg p-3 ring-1 ring-inset ring-gray-950/10 transition hover:bg-gray-50 dark:ring-white/10 dark:hover:bg-white/5">
                    <x-heroicon-o-command-line class="w-5 h-5 text-warning-500"/>
                    <div>
                        <p class="font-medium text-gray-950 dark:text-white">Robots.txt</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Налаштування роботів</p>
                    </div>
                </a>
            </div>
        </x-filament::section>

        {{-- Google Ping Status --}}
        @if($stats['google_last_pinged'] !== 'Ніколи')
        <x-filament::section icon="heroicon-o-bell" icon-color="success">
            <x-slot name="heading">Статус Google Ping</x-slot>

            <div class="flex items-center gap-3 rounded-lg bg-success-50 p-4 ring-1 ring-inset ring-success-600/20 dark:bg-success-400/10 dark:ring-success-400/30">
                <x-heroicon-o-check-circle class="w-6 h-6 text-success-500"/>
                <div>
                    <p class="font-medium text-success-800 dark:text-success-300">Google успішно сповіщено</p>
                    <p class="text-sm text-success-600 dark:text-success-400">Останній ping: {{ $stats['google_last_pinged'] }}</p>
                </div>
            </div>
        </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
