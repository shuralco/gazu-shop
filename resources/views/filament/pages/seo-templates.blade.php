<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header Info --}}
        <x-filament::section icon="heroicon-o-document-text" icon-color="primary">
            <x-slot name="heading">SEO Шаблони та Налаштування</x-slot>
            <x-slot name="description">Налаштуйте шаблони для автоматичної генерації SEO метаданих</x-slot>
        </x-filament::section>

        {{-- SEO Statistics --}}
        @php
            $stats = [
                'total_products' => \App\Models\Product::count(),
                'products_with_seo' => \App\Models\SeoMeta::where('seoable_type', \App\Models\Product::class)->count(),
                'total_categories' => \App\Models\Category::count(),
                'categories_with_seo' => \App\Models\SeoMeta::where('seoable_type', \App\Models\Category::class)->count(),
                'total_pages' => \App\Models\SeoMeta::whereNotNull('page_type')->count(),
                'auto_generated' => \App\Models\SeoMeta::where('auto_generated', true)->count(),
            ];
            $productsCoverage = $stats['total_products'] > 0 ? ($stats['products_with_seo'] / $stats['total_products']) * 100 : 0;
            $categoriesCoverage = $stats['total_categories'] > 0 ? ($stats['categories_with_seo'] / $stats['total_categories']) * 100 : 0;
        @endphp

        <x-filament::section icon="heroicon-o-chart-bar" icon-color="success">
            <x-slot name="heading">Поточна статистика SEO</x-slot>

            <div class="overflow-hidden rounded-lg bg-gray-200 dark:bg-white/10" style="display:grid;gap:1px;grid-template-columns:repeat(auto-fit,minmax(110px,1fr))">
                <div class="bg-white p-3 dark:bg-gray-900">
                    <div class="text-xl font-bold text-primary-600 dark:text-primary-400">{{ $stats['total_products'] }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Всього товарів</div>
                </div>
                <div class="bg-white p-3 dark:bg-gray-900">
                    <div class="text-xl font-bold text-success-600 dark:text-success-400">{{ $stats['products_with_seo'] }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Товари з SEO</div>
                </div>
                <div class="bg-white p-3 dark:bg-gray-900">
                    <div class="text-xl font-bold text-info-600 dark:text-info-400">{{ $stats['total_categories'] }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Всього категорій</div>
                </div>
                <div class="bg-white p-3 dark:bg-gray-900">
                    <div class="text-xl font-bold text-warning-600 dark:text-warning-400">{{ $stats['categories_with_seo'] }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Категорії з SEO</div>
                </div>
            </div>

            {{-- Progress Bars --}}
            <div class="mt-4 space-y-3">
                <div>
                    <div class="mb-1 flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Покриття товарів</span>
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ round($productsCoverage, 1) }}%</span>
                    </div>
                    <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-white/10">
                        <div class="h-2 rounded-full bg-success-500" style="width: {{ $productsCoverage }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="mb-1 flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Покриття категорій</span>
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ round($categoriesCoverage, 1) }}%</span>
                    </div>
                    <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-white/10">
                        <div class="h-2 rounded-full bg-info-500" style="width: {{ $categoriesCoverage }}%"></div>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="mt-4 flex flex-wrap items-center gap-2">
                <x-filament::button
                    tag="a"
                    href="{{ route('filament.admin.pages.seo-management') }}"
                    icon="heroicon-o-rocket-launch"
                    color="primary"
                    size="sm"
                >
                    Генерувати SEO
                </x-filament::button>
                <x-filament::button
                    tag="a"
                    href="{{ route('filament.admin.resources.seo-metas.index') }}"
                    icon="heroicon-o-chart-bar"
                    color="gray"
                    size="sm"
                >
                    Переглянути всі SEO
                </x-filament::button>
                @if($stats['total_products'] > 0 && $stats['products_with_seo'] == 0)
                    <x-filament::badge color="warning" icon="heroicon-o-exclamation-triangle">
                        Немає SEO для товарів
                    </x-filament::badge>
                @endif
            </div>

            {{-- Status Messages --}}
            @if($stats['products_with_seo'] > $stats['total_products'] || $stats['categories_with_seo'] > $stats['total_categories'])
                <div class="mt-4">
                    <x-filament::section icon="heroicon-o-exclamation-circle" icon-color="danger">
                        <x-slot name="heading">Виявлено дублікати в SEO записах</x-slot>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Товарів з SEO ({{ $stats['products_with_seo'] }}) більше ніж загальна кількість ({{ $stats['total_products'] }}).
                            Натисніть «Очистити весь SEO», а потім «Повна перегенерація» для виправлення.
                        </p>
                    </x-filament::section>
                </div>
            @elseif($stats['total_products'] > 0 && $stats['products_with_seo'] / $stats['total_products'] < 0.5)
                <div class="mt-4">
                    <x-filament::section icon="heroicon-o-exclamation-triangle" icon-color="warning">
                        <x-slot name="heading">Низьке покриття SEO</x-slot>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Поточне покриття {{ round(($stats['products_with_seo'] / $stats['total_products']) * 100, 1) }}%.
                            Рекомендуємо згенерувати SEO для всіх товарів.
                        </p>
                    </x-filament::section>
                </div>
            @elseif($stats['total_products'] > 0 && $stats['products_with_seo'] == $stats['total_products'])
                <div class="mt-4">
                    <x-filament::section icon="heroicon-o-check-circle" icon-color="success">
                        <x-slot name="heading">Відмінно</x-slot>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Всі товари мають SEO метадані.
                        </p>
                    </x-filament::section>
                </div>
            @endif
        </x-filament::section>

        {{-- Preview Section --}}
        <x-filament::section icon="heroicon-o-eye" icon-color="info">
            <x-slot name="heading">Приклади генерації</x-slot>
            <div style="display:grid;gap:1.5rem;grid-template-columns:repeat(auto-fit,minmax(340px,1fr))">
                <div class="rounded-lg p-4 ring-1 ring-gray-950/5 dark:ring-white/10">
                    <h4 class="mb-2 font-medium text-gray-950 dark:text-white">Приклад категорії</h4>
                    <p class="mb-1 text-sm text-gray-600 dark:text-gray-400"><strong>Заголовок:</strong> Смартфони | SimpleShop</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400"><strong>Опис:</strong> Великий вибір товарів у категорії Смартфони. Швидка доставка по Україні.</p>
                </div>
                <div class="rounded-lg p-4 ring-1 ring-gray-950/5 dark:ring-white/10">
                    <h4 class="mb-2 font-medium text-gray-950 dark:text-white">Приклад товару</h4>
                    <p class="mb-1 text-sm text-gray-600 dark:text-gray-400"><strong>Заголовок:</strong> Купити iPhone 15 Pro за 45000 грн | SimpleShop</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400"><strong>Опис:</strong> Купити iPhone 15 Pro за найкращою ціною 45000 грн. Новий флагман Apple.</p>
                </div>
            </div>
        </x-filament::section>

        {{-- Variable Guide --}}
        <x-filament::section icon="heroicon-o-code-bracket" icon-color="warning">
            <x-slot name="heading">Доступні змінні для шаблонів</x-slot>
            <div style="display:grid;gap:1.5rem;grid-template-columns:repeat(auto-fit,minmax(220px,1fr))">
                <div>
                    <h4 class="mb-2 font-medium text-gray-950 dark:text-white">Категорії</h4>
                    <ul class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                        <li>• <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">%s</code> — назва категорії</li>
                        <li>• <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">%d</code> — кількість товарів</li>
                        <li>• <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">%site%</code> — назва сайту</li>
                    </ul>
                </div>
                <div>
                    <h4 class="mb-2 font-medium text-gray-950 dark:text-white">Товари</h4>
                    <ul class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                        <li>• <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">%s</code> — назва товару</li>
                        <li>• <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">%price%</code> — ціна</li>
                        <li>• <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">%category%</code> — категорія</li>
                        <li>• <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">%brand%</code> — бренд</li>
                    </ul>
                </div>
                <div>
                    <h4 class="mb-2 font-medium text-gray-950 dark:text-white">Сторінки</h4>
                    <ul class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                        <li>• <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">%s</code> — назва сторінки</li>
                        <li>• <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">%site%</code> — назва сайту</li>
                        <li>• <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">%year%</code> — поточний рік</li>
                    </ul>
                </div>
            </div>
        </x-filament::section>

        {{-- SEO Templates Form --}}
        <form wire:submit="save">
            {{ $this->form }}

            <div class="mt-6 flex flex-wrap gap-4">
                <x-filament::button type="submit" icon="heroicon-o-check">
                    Зберегти шаблони
                </x-filament::button>
                <x-filament::button type="button" wire:click="resetToDefaults" color="gray" icon="heroicon-o-arrow-path">
                    Скинути до стандартних
                </x-filament::button>
            </div>
        </form>

        {{-- SEO Best Practices --}}
        <x-filament::section icon="heroicon-o-light-bulb" icon-color="info">
            <x-slot name="heading">SEO Рекомендації Google 2025</x-slot>
            <div style="display:grid;gap:1.5rem;grid-template-columns:repeat(auto-fit,minmax(340px,1fr))">
                <div>
                    <h4 class="mb-2 font-medium text-gray-950 dark:text-white">Заголовки (Title)</h4>
                    <ul class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                        <li>• Максимум 60 символів</li>
                        <li>• Ключові слова на початку</li>
                        <li>• Унікальність для кожної сторінки</li>
                        <li>• Бренд в кінці</li>
                    </ul>
                </div>
                <div>
                    <h4 class="mb-2 font-medium text-gray-950 dark:text-white">Описи (Description)</h4>
                    <ul class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                        <li>• 150-160 символів</li>
                        <li>• Заклик до дії (CTA)</li>
                        <li>• Переваги для користувача</li>
                        <li>• Ключові слова природно</li>
                    </ul>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
