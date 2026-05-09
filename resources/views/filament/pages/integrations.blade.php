<x-filament-panels::page>
    <div class="space-y-8">
        @php
            // Pre-compute totals for header summary
            $allIntegrations = collect($this->getIntegrationManager()->all());
            $enabledCount = $allIntegrations->filter(fn($i) => $i->isEnabled())->count();
            $totalCount = $allIntegrations->count();
            $statusCounts = ['ok' => 0, 'warning' => 0, 'error' => 0, 'unknown' => 0];
            foreach ($allIntegrations as $i) {
                $lvl = $i->getStatus()['level'] ?? 'unknown';
                $statusCounts[$lvl] = ($statusCounts[$lvl] ?? 0) + 1;
            }
            $matched = $this->getMatchedTotal();
            $activeFilter = $this->filter;
        @endphp

        {{-- Header summary --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 space-y-4">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Модулі та інтеграції</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Увімкніть потрібні модулі та налаштуйте їх для роботи магазину.
                    </p>
                </div>
                <div class="flex items-center gap-2 text-sm flex-wrap">
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                        <span class="inline-block w-2 h-2 rounded-full bg-green-500"></span>
                        Готових: {{ $statusCounts['ok'] }}
                    </span>
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400">
                        <span class="inline-block w-2 h-2 rounded-full bg-yellow-500"></span>
                        Увага: {{ $statusCounts['warning'] }}
                    </span>
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400">
                        <span class="inline-block w-2 h-2 rounded-full bg-red-500"></span>
                        Помилок: {{ $statusCounts['error'] }}
                    </span>
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                        Усього: {{ $enabledCount }}/{{ $totalCount }}
                    </span>
                </div>
            </div>

            {{-- Search + filter --}}
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative flex-1 min-w-[240px]">
                    <input
                        type="search"
                        wire:model.live.debounce.250ms="search"
                        placeholder="Пошук модуля..."
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white pl-9 pr-9 py-2 text-sm focus:border-primary-500 focus:ring-primary-500"
                    />
                    <x-heroicon-m-magnifying-glass class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                    @if (! empty($this->search))
                        <button type="button" wire:click="clearSearch" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <x-heroicon-m-x-mark class="w-4 h-4" />
                        </button>
                    @endif
                </div>

                @php
                    $filters = [
                        'all' => 'Усі',
                        'enabled' => 'Увімкнені',
                        'disabled' => 'Вимкнені',
                        'ok' => 'Готові',
                        'warning' => 'Увага',
                        'error' => 'Помилки',
                    ];
                @endphp
                <div class="flex items-center gap-1 flex-wrap">
                    @foreach ($filters as $key => $label)
                        <button
                            type="button"
                            wire:click="setFilter('{{ $key }}')"
                            class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors {{ $activeFilter === $key ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600' }}"
                        >
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>

            @if ($matched === 0)
                <div class="text-sm text-gray-500 dark:text-gray-400 py-2">
                    Нічого не знайдено за запитом «{{ $this->search }}» з фільтром «{{ $filters[$activeFilter] ?? 'Усі' }}».
                </div>
            @endif
        </div>

        @foreach ($this->getGroups() as $groupKey => $groupLabel)
            @php $integrations = $this->getIntegrationsByGroup($groupKey); @endphp

            @if (count($integrations) > 0)
                <div>
                    <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">
                        {{ $groupLabel }}
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($integrations as $integration)
                            @php
                                $key = $integration->getKey();
                                $enabled = $integration->isEnabled();
                                $hasConfig = count($integration->getConfigFields()) > 0;
                                $settingsRoute = $integration->getSettingsRoute();
                                $status = $integration->getStatus();
                                $statusColors = [
                                    'ok' => ['bg' => 'bg-green-50 dark:bg-green-900/30', 'text' => 'text-green-700 dark:text-green-400', 'dot' => 'bg-green-500'],
                                    'warning' => ['bg' => 'bg-yellow-50 dark:bg-yellow-900/30', 'text' => 'text-yellow-700 dark:text-yellow-400', 'dot' => 'bg-yellow-500'],
                                    'error' => ['bg' => 'bg-red-50 dark:bg-red-900/30', 'text' => 'text-red-700 dark:text-red-400', 'dot' => 'bg-red-500'],
                                    'unknown' => ['bg' => 'bg-gray-100 dark:bg-gray-700', 'text' => 'text-gray-600 dark:text-gray-300', 'dot' => 'bg-gray-400'],
                                ];
                                $sc = $statusColors[$status['level']] ?? $statusColors['unknown'];
                            @endphp

                            <div class="relative bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 transition-all duration-200 hover:shadow-md flex flex-col {{ $enabled ? 'ring-2 ring-primary-500/30' : '' }}">
                                {{-- Status indicator dot (top right) --}}
                                <div class="absolute top-4 right-4">
                                    <span class="inline-block w-3 h-3 rounded-full {{ $enabled ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600' }}"></span>
                                </div>

                                {{-- Icon + Name --}}
                                <div class="flex items-center gap-3 mb-3 pr-8">
                                    <span class="text-2xl">{{ $integration->getIcon() }}</span>
                                    <div class="min-w-0">
                                        <h3 class="font-semibold text-gray-900 dark:text-white truncate">
                                            {{ $integration->getName() }}
                                        </h3>
                                    </div>
                                </div>

                                {{-- Description --}}
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3 line-clamp-2 flex-1">
                                    {{ $integration->getDescription() }}
                                </p>

                                {{-- Status badge --}}
                                <div class="mb-3">
                                    <span class="inline-flex items-center gap-1.5 px-2 py-1 text-xs font-medium rounded-md {{ $sc['bg'] }} {{ $sc['text'] }}">
                                        <span class="inline-block w-1.5 h-1.5 rounded-full {{ $sc['dot'] }}"></span>
                                        {{ $status['message'] }}
                                    </span>
                                </div>

                                {{-- Actions --}}
                                <div class="flex items-center justify-between gap-2 pt-3 border-t border-gray-100 dark:border-gray-700">
                                    {{-- Toggle --}}
                                    <button
                                        wire:click="toggleIntegration('{{ $key }}')"
                                        type="button"
                                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 {{ $enabled ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-600' }}"
                                        role="switch"
                                        aria-checked="{{ $enabled ? 'true' : 'false' }}"
                                        aria-label="{{ $enabled ? 'Вимкнути' : 'Увімкнути' }} {{ $integration->getName() }}"
                                    >
                                        <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $enabled ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                    </button>

                                    <div class="flex items-center gap-2 flex-wrap justify-end">
                                        @if ($key === 'telegram' && $enabled)
                                            <button
                                                wire:click="testTelegram"
                                                type="button"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-lg text-blue-700 bg-blue-50 hover:bg-blue-100 dark:text-blue-400 dark:bg-blue-900/30 dark:hover:bg-blue-900/50 transition-colors"
                                            >
                                                Тест
                                            </button>
                                        @endif

                                        @php
                                            $genericUrl = method_exists($integration, 'getGenericConfigUrl') ? $integration->getGenericConfigUrl() : null;
                                            $settingsUrl = $settingsRoute ? route($settingsRoute) : $genericUrl;
                                        @endphp
                                        @if ($settingsUrl)
                                            <a
                                                href="{{ $settingsUrl }}"
                                                wire:navigate
                                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-lg text-primary-700 bg-primary-50 hover:bg-primary-100 dark:text-primary-400 dark:bg-primary-900/30 dark:hover:bg-primary-900/50 transition-colors"
                                            >
                                                <x-heroicon-m-cog-6-tooth class="w-3.5 h-3.5" />
                                                Налаштувати
                                            </a>
                                        @elseif ($hasConfig)
                                            <button
                                                wire:click="openConfig('{{ $key }}')"
                                                type="button"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-lg text-gray-700 bg-gray-100 hover:bg-gray-200 dark:text-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 transition-colors"
                                            >
                                                <x-heroicon-m-cog-6-tooth class="w-3.5 h-3.5" />
                                                Налаштувати
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    {{-- Configuration Modal (used by integrations without dedicated settings page) --}}
    <x-filament::modal id="integration-config" width="lg">
        <x-slot name="heading">
            @if ($this->editingIntegration)
                @php $editIntegration = $this->getIntegrationManager()->get($this->editingIntegration); @endphp
                {{ $editIntegration?->getIcon() }} Налаштування {{ $editIntegration?->getName() }}
            @endif
        </x-slot>

        @if ($this->editingIntegration)
            @php $editIntegration = $this->getIntegrationManager()->get($this->editingIntegration); @endphp

            <div class="space-y-4">
                @foreach ($editIntegration->getConfigFields() as $field)
                    <div>
                        @if ($field['type'] === 'toggle')
                            <label class="flex items-center justify-between gap-3 cursor-pointer">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ $field['label'] }}
                                </span>
                                <button
                                    wire:click="$set('configData.{{ $field['key'] }}', {{ isset($this->configData[$field['key']]) && $this->configData[$field['key']] ? 'false' : 'true' }})"
                                    type="button"
                                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 {{ !empty($this->configData[$field['key']]) ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-600' }}"
                                    role="switch"
                                    aria-checked="{{ !empty($this->configData[$field['key']]) ? 'true' : 'false' }}"
                                >
                                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ !empty($this->configData[$field['key']]) ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                </button>
                            </label>
                        @else
                            <label class="block">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ $field['label'] }}
                                </span>
                                <input
                                    type="{{ $field['type'] }}"
                                    wire:model.defer="configData.{{ $field['key'] }}"
                                    placeholder="{{ $field['placeholder'] ?? '' }}"
                                    class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                                />
                            </label>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        <x-slot name="footerActions">
            <x-filament::button wire:click="saveConfig" color="primary">
                Зберегти
            </x-filament::button>
            <x-filament::button wire:click="closeConfig" color="gray">
                Скасувати
            </x-filament::button>
        </x-slot>
    </x-filament::modal>
</x-filament-panels::page>
