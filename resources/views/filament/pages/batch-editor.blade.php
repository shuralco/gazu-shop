<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Tab Navigation --}}
        <div class="flex gap-1 border-b border-gray-200 dark:border-white/10">
            <button
                wire:click="$set('activeTab', 'products')"
                class="px-4 py-2 text-sm font-medium rounded-t-lg transition {{ $activeTab === 'products' ? 'bg-primary-500 text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5' }}"
            >
                ТОВАРИ
            </button>
            <button
                wire:click="$set('activeTab', 'categories')"
                class="px-4 py-2 text-sm font-medium rounded-t-lg transition {{ $activeTab === 'categories' ? 'bg-primary-500 text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5' }}"
            >
                КАТЕГОРІЇ
            </button>
            <button
                wire:click="$set('activeTab', 'orders')"
                class="px-4 py-2 text-sm font-medium rounded-t-lg transition {{ $activeTab === 'orders' ? 'bg-primary-500 text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5' }}"
            >
                ЗАМОВЛЕННЯ
            </button>
            <button
                wire:click="$set('activeTab', 'reviews')"
                class="px-4 py-2 text-sm font-medium rounded-t-lg transition {{ $activeTab === 'reviews' ? 'bg-primary-500 text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5' }}"
            >
                ВІДГУКИ
            </button>
            <button
                wire:click="$set('activeTab', 'journal')"
                class="px-4 py-2 text-sm font-medium rounded-t-lg transition {{ $activeTab === 'journal' ? 'bg-primary-500 text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5' }}"
            >
                ЖУРНАЛ
            </button>
        </div>

        {{-- ========== PRODUCTS TAB ========== --}}
        @if($activeTab === 'products')

        {{-- Compact Filters --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-3 mb-3">
            <div class="flex flex-wrap items-end gap-2">
                <select wire:model.live="filterCategory" class="text-xs rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-2 py-1.5 min-w-[120px]">
                    <option value="">Категорія: всі</option>
                    @foreach($this->getCategories() as $id => $title)
                    <option value="{{ $id }}">{{ $title }}</option>
                    @endforeach
                </select>
                <select wire:model.live="filterBrand" class="text-xs rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-2 py-1.5 min-w-[100px]">
                    <option value="">Бренд: всі</option>
                    @foreach($this->getBrands() as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
                <select wire:model.live="filterStatus" class="text-xs rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-2 py-1.5 min-w-[100px]">
                    <option value="">Статус: всі</option>
                    <option value="active">Активні</option>
                    <option value="inactive">Неактивні</option>
                    <option value="hit">Хіти</option>
                    <option value="new">Новинки</option>
                    <option value="sale">Акційні</option>
                </select>
                <select wire:model.live="filterStockStatus" class="text-xs rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-2 py-1.5 min-w-[100px]">
                    <option value="">Наявність: всі</option>
                    <option value="in_stock">В наявності</option>
                    <option value="out_of_stock">Немає</option>
                    <option value="preorder">Предзамовлення</option>
                </select>
                <input type="text" wire:model.live.debounce.500ms="filterManufacturer" placeholder="Виробник..." class="text-xs rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-2 py-1.5 w-28">
                <input type="number" wire:model.live.debounce.500ms="filterPriceFrom" placeholder="Ціна від" class="text-xs rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-2 py-1.5 w-20">
                <input type="number" wire:model.live.debounce.500ms="filterPriceTo" placeholder="до" class="text-xs rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-2 py-1.5 w-20">
                <input type="text" wire:model.live.debounce.500ms="filterSearch" placeholder="🔍 Пошук..." class="text-xs rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-2 py-1.5 w-36">
                <button wire:click="$toggle('showAdvancedFilters')" class="text-xs text-primary-600 dark:text-primary-400 font-medium px-2 py-1.5 hover:underline">
                    {{ $showAdvancedFilters ? '▲ Менше' : '▼ Більше' }}
                </button>
                <button wire:click="resetFilters" class="text-xs text-danger-600 dark:text-danger-400 font-medium px-2 py-1.5 hover:underline">× Скинути</button>
            </div>
            <div x-data="{ show: @entangle('showAdvancedFilters') }" x-show="show" x-cloak x-transition
                 class="flex flex-wrap items-center gap-2 mt-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                <label class="flex items-center gap-2 text-xs text-gray-700 dark:text-gray-300">
                    <input type="checkbox" wire:model.live="filterNoImage" class="fi-checkbox-input rounded text-primary-600"> Без фото
                </label>
                <label class="flex items-center gap-2 text-xs text-gray-700 dark:text-gray-300">
                    <input type="checkbox" wire:model.live="filterNoDescription" class="fi-checkbox-input rounded text-primary-600"> Без опису
                </label>
                <label class="flex items-center gap-2 text-xs text-gray-700 dark:text-gray-300">
                    <input type="checkbox" wire:model.live="filterNoSeo" class="fi-checkbox-input rounded text-primary-600"> Без SEO
                </label>
                <label class="flex items-center gap-2 text-xs text-gray-700 dark:text-gray-300">
                    <input type="checkbox" wire:model.live="filterHasVariants" class="fi-checkbox-input rounded text-primary-600"> З варіантами
                </label>
                <label class="flex items-center gap-2 text-xs text-gray-700 dark:text-gray-300">
                    <input type="checkbox" wire:model.live="filterHasGroupPrice" class="fi-checkbox-input rounded text-primary-600"> З гуртовою
                </label>
                <input type="number" wire:model.live.debounce.500ms="filterQtyFrom" placeholder="К-сть від" class="text-xs rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-2 py-1.5 w-20">
                <input type="number" wire:model.live.debounce.500ms="filterQtyTo" placeholder="К-сть до" class="text-xs rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-2 py-1.5 w-20">
                <input type="date" wire:model.live="filterDateFrom" class="text-xs rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-2 py-1.5">
                <input type="date" wire:model.live="filterDateTo" class="text-xs rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-2 py-1.5">
            </div>
        </div>

        {{-- Toolbar --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-3 mb-4">
            {{-- Row 1: Save + Selected count + Column settings --}}
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-3">
                    <button wire:click="saveChanges" class="fi-btn fi-btn-size-sm inline-flex items-center gap-1.5 rounded-lg bg-success-600 hover:bg-success-500 text-white px-4 py-2 text-sm font-medium shadow-sm transition">
                        <x-heroicon-m-check class="h-4 w-4" />
                        Зберегти
                        @if(count($editedData) > 0)
                        <span class="bg-white/20 rounded-full px-2 py-0.5 text-xs">{{ count($editedData) }}</span>
                        @endif
                    </button>
                    @if(count($selectedIds) > 0)
                    <span class="text-xs text-gray-500 dark:text-gray-400">Вибрано: <strong class="text-primary-600 dark:text-primary-400">{{ count($selectedIds) }}</strong></span>
                    @endif
                </div>

                {{-- Column visibility dropdown --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 dark:bg-white/5 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-white/10 transition">
                        <x-heroicon-m-view-columns class="h-4 w-4" />
                        Колонки
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak x-transition
                         class="absolute right-0 mt-1 w-52 bg-white dark:bg-gray-800 rounded-xl shadow-lg ring-1 ring-gray-950/5 dark:ring-white/10 p-2 z-50 max-h-80 overflow-y-auto">
                        @foreach($this->getAvailableColumns() as $key => $label)
                        <label class="flex items-center gap-2 px-2 py-1.5 text-xs hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg cursor-pointer transition">
                            <input type="checkbox" value="{{ $key }}" wire:model.live="visibleColumns" class="fi-checkbox-input rounded text-primary-600">
                            {{ $label }}
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Row 2: Action buttons as scrollable row --}}
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1" style="-ms-overflow-style: none; scrollbar-width: thin;">
                <span class="text-xs text-gray-400 dark:text-gray-500 font-medium flex-shrink-0 mr-1">ДII:</span>

                {{-- Pricing group --}}
                <button wire:click="$set('showPriceModal', true)" class="inline-flex items-center gap-1 rounded-md bg-primary-50 dark:bg-primary-400/10 text-primary-600 dark:text-primary-400 px-2.5 py-1.5 text-xs font-medium flex-shrink-0 hover:bg-primary-100 dark:hover:bg-primary-400/20 transition">
                    <x-heroicon-m-currency-dollar class="h-3.5 w-3.5" /> Ціна
                </button>
                <button wire:click="$set('showSaleModal', true)" class="inline-flex items-center gap-1 rounded-md bg-warning-50 dark:bg-warning-400/10 text-warning-600 dark:text-warning-400 px-2.5 py-1.5 text-xs font-medium flex-shrink-0 hover:bg-warning-100 dark:hover:bg-warning-400/20 transition">
                    <x-heroicon-m-tag class="h-3.5 w-3.5" /> Акція
                </button>
                <button wire:click="removeSale" wire:confirm="Зняти акцію з вибраних товарів?" class="inline-flex items-center gap-1 rounded-md bg-gray-50 dark:bg-gray-400/10 text-gray-600 dark:text-gray-400 px-2.5 py-1.5 text-xs font-medium flex-shrink-0 hover:bg-gray-100 dark:hover:bg-gray-400/20 transition">
                    <x-heroicon-m-arrow-uturn-left class="h-3.5 w-3.5" /> Зняти
                </button>
                <button wire:click="$set('showGroupPriceModal', true)" class="inline-flex items-center gap-1 rounded-md bg-success-50 dark:bg-success-400/10 text-success-600 dark:text-success-400 px-2.5 py-1.5 text-xs font-medium flex-shrink-0 hover:bg-success-100 dark:hover:bg-success-400/20 transition">
                    <x-heroicon-m-user-group class="h-3.5 w-3.5" /> Гуртові
                </button>

                <span class="w-px h-5 bg-gray-200 dark:bg-gray-700 flex-shrink-0"></span>

                {{-- Content group --}}
                <button wire:click="$set('showStatusModal', true)" class="inline-flex items-center gap-1 rounded-md bg-gray-50 dark:bg-gray-400/10 text-gray-600 dark:text-gray-400 px-2.5 py-1.5 text-xs font-medium flex-shrink-0 hover:bg-gray-100 dark:hover:bg-gray-400/20 transition">
                    <x-heroicon-m-eye class="h-3.5 w-3.5" /> Статус
                </button>
                <button wire:click="$set('showCategoryModal', true)" class="inline-flex items-center gap-1 rounded-md bg-gray-50 dark:bg-gray-400/10 text-gray-600 dark:text-gray-400 px-2.5 py-1.5 text-xs font-medium flex-shrink-0 hover:bg-gray-100 dark:hover:bg-gray-400/20 transition">
                    <x-heroicon-m-folder class="h-3.5 w-3.5" /> Категорія
                </button>
                <button wire:click="$set('showBrandModal', true)" class="inline-flex items-center gap-1 rounded-md bg-gray-50 dark:bg-gray-400/10 text-gray-600 dark:text-gray-400 px-2.5 py-1.5 text-xs font-medium flex-shrink-0 hover:bg-gray-100 dark:hover:bg-gray-400/20 transition">
                    <x-heroicon-m-building-storefront class="h-3.5 w-3.5" /> Бренд
                </button>
                <button wire:click="$set('showFilterModal', true)" class="inline-flex items-center gap-1 rounded-md bg-gray-50 dark:bg-gray-400/10 text-gray-600 dark:text-gray-400 px-2.5 py-1.5 text-xs font-medium flex-shrink-0 hover:bg-gray-100 dark:hover:bg-gray-400/20 transition">
                    <x-heroicon-m-funnel class="h-3.5 w-3.5" /> Фільтри
                </button>

                <span class="w-px h-5 bg-gray-200 dark:bg-gray-700 flex-shrink-0"></span>

                {{-- SEO & Tools group --}}
                <button wire:click="$set('showSeoModal', true)" class="inline-flex items-center gap-1 rounded-md bg-purple-50 dark:bg-purple-400/10 text-purple-600 dark:text-purple-400 px-2.5 py-1.5 text-xs font-medium flex-shrink-0 hover:bg-purple-100 dark:hover:bg-purple-400/20 transition">
                    <x-heroicon-m-magnifying-glass class="h-3.5 w-3.5" /> SEO
                </button>
                <button wire:click="$set('showSearchReplaceModal', true)" class="inline-flex items-center gap-1 rounded-md bg-gray-50 dark:bg-gray-400/10 text-gray-600 dark:text-gray-400 px-2.5 py-1.5 text-xs font-medium flex-shrink-0 hover:bg-gray-100 dark:hover:bg-gray-400/20 transition">
                    <x-heroicon-m-magnifying-glass class="h-3.5 w-3.5" /> Пошук
                </button>
                <button wire:click="$set('showWeightModal', true)" class="inline-flex items-center gap-1 rounded-md bg-gray-50 dark:bg-gray-400/10 text-gray-600 dark:text-gray-400 px-2.5 py-1.5 text-xs font-medium flex-shrink-0 hover:bg-gray-100 dark:hover:bg-gray-400/20 transition">
                    <x-heroicon-m-scale class="h-3.5 w-3.5" /> Вага
                </button>
                <button wire:click="duplicateSelected" wire:confirm="Дублювати вибрані товари?" class="inline-flex items-center gap-1 rounded-md bg-gray-50 dark:bg-gray-400/10 text-gray-600 dark:text-gray-400 px-2.5 py-1.5 text-xs font-medium flex-shrink-0 hover:bg-gray-100 dark:hover:bg-gray-400/20 transition">
                    <x-heroicon-m-document-duplicate class="h-3.5 w-3.5" /> Копія
                </button>
                <button wire:click="openVariantModal" class="inline-flex items-center gap-1 rounded-md bg-indigo-50 dark:bg-indigo-400/10 text-indigo-600 dark:text-indigo-400 px-2.5 py-1.5 text-xs font-medium flex-shrink-0 hover:bg-indigo-100 dark:hover:bg-indigo-400/20 transition">
                    <x-heroicon-m-squares-plus class="h-3.5 w-3.5" /> Варіанти
                </button>

                <span class="w-px h-5 bg-gray-200 dark:bg-gray-700 flex-shrink-0"></span>

                {{-- Import/Export/Delete group --}}
                <button wire:click="openImportModal" class="inline-flex items-center gap-1 rounded-md bg-purple-50 dark:bg-purple-400/10 text-purple-600 dark:text-purple-400 px-2.5 py-1.5 text-xs font-medium flex-shrink-0 hover:bg-purple-100 dark:hover:bg-purple-400/20 transition">
                    <x-heroicon-m-arrow-up-tray class="h-3.5 w-3.5" /> Імпорт
                </button>
                <button wire:click="exportSelected" class="inline-flex items-center gap-1 rounded-md bg-gray-50 dark:bg-gray-400/10 text-gray-600 dark:text-gray-400 px-2.5 py-1.5 text-xs font-medium flex-shrink-0 hover:bg-gray-100 dark:hover:bg-gray-400/20 transition">
                    <x-heroicon-m-arrow-down-tray class="h-3.5 w-3.5" /> Експорт
                </button>
                <button wire:click="deleteSelected" wire:confirm="Видалити вибрані товари? Цю дію не можна скасувати!" class="inline-flex items-center gap-1 rounded-md bg-danger-50 dark:bg-danger-400/10 text-danger-600 dark:text-danger-400 px-2.5 py-1.5 text-xs font-medium flex-shrink-0 hover:bg-danger-100 dark:hover:bg-danger-400/20 transition">
                    <x-heroicon-m-trash class="h-3.5 w-3.5" /> Видалити
                </button>
            </div>
        </div>

        {{-- Products Grid --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 dark:divide-white/5 text-start">
                    <thead class="bg-gray-50 dark:bg-white/5">
                        <tr>
                            <th class="fi-ta-header-cell px-3 py-2.5 w-10">
                                <input type="checkbox" wire:model.live="selectAll" wire:click="toggleSelectAll" class="fi-checkbox-input rounded border-gray-300 text-primary-600 dark:border-white/10 dark:bg-white/5">
                            </th>
                            @if(in_array('id', $visibleColumns))<th class="fi-ta-header-cell px-3 py-2.5 text-xs font-medium text-gray-600 dark:text-gray-400 text-start w-16">ID</th>@endif
                            @if(in_array('title', $visibleColumns))<th class="fi-ta-header-cell px-3 py-2.5 text-xs font-medium text-gray-600 dark:text-gray-400 text-start min-w-[200px]">Назва</th>@endif
                            @if(in_array('sku', $visibleColumns))<th class="fi-ta-header-cell px-3 py-2.5 text-xs font-medium text-gray-600 dark:text-gray-400 text-start w-28">SKU</th>@endif
                            @if(in_array('price', $visibleColumns))<th class="fi-ta-header-cell px-3 py-2.5 text-xs font-medium text-gray-600 dark:text-gray-400 text-start w-28">Ціна</th>@endif
                            @if(in_array('old_price', $visibleColumns))<th class="fi-ta-header-cell px-3 py-2.5 text-xs font-medium text-gray-600 dark:text-gray-400 text-start w-28">Стара ціна</th>@endif
                            @if(in_array('quantity', $visibleColumns))<th class="fi-ta-header-cell px-3 py-2.5 text-xs font-medium text-gray-600 dark:text-gray-400 text-start w-20">К-сть</th>@endif
                            @if(in_array('stock_status', $visibleColumns))<th class="fi-ta-header-cell px-3 py-2.5 text-xs font-medium text-gray-600 dark:text-gray-400 text-start w-36">Наявність</th>@endif
                            @if(in_array('is_active', $visibleColumns))<th class="fi-ta-header-cell px-3 py-2.5 text-xs font-medium text-gray-600 dark:text-gray-400 text-center w-14">Акт</th>@endif
                            @if(in_array('is_hit', $visibleColumns))<th class="fi-ta-header-cell px-3 py-2.5 text-xs font-medium text-gray-600 dark:text-gray-400 text-center w-14">Хіт</th>@endif
                            @if(in_array('is_new', $visibleColumns))<th class="fi-ta-header-cell px-3 py-2.5 text-xs font-medium text-gray-600 dark:text-gray-400 text-center w-14">Нов</th>@endif
                            @if(in_array('category', $visibleColumns))<th class="fi-ta-header-cell px-3 py-2.5 text-xs font-medium text-gray-600 dark:text-gray-400 text-start w-40">Категорія</th>@endif
                            @if(in_array('brand', $visibleColumns))<th class="fi-ta-header-cell px-3 py-2.5 text-xs font-medium text-gray-600 dark:text-gray-400 text-start w-36">Бренд</th>@endif
                            @if(in_array('manufacturer', $visibleColumns))<th class="fi-ta-header-cell px-3 py-2.5 text-xs font-medium text-gray-600 dark:text-gray-400 text-start w-32">Виробник</th>@endif
                            @if(in_array('weight', $visibleColumns))<th class="fi-ta-header-cell px-3 py-2.5 text-xs font-medium text-gray-600 dark:text-gray-400 text-start w-20">Вага</th>@endif
                            @if(in_array('rating', $visibleColumns))<th class="fi-ta-header-cell px-3 py-2.5 text-xs font-medium text-gray-600 dark:text-gray-400 text-start w-16">&#9733;</th>@endif
                            @if(in_array('reviews_count', $visibleColumns))<th class="fi-ta-header-cell px-3 py-2.5 text-xs font-medium text-gray-600 dark:text-gray-400 text-start w-16">Відг</th>@endif
                            @if(in_array('created_at', $visibleColumns))<th class="fi-ta-header-cell px-3 py-2.5 text-xs font-medium text-gray-600 dark:text-gray-400 text-start w-28">Створено</th>@endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                        @forelse($this->getProducts() as $product)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition {{ isset($editedData[$product->id]) ? 'bg-yellow-50/50 dark:bg-yellow-900/10' : '' }}" wire:key="product-{{ $product->id }}">
                            <td class="px-3 py-1.5 text-center">
                                <input type="checkbox" value="{{ $product->id }}" wire:model.live="selectedIds" class="fi-checkbox-input rounded border-gray-300 text-primary-600 dark:border-white/10 dark:bg-white/5">
                            </td>

                            @if(in_array('id', $visibleColumns))
                            <td class="px-3 py-1.5 text-xs text-gray-500 dark:text-gray-400 font-mono">{{ $product->id }}</td>
                            @endif

                            @if(in_array('title', $visibleColumns))
                            <td class="px-3 py-1.5 relative group/title">
                                <input type="text" value="{{ $editedData[$product->id]['title'] ?? $product->title }}"
                                    wire:change="updateField({{ $product->id }}, 'title', $event.target.value)"
                                    class="fi-input w-full text-sm border-0 bg-transparent px-1.5 py-1 focus:ring-1 focus:ring-primary-500 rounded transition {{ isset($editedData[$product->id]['title']) ? 'bg-yellow-100 dark:bg-yellow-900/30 ring-1 ring-yellow-400/50' : 'hover:bg-gray-50 dark:hover:bg-white/5' }}">
                                @if($product->image)
                                <div class="hidden group-hover/title:block absolute z-50 left-0 top-full mt-1 w-32 h-32 bg-white dark:bg-gray-800 rounded-lg shadow-xl ring-1 ring-gray-200 dark:ring-white/10 p-1">
                                    <img src="{{ asset($product->getImage()) }}" alt="" class="w-full h-full object-contain">
                                </div>
                                @endif
                            </td>
                            @endif

                            @if(in_array('sku', $visibleColumns))
                            <td class="px-3 py-1.5">
                                <input type="text" value="{{ $editedData[$product->id]['sku'] ?? $product->sku }}"
                                    wire:change="updateField({{ $product->id }}, 'sku', $event.target.value)"
                                    class="fi-input w-full text-xs font-mono border-0 bg-transparent px-1.5 py-1 focus:ring-1 focus:ring-primary-500 rounded transition {{ isset($editedData[$product->id]['sku']) ? 'bg-yellow-100 dark:bg-yellow-900/30 ring-1 ring-yellow-400/50' : 'hover:bg-gray-50 dark:hover:bg-white/5' }}">
                            </td>
                            @endif

                            @if(in_array('price', $visibleColumns))
                            <td class="px-3 py-1.5">
                                <input type="number" step="0.01" value="{{ $editedData[$product->id]['price'] ?? $product->price }}"
                                    wire:change="updateField({{ $product->id }}, 'price', $event.target.value)"
                                    class="fi-input w-full text-sm border-0 bg-transparent px-1.5 py-1 focus:ring-1 focus:ring-primary-500 rounded text-right font-medium transition {{ isset($editedData[$product->id]['price']) ? 'bg-yellow-100 dark:bg-yellow-900/30 ring-1 ring-yellow-400/50' : 'hover:bg-gray-50 dark:hover:bg-white/5' }}">
                            </td>
                            @endif

                            @if(in_array('old_price', $visibleColumns))
                            <td class="px-3 py-1.5">
                                <input type="number" step="0.01" value="{{ $editedData[$product->id]['old_price'] ?? $product->old_price }}"
                                    wire:change="updateField({{ $product->id }}, 'old_price', $event.target.value)"
                                    class="fi-input w-full text-xs border-0 bg-transparent px-1.5 py-1 focus:ring-1 focus:ring-primary-500 rounded text-right transition {{ isset($editedData[$product->id]['old_price']) ? 'bg-yellow-100 dark:bg-yellow-900/30 ring-1 ring-yellow-400/50' : 'hover:bg-gray-50 dark:hover:bg-white/5' }}">
                            </td>
                            @endif

                            @if(in_array('quantity', $visibleColumns))
                            <td class="px-3 py-1.5">
                                <input type="number" value="{{ $editedData[$product->id]['quantity'] ?? $product->quantity }}"
                                    wire:change="updateField({{ $product->id }}, 'quantity', $event.target.value)"
                                    class="fi-input w-20 text-sm border-0 bg-transparent px-1.5 py-1 focus:ring-1 focus:ring-primary-500 rounded text-center transition {{ isset($editedData[$product->id]['quantity']) ? 'bg-yellow-100 dark:bg-yellow-900/30 ring-1 ring-yellow-400/50' : 'hover:bg-gray-50 dark:hover:bg-white/5' }}">
                            </td>
                            @endif

                            @if(in_array('stock_status', $visibleColumns))
                            <td class="px-3 py-1.5">
                                @php $stockVal = $editedData[$product->id]['stock_status'] ?? $product->stock_status ?? 'in_stock'; @endphp
                                <select wire:change="updateField({{ $product->id }}, 'stock_status', $event.target.value)"
                                    class="text-xs w-full py-1 px-1.5 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-200 border border-gray-200 dark:border-gray-600 {{ isset($editedData[$product->id]['stock_status']) ? 'bg-yellow-100 dark:bg-yellow-900/30 border-yellow-400' : '' }}">
                                    <option value="in_stock" {{ $stockVal === 'in_stock' ? 'selected' : '' }}>В наявності</option>
                                    <option value="out_of_stock" {{ $stockVal === 'out_of_stock' ? 'selected' : '' }}>Немає</option>
                                    <option value="preorder" {{ $stockVal === 'preorder' ? 'selected' : '' }}>Предзамовл.</option>
                                </select>
                            </td>
                            @endif

                            @if(in_array('is_active', $visibleColumns))
                            <td class="px-3 py-1.5 text-center">
                                <input type="checkbox" {{ ($editedData[$product->id]['is_active'] ?? $product->is_active) ? 'checked' : '' }}
                                    wire:change="updateField({{ $product->id }}, 'is_active', $event.target.checked)"
                                    class="fi-checkbox-input rounded border-gray-300 text-primary-600 dark:border-white/10 dark:bg-white/5 {{ isset($editedData[$product->id]['is_active']) ? 'ring-2 ring-yellow-400' : '' }}">
                            </td>
                            @endif

                            @if(in_array('is_hit', $visibleColumns))
                            <td class="px-3 py-1.5 text-center">
                                <input type="checkbox" {{ ($editedData[$product->id]['is_hit'] ?? $product->is_hit) ? 'checked' : '' }}
                                    wire:change="updateField({{ $product->id }}, 'is_hit', $event.target.checked)"
                                    class="fi-checkbox-input rounded border-gray-300 text-primary-600 dark:border-white/10 dark:bg-white/5 {{ isset($editedData[$product->id]['is_hit']) ? 'ring-2 ring-yellow-400' : '' }}">
                            </td>
                            @endif

                            @if(in_array('is_new', $visibleColumns))
                            <td class="px-3 py-1.5 text-center">
                                <input type="checkbox" {{ ($editedData[$product->id]['is_new'] ?? $product->is_new) ? 'checked' : '' }}
                                    wire:change="updateField({{ $product->id }}, 'is_new', $event.target.checked)"
                                    class="fi-checkbox-input rounded border-gray-300 text-primary-600 dark:border-white/10 dark:bg-white/5 {{ isset($editedData[$product->id]['is_new']) ? 'ring-2 ring-yellow-400' : '' }}">
                            </td>
                            @endif

                            @if(in_array('category', $visibleColumns))
                            <td class="px-3 py-1.5">
                                @php $catCurrent = $editedData[$product->id]['category_id'] ?? $product->category_id; @endphp
                                <div x-data="{
                                    open: false, search: '', selected: '{{ $catCurrent }}',
                                    label: '{{ addslashes($this->getCategories()[$catCurrent] ?? '—') }}',
                                    items: {{ json_encode(collect($this->getCategories())->map(fn($t,$i) => ['id'=>$i,'title'=>$t])->values()) }},
                                    get filtered() { return this.search ? this.items.filter(i => i.title.toLowerCase().includes(this.search.toLowerCase())) : this.items; },
                                    pick(item) { this.selected = item.id; this.label = item.title; this.open = false; this.search = '';
                                        $wire.updateField({{ $product->id }}, 'category_id', item.id); }
                                }" class="relative">
                                    <button @click="open = !open" type="button"
                                        class="w-full text-left text-xs px-1.5 py-1 rounded truncate max-w-[140px] {{ isset($editedData[$product->id]['category_id']) ? 'bg-yellow-100 dark:bg-yellow-900/30 ring-1 ring-yellow-400/50' : 'hover:bg-gray-100 dark:hover:bg-white/10' }}"
                                        x-text="label"></button>
                                    <div x-show="open" @click.away="open = false; search = ''" x-cloak x-transition
                                         class="absolute z-50 mt-1 w-56 bg-white dark:bg-gray-800 rounded-lg shadow-lg ring-1 ring-gray-900/10 dark:ring-white/10 overflow-hidden">
                                        <input x-model="search" placeholder="Пошук..." class="w-full px-3 py-2 text-xs border-b border-gray-200 dark:border-gray-700 bg-transparent dark:text-white outline-none">
                                        <div class="max-h-48 overflow-y-auto">
                                            <template x-for="item in filtered" :key="item.id">
                                                <button @click="pick(item)" type="button"
                                                    class="block w-full text-left px-3 py-1.5 text-xs hover:bg-primary-50 dark:hover:bg-primary-900/20 dark:text-gray-200"
                                                    :class="selected == item.id ? 'bg-primary-50 dark:bg-primary-900/30 font-bold' : ''"
                                                    x-text="item.title"></button>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            @endif

                            @if(in_array('brand', $visibleColumns))
                            <td class="px-3 py-1.5">
                                @php $brandCurrent = $editedData[$product->id]['brand_id'] ?? $product->brand_id; @endphp
                                <div x-data="{
                                    open: false, search: '', selected: '{{ $brandCurrent }}',
                                    label: '{{ addslashes($this->getBrands()[$brandCurrent] ?? '—') }}',
                                    items: {{ json_encode(collect($this->getBrands())->map(fn($n,$i) => ['id'=>$i,'name'=>$n])->values()) }},
                                    get filtered() { return this.search ? this.items.filter(i => i.name.toLowerCase().includes(this.search.toLowerCase())) : this.items; },
                                    pick(item) { this.selected = item.id; this.label = item.name; this.open = false; this.search = '';
                                        $wire.updateField({{ $product->id }}, 'brand_id', item.id); }
                                }" class="relative">
                                    <button @click="open = !open" type="button"
                                        class="w-full text-left text-xs px-1.5 py-1 rounded truncate max-w-[120px] {{ isset($editedData[$product->id]['brand_id']) ? 'bg-yellow-100 dark:bg-yellow-900/30 ring-1 ring-yellow-400/50' : 'hover:bg-gray-100 dark:hover:bg-white/10' }}"
                                        x-text="label || '—'"></button>
                                    <div x-show="open" @click.away="open = false; search = ''" x-cloak x-transition
                                         class="absolute z-50 mt-1 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg ring-1 ring-gray-900/10 dark:ring-white/10 overflow-hidden">
                                        <input x-model="search" placeholder="Пошук..." class="w-full px-3 py-2 text-xs border-b border-gray-200 dark:border-gray-700 bg-transparent dark:text-white outline-none">
                                        <div class="max-h-48 overflow-y-auto">
                                            <button @click="pick({id:'',name:'—'})" type="button" class="block w-full text-left px-3 py-1.5 text-xs hover:bg-gray-100 dark:hover:bg-white/10 dark:text-gray-400">—</button>
                                            <template x-for="item in filtered" :key="item.id">
                                                <button @click="pick(item)" type="button"
                                                    class="block w-full text-left px-3 py-1.5 text-xs hover:bg-primary-50 dark:hover:bg-primary-900/20 dark:text-gray-200"
                                                    :class="selected == item.id ? 'bg-primary-50 dark:bg-primary-900/30 font-bold' : ''"
                                                    x-text="item.name"></button>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            @endif

                            @if(in_array('manufacturer', $visibleColumns))
                            <td class="px-3 py-1.5">
                                <input type="text" value="{{ $editedData[$product->id]['manufacturer'] ?? $product->manufacturer }}"
                                    wire:change="updateField({{ $product->id }}, 'manufacturer', $event.target.value)"
                                    class="fi-input text-xs border-0 bg-transparent w-full px-1.5 py-1 rounded transition {{ isset($editedData[$product->id]['manufacturer']) ? 'bg-yellow-100 dark:bg-yellow-900/30 ring-1 ring-yellow-400/50' : 'hover:bg-gray-50 dark:hover:bg-white/5' }}">
                            </td>
                            @endif

                            @if(in_array('weight', $visibleColumns))
                            <td class="px-3 py-1.5">
                                <input type="number" step="0.001" value="{{ $editedData[$product->id]['weight'] ?? $product->weight }}"
                                    wire:change="updateField({{ $product->id }}, 'weight', $event.target.value)"
                                    class="fi-input w-20 text-xs border-0 bg-transparent px-1.5 py-1 text-right rounded transition {{ isset($editedData[$product->id]['weight']) ? 'bg-yellow-100 dark:bg-yellow-900/30 ring-1 ring-yellow-400/50' : 'hover:bg-gray-50 dark:hover:bg-white/5' }}">
                            </td>
                            @endif

                            @if(in_array('rating', $visibleColumns))
                            <td class="px-3 py-1.5 text-xs text-gray-500 dark:text-gray-400">{{ number_format($product->rating ?? 0, 1) }}</td>
                            @endif

                            @if(in_array('reviews_count', $visibleColumns))
                            <td class="px-3 py-1.5 text-xs text-gray-500 dark:text-gray-400">{{ $product->reviews_count ?? 0 }}</td>
                            @endif

                            @if(in_array('created_at', $visibleColumns))
                            <td class="px-3 py-1.5 text-xs text-gray-500 dark:text-gray-400">{{ $product->created_at?->format('d.m.Y') }}</td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="20" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                <x-heroicon-o-inbox class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-600 mb-2" />
                                Товарів не знайдено
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-200 dark:border-white/5">
                {{ $this->getProducts()->links() }}
            </div>
        </div>

        {{-- ===== PRODUCT MODALS ===== --}}

        {{-- Price Modal --}}
        @if($showPriceModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" x-data x-on:keydown.escape.window="$wire.set('showPriceModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-gray-900">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Масова зміна ціни</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Тип операції</label>
                        <select wire:model="priceType" class="fi-select-input block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                            <option value="set">Встановити фіксовану ціну</option>
                            <option value="increase">Збільшити на суму</option>
                            <option value="decrease">Зменшити на суму</option>
                            <option value="increase_percent">Збільшити на %</option>
                            <option value="decrease_percent">Зменшити на %</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Значення</label>
                        <input type="number" wire:model="priceValue" step="0.01" min="0" class="fi-input block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('showPriceModal', false)" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5">Скасувати</button>
                    <button wire:click="previewPrice" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-500">Попередній перегляд</button>
                </div>
            </div>
        </div>
        @endif

        {{-- Sale Modal --}}
        @if($showSaleModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" x-data x-on:keydown.escape.window="$wire.set('showSaleModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-gray-900">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Встановити акцію</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Тип знижки</label>
                        <select wire:model="saleType" class="fi-select-input block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                            <option value="percent">Відсоток (%)</option>
                            <option value="fixed">Фіксована сума (грн)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Значення знижки</label>
                        <input type="number" wire:model="saleValue" step="0.01" min="0" class="fi-input block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="removeSale" class="rounded-lg px-4 py-2 text-sm font-medium text-danger-600 hover:bg-danger-50 dark:text-danger-400 dark:hover:bg-danger-400/10">Зняти акцію</button>
                    <button wire:click="$set('showSaleModal', false)" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5">Скасувати</button>
                    <button wire:click="previewSale" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-500">Попередній перегляд</button>
                </div>
            </div>
        </div>
        @endif

        {{-- Group Price Modal --}}
        @if($showGroupPriceModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" x-data x-on:keydown.escape.window="$wire.set('showGroupPriceModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-gray-900">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Гуртові ціни</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Група клієнтів</label>
                        <select wire:model="groupPriceGroupId" class="fi-select-input block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                            <option value="">Виберіть групу</option>
                            @foreach($this->getCustomerGroups() as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Тип ціни</label>
                        <select wire:model="groupPriceType" class="fi-select-input block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                            <option value="percent">Знижка від основної ціни (%)</option>
                            <option value="fixed">Фіксована ціна (грн)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Значення</label>
                        <input type="number" wire:model="groupPriceValue" step="0.01" min="0" class="fi-input block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('showGroupPriceModal', false)" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5">Скасувати</button>
                    <button wire:click="previewGroupPrice" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-500">Попередній перегляд</button>
                </div>
            </div>
        </div>
        @endif

        {{-- Status Modal --}}
        @if($showStatusModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" x-data x-on:keydown.escape.window="$wire.set('showStatusModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-gray-900">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Масова зміна статусу</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Поле</label>
                        <select wire:model="statusField" class="fi-select-input block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                            <option value="is_active">Активність</option>
                            <option value="is_hit">Хіт продажів</option>
                            <option value="is_new">Новинка</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Значення</label>
                        <select wire:model="statusValue" class="fi-select-input block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                            <option value="1">Увімкнено</option>
                            <option value="0">Вимкнено</option>
                        </select>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('showStatusModal', false)" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5">Скасувати</button>
                    <button wire:click="applyStatus" wire:confirm="Змінити статус для {{ count($selectedIds) }} товарів?" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-500">Застосувати</button>
                </div>
            </div>
        </div>
        @endif

        {{-- Category Modal --}}
        @if($showCategoryModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" x-data x-on:keydown.escape.window="$wire.set('showCategoryModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-gray-900">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Змінити категорію</h3>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Нова категорія</label>
                    <select wire:model="newCategoryId" class="fi-select-input block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                        <option value="">Виберіть категорію</option>
                        @foreach($this->getCategories() as $id => $title)
                            <option value="{{ $id }}">{{ $title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('showCategoryModal', false)" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5">Скасувати</button>
                    <button wire:click="applyCategory" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-500">Застосувати</button>
                </div>
            </div>
        </div>
        @endif

        {{-- Brand Modal --}}
        @if($showBrandModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" x-data x-on:keydown.escape.window="$wire.set('showBrandModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-gray-900">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Змінити бренд/виробника</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Бренд</label>
                        <select wire:model="newBrandId" class="fi-select-input block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                            <option value="">Не змінювати</option>
                            @foreach($this->getBrands() as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Виробник (текст)</label>
                        <input type="text" wire:model="newManufacturer" placeholder="Не змінювати" class="fi-input block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('showBrandModal', false)" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5">Скасувати</button>
                    <button wire:click="applyBrand" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-500">Застосувати</button>
                </div>
            </div>
        </div>
        @endif

        {{-- Filter Modal --}}
        @if($showFilterModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" x-data x-on:keydown.escape.window="$wire.set('showFilterModal', false)">
            <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl dark:bg-gray-900">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Масове управління фільтрами</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Дія</label>
                        <select wire:model="filterAction" class="fi-select-input block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                            <option value="attach">Додати фільтри</option>
                            <option value="detach">Видалити фільтри</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Фільтри</label>
                        <div class="max-h-60 overflow-y-auto space-y-3 rounded-lg border border-gray-200 dark:border-white/10 p-3">
                            @foreach($this->getFilterGroups() as $group)
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1">{{ $group->name }}</p>
                                    <div class="space-y-1">
                                        @foreach($group->filters as $filter)
                                            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                                                <input type="checkbox" value="{{ $filter->id }}" wire:model="selectedFilterIds" class="fi-checkbox-input rounded border-gray-300 text-primary-600 dark:border-white/10 dark:bg-white/5">
                                                {{ $filter->name }}
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('showFilterModal', false)" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5">Скасувати</button>
                    <button wire:click="applyFilters" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-500">Застосувати</button>
                </div>
            </div>
        </div>
        @endif

        {{-- Search & Replace Modal --}}
        @if($showSearchReplaceModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" x-data x-on:keydown.escape.window="$wire.set('showSearchReplaceModal', false)">
            <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl dark:bg-gray-900">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Пошук та заміна</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Поле</label>
                        <select wire:model="srField" class="fi-select-input block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                            <option value="title">Назва</option>
                            <option value="excerpt">Короткий опис</option>
                            <option value="content">Повний опис</option>
                            <option value="meta_title">SEO Title</option>
                            <option value="meta_description">SEO Description</option>
                            <option value="manufacturer">Виробник</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Знайти</label>
                        <input type="text" wire:model="srSearch" class="fi-input block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white" placeholder="Текст для пошуку...">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Замінити на</label>
                        <input type="text" wire:model="srReplace" class="fi-input block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white" placeholder="Текст заміни...">
                    </div>
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                            <input type="checkbox" wire:model="srCaseSensitive" class="fi-checkbox-input rounded border-gray-300 text-primary-600 dark:border-white/10 dark:bg-white/5">
                            Враховувати регістр
                        </label>
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                            <input type="checkbox" wire:model="srUseRegex" class="fi-checkbox-input rounded border-gray-300 text-primary-600 dark:border-white/10 dark:bg-white/5">
                            Regex
                        </label>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('showSearchReplaceModal', false)" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5">Скасувати</button>
                    <button wire:click="previewSR" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-500">Знайти</button>
                </div>
            </div>
        </div>
        @endif

        {{-- Weight Modal --}}
        @if($showWeightModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" x-data x-on:keydown.escape.window="$wire.set('showWeightModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-gray-900">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Вага та розміри</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Вага (кг)</label>
                        <input type="number" wire:model="newWeight" step="0.001" min="0" class="fi-input block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white" placeholder="0.000">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Розміри (Д x Ш x В)</label>
                        <input type="text" wire:model="newDimensions" class="fi-input block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white" placeholder="10x20x30">
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('showWeightModal', false)" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5">Скасувати</button>
                    <button wire:click="applyWeight" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-500">Застосувати</button>
                </div>
            </div>
        </div>
        @endif

        {{-- Import Modal (3-step wizard) --}}
        @if($showImportModal)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" x-data x-on:keydown.escape.window="$wire.set('showImportModal', false)">
            <div class="bg-white dark:bg-gray-800 rounded-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto p-6">
                {{-- Header --}}
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Імпорт CSV</h3>
                    <button wire:click="$set('showImportModal', false)" class="text-gray-400 hover:text-gray-900 dark:hover:text-white text-xl" aria-label="Закрити">&times;</button>
                </div>

                {{-- Step indicators --}}
                <div class="flex items-center gap-2 mb-6">
                    @foreach([1 => 'Завантаження', 2 => 'Налаштування', 3 => 'Результат'] as $step => $label)
                    <div class="flex items-center gap-2 {{ $step < 3 ? 'flex-1' : '' }}">
                        <div class="flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold transition
                            {{ $importStep >= $step ? 'bg-primary-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400' }}">
                            @if($importStep > $step)
                                <x-heroicon-m-check class="h-4 w-4" />
                            @else
                                {{ $step }}
                            @endif
                        </div>
                        <span class="text-xs font-medium {{ $importStep >= $step ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500' }}">{{ $label }}</span>
                        @if($step < 3)
                        <div class="flex-1 h-px {{ $importStep > $step ? 'bg-primary-400' : 'bg-gray-200 dark:bg-gray-700' }}"></div>
                        @endif
                    </div>
                    @endforeach
                </div>

                {{-- Step 1: File upload --}}
                @if($importStep === 1)
                <div class="space-y-4">
                    <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-8 text-center hover:border-primary-400 dark:hover:border-primary-500 transition">
                        <x-heroicon-o-arrow-up-tray class="mx-auto h-10 w-10 text-gray-400 dark:text-gray-500 mb-3" />
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">Виберіть CSV файл для імпорту товарів</p>
                        <input type="file" wire:model="importFile" accept=".csv,.txt"
                            class="fi-input block w-full max-w-sm mx-auto rounded-lg border-gray-300 text-sm shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-white p-2">
                        <div wire:loading wire:target="importFile" class="text-sm text-primary-600 dark:text-primary-400 mt-3 flex items-center justify-center gap-2">
                            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            Завантаження файлу...
                        </div>
                    </div>
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3">
                        <p class="text-xs text-blue-700 dark:text-blue-300 font-medium mb-1">Вимоги до файлу:</p>
                        <ul class="text-xs text-blue-600 dark:text-blue-400 space-y-0.5 list-disc list-inside">
                            <li>Формат CSV з роздільником ","</li>
                            <li>Перший рядок -- заголовки колонок</li>
                            <li>Кодування UTF-8</li>
                            <li>Для оновлення існуючих товарів використовується поле SKU</li>
                        </ul>
                    </div>
                </div>
                @endif

                {{-- Step 2: Preview + Column mapping --}}
                @if($importStep === 2)
                <div class="space-y-4">
                    {{-- File info --}}
                    <div class="flex items-center justify-between bg-gray-50 dark:bg-white/5 rounded-lg p-3">
                        <div class="flex items-center gap-2">
                            <x-heroicon-m-document-text class="h-5 w-5 text-gray-400" />
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $importTotalRows }} рядків знайдено</span>
                        </div>
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                            <input type="checkbox" wire:model="importUpdateExisting" class="fi-checkbox-input rounded border-gray-300 text-primary-600 dark:border-white/10 dark:bg-white/5">
                            Оновлювати існуючі (за SKU)
                        </label>
                    </div>

                    {{-- Column mapping --}}
                    <div>
                        <h4 class="font-semibold text-sm text-gray-900 dark:text-white mb-3">Відповідність колонок</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            @php $fieldLabels = $this->getImportFieldLabels(); @endphp
                            @foreach($importHeaders as $i => $header)
                            <div class="flex items-center gap-2 bg-gray-50 dark:bg-white/5 rounded-lg px-3 py-2">
                                <span class="text-xs font-mono bg-white dark:bg-gray-700 px-2 py-1 rounded shadow-sm text-gray-700 dark:text-gray-300 min-w-[80px] truncate" title="{{ $header }}">{{ Str::limit($header, 18) }}</span>
                                <x-heroicon-m-arrow-right class="h-3.5 w-3.5 text-gray-400 flex-shrink-0" />
                                <select wire:model="importMapping.{{ $i }}" class="fi-select-input rounded-lg border-gray-300 text-xs shadow-sm flex-1 dark:border-white/10 dark:bg-white/5 dark:text-white py-1.5">
                                    <option value="skip">-- Пропустити --</option>
                                    @foreach($fieldLabels as $field => $label)
                                    <option value="{{ $field }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Preview table --}}
                    @if(!empty($importPreview))
                    <div>
                        <h4 class="font-semibold text-sm text-gray-900 dark:text-white mb-2">Перегляд (перші {{ count($importPreview) }} рядків)</h4>
                        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-white/10">
                            <table class="w-full text-xs border-collapse">
                                <thead>
                                    <tr>
                                        <th class="border-b border-r border-gray-200 dark:border-white/10 p-2 bg-gray-100 dark:bg-white/5 text-gray-500 dark:text-gray-400 text-center w-10">#</th>
                                        @foreach($importHeaders as $idx => $h)
                                        <th class="border-b border-r last:border-r-0 border-gray-200 dark:border-white/10 p-2 bg-gray-100 dark:bg-white/5 text-left">
                                            <span class="text-gray-700 dark:text-gray-300 font-semibold">{{ $h }}</span>
                                            @if(isset($importMapping[$idx]) && $importMapping[$idx] !== 'skip')
                                            <span class="block text-[10px] text-primary-600 dark:text-primary-400 font-normal mt-0.5">
                                                &rarr; {{ $fieldLabels[$importMapping[$idx]] ?? $importMapping[$idx] }}
                                            </span>
                                            @endif
                                        </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($importPreview as $rowIdx => $row)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                        <td class="border-b border-r border-gray-200 dark:border-white/10 p-1.5 text-center text-gray-400 font-mono">{{ $rowIdx + 1 }}</td>
                                        @foreach($row as $cellIdx => $cell)
                                        <td class="border-b border-r last:border-r-0 border-gray-200 dark:border-white/10 p-1.5 text-gray-600 dark:text-gray-400
                                            {{ isset($importMapping[$cellIdx]) && $importMapping[$cellIdx] !== 'skip' ? 'bg-primary-50/50 dark:bg-primary-900/10' : '' }}">
                                            {{ Str::limit($cell, 35) }}
                                        </td>
                                        @endforeach
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    {{-- Actions --}}
                    <div class="flex items-center justify-between pt-2">
                        <button wire:click="resetImport" class="inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5 transition">
                            <x-heroicon-m-arrow-left class="h-4 w-4" /> Назад
                        </button>
                        <button wire:click="executeImport" wire:confirm="Імпортувати {{ $importTotalRows }} рядків?"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-success-600 px-6 py-2 text-sm font-bold text-white hover:bg-success-500 shadow-sm transition">
                            <x-heroicon-m-arrow-up-tray class="h-4 w-4" />
                            Імпортувати ({{ $importTotalRows }} рядків)
                        </button>
                    </div>
                </div>
                @endif

                {{-- Step 3: Results --}}
                @if($importStep === 3)
                <div class="space-y-4">
                    {{-- Stats cards --}}
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4 text-center">
                            <p class="text-2xl font-bold text-green-700 dark:text-green-300">{{ $importStats['created'] ?? 0 }}</p>
                            <p class="text-xs text-green-600 dark:text-green-400 font-medium mt-1">Створено</p>
                        </div>
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4 text-center">
                            <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{ $importStats['updated'] ?? 0 }}</p>
                            <p class="text-xs text-blue-600 dark:text-blue-400 font-medium mt-1">Оновлено</p>
                        </div>
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-4 text-center">
                            <p class="text-2xl font-bold text-yellow-700 dark:text-yellow-300">{{ $importStats['skipped'] ?? 0 }}</p>
                            <p class="text-xs text-yellow-600 dark:text-yellow-400 font-medium mt-1">Пропущено</p>
                        </div>
                        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4 text-center">
                            <p class="text-2xl font-bold text-red-700 dark:text-red-300">{{ $importStats['errors'] ?? 0 }}</p>
                            <p class="text-xs text-red-600 dark:text-red-400 font-medium mt-1">Помилок</p>
                        </div>
                    </div>

                    {{-- Error details --}}
                    @if(!empty($importStats['error_messages']))
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3">
                        <p class="text-xs font-semibold text-red-700 dark:text-red-300 mb-2">Деталі помилок:</p>
                        <ul class="text-xs text-red-600 dark:text-red-400 space-y-1 list-disc list-inside max-h-40 overflow-y-auto">
                            @foreach($importStats['error_messages'] as $errMsg)
                            <li>{{ $errMsg }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    {{-- Actions --}}
                    <div class="flex items-center justify-between pt-2">
                        <button wire:click="resetImport" class="inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5 transition">
                            <x-heroicon-m-arrow-path class="h-4 w-4" /> Імпортувати ще
                        </button>
                        <button wire:click="$set('showImportModal', false)" class="inline-flex items-center gap-1.5 rounded-lg bg-primary-600 px-6 py-2 text-sm font-bold text-white hover:bg-primary-500 shadow-sm transition">
                            Закрити
                        </button>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- SEO Modal --}}
        @if($showSeoModal)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" x-data x-on:keydown.escape.window="$wire.set('showSeoModal', false)">
            <div class="bg-white dark:bg-gray-900 rounded-xl max-w-lg w-full p-6 shadow-xl">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">МАСОВЕ SEO</h3>
                <div class="space-y-4">
                    {{-- Action type --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Дія</label>
                        <select wire:model.live="seoAction" class="fi-select-input block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                            <option value="template">За шаблоном</option>
                            <option value="auto_generate">Авто-генерація (SeoMetaGenerator)</option>
                        </select>
                    </div>

                    {{-- SEO field --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Поле</label>
                        <select wire:model="seoField" class="fi-select-input block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                            <option value="meta_title">Meta Title</option>
                            <option value="meta_description">Meta Description</option>
                            <option value="meta_keywords">Meta Keywords</option>
                            @if($seoAction === 'auto_generate')
                            <option value="all">Всі поля</option>
                            @endif
                        </select>
                    </div>

                    {{-- Template input (only for template action) --}}
                    @if($seoAction === 'template')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Шаблон</label>
                        <textarea wire:model="seoTemplate" rows="3" class="fi-input block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white"
                            placeholder="{{ $seoField === 'meta_keywords' ? '{title}, купити {title}, {brand}, {category}' : 'Купити {title} від {brand} в {category} | Ціна {price} грн' }}"></textarea>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Плейсхолдери: {title}, {brand}, {category}, {price}, {sku}</p>
                        @if($seoField === 'meta_keywords')
                        <p class="text-xs text-warning-600 dark:text-warning-400 mt-1">Keywords розділяються комами</p>
                        @endif
                    </div>
                    @else
                    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                        <p class="text-xs text-blue-700 dark:text-blue-300">SEO буде згенеровано автоматично на основі назви товару, ціни, категорії та налаштувань магазину.</p>
                    </div>
                    @endif
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('showSeoModal', false)" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5">СКАСУВАТИ</button>
                    <button wire:click="applySeoMeta" wire:confirm="Оновити SEO для {{ count($selectedIds) }} товарів?" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-500">ЗАСТОСУВАТИ</button>
                </div>
            </div>
        </div>
        @endif

        {{-- Variant Generation Modal --}}
        @if($showVariantModal)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" x-data x-on:keydown.escape.window="$wire.set('showVariantModal', false)">
            <div class="bg-white dark:bg-gray-900 rounded-xl max-w-2xl w-full max-h-[80vh] overflow-y-auto p-6 shadow-xl">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Генерація варіантів</h3>

                @if(empty($variantPreview))
                <div class="text-center py-8">
                    <x-heroicon-o-cube class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-600 mb-2" />
                    <p class="text-sm text-gray-500 dark:text-gray-400">Жоден з вибраних товарів не має активних опцій для генерації варіантів.</p>
                </div>
                @else
                <div class="mb-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                        Буде згенеровано варіанти для <strong class="text-primary-600 dark:text-primary-400">{{ count($variantPreview) }}</strong> товарів:
                    </p>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm border-collapse">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-white/5">
                                    <th class="border border-gray-200 dark:border-white/10 px-3 py-2 text-left text-xs font-semibold text-gray-600 dark:text-gray-400">Товар</th>
                                    <th class="border border-gray-200 dark:border-white/10 px-3 py-2 text-left text-xs font-semibold text-gray-600 dark:text-gray-400">Опції</th>
                                    <th class="border border-gray-200 dark:border-white/10 px-3 py-2 text-center text-xs font-semibold text-gray-600 dark:text-gray-400 w-24">Комбінацій</th>
                                    <th class="border border-gray-200 dark:border-white/10 px-3 py-2 text-center text-xs font-semibold text-gray-600 dark:text-gray-400 w-20">Вже є</th>
                                    <th class="border border-gray-200 dark:border-white/10 px-3 py-2 text-center text-xs font-semibold text-gray-600 dark:text-gray-400 w-20">Нових</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                                @foreach($variantPreview as $item)
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                    <td class="border border-gray-200 dark:border-white/10 px-3 py-2 text-xs text-gray-700 dark:text-gray-300">
                                        <span class="font-mono text-gray-400 mr-1">#{{ $item['id'] }}</span>
                                        {{ Str::limit($item['title'], 40) }}
                                    </td>
                                    <td class="border border-gray-200 dark:border-white/10 px-3 py-2 text-xs text-gray-500 dark:text-gray-400">{{ $item['options'] }}</td>
                                    <td class="border border-gray-200 dark:border-white/10 px-3 py-2 text-center text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $item['combinations'] }}</td>
                                    <td class="border border-gray-200 dark:border-white/10 px-3 py-2 text-center text-xs text-gray-500 dark:text-gray-400">{{ $item['existing'] }}</td>
                                    <td class="border border-gray-200 dark:border-white/10 px-3 py-2 text-center text-xs font-semibold {{ $item['new'] > 0 ? 'text-success-600 dark:text-success-400' : 'text-gray-400' }}">{{ $item['new'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-gray-50 dark:bg-white/5">
                                    <td colspan="4" class="border border-gray-200 dark:border-white/10 px-3 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300 text-right">Всього нових варіантів:</td>
                                    <td class="border border-gray-200 dark:border-white/10 px-3 py-2 text-center text-sm font-bold text-success-600 dark:text-success-400">{{ collect($variantPreview)->sum('new') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                @endif

                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('showVariantModal', false)" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5">Скасувати</button>
                    @if(!empty($variantPreview) && collect($variantPreview)->sum('new') > 0)
                    <button wire:click="generateVariants" wire:confirm="Згенерувати {{ collect($variantPreview)->sum('new') }} варіантів?" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 transition">
                        Генерувати ({{ collect($variantPreview)->sum('new') }})
                    </button>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- Search/Replace Preview --}}
        @if(count($srPreview) > 0)
        <div class="fi-section rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Результати заміни</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-white/10">
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 dark:text-gray-400">ID</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 dark:text-gray-400">Товар</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 dark:text-gray-400">Було</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 dark:text-gray-400">Стало</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @foreach($srPreview as $item)
                            <tr>
                                <td class="px-3 py-2 text-xs font-mono text-gray-500">{{ $item['id'] }}</td>
                                <td class="px-3 py-2 text-xs text-gray-700 dark:text-gray-300">{{ $item['title'] }}</td>
                                <td class="px-3 py-2 text-xs text-danger-600 dark:text-danger-400 line-through">{{ $item['original'] }}</td>
                                <td class="px-3 py-2 text-xs text-success-600 dark:text-success-400">{{ $item['new'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3 flex justify-end">
                <button wire:click="$set('srPreview', [])" class="text-xs text-gray-500 hover:text-primary-500">Закрити перегляд</button>
            </div>
        </div>
        @endif

        @endif {{-- end activeTab === products --}}

        {{-- ========== CATEGORIES TAB ========== --}}
        @if($activeTab === 'categories')

        {{-- Categories Toolbar --}}
        <div class="fi-section rounded-xl bg-white p-3 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex flex-wrap items-center gap-2">
                <button wire:click="saveCategoryChanges" class="inline-flex items-center gap-1.5 rounded-lg bg-success-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-success-500 transition relative">
                    <x-heroicon-m-check class="h-4 w-4" />
                    Зберегти зміни
                    @if(count($editedCategoryData) > 0)
                        <span class="absolute -top-1.5 -right-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-danger-500 text-[10px] font-bold text-white">{{ count($editedCategoryData) }}</span>
                    @endif
                </button>

                <span class="h-6 w-px bg-gray-300 dark:bg-white/10"></span>

                <button wire:click="batchActivateCategories" class="inline-flex items-center gap-1 rounded-lg bg-gray-100 px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-200 dark:bg-white/5 dark:text-gray-300 dark:hover:bg-white/10 transition">
                    <x-heroicon-m-eye class="h-4 w-4" /> Активувати
                </button>
                <button wire:click="batchDeactivateCategories" class="inline-flex items-center gap-1 rounded-lg bg-gray-100 px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-200 dark:bg-white/5 dark:text-gray-300 dark:hover:bg-white/10 transition">
                    <x-heroicon-m-eye-slash class="h-4 w-4" /> Деактивувати
                </button>
                <button wire:click="$set('showParentCategoryModal', true)" class="inline-flex items-center gap-1 rounded-lg bg-gray-100 px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-200 dark:bg-white/5 dark:text-gray-300 dark:hover:bg-white/10 transition">
                    <x-heroicon-m-folder class="h-4 w-4" /> Змінити батьківську
                </button>

                @if(count($selectedIds) > 0)
                    <span class="ml-auto text-xs text-gray-500 dark:text-gray-400">
                        Вибрано: <strong class="text-primary-600 dark:text-primary-400">{{ count($selectedIds) }}</strong>
                    </span>
                @endif
            </div>
        </div>

        {{-- Categories Grid --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-white/10">
                            <th class="px-2 py-3 text-center w-10">
                                <input type="checkbox" wire:model.live="selectAll" wire:click="toggleSelectAll" class="fi-checkbox-input rounded border-gray-300 text-primary-600 shadow-sm dark:border-white/10 dark:bg-white/5">
                            </th>
                            <th class="px-2 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 w-16">ID</th>
                            <th class="px-2 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 min-w-[250px]">Назва</th>
                            <th class="px-2 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 w-48 hidden md:table-cell">Slug</th>
                            <th class="px-2 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 w-40">Батьківська</th>
                            <th class="px-2 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 w-24">Сортування</th>
                            <th class="px-2 py-3 text-center text-xs font-semibold text-gray-600 dark:text-gray-400 w-16">Акт.</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @forelse($this->getCategoryItems() as $category)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition" wire:key="category-{{ $category->id }}">
                                <td class="px-2 py-1.5 text-center">
                                    <input type="checkbox" value="{{ $category->id }}" wire:model.live="selectedIds" class="fi-checkbox-input rounded border-gray-300 text-primary-600 shadow-sm dark:border-white/10 dark:bg-white/5">
                                </td>
                                <td class="px-2 py-1.5 text-xs text-gray-500 dark:text-gray-400 font-mono">{{ $category->id }}</td>
                                <td class="px-2 py-1.5">
                                    <input
                                        type="text"
                                        value="{{ $editedCategoryData[$category->id]['title'] ?? $category->title }}"
                                        wire:change="updateCategoryField({{ $category->id }}, 'title', $event.target.value)"
                                        class="block w-full rounded-md border-gray-300 text-sm shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-white focus:border-primary-500 focus:ring-primary-500 {{ isset($editedCategoryData[$category->id]['title']) ? 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-400 dark:border-yellow-600' : '' }}"
                                    >
                                </td>
                                <td class="px-2 py-1.5 text-xs text-gray-500 dark:text-gray-400 font-mono hidden md:table-cell">{{ $category->slug }}</td>
                                <td class="px-2 py-1.5 text-xs text-gray-600 dark:text-gray-400">
                                    @if($category->parent)
                                        <span class="inline-flex items-center rounded-md bg-primary-50 px-2 py-1 text-xs font-medium text-primary-700 ring-1 ring-inset ring-primary-600/20 dark:bg-primary-400/10 dark:text-primary-400 dark:ring-primary-400/30">
                                            {{ $category->parent->title }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">--</span>
                                    @endif
                                </td>
                                <td class="px-2 py-1.5">
                                    <input
                                        type="number"
                                        min="0"
                                        value="{{ $editedCategoryData[$category->id]['sort_order'] ?? $category->sort_order }}"
                                        wire:change="updateCategoryField({{ $category->id }}, 'sort_order', $event.target.value)"
                                        class="block w-full rounded-md border-gray-300 text-sm shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-white focus:border-primary-500 focus:ring-primary-500 {{ isset($editedCategoryData[$category->id]['sort_order']) ? 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-400 dark:border-yellow-600' : '' }}"
                                    >
                                </td>
                                <td class="px-2 py-1.5 text-center">
                                    <input
                                        type="checkbox"
                                        {{ ($editedCategoryData[$category->id]['is_active'] ?? $category->is_active) ? 'checked' : '' }}
                                        wire:change="updateCategoryField({{ $category->id }}, 'is_active', $event.target.checked)"
                                        class="fi-checkbox-input rounded border-gray-300 text-primary-600 shadow-sm dark:border-white/10 dark:bg-white/5 {{ isset($editedCategoryData[$category->id]['is_active']) ? 'ring-2 ring-yellow-400' : '' }}"
                                    >
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                    <x-heroicon-o-inbox class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-600 mb-2" />
                                    Категорії не знайдено
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-200 dark:border-white/10">
                {{ $this->getCategoryItems()->links() }}
            </div>
        </div>

        {{-- Parent Category Modal --}}
        @if($showParentCategoryModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" x-data x-on:keydown.escape.window="$wire.set('showParentCategoryModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-gray-900">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Змінити батьківську категорію</h3>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Батьківська категорія</label>
                    <select wire:model="newParentCategoryId" class="fi-select-input block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                        <option value="">Коренева (без батьківської)</option>
                        @foreach($this->getCategories() as $id => $title)
                            <option value="{{ $id }}">{{ $title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('showParentCategoryModal', false)" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5">Скасувати</button>
                    <button wire:click="applyParentCategory" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-500">Застосувати</button>
                </div>
            </div>
        </div>
        @endif

        @endif {{-- end activeTab === categories --}}

        {{-- ========== ORDERS TAB ========== --}}
        @if($activeTab === 'orders')

        {{-- Orders Filter --}}
        <div class="fi-section rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Статус замовлення</label>
                    <select wire:model.live="orderStatusFilter" class="fi-select-input block w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                        <option value="">Всі</option>
                        <option value="pending">Очікує</option>
                        <option value="processing">В обробці</option>
                        <option value="shipped">Відправлено</option>
                        <option value="delivered">Доставлено</option>
                        <option value="cancelled">Скасовано</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Orders Toolbar --}}
        <div class="fi-section rounded-xl bg-white p-3 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex flex-wrap items-center gap-2">
                <button wire:click="$set('showOrderStatusModal', true)" class="inline-flex items-center gap-1 rounded-lg bg-gray-100 px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-200 dark:bg-white/5 dark:text-gray-300 dark:hover:bg-white/10 transition">
                    <x-heroicon-m-arrow-path class="h-4 w-4" /> Змінити статус
                </button>
                <button wire:click="exportOrders" class="inline-flex items-center gap-1 rounded-lg bg-gray-100 px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-200 dark:bg-white/5 dark:text-gray-300 dark:hover:bg-white/10 transition">
                    <x-heroicon-m-arrow-down-tray class="h-4 w-4" /> Експорт CSV
                </button>

                @if(count($selectedIds) > 0)
                    <span class="ml-auto text-xs text-gray-500 dark:text-gray-400">
                        Вибрано: <strong class="text-primary-600 dark:text-primary-400">{{ count($selectedIds) }}</strong>
                    </span>
                @endif
            </div>
        </div>

        {{-- Orders Grid --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-white/10">
                            <th class="px-2 py-3 text-center w-10">
                                <input type="checkbox" wire:model.live="selectAll" wire:click="toggleSelectAll" class="fi-checkbox-input rounded border-gray-300 text-primary-600 shadow-sm dark:border-white/10 dark:bg-white/5">
                            </th>
                            <th class="px-2 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 w-16">ID</th>
                            <th class="px-2 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400">Клієнт</th>
                            <th class="px-2 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 hidden md:table-cell">Email</th>
                            <th class="px-2 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 hidden md:table-cell">Телефон</th>
                            <th class="px-2 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-400 w-28">Сума</th>
                            <th class="px-2 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 w-36">Статус</th>
                            <th class="px-2 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 w-32 hidden md:table-cell">Оплата</th>
                            <th class="px-2 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 w-32">Створено</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @forelse($this->getOrderItems() as $order)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition" wire:key="order-{{ $order->id }}">
                                <td class="px-2 py-1.5 text-center">
                                    <input type="checkbox" value="{{ $order->id }}" wire:model.live="selectedIds" class="fi-checkbox-input rounded border-gray-300 text-primary-600 shadow-sm dark:border-white/10 dark:bg-white/5">
                                </td>
                                <td class="px-2 py-1.5 text-xs text-gray-500 dark:text-gray-400 font-mono">{{ $order->id }}</td>
                                <td class="px-2 py-1.5 text-xs text-gray-700 dark:text-gray-300">{{ $order->name ?? ($order->user?->name ?? '--') }}</td>
                                <td class="px-2 py-1.5 text-xs text-gray-500 dark:text-gray-400 hidden md:table-cell">{{ $order->email ?? '--' }}</td>
                                <td class="px-2 py-1.5 text-xs text-gray-500 dark:text-gray-400 hidden md:table-cell">{{ $order->phone ?? '--' }}</td>
                                <td class="px-2 py-1.5 text-xs text-right font-semibold text-gray-700 dark:text-gray-300">{{ number_format($order->total, 2) }} &#8372;</td>
                                <td class="px-2 py-1.5">
                                    @php
                                        $statusColors = [
                                            'pending' => 'bg-yellow-50 text-yellow-700 ring-yellow-600/20 dark:bg-yellow-400/10 dark:text-yellow-400 dark:ring-yellow-400/30',
                                            'processing' => 'bg-blue-50 text-blue-700 ring-blue-600/20 dark:bg-blue-400/10 dark:text-blue-400 dark:ring-blue-400/30',
                                            'shipped' => 'bg-purple-50 text-purple-700 ring-purple-600/20 dark:bg-purple-400/10 dark:text-purple-400 dark:ring-purple-400/30',
                                            'delivered' => 'bg-green-50 text-green-700 ring-green-600/20 dark:bg-green-400/10 dark:text-green-400 dark:ring-green-400/30',
                                            'cancelled' => 'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-400/10 dark:text-red-400 dark:ring-red-400/30',
                                        ];
                                        $statusLabels = [
                                            'pending' => 'Очікує',
                                            'processing' => 'В обробці',
                                            'shipped' => 'Відправлено',
                                            'delivered' => 'Доставлено',
                                            'cancelled' => 'Скасовано',
                                        ];
                                        $sc = $statusColors[$order->status] ?? 'bg-gray-50 text-gray-700 ring-gray-600/20';
                                    @endphp
                                    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $sc }}">
                                        {{ $statusLabels[$order->status] ?? $order->status }}
                                    </span>
                                </td>
                                <td class="px-2 py-1.5 text-xs text-gray-500 dark:text-gray-400 hidden md:table-cell">{{ $order->payment_status ?? '--' }}</td>
                                <td class="px-2 py-1.5 text-xs text-gray-500 dark:text-gray-400">{{ $order->created_at?->format('d.m.Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                    <x-heroicon-o-inbox class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-600 mb-2" />
                                    Замовлення не знайдено
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-200 dark:border-white/10">
                {{ $this->getOrderItems()->links() }}
            </div>
        </div>

        {{-- Order Status Modal --}}
        @if($showOrderStatusModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" x-data x-on:keydown.escape.window="$wire.set('showOrderStatusModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-gray-900">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Змінити статус замовлень</h3>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Новий статус</label>
                    <select wire:model="orderBatchStatus" class="fi-select-input block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                        <option value="">Виберіть статус</option>
                        <option value="pending">Очікує</option>
                        <option value="processing">В обробці</option>
                        <option value="shipped">Відправлено</option>
                        <option value="delivered">Доставлено</option>
                        <option value="cancelled">Скасовано</option>
                    </select>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('showOrderStatusModal', false)" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5">Скасувати</button>
                    <button wire:click="batchChangeOrderStatus" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-500">Застосувати</button>
                </div>
            </div>
        </div>
        @endif

        @endif {{-- end activeTab === orders --}}

        {{-- ========== REVIEWS TAB ========== --}}
        @if($activeTab === 'reviews')

        {{-- Reviews Toolbar --}}
        <div class="fi-section rounded-xl bg-white p-3 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex flex-wrap items-center gap-2">
                <button wire:click="batchApproveReviews" class="inline-flex items-center gap-1 rounded-lg bg-success-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-success-500 transition">
                    <x-heroicon-m-check-circle class="h-4 w-4" /> Схвалити всі вибрані
                </button>
                <button wire:click="batchRejectReviews" wire:confirm="Видалити вибрані відгуки назавжди?" class="inline-flex items-center gap-1 rounded-lg bg-danger-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-danger-500 transition">
                    <x-heroicon-m-trash class="h-4 w-4" /> Видалити вибрані
                </button>

                @if(count($selectedIds) > 0)
                    <span class="ml-auto text-xs text-gray-500 dark:text-gray-400">
                        Вибрано: <strong class="text-primary-600 dark:text-primary-400">{{ count($selectedIds) }}</strong>
                    </span>
                @endif
            </div>
        </div>

        {{-- Reviews Grid --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-white/10">
                            <th class="px-2 py-3 text-center w-10">
                                <input type="checkbox" wire:model.live="selectAll" wire:click="toggleSelectAll" class="fi-checkbox-input rounded border-gray-300 text-primary-600 shadow-sm dark:border-white/10 dark:bg-white/5">
                            </th>
                            <th class="px-2 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 w-16">ID</th>
                            <th class="px-2 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 min-w-[200px]">Товар</th>
                            <th class="px-2 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 w-40 hidden md:table-cell">Автор</th>
                            <th class="px-2 py-3 text-center text-xs font-semibold text-gray-600 dark:text-gray-400 w-24">Рейтинг</th>
                            <th class="px-2 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 min-w-[300px]">Текст</th>
                            <th class="px-2 py-3 text-center text-xs font-semibold text-gray-600 dark:text-gray-400 w-20">Схвалено</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @forelse($this->getReviewItems() as $review)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition" wire:key="review-{{ $review->id }}">
                                <td class="px-2 py-1.5 text-center">
                                    <input type="checkbox" value="{{ $review->id }}" wire:model.live="selectedIds" class="fi-checkbox-input rounded border-gray-300 text-primary-600 shadow-sm dark:border-white/10 dark:bg-white/5">
                                </td>
                                <td class="px-2 py-1.5 text-xs text-gray-500 dark:text-gray-400 font-mono">{{ $review->id }}</td>
                                <td class="px-2 py-1.5 text-xs text-gray-700 dark:text-gray-300">
                                    @if($review->product)
                                        <span class="inline-flex items-center rounded-md bg-primary-50 px-2 py-1 text-xs font-medium text-primary-700 ring-1 ring-inset ring-primary-600/20 dark:bg-primary-400/10 dark:text-primary-400 dark:ring-primary-400/30">
                                            {{ Str::limit($review->product->title, 40) }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">--</span>
                                    @endif
                                </td>
                                <td class="px-2 py-1.5 text-xs text-gray-600 dark:text-gray-400 hidden md:table-cell">{{ $review->author_name ?? '--' }}</td>
                                <td class="px-2 py-1.5 text-center">
                                    <span class="text-yellow-500 text-xs">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $review->rating)
                                                &#9733;
                                            @else
                                                &#9734;
                                            @endif
                                        @endfor
                                    </span>
                                </td>
                                <td class="px-2 py-1.5 text-xs text-gray-600 dark:text-gray-400">{{ Str::limit($review->comment, 80) }}</td>
                                <td class="px-2 py-1.5 text-center">
                                    @if($review->status === 'approved')
                                        <span class="inline-flex items-center rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700 dark:bg-green-400/10 dark:text-green-400">
                                            <x-heroicon-m-check class="h-3 w-3" />
                                        </span>
                                    @elseif($review->status === 'pending')
                                        <span class="inline-flex items-center rounded-full bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-700 dark:bg-yellow-400/10 dark:text-yellow-400">
                                            <x-heroicon-m-clock class="h-3 w-3" />
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-red-50 px-2 py-1 text-xs font-medium text-red-700 dark:bg-red-400/10 dark:text-red-400">
                                            <x-heroicon-m-x-mark class="h-3 w-3" />
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                    <x-heroicon-o-inbox class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-600 mb-2" />
                                    Відгуки не знайдено
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-200 dark:border-white/10">
                {{ $this->getReviewItems()->links() }}
            </div>
        </div>

        @endif {{-- end activeTab === reviews --}}

        {{-- ========== JOURNAL TAB ========== --}}
        @if($activeTab === 'journal')

        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-white/10">
                            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 w-36">Дата</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 w-32">Користувач</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 w-32">Дія</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400">Опис</th>
                            <th class="px-3 py-3 text-center text-xs font-semibold text-gray-600 dark:text-gray-400 w-20">К-сть</th>
                            <th class="px-3 py-3 text-center text-xs font-semibold text-gray-600 dark:text-gray-400 w-24">Скасовано</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @forelse($this->getJournalItems() as $logItem)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition" wire:key="log-{{ $logItem->id }}">
                                <td class="px-3 py-2 text-xs text-gray-500 dark:text-gray-400">{{ $logItem->created_at?->format('d.m.Y H:i:s') }}</td>
                                <td class="px-3 py-2 text-xs text-gray-700 dark:text-gray-300">{{ $logItem->user?->name ?? '--' }}</td>
                                <td class="px-3 py-2">
                                    @php
                                        $actionColors = [
                                            'price_change' => 'bg-blue-50 text-blue-700 ring-blue-600/20 dark:bg-blue-400/10 dark:text-blue-400',
                                            'sale' => 'bg-orange-50 text-orange-700 ring-orange-600/20 dark:bg-orange-400/10 dark:text-orange-400',
                                            'status' => 'bg-green-50 text-green-700 ring-green-600/20 dark:bg-green-400/10 dark:text-green-400',
                                            'category' => 'bg-purple-50 text-purple-700 ring-purple-600/20 dark:bg-purple-400/10 dark:text-purple-400',
                                            'search_replace' => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20 dark:bg-indigo-400/10 dark:text-indigo-400',
                                            'delete' => 'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-400/10 dark:text-red-400',
                                            'import' => 'bg-teal-50 text-teal-700 ring-teal-600/20 dark:bg-teal-400/10 dark:text-teal-400',
                                        ];
                                        $ac = $actionColors[$logItem->action_type] ?? 'bg-gray-50 text-gray-700 ring-gray-600/20 dark:bg-gray-400/10 dark:text-gray-400';
                                    @endphp
                                    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $ac }}">
                                        {{ $logItem->action_type }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-xs text-gray-700 dark:text-gray-300">{{ $logItem->description }}</td>
                                <td class="px-3 py-2 text-center text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $logItem->affected_count }}</td>
                                <td class="px-3 py-2 text-center">
                                    @if($logItem->rolled_back)
                                        <span class="inline-flex items-center rounded-full bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-700 dark:bg-yellow-400/10 dark:text-yellow-400">
                                            Скасовано
                                        </span>
                                    @elseif(!empty($logItem->changes_data))
                                        <x-filament::icon-button
                                            icon="heroicon-m-arrow-uturn-left"
                                            size="sm"
                                            wire:click="rollbackAction({{ $logItem->id }})"
                                            wire:confirm="Скасувати цю операцію?"
                                            color="warning"
                                            label="Скасувати"
                                        />
                                    @else
                                        <span class="text-gray-400 text-xs">--</span>
                                    @endif
                                </td>
                            </tr>
                            @if(!empty($logItem->affected_ids))
                            <tr wire:key="log-detail-{{ $logItem->id }}" x-data="{ showIds: false }">
                                <td colspan="6" class="px-3 py-0">
                                    <button @click="showIds = !showIds" class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 py-1">
                                        <span x-text="showIds ? '&#9650; Сховати ID' : '&#9660; Показати ID (' + {{ count($logItem->affected_ids) }} + ')'"></span>
                                    </button>
                                    <div x-show="showIds" x-cloak class="pb-2 text-xs text-gray-500 dark:text-gray-400 font-mono">
                                        {{ implode(', ', array_slice($logItem->affected_ids, 0, 50)) }}
                                        @if(count($logItem->affected_ids) > 50)
                                            <span class="text-gray-400">... та ще {{ count($logItem->affected_ids) - 50 }}</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                    <x-heroicon-o-inbox class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-600 mb-2" />
                                    Журнал порожній
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-200 dark:border-white/10">
                {{ $this->getJournalItems()->links() }}
            </div>
        </div>

        @endif {{-- end activeTab === journal --}}

        {{-- ===== UNIVERSAL PREVIEW OVERLAY ===== --}}
        @if($showPreview && !empty($previewData))
        <div class="fixed inset-0 bg-black/50 z-[60] flex items-center justify-center p-4" x-data x-on:keydown.escape.window="$wire.cancelPreview()">
            <div class="bg-white dark:bg-gray-800 rounded-xl max-w-3xl w-full max-h-[80vh] overflow-y-auto p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
                    PREVIEW -- перегляд змін ({{ count($previewData) }} записів)
                </h3>
                <table class="w-full text-sm border-collapse mb-4">
                    <thead>
                        <tr class="bg-gray-100 dark:bg-gray-700">
                            <th class="border border-gray-200 dark:border-white/10 p-2 text-left text-xs font-semibold text-gray-600 dark:text-gray-400">ID</th>
                            <th class="border border-gray-200 dark:border-white/10 p-2 text-left text-xs font-semibold text-gray-600 dark:text-gray-400">Назва</th>
                            <th class="border border-gray-200 dark:border-white/10 p-2 text-left text-xs font-semibold text-gray-600 dark:text-gray-400">Було</th>
                            <th class="border border-gray-200 dark:border-white/10 p-2 text-left text-xs font-semibold text-gray-600 dark:text-gray-400">Стане</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($previewData as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                            <td class="border border-gray-200 dark:border-white/10 p-2 text-xs font-mono text-gray-500 dark:text-gray-400">{{ $row['id'] }}</td>
                            <td class="border border-gray-200 dark:border-white/10 p-2 text-xs text-gray-700 dark:text-gray-300">{{ $row['title'] }}</td>
                            <td class="border border-gray-200 dark:border-white/10 p-2 text-xs text-red-600 dark:text-red-400">{{ $row['old'] ?? $row['original'] ?? '' }}</td>
                            <td class="border border-gray-200 dark:border-white/10 p-2 text-xs text-green-600 dark:text-green-400">{{ $row['new'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="flex gap-3">
                    <button wire:click="confirmAndApply" wire:confirm="Застосувати зміни для {{ count($previewData) }} товарів? Цю дію можна скасувати через журнал." class="rounded-lg bg-green-600 text-white px-6 py-2 text-sm font-bold hover:bg-green-700 transition">
                        ЗАСТОСУВАТИ ({{ count($previewData) }})
                    </button>
                    <button wire:click="cancelPreview" class="rounded-lg border border-gray-300 dark:border-white/10 px-6 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5 transition">
                        СКАСУВАТИ
                    </button>
                </div>
            </div>
        </div>
        @endif

    </div>
</x-filament-panels::page>
