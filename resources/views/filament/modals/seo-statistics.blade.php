<div class="space-y-6">
    <!-- Overall Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-blue-900/50 rounded-lg p-4 border border-blue-600">
            <div class="text-2xl font-bold text-blue-300">{{ $stats['total_products'] }}</div>
            <div class="text-sm text-blue-200">Всього товарів</div>
        </div>
        <div class="bg-green-900/50 rounded-lg p-4 border border-green-600">
            <div class="text-2xl font-bold text-green-300">{{ $stats['products_with_seo'] }}</div>
            <div class="text-sm text-green-200">Товари з SEO</div>
        </div>
        <div class="bg-purple-900/50 rounded-lg p-4 border border-purple-600">
            <div class="text-2xl font-bold text-purple-300">{{ $stats['total_categories'] }}</div>
            <div class="text-sm text-purple-200">Всього категорій</div>
        </div>
        <div class="bg-yellow-900/50 rounded-lg p-4 border border-yellow-600">
            <div class="text-2xl font-bold text-yellow-300">{{ $stats['categories_with_seo'] }}</div>
            <div class="text-sm text-yellow-200">Категорії з SEO</div>
        </div>
    </div>

    <!-- Coverage Percentages -->
    <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
        <h3 class="text-lg font-semibold mb-4 text-white flex items-center">
            <x-heroicon-o-chart-bar class="w-5 h-5 mr-2 text-blue-400"/>
            Покриття SEO
        </h3>
        
        <div class="space-y-4">
            <!-- Products Coverage -->
            <div>
                <div class="flex justify-between mb-2">
                    <span class="text-sm text-gray-300">Товари</span>
                    <span class="text-sm text-gray-300">
                        {{ $stats['total_products'] > 0 ? round(($stats['products_with_seo'] / $stats['total_products']) * 100, 1) : 0 }}%
                    </span>
                </div>
                <div class="w-full bg-gray-700 rounded-full h-2">
                    <div class="bg-green-500 h-2 rounded-full" style="width: {{ $stats['total_products'] > 0 ? ($stats['products_with_seo'] / $stats['total_products']) * 100 : 0 }}%"></div>
                </div>
            </div>

            <!-- Categories Coverage -->
            <div>
                <div class="flex justify-between mb-2">
                    <span class="text-sm text-gray-300">Категорії</span>
                    <span class="text-sm text-gray-300">
                        {{ $stats['total_categories'] > 0 ? round(($stats['categories_with_seo'] / $stats['total_categories']) * 100, 1) : 0 }}%
                    </span>
                </div>
                <div class="w-full bg-gray-700 rounded-full h-2">
                    <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $stats['total_categories'] > 0 ? ($stats['categories_with_seo'] / $stats['total_categories']) * 100 : 0 }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-gray-800 rounded-lg p-4 border border-gray-700">
            <div class="text-xl font-bold text-orange-300">{{ $stats['total_pages'] }}</div>
            <div class="text-sm text-orange-200">Статичні сторінки</div>
        </div>
        <div class="bg-gray-800 rounded-lg p-4 border border-gray-700">
            <div class="text-xl font-bold text-cyan-300">{{ $stats['auto_generated'] }}</div>
            <div class="text-sm text-cyan-200">Автогенеровано</div>
        </div>
        <div class="bg-gray-800 rounded-lg p-4 border border-gray-700">
            <div class="text-xl font-bold text-pink-300">{{ $stats['products_with_seo'] + $stats['categories_with_seo'] + $stats['total_pages'] }}</div>
            <div class="text-sm text-pink-200">Всього SEO записів</div>
        </div>
    </div>

    <!-- Recommendations -->
    @if($stats['total_products'] > 0 && $stats['products_with_seo'] / $stats['total_products'] < 0.8)
    <div class="bg-orange-900/30 border border-orange-600 rounded-lg p-4">
        <div class="flex items-center">
            <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-orange-400 mr-2"/>
            <span class="text-orange-300 font-medium">Рекомендація:</span>
        </div>
        <p class="text-orange-200 mt-2">
            Менше 80% товарів має SEO метадані. Рекомендуємо згенерувати SEO для всіх товарів через 
            <a href="{{ route('filament.admin.pages.seo-management') }}" class="text-orange-400 underline">SEO Управління</a>.
        </p>
    </div>
    @endif

    @if($stats['total_categories'] > 0 && $stats['categories_with_seo'] / $stats['total_categories'] < 0.9)
    <div class="bg-blue-900/30 border border-blue-600 rounded-lg p-4">
        <div class="flex items-center">
            <x-heroicon-o-information-circle class="w-5 h-5 text-blue-400 mr-2"/>
            <span class="text-blue-300 font-medium">Порада:</span>
        </div>
        <p class="text-blue-200 mt-2">
            Не всі категорії мають SEO. Категорії важливі для структури сайту та індексації.
        </p>
    </div>
    @endif
</div>