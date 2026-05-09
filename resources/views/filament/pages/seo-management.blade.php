<x-filament-panels::page>
    <div class="space-y-6">
        @php
            $stats = $this->getSeoStats();
            $recentUpdates = $this->getRecentSeoUpdates();
            $issues = $this->getSeoIssues();
        @endphp

        <!-- Hero Section -->
        <div class="bg-gradient-to-br from-gray-900 to-blue-900 rounded-xl p-6 text-white">
            <h1 class="text-2xl font-bold mb-2">🎯 SEO Управління</h1>
            <p class="text-blue-100">Централізоване управління всіма SEO параметрами сайту</p>
            
            <!-- Action Buttons -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 mt-6">
                {{ $this->generate_all_seo }}
                {{ $this->generate_categories_seo }}
                {{ $this->generate_products_seo }}
                {{ $this->generate_pages_seo }}
                {{ $this->clear_seo_cache }}
                {{ $this->clear_all_cache }}
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-lg border">
                <div class="text-2xl font-bold text-blue-600">{{ $stats['total_seo'] }}</div>
                <div class="text-sm text-gray-600">Всього SEO записів</div>
            </div>
            
            <div class="bg-white p-4 rounded-lg border">
                <div class="text-2xl font-bold text-green-600">{{ $stats['categories_with_seo'] }}/{{ $stats['categories_total'] }}</div>
                <div class="text-sm text-gray-600">Категорії з SEO</div>
            </div>
            
            <div class="bg-white p-4 rounded-lg border">
                <div class="text-2xl font-bold text-purple-600">{{ $stats['products_with_seo'] }}/{{ $stats['products_total'] }}</div>
                <div class="text-sm text-gray-600">Товари з SEO</div>
            </div>
            
            <div class="bg-white p-4 rounded-lg border">
                <div class="text-2xl font-bold text-orange-600">{{ $stats['static_pages'] }}</div>
                <div class="text-sm text-gray-600">Статичні сторінки</div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ url('/admin/seo-metas') }}" class="block p-4 bg-blue-50 rounded-lg border hover:bg-blue-100">
                <div class="font-medium text-blue-900">📊 SEO Meta записи</div>
                <div class="text-sm text-blue-700">CRUD таблиця всіх SEO даних</div>
            </a>
            
            <a href="{{ url('/admin/products') }}" class="block p-4 bg-green-50 rounded-lg border hover:bg-green-100">
                <div class="font-medium text-green-900">📦 Товари</div>
                <div class="text-sm text-green-700">Редагування SEO товарів</div>
            </a>
            
            <a href="{{ url('/admin/categories') }}" class="block p-4 bg-purple-50 rounded-lg border hover:bg-purple-100">
                <div class="font-medium text-purple-900">🏷️ Категорії</div>
                <div class="text-sm text-purple-700">Редагування SEO категорій</div>
            </a>
        </div>

        <!-- SEO Issues -->
        @if(!empty($issues))
        <div class="bg-white p-6 rounded-lg border">
            <h3 class="font-bold mb-4">⚠️ Проблеми та попередження</h3>
            <div class="space-y-2">
                @foreach($issues as $issue)
                <div class="p-3 rounded-lg {{ $issue['type'] === 'error' ? 'bg-red-50 border border-red-200' : 'bg-yellow-50 border border-yellow-200' }}">
                    <div class="text-sm {{ $issue['type'] === 'error' ? 'text-red-700' : 'text-yellow-700' }}">
                        {{ $issue['message'] }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Recent Updates -->
        <div class="bg-white p-6 rounded-lg border">
            <h3 class="font-bold mb-4">🕒 Останні оновлення</h3>
            <div class="space-y-2 max-h-60 overflow-y-auto">
                @foreach($recentUpdates as $update)
                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                    <div class="flex-1">
                        <div class="font-medium text-sm">{{ $update['title'] }}</div>
                        <div class="text-xs text-gray-500">{{ $update['type'] }} - {{ $update['updated_at']->diffForHumans() }}</div>
                    </div>
                    <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">{{ $update['language'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</x-filament-panels::page>