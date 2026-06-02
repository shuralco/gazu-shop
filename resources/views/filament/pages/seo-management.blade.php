<x-filament-panels::page>
    <div class="space-y-6">
        @php
            $stats = $this->getSeoStats();
            $recentUpdates = $this->getRecentSeoUpdates();
            $issues = $this->getSeoIssues();
        @endphp

        <!-- Hero Section -->
        <x-filament::section>
            <x-slot name="heading">🎯 SEO Управління</x-slot>
            <x-slot name="description">Централізоване управління всіма SEO параметрами сайту</x-slot>

            <!-- Action Buttons -->
            <div style="display:grid;gap:0.75rem;grid-template-columns:repeat(auto-fit,minmax(160px,1fr))">
                {{ $this->generate_all_seo }}
                {{ $this->generate_categories_seo }}
                {{ $this->generate_products_seo }}
                {{ $this->generate_pages_seo }}
                {{ $this->clear_seo_cache }}
                {{ $this->clear_all_cache }}
            </div>
        </x-filament::section>

        <!-- Stats Grid -->
        <div style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(160px,1fr))">
            <div class="bg-white dark:bg-white/5 p-4 rounded-lg ring-1 ring-gray-950/10 dark:ring-white/10">
                <div class="text-2xl font-bold text-primary-600">{{ $stats['total_seo'] }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Всього SEO записів</div>
            </div>

            <div class="bg-white dark:bg-white/5 p-4 rounded-lg ring-1 ring-gray-950/10 dark:ring-white/10">
                <div class="text-2xl font-bold text-success-600">{{ $stats['categories_with_seo'] }}/{{ $stats['categories_total'] }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Категорії з SEO</div>
            </div>

            <div class="bg-white dark:bg-white/5 p-4 rounded-lg ring-1 ring-gray-950/10 dark:ring-white/10">
                <div class="text-2xl font-bold text-info-600">{{ $stats['products_with_seo'] }}/{{ $stats['products_total'] }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Товари з SEO</div>
            </div>

            <div class="bg-white dark:bg-white/5 p-4 rounded-lg ring-1 ring-gray-950/10 dark:ring-white/10">
                <div class="text-2xl font-bold text-warning-600">{{ $stats['static_pages'] }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Статичні сторінки</div>
            </div>
        </div>

        <!-- Quick Links -->
        <div style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(220px,1fr))">
            <a href="{{ url('/admin/seo-metas') }}" class="block p-4 rounded-lg bg-primary-50 hover:bg-primary-100 ring-1 ring-primary-600/20 transition dark:bg-primary-400/10 dark:hover:bg-primary-400/20 dark:ring-primary-400/20">
                <div class="font-medium text-primary-700 dark:text-primary-300">📊 SEO Meta записи</div>
                <div class="text-sm text-primary-600 dark:text-primary-400">CRUD таблиця всіх SEO даних</div>
            </a>

            <a href="{{ url('/admin/products') }}" class="block p-4 rounded-lg bg-success-50 hover:bg-success-100 ring-1 ring-success-600/20 transition dark:bg-success-400/10 dark:hover:bg-success-400/20 dark:ring-success-400/20">
                <div class="font-medium text-success-700 dark:text-success-300">📦 Товари</div>
                <div class="text-sm text-success-600 dark:text-success-400">Редагування SEO товарів</div>
            </a>

            <a href="{{ url('/admin/categories') }}" class="block p-4 rounded-lg bg-info-50 hover:bg-info-100 ring-1 ring-info-600/20 transition dark:bg-info-400/10 dark:hover:bg-info-400/20 dark:ring-info-400/20">
                <div class="font-medium text-info-700 dark:text-info-300">🏷️ Категорії</div>
                <div class="text-sm text-info-600 dark:text-info-400">Редагування SEO категорій</div>
            </a>
        </div>

        <!-- SEO Issues -->
        @if(!empty($issues))
        <x-filament::section icon="heroicon-o-exclamation-triangle" icon-color="warning">
            <x-slot name="heading">⚠️ Проблеми та попередження</x-slot>
            <div class="space-y-2">
                @foreach($issues as $issue)
                <div class="p-3 rounded-lg {{ $issue['type'] === 'error' ? 'bg-danger-50 ring-1 ring-danger-600/20 dark:bg-danger-400/10 dark:ring-danger-400/20' : 'bg-warning-50 ring-1 ring-warning-600/20 dark:bg-warning-400/10 dark:ring-warning-400/20' }}">
                    <div class="text-sm {{ $issue['type'] === 'error' ? 'text-danger-700 dark:text-danger-400' : 'text-warning-700 dark:text-warning-400' }}">
                        {{ $issue['message'] }}
                    </div>
                </div>
                @endforeach
            </div>
        </x-filament::section>
        @endif

        <!-- Recent Updates -->
        <x-filament::section icon="heroicon-o-clock">
            <x-slot name="heading">🕒 Останні оновлення</x-slot>
            <div class="space-y-2 max-h-60 overflow-y-auto">
                @foreach($recentUpdates as $update)
                <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-white/5 rounded-lg">
                    <div style="flex:1 1 0%">
                        <div class="font-medium text-sm">{{ $update['title'] }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $update['type'] }} - {{ $update['updated_at']->diffForHumans() }}</div>
                    </div>
                    <x-filament::badge color="primary">{{ $update['language'] }}</x-filament::badge>
                </div>
                @endforeach
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>