<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Header actions --}}
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Перетягуйте модулі для зміни порядку. Натисніть на модуль для редагування налаштувань.
                </p>
            </div>
            <x-filament::button wire:click="openAddModal" icon="heroicon-o-plus">
                Додати модуль
            </x-filament::button>
        </div>

        {{-- Modules list --}}
        <div class="space-y-3">
            @php $modules = $this->getModules(); @endphp
            @forelse($modules as $module)
                @php
                    $types = $this->getAvailableTypes();
                    $typeInfo = $types[$module->type] ?? ['name' => $module->type, 'emoji' => '📦', 'description' => ''];
                @endphp
                <div class="relative bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 transition-all
                    {{ !$module->is_active ? 'opacity-50' : '' }}">
                    <div class="flex items-center gap-4">
                        {{-- Reorder buttons --}}
                        <div class="flex flex-col gap-1">
                            <button wire:click="moveUp({{ $module->id }})"
                                    class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                                    title="Вгору">
                                <x-heroicon-o-chevron-up class="w-4 h-4" />
                            </button>
                            <button wire:click="moveDown({{ $module->id }})"
                                    class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                                    title="Вниз">
                                <x-heroicon-o-chevron-down class="w-4 h-4" />
                            </button>
                        </div>

                        {{-- Module icon --}}
                        <div class="flex-shrink-0 w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center text-2xl">
                            {{ $typeInfo['emoji'] }}
                        </div>

                        {{-- Module info --}}
                        <div class="flex-grow min-w-0">
                            <div class="flex items-center gap-2">
                                <h3 class="font-semibold text-gray-900 dark:text-white truncate">
                                    {{ $module->title ?: $typeInfo['name'] }}
                                </h3>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                                    {{ $typeInfo['name'] }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                                {{ $typeInfo['description'] }}
                            </p>
                        </div>

                        {{-- Sort order badge --}}
                        <div class="flex-shrink-0 w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                            <span class="text-xs font-bold text-gray-500 dark:text-gray-400">{{ $module->sort_order }}</span>
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center gap-2 flex-shrink-0">
                            {{-- Toggle --}}
                            <button wire:click="toggleModule({{ $module->id }})"
                                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none
                                        {{ $module->is_active ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-600' }}"
                                    title="{{ $module->is_active ? 'Вимкнути' : 'Увімкнути' }}">
                                <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out
                                    {{ $module->is_active ? 'translate-x-5' : 'translate-x-0' }}"></span>
                            </button>

                            {{-- Edit --}}
                            <x-filament::icon-button
                                wire:click="openEditModal({{ $module->id }})"
                                icon="heroicon-o-pencil-square"
                                color="gray"
                                tooltip="Редагувати"
                            />

                            {{-- Delete --}}
                            <x-filament::icon-button
                                wire:click="deleteModule({{ $module->id }})"
                                wire:confirm="Видалити цей модуль?"
                                icon="heroicon-o-trash"
                                color="danger"
                                tooltip="Видалити"
                            />
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12">
                    <x-heroicon-o-squares-plus class="w-12 h-12 mx-auto text-gray-400" />
                    <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">Немає модулів</h3>
                    <p class="mt-2 text-sm text-gray-500">Додайте перший модуль для побудови головної сторінки.</p>
                </div>
            @endforelse
        </div>

        {{-- Preview link --}}
        <div class="flex justify-center pt-4">
            <a href="{{ url('/') }}" target="_blank" class="inline-flex items-center gap-2 text-sm text-primary-600 hover:text-primary-700">
                <x-heroicon-o-eye class="w-4 h-4" />
                Переглянути головну сторінку
            </a>
        </div>
    </div>

    {{-- ADD MODULE MODAL --}}
    @if($showAddModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/50 transition-opacity" wire:click="closeAddModal"></div>

            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-2xl w-full p-6 z-10">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Додати модуль</h2>
                    <button wire:click="closeAddModal" class="text-gray-400 hover:text-gray-600">
                        <x-heroicon-o-x-mark class="w-6 h-6" />
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    @foreach($this->getAvailableTypes() as $type => $info)
                        <button wire:click="addModule('{{ $type }}')"
                                class="flex items-start gap-3 p-4 rounded-lg border-2 border-gray-200 dark:border-gray-600 hover:border-primary-500 dark:hover:border-primary-500 transition-colors text-left">
                            <span class="text-2xl flex-shrink-0 mt-0.5">{{ $info['emoji'] }}</span>
                            <div>
                                <div class="font-semibold text-gray-900 dark:text-white">{{ $info['name'] }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $info['description'] }}</div>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- EDIT MODULE MODAL --}}
    @if($showEditModal && $editingModuleId)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/50 transition-opacity" wire:click="closeEditModal"></div>

            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-2xl w-full p-6 z-10 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-6">
                    @php
                        $types = $this->getAvailableTypes();
                        $typeInfo = $types[$editingModuleType] ?? ['name' => $editingModuleType, 'emoji' => '📦'];
                    @endphp
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                        {{ $typeInfo['emoji'] }} Редагувати: {{ $typeInfo['name'] }}
                    </h2>
                    <button wire:click="closeEditModal" class="text-gray-400 hover:text-gray-600">
                        <x-heroicon-o-x-mark class="w-6 h-6" />
                    </button>
                </div>

                <div class="space-y-4">
                    {{-- Common: Title --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Заголовок секції</label>
                        <input type="text" wire:model="moduleTitle"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                               placeholder="Заголовок (або залиште порожнім)">
                    </div>

                    {{-- Type-specific settings --}}
                    @if($editingModuleType === 'hero')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Підзаголовок (badge)</label>
                            <input type="text" wire:model="moduleSettings.subtitle"
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Рядок 1 заголовку</label>
                                <input type="text" wire:model="moduleSettings.title_line1"
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Рядок 2 заголовку</label>
                                <input type="text" wire:model="moduleSettings.title_line2"
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Опис (кожен рядок окремо)</label>
                            <textarea wire:model="moduleSettings.description" rows="3"
                                      class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Текст кнопки</label>
                                <input type="text" wire:model="moduleSettings.button_text"
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">URL кнопки</label>
                                <input type="text" wire:model="moduleSettings.button_url"
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Колір фону</label>
                            <input type="color" wire:model="moduleSettings.bg_color"
                                   class="w-20 h-10 rounded border-gray-300 cursor-pointer">
                        </div>

                    @elseif($editingModuleType === 'products_grid')
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Фільтр</label>
                                <select wire:model="moduleSettings.filter"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
                                    <option value="hits">Хіти продажів</option>
                                    <option value="new">Новинки</option>
                                    <option value="specials">Акційні</option>
                                    <option value="all">Всі товари</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Кількість</label>
                                <select wire:model="moduleSettings.limit"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
                                    <option value="4">4</option>
                                    <option value="8">8</option>
                                    <option value="12">12</option>
                                    <option value="16">16</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Колонки</label>
                                <select wire:model="moduleSettings.columns"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                </select>
                            </div>
                        </div>

                    @elseif($editingModuleType === 'categories')
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Кількість</label>
                                <input type="number" wire:model="moduleSettings.limit" min="1" max="20"
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Стиль</label>
                                <select wire:model="moduleSettings.style"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
                                    <option value="grid">Сітка</option>
                                    <option value="list">Список</option>
                                </select>
                            </div>
                        </div>

                    @elseif($editingModuleType === 'banner')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Основний текст</label>
                            <input type="text" wire:model="moduleSettings.text"
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Додатковий текст</label>
                            <input type="text" wire:model="moduleSettings.subtext"
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Текст кнопки</label>
                                <input type="text" wire:model="moduleSettings.button_text"
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">URL кнопки</label>
                                <input type="text" wire:model="moduleSettings.button_url"
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Колір фону</label>
                                <input type="color" wire:model="moduleSettings.bg_color"
                                       class="w-20 h-10 rounded border-gray-300 cursor-pointer">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Колір тексту</label>
                                <input type="color" wire:model="moduleSettings.text_color"
                                       class="w-20 h-10 rounded border-gray-300 cursor-pointer">
                            </div>
                        </div>

                    @elseif($editingModuleType === 'text')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">HTML контент</label>
                            <textarea wire:model="moduleSettings.content" rows="10"
                                      class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm font-mono text-sm"></textarea>
                        </div>

                    @elseif($editingModuleType === 'brands')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Кількість брендів</label>
                            <input type="number" wire:model="moduleSettings.limit" min="1" max="30"
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
                        </div>

                    @elseif($editingModuleType === 'advantages')
                        <div class="space-y-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Переваги (до 6 елементів)</label>
                            @foreach($moduleSettings['items'] ?? [] as $index => $item)
                                <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                    <div class="w-16">
                                        <label class="block text-xs text-gray-500 mb-1">Іконка</label>
                                        <input type="text" wire:model="moduleSettings.items.{{ $index }}.icon"
                                               class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-center text-xl p-1">
                                    </div>
                                    <div class="flex-1">
                                        <label class="block text-xs text-gray-500 mb-1">Заголовок</label>
                                        <input type="text" wire:model="moduleSettings.items.{{ $index }}.title"
                                               class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                                    </div>
                                    <div class="flex-1">
                                        <label class="block text-xs text-gray-500 mb-1">Текст</label>
                                        <input type="text" wire:model="moduleSettings.items.{{ $index }}.text"
                                               class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                                    </div>
                                    <button wire:click="$set('moduleSettings.items', {{ json_encode(collect($moduleSettings['items'] ?? [])->forget($index)->values()->toArray()) }})"
                                            class="mt-5 text-red-500 hover:text-red-700">
                                        <x-heroicon-o-trash class="w-4 h-4" />
                                    </button>
                                </div>
                            @endforeach
                            @if(count($moduleSettings['items'] ?? []) < 6)
                                <button wire:click="$set('moduleSettings.items', {{ json_encode(array_merge($moduleSettings['items'] ?? [], [['icon' => '⭐', 'title' => '', 'text' => '']])) }})"
                                        class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                                    + Додати перевагу
                                </button>
                            @endif
                        </div>

                    @elseif($editingModuleType === 'newsletter')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Заголовок</label>
                            <input type="text" wire:model="moduleSettings.title"
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Опис</label>
                            <input type="text" wire:model="moduleSettings.description"
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Текст кнопки</label>
                            <input type="text" wire:model="moduleSettings.button_text"
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
                        </div>

                    @elseif($editingModuleType === 'reviews')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Кількість відгуків</label>
                            <input type="number" wire:model="moduleSettings.limit" min="1" max="20"
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
                        </div>

                    @elseif($editingModuleType === 'hero_slider')
                        <div class="space-y-4">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Налаштування слайдів. Кожен слайд — JSON-об'єкт з полями: subtitle, title, description, button_text, button_url, bg_color, text_color.</p>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Кількість слайдів: {{ count($moduleSettings['slides'] ?? []) }}</label>
                                <textarea wire:model="moduleSettings.slides_json" rows="10"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm font-mono text-sm"
                                    placeholder='[{"subtitle":"АКЦІЯ","title":"ЗНИЖКИ","description":"...","button_text":"ДИВИТИСЬ","button_url":"/specials","bg_color":"#000","text_color":"#fff"}]'></textarea>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    <input type="checkbox" wire:model="moduleSettings.autoplay"
                                        class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500">
                                    Автопрокрутка
                                </label>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Інтервал (мс)</label>
                                    <input type="number" wire:model="moduleSettings.interval" min="1000" max="30000" step="500"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
                                </div>
                            </div>
                        </div>

                    @elseif($editingModuleType === 'countdown')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Дата закінчення</label>
                            <input type="datetime-local" wire:model="moduleSettings.end_date"
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Заголовок</label>
                            <input type="text" wire:model="moduleSettings.title"
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Опис</label>
                            <input type="text" wire:model="moduleSettings.description"
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
                        </div>
                    @endif
                </div>

                {{-- Save / Cancel --}}
                <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <x-filament::button color="gray" wire:click="closeEditModal">
                        Скасувати
                    </x-filament::button>
                    <x-filament::button wire:click="saveModuleSettings">
                        Зберегти
                    </x-filament::button>
                </div>
            </div>
        </div>
    </div>
    @endif
</x-filament-panels::page>
