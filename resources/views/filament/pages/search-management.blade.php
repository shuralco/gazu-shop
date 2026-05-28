<x-filament-panels::page>
    {{-- Tab Navigation --}}
    <div class="flex flex-wrap gap-2 border-b border-gray-200 dark:border-gray-700 pb-3 mb-6">
        @foreach ([
            'analytics' => ['Аналітика пошуку', 'heroicon-o-chart-bar-square'],
            'synonyms' => ['Синоніми', 'heroicon-o-arrows-right-left'],
            'stopwords' => ['Stop-слова', 'heroicon-o-no-symbol'],
            'index' => ['Налаштування індексу', 'heroicon-o-cog-6-tooth'],
            'zero_results' => ['Запити без результатів', 'heroicon-o-exclamation-triangle'],
            'ai' => ['AI пошук', 'heroicon-o-sparkles'],
        ] as $tab => [$label, $icon])
            <button
                wire:click="$set('activeTab', '{{ $tab }}')"
                @class([
                    'inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-lg transition-colors duration-150',
                    'bg-primary-600 text-white shadow-sm' => $activeTab === $tab,
                    'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' => $activeTab !== $tab,
                ])
            >
                <x-dynamic-component :component="$icon" class="w-5 h-5" />
                {{ $label }}
                @if ($tab === 'zero_results')
                    @php $zeroCount = \App\Models\SearchQuery::where('results_count', 0)->count(); @endphp
                    @if ($zeroCount > 0)
                        <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full">
                            {{ $zeroCount > 99 ? '99+' : $zeroCount }}
                        </span>
                    @endif
                @endif
            </button>
        @endforeach
    </div>

    {{-- Tab 1: Analytics --}}
    @if ($activeTab === 'analytics')
        {{-- Stats Cards --}}
        @php $stats = $this->analyticsStats; @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="rounded-xl bg-white dark:bg-gray-900 p-4 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
                <div class="flex items-center gap-3">
                    <div class="rounded-lg bg-blue-50 dark:bg-blue-500/10 p-2.5">
                        <x-heroicon-o-magnifying-glass class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Всього пошуків</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total_searches'] }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-white dark:bg-gray-900 p-4 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
                <div class="flex items-center gap-3">
                    <div class="rounded-lg bg-purple-50 dark:bg-purple-500/10 p-2.5">
                        <x-heroicon-o-list-bullet class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Унікальних запитів</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['unique_queries'] }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-white dark:bg-gray-900 p-4 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
                <div class="flex items-center gap-3">
                    <div class="rounded-lg bg-red-50 dark:bg-red-500/10 p-2.5">
                        <x-heroicon-o-x-circle class="w-5 h-5 text-red-600 dark:text-red-400" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Без результатів</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['zero_result_percent'] }}%</p>
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-white dark:bg-gray-900 p-4 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
                <div class="flex items-center gap-3">
                    <div class="rounded-lg bg-green-50 dark:bg-green-500/10 p-2.5">
                        <x-heroicon-o-cursor-arrow-rays class="w-5 h-5 text-green-600 dark:text-green-400" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Середній CTR</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['avg_ctr'] }}%</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        {{ $this->table }}
    @endif

    {{-- Tab 2: Synonyms --}}
    @if ($activeTab === 'synonyms')
        <div class="space-y-6">
            <div class="rounded-xl bg-white dark:bg-gray-900 p-6 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Групи синонімів</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Кожна група містить основне слово та його синоніми (через кому). При пошуку будь-якого із синонімів Meilisearch знайде результати для всієї групи.
                        </p>
                    </div>
                    <x-filament::button
                        wire:click="addSynonymGroup"
                        color="primary"
                        icon="heroicon-o-plus"
                    >
                        Додати групу
                    </x-filament::button>
                </div>

                <div class="space-y-3">
                    @forelse ($synonymGroups as $index => $group)
                        <div class="flex items-start gap-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-800" wire:key="synonym-{{ $index }}">
                            <div class="w-1/4">
                                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Основне слово</label>
                                <input
                                    type="text"
                                    wire:model.defer="synonymGroups.{{ $index }}.main_word"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    placeholder="наприклад: ноутбук"
                                >
                            </div>
                            <div class="flex-1">
                                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Синоніми (через кому)</label>
                                <input
                                    type="text"
                                    wire:model.defer="synonymGroups.{{ $index }}.synonyms"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    placeholder="наприклад: ноут, лептоп, laptop, нотбук"
                                >
                            </div>
                            <div class="pt-5">
                                <x-filament::icon-button
                                    icon="heroicon-o-trash"
                                    wire:click="removeSynonymGroup({{ $index }})"
                                    color="danger"
                                    label="Видалити групу"
                                />
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <x-heroicon-o-arrows-right-left class="w-12 h-12 mx-auto mb-3 opacity-50" />
                            <p>Немає груп синонімів</p>
                            <p class="text-sm">Натисніть "Додати групу" щоб створити першу</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="flex items-center gap-3">
                <x-filament::button
                    wire:click="saveSynonyms"
                    wire:loading.attr="disabled"
                    wire:target="saveSynonyms"
                    color="success"
                    icon="heroicon-o-check"
                >
                    Зберегти та переіндексувати
                </x-filament::button>
                <span wire:loading wire:target="saveSynonyms" class="text-sm text-gray-500">
                    Збереження та переіндексація...
                </span>
            </div>
        </div>
    @endif

    {{-- Tab 3: Stop Words --}}
    @if ($activeTab === 'stopwords')
        <div class="space-y-6">
            <div class="rounded-xl bg-white dark:bg-gray-900 p-6 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Stop-слова</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Слова, які ігноруються при пошуку (прийменники, сполучники тощо). Одне слово на рядок або через кому.
                    </p>
                </div>

                <textarea
                    wire:model.defer="stopWordsText"
                    rows="15"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 font-mono"
                    placeholder="і&#10;в&#10;на&#10;з&#10;до"
                ></textarea>

                <p class="text-xs text-gray-400 mt-2">
                    Поточна кількість: {{ count(array_filter(preg_split('/[\n,;]+/', $stopWordsText), fn ($w) => trim($w) !== '')) }} слів
                </p>
            </div>

            <div class="flex items-center gap-3">
                <x-filament::button
                    wire:click="saveStopWords"
                    wire:loading.attr="disabled"
                    wire:target="saveStopWords"
                    color="success"
                    icon="heroicon-o-check"
                >
                    Зберегти та переіндексувати
                </x-filament::button>
            </div>
        </div>
    @endif

    {{-- Tab 4: Index Settings --}}
    @if ($activeTab === 'index')
        @php $msInfo = $this->meilisearchInfo; @endphp
        <div class="space-y-6">
            {{-- Meilisearch Status --}}
            <div class="rounded-xl bg-white dark:bg-gray-900 p-6 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Статус Meilisearch</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Статус</p>
                        <p class="mt-1 flex items-center gap-2">
                            @if ($msInfo['connected'])
                                <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>
                                <span class="text-sm font-medium text-green-600 dark:text-green-400">{{ $msInfo['status'] }}</span>
                            @else
                                <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                                <span class="text-sm font-medium text-red-600 dark:text-red-400">{{ $msInfo['status'] }}</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Версія</p>
                        <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $msInfo['version'] }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Документів</p>
                        <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $msInfo['documents'] }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Остання синхронізація</p>
                        <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">
                            {{ \Illuminate\Support\Facades\Cache::get('search_last_sync', 'Ніколи') }}
                        </p>
                    </div>
                </div>

                @if (!$msInfo['connected'] && isset($msInfo['error']))
                    <div class="mt-4 p-3 rounded-lg bg-red-50 dark:bg-red-500/10 text-sm text-red-700 dark:text-red-300">
                        <strong>Помилка:</strong> {{ $msInfo['error'] }}
                    </div>
                @endif

                @if ($msInfo['is_indexing'])
                    <div class="mt-4 p-3 rounded-lg bg-yellow-50 dark:bg-yellow-500/10 text-sm text-yellow-700 dark:text-yellow-300 flex items-center gap-2">
                        <x-heroicon-o-arrow-path class="w-5 h-5 animate-spin" />
                        Індексація в процесі...
                    </div>
                @endif
            </div>

            {{-- Searchable Attributes (editable priority) --}}
            <div class="rounded-xl bg-white dark:bg-gray-900 p-6 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Пошукові атрибути (пріоритет)</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    Перетягніть стрілками для зміни пріоритету. Перші атрибути мають найвищий пріоритет при пошуку.
                </p>
                <div class="space-y-1.5 mb-4">
                    @foreach ($searchableAttrs as $i => $attr)
                        <div class="flex items-center gap-2 p-2.5 rounded-lg bg-gray-50 dark:bg-gray-800 group">
                            <span class="text-xs font-mono text-gray-400 w-6 text-right">{{ $i + 1 }}.</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white flex-1">{{ $attr }}</span>
                            <div class="flex gap-1 opacity-50 group-hover:opacity-100 transition-opacity">
                                <span @class(['invisible' => $i === 0])>
                                    <x-filament::icon-button icon="heroicon-m-chevron-up" wire:click="moveAttrUp({{ $i }})" color="gray" size="sm" label="Вгору" />
                                </span>
                                <span @class(['invisible' => $i === count($searchableAttrs) - 1])>
                                    <x-filament::icon-button icon="heroicon-m-chevron-down" wire:click="moveAttrDown({{ $i }})" color="gray" size="sm" label="Вниз" />
                                </span>
                                <x-filament::icon-button icon="heroicon-m-x-mark" wire:click="toggleSearchableAttr('{{ $attr }}')" color="danger" size="sm" label="Видалити" />
                            </div>
                        </div>
                    @endforeach
                </div>
                {{-- Add attribute --}}
                @php $unusedAttrs = array_diff($allAvailableAttrs, $searchableAttrs); @endphp
                @if(count($unusedAttrs))
                    <div class="flex flex-wrap gap-2 pt-3 border-t border-gray-200 dark:border-gray-700">
                        <span class="text-xs text-gray-500 self-center mr-1">Додати:</span>
                        @foreach($unusedAttrs as $attr)
                            <x-filament::button wire:click="toggleSearchableAttr('{{ $attr }}')" color="success" size="xs" icon="heroicon-m-plus">
                                {{ $attr }}
                            </x-filament::button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Filterable Attributes (checkboxes) --}}
            <div class="rounded-xl bg-white dark:bg-gray-900 p-6 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Фільтрувальні атрибути</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    Увімкніть атрибути за якими можна фільтрувати результати пошуку.
                </p>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                    @foreach ($allFilterableOptions as $attr)
                        <label class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer">
                            <input type="checkbox"
                                   wire:click="toggleFilterableAttr('{{ $attr }}')"
                                   @checked(in_array($attr, $filterableAttrs))
                                   class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $attr }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Typo Tolerance (editable) --}}
            <div class="rounded-xl bg-white dark:bg-gray-900 p-6 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Толерантність до помилок</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <div class="relative inline-flex items-center">
                                <input type="checkbox" wire:model.live="typoToleranceEnabled" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:after:border-gray-600 peer-checked:bg-primary-600"></div>
                            </div>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Увімкнено</span>
                        </label>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Мін. символів для 1 помилки</label>
                        <input type="number" wire:model.live="minWordOneTypo" min="1" max="20"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Мін. символів для 2 помилок</label>
                        <input type="number" wire:model.live="minWordTwoTypos" min="2" max="30"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white text-sm">
                    </div>
                </div>
            </div>

            {{-- Save Index Settings --}}
            <div class="flex justify-end">
                <x-filament::button wire:click="saveIndexSettings" wire:loading.attr="disabled"
                        wire:target="saveIndexSettings"
                        wire:confirm="Зберегти налаштування та переналаштувати індекс?"
                        color="primary"
                        icon="heroicon-o-check">
                    Зберегти налаштування індексу
                </x-filament::button>
            </div>

            {{-- Action Buttons --}}
            <div class="rounded-xl bg-white dark:bg-gray-900 p-6 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Дії з індексом</h3>
                <div class="flex flex-wrap gap-3">
                    <x-filament::button
                        wire:click="handleReindex"
                        wire:loading.attr="disabled"
                        wire:target="handleReindex"
                        wire:confirm="Переіндексувати всі товари? Це може зайняти деякий час."
                        color="info"
                        icon="heroicon-o-arrow-path"
                    >
                        Переіндексувати
                    </x-filament::button>

                    <x-filament::button
                        wire:click="handleClearIndex"
                        wire:loading.attr="disabled"
                        wire:target="handleClearIndex"
                        wire:confirm="Очистити індекс? Всі документи будуть видалені."
                        color="warning"
                        icon="heroicon-o-trash"
                    >
                        Очистити індекс
                    </x-filament::button>

                    <x-filament::button
                        wire:click="handleFullRebuild"
                        wire:loading.attr="disabled"
                        wire:target="handleFullRebuild"
                        wire:confirm="Повністю перебудувати індекс? Індекс буде видалено та перестворено з нуля."
                        color="danger"
                        icon="heroicon-o-fire"
                    >
                        Перебудувати повністю
                    </x-filament::button>
                </div>
            </div>
        </div>
    @endif

    {{-- Tab 5: Zero Result Queries --}}
    @if ($activeTab === 'zero_results')
        <div class="space-y-6">
            <div class="rounded-xl bg-white dark:bg-gray-900 p-6 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Запити без результатів</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Запити, за якими користувачі нічого не знайшли. Додайте синоніми або товари для покращення пошуку.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="text-left py-3 px-4 font-medium text-gray-500 dark:text-gray-400">Запит</th>
                                <th class="text-center py-3 px-4 font-medium text-gray-500 dark:text-gray-400">Пошуків</th>
                                <th class="text-center py-3 px-4 font-medium text-gray-500 dark:text-gray-400">Останній пошук</th>
                                <th class="text-right py-3 px-4 font-medium text-gray-500 dark:text-gray-400">Дії</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($this->zeroResultQueries as $query)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50" wire:key="zero-{{ $query->id }}">
                                    <td class="py-3 px-4">
                                        <span class="font-medium text-gray-900 dark:text-white">{{ $query->query }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <x-filament::badge color="danger" class="inline-flex">
                                            {{ $query->search_count }}
                                        </x-filament::badge>
                                    </td>
                                    <td class="py-3 px-4 text-center text-gray-500 dark:text-gray-400">
                                        {{ $query->last_searched_at?->format('d.m.Y H:i') ?? '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <x-filament::button
                                                wire:click="openSynonymModalForQuery({{ $query->id }})"
                                                color="warning"
                                                size="xs"
                                                icon="heroicon-o-plus-circle"
                                            >
                                                Додати синонім
                                            </x-filament::button>
                                            <x-filament::button
                                                wire:click="ignoreZeroResultQuery({{ $query->id }})"
                                                wire:confirm="Видалити цей запит зі списку?"
                                                color="gray"
                                                size="xs"
                                                icon="heroicon-o-eye-slash"
                                            >
                                                Ігнорувати
                                            </x-filament::button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-12 text-center text-gray-500 dark:text-gray-400">
                                        <x-heroicon-o-check-circle class="w-12 h-12 mx-auto mb-3 text-green-500 opacity-50" />
                                        <p class="font-medium">Немає запитів без результатів</p>
                                        <p class="text-sm mt-1">Всі пошукові запити повертають результати</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- Synonym Modal --}}
    @if ($showSynonymModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="closeSynonymModal">
            <div class="w-full max-w-lg rounded-xl bg-white dark:bg-gray-900 p-6 shadow-xl">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Додати синонім</h3>
                    <x-filament::icon-button icon="heroicon-o-x-mark" wire:click="closeSynonymModal" color="gray" label="Закрити" />
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Пошуковий запит</label>
                        <input
                            type="text"
                            wire:model="synonymModalQuery"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            readonly
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Синоніми (через кому)</label>
                        <input
                            type="text"
                            wire:model="synonymModalSynonyms"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            placeholder="слово1, слово2, слово3"
                            autofocus
                        >
                        <p class="text-xs text-gray-400 mt-1">Введіть слова, які повинні знаходити цей запит</p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6">
                    <x-filament::button
                        wire:click="closeSynonymModal"
                        color="gray"
                    >
                        Скасувати
                    </x-filament::button>
                    <x-filament::button
                        wire:click="saveSynonymFromModal"
                        color="primary"
                    >
                        Зберегти та переіндексувати
                    </x-filament::button>
                </div>
            </div>
        </div>
    @endif

    {{-- ═══════ AI Search Tab ═══════ --}}
    @if ($activeTab === 'ai')
        <div class="space-y-6">
            {{-- AI Tags --}}
            <div class="rounded-xl bg-white dark:bg-gray-900 p-6 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">AI генерація пошукових тегів</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    AI аналізує назву, категорію, бренд та ціну товару і генерує теги для покращення пошуку:
                    синоніми, призначення, цінову категорію, розмовні назви.
                </p>
                <div class="flex flex-wrap gap-3">
                    <x-filament::button wire:click="generateAiTagsPrompt" wire:loading.attr="disabled"
                            wire:target="generateAiTagsPrompt"
                            color="primary"
                            icon="heroicon-o-sparkles">
                        Згенерувати промт для тегів
                    </x-filament::button>
                    <span class="text-xs text-gray-400 self-center">
                        Товарів без тегів: {{ \App\Models\Product::where('is_active', true)->where(function($q) { $q->whereNull('search_tags')->orWhere('search_tags', ''); })->count() }}
                        / Всього: {{ \App\Models\Product::where('is_active', true)->count() }}
                    </span>
                </div>
            </div>

            {{-- AI Synonyms --}}
            <div class="rounded-xl bg-white dark:bg-gray-900 p-6 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">AI генерація синонімів</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    AI аналізує запити без результатів і пропонує синоніми для покращення пошуку.
                    Наприклад: якщо шукають "зарядка" → додасть синонім "зарядка" = "power bank, кабель, зарядний".
                </p>
                <div class="flex flex-wrap gap-3">
                    <x-filament::button wire:click="generateAiSynonymsPrompt" wire:loading.attr="disabled"
                            wire:target="generateAiSynonymsPrompt"
                            color="warning"
                            icon="heroicon-o-sparkles">
                        Згенерувати промт для синонімів
                    </x-filament::button>
                    <span class="text-xs text-gray-400 self-center">
                        Запитів без результатів: {{ \App\Models\SearchQuery::where('results_count', 0)->count() }}
                    </span>
                </div>
            </div>

            {{-- How it works --}}
            <div class="rounded-xl bg-blue-50 dark:bg-blue-900/20 p-6 ring-1 ring-blue-200 dark:ring-blue-800">
                <h4 class="font-semibold text-blue-900 dark:text-blue-200 mb-3">Як це працює</h4>
                <ol class="list-decimal list-inside space-y-2 text-sm text-blue-800 dark:text-blue-300">
                    <li>Натисніть "Згенерувати промт" — система створить детальний промт</li>
                    <li><strong>Без API:</strong> Скопіюйте промт → вставте в ChatGPT/Claude/Gemini → скопіюйте JSON відповідь → вставте в поле → "Застосувати"</li>
                    <li><strong>З API:</strong> Натисніть "Згенерувати через API" → система зробить все автоматично</li>
                    <li>Результат зберігається в товарах та/або синонімах і одразу доступний для пошуку</li>
                </ol>
            </div>
        </div>
    @endif

    {{-- ═══════ AI Tags Modal ═══════ --}}
    @if ($showAiTagsModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background: rgba(0,0,0,0.5)">
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-semibold">AI генерація тегів ({{ $aiTagsTotal }} товарів)</h3>
                    <x-filament::icon-button icon="heroicon-o-x-mark" wire:click="$set('showAiTagsModal', false)" color="gray" label="Закрити" />
                </div>
                <div class="p-6 space-y-4">
                    {{-- Prompt --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">Промт (скопіюйте в AI)</label>
                        <textarea readonly class="w-full h-48 text-xs font-mono border border-gray-300 dark:border-gray-600 rounded-lg p-3 bg-gray-50 dark:bg-gray-800 dark:text-gray-200">{{ $aiTagsPrompt }}</textarea>
                        <button onclick="navigator.clipboard.writeText(document.querySelector('[wire\\:click=\'generateAiTagsViaApi\']')?.previousElementSibling?.previousElementSibling?.querySelector('textarea')?.value || this.previousElementSibling.value); this.textContent='Скопійовано!'; setTimeout(() => this.textContent='Копіювати промт', 2000)"
                                class="mt-1 text-xs text-primary-600 hover:text-primary-800 font-medium">Копіювати промт</button>
                    </div>

                    {{-- API button --}}
                    @if(\App\Models\DisplaySetting::get('ai_provider', 'none') !== 'none')
                    <x-filament::button wire:click="generateAiTagsViaApi" wire:loading.attr="disabled"
                            wire:target="generateAiTagsViaApi"
                            color="success"
                            icon="heroicon-o-bolt">
                        Згенерувати через API
                    </x-filament::button>
                    @endif

                    {{-- Result --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">JSON відповідь від AI (вставте сюди)</label>
                        <textarea wire:model.defer="aiTagsResult" rows="10"
                                  class="w-full text-xs font-mono border border-gray-300 dark:border-gray-600 rounded-lg p-3 dark:bg-gray-800 dark:text-gray-200"
                                  placeholder='[{"id": 1, "search_tags": "тег1, тег2, тег3"}, ...]'></textarea>
                    </div>
                </div>
                <div class="p-6 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                    <x-filament::button wire:click="$set('showAiTagsModal', false)" color="gray">
                        Скасувати
                    </x-filament::button>
                    <x-filament::button wire:click="applyAiTags" wire:loading.attr="disabled"
                            wire:target="applyAiTags"
                            wire:confirm="Застосувати AI теги до товарів?"
                            color="primary">
                        Застосувати теги
                    </x-filament::button>
                </div>
            </div>
        </div>
    @endif

    {{-- ═══════ AI Synonyms Modal ═══════ --}}
    @if ($showAiSynonymsModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background: rgba(0,0,0,0.5)">
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-semibold">AI генерація синонімів</h3>
                    <x-filament::icon-button icon="heroicon-o-x-mark" wire:click="$set('showAiSynonymsModal', false)" color="gray" label="Закрити" />
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Промт (скопіюйте в AI)</label>
                        <textarea readonly class="w-full h-48 text-xs font-mono border border-gray-300 dark:border-gray-600 rounded-lg p-3 bg-gray-50 dark:bg-gray-800 dark:text-gray-200">{{ $aiSynonymsPrompt }}</textarea>
                        <button onclick="navigator.clipboard.writeText(this.previousElementSibling.value); this.textContent='Скопійовано!'; setTimeout(() => this.textContent='Копіювати промт', 2000)"
                                class="mt-1 text-xs text-primary-600 hover:text-primary-800 font-medium">Копіювати промт</button>
                    </div>

                    @if(\App\Models\DisplaySetting::get('ai_provider', 'none') !== 'none')
                    <x-filament::button wire:click="generateAiSynonymsViaApi" wire:loading.attr="disabled"
                            wire:target="generateAiSynonymsViaApi"
                            color="success"
                            icon="heroicon-o-bolt">
                        Згенерувати через API
                    </x-filament::button>
                    @endif

                    <div>
                        <label class="block text-sm font-medium mb-1">JSON відповідь від AI</label>
                        <textarea wire:model.defer="aiSynonymsResult" rows="10"
                                  class="w-full text-xs font-mono border border-gray-300 dark:border-gray-600 rounded-lg p-3 dark:bg-gray-800 dark:text-gray-200"
                                  placeholder='[{"query": "зарядка", "main_word": "power bank", "synonyms": "зарядка, зарядний"}, ...]'></textarea>
                    </div>
                </div>
                <div class="p-6 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                    <x-filament::button wire:click="$set('showAiSynonymsModal', false)" color="gray">
                        Скасувати
                    </x-filament::button>
                    <x-filament::button wire:click="applyAiSynonyms" wire:loading.attr="disabled"
                            wire:target="applyAiSynonyms"
                            wire:confirm="Додати AI синоніми та переіндексувати?"
                            color="primary">
                        Застосувати синоніми
                    </x-filament::button>
                </div>
            </div>
        </div>
    @endif

    <x-filament-actions::modals />
</x-filament-panels::page>
