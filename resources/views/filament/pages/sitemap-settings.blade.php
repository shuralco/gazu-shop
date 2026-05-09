<x-filament-panels::page>
    @php
        $stats = $this->getStats();
    @endphp

    <div class="space-y-6">
        <!-- Google 2025 Trends Info -->
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg p-6 text-white mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold mb-2">🚀 Google 2025 SEO Оптимізація</h2>
                    <p class="text-blue-100">Налаштування відповідно до найновіших рекомендацій Google для підвищення ранжування</p>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold">{{ $stats['total_urls'] }}</div>
                    <div class="text-blue-200 text-sm">URL в sitemap</div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Категорії</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $stats['categories'] }}</p>
                    </div>
                    <x-heroicon-o-tag class="w-8 h-8 text-blue-500"/>
                </div>
            </div>

            <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Товари</p>
                        <p class="text-2xl font-bold text-green-600">{{ $stats['products'] }}</p>
                    </div>
                    <x-heroicon-o-cube class="w-8 h-8 text-green-500"/>
                </div>
            </div>

            <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Розмір кешу</p>
                        <p class="text-lg font-bold text-purple-600">{{ $stats['cache_size'] }}</p>
                    </div>
                    <x-heroicon-o-server class="w-8 h-8 text-purple-500"/>
                </div>
            </div>

            <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Останнє оновлення</p>
                        <p class="text-sm font-medium text-orange-600">{{ $stats['last_generated'] }}</p>
                    </div>
                    <x-heroicon-o-clock class="w-8 h-8 text-orange-500"/>
                </div>
            </div>
        </div>

        <!-- Google 2025 Features -->
        <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
            <h3 class="text-lg font-semibold mb-4 flex items-center">
                <x-heroicon-o-sparkles class="w-5 h-5 mr-2 text-yellow-500"/>
                Тренди Google 2025
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <h4 class="font-medium text-blue-800 mb-2">📱 Mobile-First Indexing</h4>
                    <p class="text-sm text-blue-700">Google пріоритизує мобільну версію сайту для індексації та ранжування</p>
                </div>
                <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                    <h4 class="font-medium text-green-800 mb-2">⚡ Core Web Vitals</h4>
                    <p class="text-sm text-green-700">Метрики швидкості та користувацького досвіду впливають на позиції в пошуку</p>
                </div>
                <div class="p-4 bg-purple-50 rounded-lg border border-purple-200">
                    <h4 class="font-medium text-purple-800 mb-2">🤖 AI Content Detection</h4>
                    <p class="text-sm text-purple-700">Google рекомендує позначати контент, створений штучним інтелектом</p>
                </div>
                <div class="p-4 bg-orange-50 rounded-lg border border-orange-200">
                    <h4 class="font-medium text-orange-800 mb-2">🛒 E-commerce Priority</h4>
                    <p class="text-sm text-orange-700">Підвищені пріоритети для товарів та категорій онлайн-магазинів</p>
                </div>
            </div>
        </div>

        <!-- Sitemap Links -->
        <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
            <h3 class="text-lg font-semibold mb-4 flex items-center">
                <x-heroicon-o-link class="w-5 h-5 mr-2"/>
                Швидкі посилання
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="/sitemap.xml" target="_blank" 
                   class="flex items-center p-3 bg-gray-50 rounded-lg border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-colors">
                    <x-heroicon-o-map class="w-5 h-5 text-blue-500 mr-3"/>
                    <div>
                        <p class="font-medium text-gray-800">Основний Sitemap</p>
                        <p class="text-xs text-gray-600">sitemap.xml</p>
                    </div>
                </a>

                <a href="/sitemap-categories.xml" target="_blank" 
                   class="flex items-center p-3 bg-gray-50 rounded-lg border border-gray-200 hover:border-green-300 hover:bg-green-50 transition-colors">
                    <x-heroicon-o-tag class="w-5 h-5 text-green-500 mr-3"/>
                    <div>
                        <p class="font-medium text-gray-800">Категорії</p>
                        <p class="text-xs text-gray-600">sitemap-categories.xml</p>
                    </div>
                </a>

                <a href="/sitemap-products.xml" target="_blank" 
                   class="flex items-center p-3 bg-gray-50 rounded-lg border border-gray-200 hover:border-purple-300 hover:bg-purple-50 transition-colors">
                    <x-heroicon-o-cube class="w-5 h-5 text-purple-500 mr-3"/>
                    <div>
                        <p class="font-medium text-gray-800">Товари</p>
                        <p class="text-xs text-gray-600">sitemap-products.xml</p>
                    </div>
                </a>

                <a href="/robots.txt" target="_blank" 
                   class="flex items-center p-3 bg-gray-50 rounded-lg border border-gray-200 hover:border-orange-300 hover:bg-orange-50 transition-colors">
                    <x-heroicon-o-command-line class="w-5 h-5 text-orange-500 mr-3"/>
                    <div>
                        <p class="font-medium text-gray-800">Robots.txt</p>
                        <p class="text-xs text-gray-600">Налаштування роботів</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Google Ping Status -->
        @if($stats['google_last_pinged'] !== 'Ніколи')
        <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
            <h3 class="text-lg font-semibold mb-4 flex items-center">
                <x-heroicon-o-bell class="w-5 h-5 mr-2 text-green-500"/>
                Статус Google Ping
            </h3>
            <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg border border-green-200">
                <div class="flex items-center">
                    <x-heroicon-o-check-circle class="w-6 h-6 text-green-500 mr-3"/>
                    <div>
                        <p class="font-medium text-green-800">Google успішно сповіщено</p>
                        <p class="text-sm text-green-600">Останній ping: {{ $stats['google_last_pinged'] }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <style>
        .fi-main {
            background-color: #f8fafc !important;
        }
        
        .fi-page-content {
            background-color: white !important;
            color: #1f2937 !important;
        }
        
        h1, h2, h3, h4, h5, h6 {
            color: #1f2937 !important;
        }
        
        p, span, div {
            color: #374151 !important;
        }
        
        .text-white {
            color: white !important;
        }
        
        .bg-gradient-to-r h1,
        .bg-gradient-to-r h2,
        .bg-gradient-to-r h3,
        .bg-gradient-to-r p,
        .bg-gradient-to-r span {
            color: white !important;
        }
    </style>
</x-filament-panels::page>
