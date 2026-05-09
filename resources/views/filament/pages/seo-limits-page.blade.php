<x-filament-panels::page>
    <div class="space-y-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-900">
                    Управління лімітами SEO
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Налаштуйте ліміти символів для автоматичної генерації SEO контенту. 
                    Ці ліміти будуть використовуватися при створенні meta-тегів у всіх частинах системи.
                </p>
            </div>

            <form wire:submit="save">
                {{ $this->form }}
            </form>
        </div>

        <!-- Current Statistics -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200 p-6">
            <h3 class="text-lg font-semibold text-blue-900 mb-4">
                📊 Поточна статистика SEO
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-lg p-4 border border-blue-100">
                    <div class="text-2xl font-bold text-blue-600">
                        {{ $productsWithSeoCount }}
                    </div>
                    <div class="text-sm text-gray-600">Товари з SEO заголовками</div>
                </div>
                
                <div class="bg-white rounded-lg p-4 border border-blue-100">
                    <div class="text-2xl font-bold text-green-600">
                        {{ $categoriesWithSeoCount }}
                    </div>
                    <div class="text-sm text-gray-600">Категорії з SEO</div>
                </div>
                
                <div class="bg-white rounded-lg p-4 border border-blue-100">
                    <div class="text-2xl font-bold text-purple-600">
                        {{ $seoMetaCount }}
                    </div>
                    <div class="text-sm text-gray-600">Записи в SeoMeta</div>
                </div>
            </div>
        </div>

        <!-- Recommendations -->
        <div class="bg-yellow-50 rounded-lg border border-yellow-200 p-6">
            <h3 class="text-lg font-semibold text-yellow-800 mb-4">
                💡 Рекомендації Google для SEO
            </h3>
            
            <ul class="space-y-2 text-sm text-yellow-700">
                <li>• <strong>Title:</strong> 50-60 символів (оптимально), до 70 символів (максимум)</li>
                <li>• <strong>Description:</strong> 150-160 символів (оптимально), до 320 символів (мобільні)</li>
                <li>• <strong>Keywords:</strong> 5-10 ключових слів (уникайте переспаму)</li>
                <li>• <strong>URL:</strong> До 100 символів, використовуйте дефіси замість підкреслень</li>
            </ul>
        </div>
    </div>
</x-filament-panels::page>
