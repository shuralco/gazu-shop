<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Sticky save bar --}}
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Налаштування меню сайту</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Горизонтальне меню, мега-меню та промо-блок</p>
            </div>
            <x-filament::button wire:click="save" color="success" icon="heroicon-m-check" size="lg">
                Зберегти
            </x-filament::button>
        </div>

        {{-- ==================== HORIZONTAL MENU ==================== --}}
        <x-filament::section icon="heroicon-m-bars-3" icon-color="gray" collapsible>
            <x-slot name="heading">
                Горизонтальне меню
            </x-slot>
            <x-slot name="description">
                Чорна стрічка під шапкою сайту з посиланнями на категорії та сторінки
            </x-slot>
            <x-slot name="headerEnd">
                <label class="inline-flex items-center gap-2">
                    <x-filament::input.checkbox wire:model.live="horizontalEnabled" />
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Увімкнено</span>
                </label>
            </x-slot>

            <div class="space-y-3">
                {{-- Action buttons --}}
                <div class="flex flex-wrap gap-2">
                    <x-filament::button wire:click="addHorizontalItem" size="sm" color="gray" icon="heroicon-m-plus">
                        Додати пункт
                    </x-filament::button>
                    <x-filament::button wire:click="autoGenerateHorizontal" size="sm" color="warning" icon="heroicon-m-bolt" outlined>
                        Згенерувати з категорій
                    </x-filament::button>
                </div>

                {{-- Items list --}}
                <div class="space-y-2">
                    @foreach($horizontalItems as $index => $item)
                    <div wire:key="h-item-{{ $index }}" class="fi-fo-repeater-item flex items-center gap-3 rounded-lg bg-white dark:bg-white/5 p-3 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
                        <span class="flex items-center justify-center w-7 h-7 rounded-full bg-gray-100 dark:bg-white/10 text-xs font-bold text-gray-500 dark:text-gray-400 flex-shrink-0">{{ $index + 1 }}</span>
                        <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <x-filament::input.wrapper>
                                <x-filament::input type="text" wire:model.blur="horizontalItems.{{ $index }}.text" placeholder="Назва пункту" />
                            </x-filament::input.wrapper>
                            <x-filament::input.wrapper prefix-icon="heroicon-m-link">
                                <x-filament::input type="text" wire:model.blur="horizontalItems.{{ $index }}.url" placeholder="/url-адреса" />
                            </x-filament::input.wrapper>
                        </div>
                        <div class="flex items-center gap-x-0.5 flex-shrink-0">
                            <x-filament::icon-button icon="heroicon-m-chevron-up" size="sm" wire:click="moveHorizontalItem({{ $index }}, 'up')" color="gray" label="Вгору" />
                            <x-filament::icon-button icon="heroicon-m-chevron-down" size="sm" wire:click="moveHorizontalItem({{ $index }}, 'down')" color="gray" label="Вниз" />
                            <x-filament::icon-button icon="heroicon-m-trash" size="sm" wire:click="removeHorizontalItem({{ $index }})" color="danger" label="Видалити" />
                        </div>
                    </div>
                    @endforeach
                </div>

                @if(empty($horizontalItems))
                <div class="rounded-xl bg-gray-50 dark:bg-white/5 p-8 text-center">
                    <x-heroicon-o-bars-3 class="w-10 h-10 text-gray-300 dark:text-gray-600 mx-auto mb-3" />
                    <p class="text-sm text-gray-500 dark:text-gray-400">Немає пунктів. Додайте вручну або згенеруйте з категорій.</p>
                </div>
                @endif
            </div>
        </x-filament::section>

        {{-- ==================== MEGA MENU ==================== --}}
        <x-filament::section icon="heroicon-m-squares-2x2" icon-color="primary" collapsible>
            <x-slot name="heading">
                Мега-меню
            </x-slot>
            <x-slot name="description">
                Випадаюче меню під кнопкою "КАТАЛОГ" з категоріями та посиланнями
            </x-slot>
            <x-slot name="headerEnd">
                <label class="inline-flex items-center gap-2">
                    <x-filament::input.checkbox wire:model.live="megaMenuEnabled" />
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Увімкнено</span>
                </label>
            </x-slot>

            <div class="space-y-4">
                {{-- Catalog trigger mode --}}
                <div class="flex items-center gap-4 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Відкриття каталогу:</span>
                    <select wire:model.live="catalogTrigger" class="text-sm border-gray-300 dark:border-gray-600 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-300">
                        <option value="click">По кліку</option>
                        <option value="hover">При наведенні</option>
                        <option value="both">Клік + наведення</option>
                    </select>
                </div>

                {{-- Actions --}}
                <div class="flex flex-wrap gap-2">
                    <x-filament::button wire:click="autoGenerateMegaMenu" size="sm" color="warning" icon="heroicon-m-bolt" outlined>
                        Згенерувати з категорій
                    </x-filament::button>
                    <x-filament::button wire:click="addMegaColumn" size="sm" color="gray" icon="heroicon-m-view-columns">
                        Додати колонку
                    </x-filament::button>
                </div>

                {{-- Columns --}}
                @if(!empty($megaMenuColumns))
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-{{ min(count($megaMenuColumns), 4) }} gap-4">
                    @foreach($megaMenuColumns as $colIndex => $column)
                    <div wire:key="mega-col-{{ $colIndex }}" class="fi-fo-repeater-item rounded-xl bg-white dark:bg-white/5 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
                        {{-- Column header --}}
                        <div class="flex items-center gap-x-3 px-4 py-3 border-b border-gray-100 dark:border-white/5">
                            <span class="fi-fo-repeater-item-label text-sm font-medium text-gray-950 dark:text-white flex-1">
                                Колонка {{ $colIndex + 1 }}
                            </span>
                            <div class="flex items-center gap-x-0.5">
                                <x-filament::icon-button icon="heroicon-m-arrow-left" size="sm" wire:click="moveColumnLeft({{ $colIndex }})" color="gray" label="Вліво" />
                                <x-filament::icon-button icon="heroicon-m-arrow-right" size="sm" wire:click="moveColumnRight({{ $colIndex }})" color="gray" label="Вправо" />
                                <x-filament::icon-button icon="heroicon-m-trash" size="sm" wire:click="removeMegaColumn({{ $colIndex }})" wire:confirm="Видалити колонку?" color="danger" label="Видалити" />
                            </div>
                        </div>

                        {{-- Items --}}
                        <div class="p-4 space-y-4">
                            @foreach($column as $itemIndex => $item)
                            <div wire:key="mega-item-{{ $colIndex }}-{{ $itemIndex }}">
                                @if(($item['type'] ?? '') === 'category')
                                {{-- Category block --}}
                                <div class="rounded-lg bg-gray-50 dark:bg-white/5 ring-1 ring-gray-200 dark:ring-white/10">
                                    {{-- Category name row --}}
                                    <div class="flex items-center gap-1.5 p-2.5 border-b border-gray-100 dark:border-white/5">
                                        <x-heroicon-m-folder class="w-4 h-4 text-primary-500 flex-shrink-0" />
                                        <div class="flex-1 min-w-0">
                                            <x-filament::input.wrapper>
                                                <x-filament::input
                                                    type="text"
                                                    wire:change="updateMegaItem({{ $colIndex }}, {{ $itemIndex }}, 'title', $event.target.value)"
                                                    :value="$item['title']"
                                                    class="font-semibold"
                                                />
                                            </x-filament::input.wrapper>
                                        </div>
                                        <x-filament::icon-button icon="heroicon-m-chevron-up" size="xs" wire:click="moveItemInColumn({{ $colIndex }}, {{ $itemIndex }}, 'up')" color="gray" label="Вгору" />
                                        <x-filament::icon-button icon="heroicon-m-chevron-down" size="xs" wire:click="moveItemInColumn({{ $colIndex }}, {{ $itemIndex }}, 'down')" color="gray" label="Вниз" />
                                        <x-filament::icon-button icon="heroicon-m-x-mark" size="xs" wire:click="removeItemFromColumn({{ $colIndex }}, {{ $itemIndex }})" color="danger" label="Видалити" />
                                    </div>
                                    {{-- Subcategories --}}
                                    <div class="p-3 space-y-2">
                                        @foreach($item['children'] ?? [] as $childIndex => $child)
                                        <div wire:key="child-{{ $colIndex }}-{{ $itemIndex }}-{{ $childIndex }}" class="flex items-center gap-1.5 py-1.5 border-b border-gray-100 dark:border-white/5 last:border-0">
                                            <span class="text-[10px] text-gray-400 dark:text-gray-500 flex-shrink-0 w-4 text-center font-medium">{{ $childIndex + 1 }}</span>
                                            <div class="flex-1 min-w-0">
                                                <x-filament::input.wrapper>
                                                    <x-filament::input
                                                        type="text"
                                                        wire:change="updateChild({{ $colIndex }}, {{ $itemIndex }}, {{ $childIndex }}, 'title', $event.target.value)"
                                                        :value="$child['title']"
                                                    />
                                                </x-filament::input.wrapper>
                                            </div>
                                            <x-filament::icon-button icon="heroicon-m-chevron-up" size="xs" wire:click="moveChild({{ $colIndex }}, {{ $itemIndex }}, {{ $childIndex }}, 'up')" color="gray" label="Вгору" />
                                            <x-filament::icon-button icon="heroicon-m-chevron-down" size="xs" wire:click="moveChild({{ $colIndex }}, {{ $itemIndex }}, {{ $childIndex }}, 'down')" color="gray" label="Вниз" />
                                            <x-filament::icon-button icon="heroicon-m-x-mark" size="xs" wire:click="removeChildFromItem({{ $colIndex }}, {{ $itemIndex }}, {{ $childIndex }})" color="danger" label="Видалити" />
                                        </div>
                                        @endforeach
                                        @php $parentCatId = $item['category_id'] ?? null; @endphp
                                        @if($parentCatId)
                                        <div x-data="{ open: false }" class="relative mt-1">
                                            <x-filament::button @click="open = !open" size="xs" color="gray" icon="heroicon-m-plus">
                                                Додати підкатегорію
                                            </x-filament::button>
                                            <div x-show="open" @click.away="open = false" x-cloak x-transition
                                                 class="absolute left-0 mt-1 w-60 bg-white dark:bg-gray-800 rounded-xl shadow-xl ring-1 ring-gray-950/5 dark:ring-white/10 z-50 max-h-52 overflow-y-auto py-1">
                                                @forelse($this->getChildCategoriesFor($parentCatId) as $childCatId => $childCatTitle)
                                                <button @click="$wire.addChildToItem({{ $colIndex }}, {{ $itemIndex }}, {{ $childCatId }}); open = false" type="button"
                                                    class="flex items-center gap-2 w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                                                    {{ $childCatTitle }}
                                                </button>
                                                @empty
                                                <p class="px-4 py-2 text-xs text-gray-400">Немає дочірніх категорій</p>
                                                @endforelse
                                                <div class="border-t border-gray-100 dark:border-white/5 mt-1 pt-1">
                                                    <button @click="$wire.addChildToItem({{ $colIndex }}, {{ $itemIndex }}); open = false" type="button"
                                                        class="flex items-center gap-2 w-full text-left px-4 py-2 text-xs text-gray-400 hover:bg-gray-50 dark:hover:bg-white/5">
                                                        + Кастомне посилання
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        @else
                                        <x-filament::button wire:click="addChildToItem({{ $colIndex }}, {{ $itemIndex }})" size="xs" color="gray" icon="heroicon-m-plus" class="mt-1">
                                            Додати пункт
                                        </x-filament::button>
                                        @endif
                                    </div>
                                </div>

                                @elseif(($item['type'] ?? '') === 'custom_link')
                                {{-- Custom link block --}}
                                <div class="rounded-lg bg-gray-50 dark:bg-white/5 ring-1 ring-gray-200 dark:ring-white/10 p-3">
                                    <div class="flex items-center gap-2 mb-2">
                                        <x-heroicon-m-link class="w-5 h-5 text-gray-400 flex-shrink-0" />
                                        <div class="flex-1 min-w-0">
                                            <x-filament::input.wrapper>
                                                <x-filament::input
                                                    type="text"
                                                    wire:change="updateMegaItem({{ $colIndex }}, {{ $itemIndex }}, 'title', $event.target.value)"
                                                    :value="$item['title']"
                                                    placeholder="Назва посилання"
                                                />
                                            </x-filament::input.wrapper>
                                        </div>
                                        <x-filament::icon-button icon="heroicon-m-x-mark" size="sm" wire:click="removeItemFromColumn({{ $colIndex }}, {{ $itemIndex }})" color="danger" label="Видалити" />
                                    </div>
                                    <div class="pl-7">
                                        <x-filament::input.wrapper prefix-icon="heroicon-m-globe-alt">
                                            <x-filament::input
                                                type="text"
                                                wire:change="updateMegaItem({{ $colIndex }}, {{ $itemIndex }}, 'url', $event.target.value)"
                                                :value="$item['url'] ?? '/'"
                                                placeholder="/url-адреса"
                                            />
                                        </x-filament::input.wrapper>
                                    </div>
                                </div>
                                @endif
                            </div>
                            @endforeach

                            @if(empty($column))
                            <div class="text-center py-8">
                                <x-heroicon-o-rectangle-group class="w-8 h-8 text-gray-300 dark:text-gray-600 mx-auto mb-2" />
                                <p class="text-xs text-gray-500 dark:text-gray-400">Додайте категорію або посилання</p>
                            </div>
                            @endif
                        </div>

                        {{-- Footer: add buttons --}}
                        <div class="flex gap-2 px-4 py-3 border-t border-gray-100 dark:border-white/5 bg-gray-50/50 dark:bg-white/[0.02] rounded-b-xl">
                            <div x-data="{ open: false }" class="relative flex-1">
                                <x-filament::button @click="open = !open" size="sm" color="gray" icon="heroicon-m-folder-plus" class="w-full justify-center">
                                    Категорія
                                </x-filament::button>
                                <div x-show="open" @click.away="open = false" x-cloak x-transition.origin.bottom
                                     class="absolute bottom-full mb-2 left-0 w-64 bg-white dark:bg-gray-800 rounded-xl shadow-xl ring-1 ring-gray-950/5 dark:ring-white/10 z-50 max-h-60 overflow-y-auto py-1">
                                    @foreach($this->getRootCategories() as $catId => $catTitle)
                                    <button @click="$wire.addCategoryToColumn({{ $colIndex }}, {{ $catId }}); open = false" type="button"
                                        class="flex items-center gap-2 w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                                        <x-heroicon-m-folder class="w-4 h-4 text-gray-400" />
                                        {{ $catTitle }}
                                    </button>
                                    @endforeach
                                </div>
                            </div>
                            <x-filament::button wire:click="addCustomLinkToColumn({{ $colIndex }})" size="sm" color="gray" icon="heroicon-m-link" class="flex-1 justify-center">
                                Посилання
                            </x-filament::button>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-16 bg-gray-50 dark:bg-white/5 rounded-xl ring-1 ring-gray-200 dark:ring-white/10">
                    <x-heroicon-o-squares-2x2 class="w-16 h-16 text-gray-300 dark:text-gray-600 mx-auto mb-4" />
                    <h3 class="text-base font-semibold text-gray-950 dark:text-white mb-1">Мега-меню порожнє</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Додайте колонки та заповніть їх категоріями</p>
                    <div class="flex justify-center gap-3">
                        <x-filament::button wire:click="autoGenerateMegaMenu" color="primary" icon="heroicon-m-bolt">
                            Згенерувати з категорій
                        </x-filament::button>
                        <x-filament::button wire:click="addMegaColumn" color="gray" icon="heroicon-m-plus">
                            Додати колонку
                        </x-filament::button>
                    </div>
                </div>
                @endif
            </div>
        </x-filament::section>

        {{-- ==================== PROMO ==================== --}}
        <x-filament::section icon="heroicon-m-megaphone" icon-color="warning" collapsible>
            <x-slot name="heading">
                Промо-блок
            </x-slot>
            <x-slot name="description">
                Рекламна стрічка внизу мега-меню
            </x-slot>
            <x-slot name="headerEnd">
                <label class="inline-flex items-center gap-2">
                    <x-filament::input.checkbox wire:model.live="showPromo" />
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Увімкнено</span>
                </label>
            </x-slot>

            <div x-data x-show="$wire.showPromo" x-cloak x-transition>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="fi-fo-field-wrp-label text-sm font-medium text-gray-950 dark:text-white">Заголовок</label>
                        <x-filament::input.wrapper>
                            <x-filament::input type="text" wire:model.blur="promoTitle" placeholder="АКЦІЇ ТИЖНЯ" />
                        </x-filament::input.wrapper>
                    </div>
                    <div class="space-y-1.5">
                        <label class="fi-fo-field-wrp-label text-sm font-medium text-gray-950 dark:text-white">Текст кнопки</label>
                        <x-filament::input.wrapper>
                            <x-filament::input type="text" wire:model.blur="promoButton" placeholder="ПЕРЕГЛЯНУТИ ВСІ" />
                        </x-filament::input.wrapper>
                    </div>
                    <div class="space-y-1.5">
                        <label class="fi-fo-field-wrp-label text-sm font-medium text-gray-950 dark:text-white">Опис</label>
                        <x-filament::input.wrapper>
                            <x-filament::input type="text" wire:model.blur="promoSubtitle" placeholder="Знижки до 50%..." />
                        </x-filament::input.wrapper>
                    </div>
                    <div class="space-y-1.5">
                        <label class="fi-fo-field-wrp-label text-sm font-medium text-gray-950 dark:text-white">URL кнопки</label>
                        <x-filament::input.wrapper prefix-icon="heroicon-m-link">
                            <x-filament::input type="text" wire:model.blur="promoUrl" placeholder="/specials" />
                        </x-filament::input.wrapper>
                    </div>
                </div>
            </div>
            <div x-data x-show="!$wire.showPromo" x-cloak>
                <p class="text-sm text-gray-500 dark:text-gray-400 italic">Промо-блок вимкнено</p>
            </div>
        </x-filament::section>

    </div>
</x-filament-panels::page>
