<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            🎯 SEO Dashboard - Управління SEO параметрами
        </x-slot>

        <x-slot name="description">
            Централізоване управління всіма SEO параметрами сайту
        </x-slot>

        <x-slot name="headerActions">
            {{ $this->generateAllCategoriesAction }}
            {{ $this->generateAllProductsAction }}
            {{ $this->generateStaticPagesAction }}
            {{ $this->clearSeoCacheAction }}
        </x-slot>

        @php
            $stats = $this->getStats();
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-4 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm">Загальна кількість SEO</p>
                        <p class="text-2xl font-bold">{{ $stats['total_seo_records'] }}</p>
                    </div>
                    <x-heroicon-o-chart-bar class="w-8 h-8 text-blue-200"/>
                </div>
            </div>

            <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-4 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm">Категорії з SEO</p>
                        <p class="text-2xl font-bold">{{ $stats['categories_with_seo'] }}/{{ $stats['total_categories'] }}</p>
                    </div>
                    <x-heroicon-o-tag class="w-8 h-8 text-green-200"/>
                </div>
            </div>

            <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg p-4 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm">Товари з SEO</p>
                        <p class="text-2xl font-bold">{{ $stats['products_with_seo'] }}/{{ $stats['total_products'] }}</p>
                    </div>
                    <x-heroicon-o-cube class="w-8 h-8 text-purple-200"/>
                </div>
            </div>

            <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg p-4 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-orange-100 text-sm">Статичні сторінки</p>
                        <p class="text-2xl font-bold">{{ $stats['static_pages_seo'] }}</p>
                    </div>
                    <x-heroicon-o-document-text class="w-8 h-8 text-orange-200"/>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-gray-50 dark:bg-white/5 rounded-lg p-4">
                <h3 class="text-lg font-semibold mb-3 text-gray-800 dark:text-gray-200">📊 Статистика покриття</h3>
                
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Категорії без SEO:</span>
                        <span class="px-2 py-1 text-xs font-medium rounded {{ $stats['missing_categories'] > 0 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                            {{ $stats['missing_categories'] }}
                        </span>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Товари без SEO:</span>
                        <span class="px-2 py-1 text-xs font-medium rounded {{ $stats['missing_products'] > 0 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                            {{ $stats['missing_products'] }}
                        </span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">🇺🇦 Українські записи:</span>
                        <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded">
                            {{ $stats['ukrainian_records'] }}
                        </span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">🇬🇧 Англійські записи:</span>
                        <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded">
                            {{ $stats['english_records'] }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 dark:bg-white/5 rounded-lg p-4">
                <h3 class="text-lg font-semibold mb-3 text-gray-800 dark:text-gray-200">🗺️ Sitemap статус</h3>
                
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Sitemap кешовано:</span>
                        <span class="px-2 py-1 text-xs font-medium rounded {{ $stats['sitemap_cached'] ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ $stats['sitemap_cached'] ? '✅ Так' : '⏳ Ні' }}
                        </span>
                    </div>

                    <div class="mt-4">
                        <a href="/sitemap.xml" target="_blank" class="inline-flex items-center px-3 py-2 text-sm font-medium text-blue-600 bg-blue-100 rounded-md hover:bg-blue-200">
                            <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4 mr-1"/>
                            Переглянути Sitemap
                        </a>
                    </div>

                    <div class="mt-2">
                        <a href="/robots.txt" target="_blank" class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200">
                            <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4 mr-1"/>
                            Переглянути robots.txt
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <h4 class="text-sm font-medium text-yellow-800 mb-2">💡 Швидкі дії</h4>
            <div class="text-sm text-yellow-700 space-y-1">
                <p>• Використовуйте кнопки вище для масової генерації SEO</p>
                <p>• Перейдіть до <strong>SEO Мета-дані</strong> для детального редагування</p>
                <p>• Очищайте кеш після змін для оновлення sitemap</p>
                <p>• Перевіряйте покриття SEO регулярно для нових товарів</p>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>