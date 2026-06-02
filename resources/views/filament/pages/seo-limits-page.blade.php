<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">Управління лімітами SEO</x-slot>
            <x-slot name="description">
                Налаштуйте ліміти символів для автоматичної генерації SEO контенту.
                Ці ліміти будуть використовуватися при створенні meta-тегів у всіх частинах системи.
            </x-slot>

            <form wire:submit="save">
                {{ $this->form }}
            </form>
        </x-filament::section>

        <x-filament::section icon="heroicon-o-chart-bar" icon-color="info">
            <x-slot name="heading">Поточна статистика SEO</x-slot>

            <div style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(200px,1fr))">
                <div class="rounded-lg p-4 ring-1 ring-gray-950/5 bg-gray-50 dark:bg-white/5 dark:ring-white/10">
                    <div class="text-2xl font-bold text-info-600 dark:text-info-400">
                        {{ $productsWithSeoCount }}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Товари з SEO заголовками</div>
                </div>

                <div class="rounded-lg p-4 ring-1 ring-gray-950/5 bg-gray-50 dark:bg-white/5 dark:ring-white/10">
                    <div class="text-2xl font-bold text-success-600 dark:text-success-400">
                        {{ $categoriesWithSeoCount }}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Категорії з SEO</div>
                </div>

                <div class="rounded-lg p-4 ring-1 ring-gray-950/5 bg-gray-50 dark:bg-white/5 dark:ring-white/10">
                    <div class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                        {{ $seoMetaCount }}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Записи в SeoMeta</div>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section icon="heroicon-o-light-bulb" icon-color="warning">
            <x-slot name="heading">Рекомендації Google для SEO</x-slot>

            <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                <li>• <strong>Title:</strong> 50-60 символів (оптимально), до 70 символів (максимум)</li>
                <li>• <strong>Description:</strong> 150-160 символів (оптимально), до 320 символів (мобільні)</li>
                <li>• <strong>Keywords:</strong> 5-10 ключових слів (уникайте переспаму)</li>
                <li>• <strong>URL:</strong> До 100 символів, використовуйте дефіси замість підкреслень</li>
            </ul>
        </x-filament::section>
    </div>
</x-filament-panels::page>
