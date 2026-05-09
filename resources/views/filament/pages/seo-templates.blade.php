<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header Info -->
        <div class="bg-gradient-to-r from-indigo-600 to-blue-600 rounded-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold mb-2">📝 SEO Шаблони та Налаштування</h2>
                    <p class="text-indigo-100">Налаштуйте шаблони для автоматичної генерації SEO метаданих</p>
                </div>
                <div class="text-right">
                    <x-heroicon-o-document-text class="w-12 h-12 text-indigo-200"/>
                </div>
            </div>
        </div>

        <!-- SEO Statistics -->
        @php
            $stats = [
                'total_products' => \App\Models\Product::count(),
                'products_with_seo' => \App\Models\SeoMeta::where('seoable_type', \App\Models\Product::class)->count(),
                'total_categories' => \App\Models\Category::count(),
                'categories_with_seo' => \App\Models\SeoMeta::where('seoable_type', \App\Models\Category::class)->count(),
                'total_pages' => \App\Models\SeoMeta::whereNotNull('page_type')->count(),
                'auto_generated' => \App\Models\SeoMeta::where('auto_generated', true)->count(),
            ];
        @endphp

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h3 class="text-lg font-semibold mb-4 flex items-center text-white">
                <x-heroicon-o-chart-bar class="w-5 h-5 mr-2 text-green-400"/>
                📊 Поточна статистика SEO
            </h3>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                <div class="bg-blue-900/50 rounded-lg p-3 border border-blue-600">
                    <div class="text-xl font-bold text-blue-300">{{ $stats['total_products'] }}</div>
                    <div class="text-xs text-blue-200">Всього товарів</div>
                </div>
                <div class="bg-green-900/50 rounded-lg p-3 border border-green-600">
                    <div class="text-xl font-bold text-green-300">{{ $stats['products_with_seo'] }}</div>
                    <div class="text-xs text-green-200">Товари з SEO</div>
                </div>
                <div class="bg-purple-900/50 rounded-lg p-3 border border-purple-600">
                    <div class="text-xl font-bold text-purple-300">{{ $stats['total_categories'] }}</div>
                    <div class="text-xs text-purple-200">Всього категорій</div>
                </div>
                <div class="bg-yellow-900/50 rounded-lg p-3 border border-yellow-600">
                    <div class="text-xl font-bold text-yellow-300">{{ $stats['categories_with_seo'] }}</div>
                    <div class="text-xs text-yellow-200">Категорії з SEO</div>
                </div>
            </div>

            <!-- Progress Bars -->
            <div class="space-y-3">
                <div>
                    <div class="flex justify-between mb-1">
                        <span class="text-sm text-gray-300">Покриття товарів</span>
                        <span class="text-sm text-gray-300">
                            {{ $stats['total_products'] > 0 ? round(($stats['products_with_seo'] / $stats['total_products']) * 100, 1) : 0 }}%
                        </span>
                    </div>
                    <div class="w-full bg-gray-700 rounded-full h-2">
                        <div class="bg-green-500 h-2 rounded-full" style="width: {{ $stats['total_products'] > 0 ? ($stats['products_with_seo'] / $stats['total_products']) * 100 : 0 }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between mb-1">
                        <span class="text-sm text-gray-300">Покриття категорій</span>
                        <span class="text-sm text-gray-300">
                            {{ $stats['total_categories'] > 0 ? round(($stats['categories_with_seo'] / $stats['total_categories']) * 100, 1) : 0 }}%
                        </span>
                    </div>
                    <div class="w-full bg-gray-700 rounded-full h-2">
                        <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $stats['total_categories'] > 0 ? ($stats['categories_with_seo'] / $stats['total_categories']) * 100 : 0 }}%"></div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mt-4 flex gap-2">
                <a href="{{ route('filament.admin.pages.seo-management') }}" 
                   class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition-colors">
                    🚀 Генерувати SEO
                </a>
                <a href="{{ route('filament.admin.resources.seo-metas.index') }}" 
                   class="px-3 py-1 bg-purple-600 text-white text-sm rounded hover:bg-purple-700 transition-colors">
                    📊 Переглянути всі SEO
                </a>
                @if($stats['total_products'] > 0 && $stats['products_with_seo'] == 0)
                <span class="px-3 py-1 bg-orange-600 text-white text-sm rounded">
                    ⚠️ Немає SEO для товарів
                </span>
                @endif
            </div>

            <!-- Status Messages -->
            @if($stats['products_with_seo'] > $stats['total_products'] || $stats['categories_with_seo'] > $stats['total_categories'])
            <div class="mt-4 bg-red-900/30 border border-red-600 rounded-lg p-3">
                <div class="flex items-center">
                    <x-heroicon-o-exclamation-circle class="w-4 h-4 text-red-400 mr-2"/>
                    <span class="text-red-300 text-sm font-medium">
                        🚨 ПОМИЛКА: Виявлено дублікати в SEO записах! 
                        Товарів з SEO ({{ $stats['products_with_seo'] }}) більше ніж загальна кількість ({{ $stats['total_products'] }}).
                        Натисніть "🗑️ Очистити весь SEO" а потім "🚀 Повна перегенерація" для виправлення.
                    </span>
                </div>
            </div>
            @elseif($stats['total_products'] > 0 && $stats['products_with_seo'] / $stats['total_products'] < 0.5)
            <div class="mt-4 bg-orange-900/30 border border-orange-600 rounded-lg p-3">
                <div class="flex items-center">
                    <x-heroicon-o-exclamation-triangle class="w-4 h-4 text-orange-400 mr-2"/>
                    <span class="text-orange-300 text-sm">
                        Низьке покриття SEO ({{ round(($stats['products_with_seo'] / $stats['total_products']) * 100, 1) }}%). 
                        Рекомендуємо згенерувати SEO для всіх товарів.
                    </span>
                </div>
            </div>
            @elseif($stats['total_products'] > 0 && $stats['products_with_seo'] == $stats['total_products'])
            <div class="mt-4 bg-green-900/30 border border-green-600 rounded-lg p-3">
                <div class="flex items-center">
                    <x-heroicon-o-check-circle class="w-4 h-4 text-green-400 mr-2"/>
                    <span class="text-green-300 text-sm">
                        ✅ Відмінно! Всі товари мають SEO метадані.
                    </span>
                </div>
            </div>
            @endif
        </div>

        <!-- Preview Section -->
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6 shadow-sm">
            <h3 class="text-lg font-semibold mb-4 flex items-center text-white">
                <x-heroicon-o-eye class="w-5 h-5 mr-2 text-blue-400"/>
                Приклади генерації
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 bg-blue-900/50 rounded-lg border border-blue-600">
                    <h4 class="font-medium text-blue-300 mb-2">📱 Приклад категорії</h4>
                    <p class="text-sm text-blue-200 mb-1"><strong>Заголовок:</strong> Смартфони | SimpleShop</p>
                    <p class="text-sm text-blue-200"><strong>Опис:</strong> Великий вибір товарів у категорії Смартфони. Швидка доставка по Україні.</p>
                </div>
                <div class="p-4 bg-green-900/50 rounded-lg border border-green-600">
                    <h4 class="font-medium text-green-300 mb-2">📦 Приклад товару</h4>
                    <p class="text-sm text-green-200 mb-1"><strong>Заголовок:</strong> Купити iPhone 15 Pro за 45000 грн | SimpleShop</p>
                    <p class="text-sm text-green-200"><strong>Опис:</strong> Купити iPhone 15 Pro за найкращою ціною 45000 грн. Новий флагман Apple.</p>
                </div>
            </div>
        </div>

        <!-- Variable Guide -->
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h3 class="text-lg font-semibold mb-4 flex items-center text-white">
                <x-heroicon-o-code-bracket class="w-5 h-5 mr-2 text-yellow-400"/>
                Доступні змінні для шаблонів
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <h4 class="font-medium text-yellow-300 mb-2">🏷️ Категорії</h4>
                    <ul class="text-sm text-yellow-200 space-y-1">
                        <li>• <code class="text-yellow-400">%s</code> - назва категорії</li>
                        <li>• <code class="text-yellow-400">%d</code> - кількість товарів</li>
                        <li>• <code class="text-yellow-400">%site%</code> - назва сайту</li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-medium text-yellow-300 mb-2">📦 Товари</h4>
                    <ul class="text-sm text-yellow-200 space-y-1">
                        <li>• <code class="text-yellow-400">%s</code> - назва товару</li>
                        <li>• <code class="text-yellow-400">%price%</code> - ціна</li>
                        <li>• <code class="text-yellow-400">%category%</code> - категорія</li>
                        <li>• <code class="text-yellow-400">%brand%</code> - бренд</li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-medium text-yellow-300 mb-2">📄 Сторінки</h4>
                    <ul class="text-sm text-yellow-200 space-y-1">
                        <li>• <code class="text-yellow-400">%s</code> - назва сторінки</li>
                        <li>• <code class="text-yellow-400">%site%</code> - назва сайту</li>
                        <li>• <code class="text-yellow-400">%year%</code> - поточний рік</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- SEO Templates Form -->
        <div class="bg-gray-800 rounded-lg border border-gray-700 shadow-sm">
            <form wire:submit="save" class="p-6">
                {{ $this->form }}
                
                <div class="mt-6 flex gap-4">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        💾 Зберегти шаблони
                    </button>
                    <button type="button" wire:click="resetToDefaults" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                        🔄 Скинути до стандартних
                    </button>
                </div>
            </form>
        </div>

        <!-- SEO Best Practices -->
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h3 class="text-lg font-semibold mb-4 flex items-center text-white">
                <x-heroicon-o-light-bulb class="w-5 h-5 mr-2 text-blue-400"/>
                SEO Рекомендації Google 2025
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <h4 class="font-medium text-blue-300 mb-2">📏 Заголовки (Title)</h4>
                    <ul class="text-sm text-blue-200 space-y-1">
                        <li>• Максимум 60 символів</li>
                        <li>• Ключові слова на початку</li>
                        <li>• Унікальність для кожної сторінки</li>
                        <li>• Бренд в кінці</li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-medium text-blue-300 mb-2">📝 Описи (Description)</h4>
                    <ul class="text-sm text-blue-200 space-y-1">
                        <li>• 150-160 символів</li>
                        <li>• Заклик до дії (CTA)</li>
                        <li>• Переваги для користувача</li>
                        <li>• Ключові слова природно</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <style>
        .fi-main {
            background-color: #111827 !important;
        }
        
        .fi-page-content {
            background-color: #111827 !important;
            color: #f9fafb !important;
        }
        
        h1, h2, h3, h4, h5, h6 {
            color: #f9fafb !important;
        }
        
        p, span, div {
            color: #d1d5db !important;
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

        /* Filament form styling for dark theme */
        .fi-form-field-wrapper {
            background-color: #1f2937 !important;
        }
        
        .fi-input {
            background-color: #374151 !important;
            border-color: #4b5563 !important;
            color: #f9fafb !important;
        }
        
        .fi-input:focus {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 1px #3b82f6 !important;
        }
        
        .fi-label {
            color: #f9fafb !important;
        }
        
        .fi-section {
            background-color: #1f2937 !important;
            border-color: #374151 !important;
        }
        
        .fi-section-header h3 {
            color: #f9fafb !important;
        }
    </style>
</x-filament-panels::page>
