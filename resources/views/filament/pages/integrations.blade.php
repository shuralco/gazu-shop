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

        {{-- Header summary --}}
        <x-filament::section icon="heroicon-o-puzzle-piece">
            <x-slot name="heading">Модулі та інтеграції</x-slot>
            <x-slot name="description">
                Увімкніть потрібні модулі та налаштуйте їх для роботи магазину.
            </x-slot>
            <x-slot name="headerEnd">
                <div class="flex items-center gap-2 flex-wrap">
                    <x-filament::badge color="success">Готових: {{ $statusCounts['ok'] }}</x-filament::badge>
                    <x-filament::badge color="warning">Увага: {{ $statusCounts['warning'] }}</x-filament::badge>
                    <x-filament::badge color="danger">Помилок: {{ $statusCounts['error'] }}</x-filament::badge>
                    <x-filament::badge color="gray">Усього: {{ $enabledCount }}/{{ $totalCount }}</x-filament::badge>
                </div>
            </x-slot>

            <div class="space-y-4">
                {{-- Search + filter --}}
                <div class="flex flex-wrap items-center gap-3">
                    <div class="relative" style="flex:1 1 0%;min-width:240px">
                        <input
                            type="search"
                            wire:model.live.debounce.250ms="search"
                            placeholder="Пошук модуля..."
                            class="fi-input block w-full rounded-lg border-none bg-white pl-9 pr-9 py-1.5 text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 transition focus:ring-2 focus:ring-inset focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/10"
                        />
                        <x-heroicon-m-magnifying-glass class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                        @if (! empty($this->search))
                            <button type="button" wire:click="clearSearch" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <x-heroicon-m-x-mark class="w-4 h-4" />
                            </button>
                        @endif
                    </div>

                    <div class="flex items-center gap-1 flex-wrap">
                        @foreach ($filters as $key => $label)
                            <x-filament::button
                                wire:click="setFilter('{{ $key }}')"
                                size="sm"
                                :color="$activeFilter === $key ? 'primary' : 'gray'"
                                :outlined="$activeFilter !== $key"
                            >
                                {{ $label }}
                            </x-filament::button>
                        @endforeach
                    </div>
                </div>

                @if ($matched === 0)
                    <div class="text-sm text-gray-500 dark:text-gray-400 py-2">
                        Нічого не знайдено за запитом «{{ $this->search }}» з фільтром «{{ $filters[$activeFilter] ?? 'Усі' }}».
                    </div>
                @endif
            </div>
        </x-filament::section>

        @foreach ($this->getGroups() as $groupKey => $groupLabel)
            @php $integrations = $this->getIntegrationsByGroup($groupKey); @endphp

            @if (count($integrations) > 0)
                <div>
                    <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">
                        {{ $groupLabel }}
                    </h2>

                    <div style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fill,minmax(290px,1fr))">
                        @foreach ($integrations as $integration)
                            @php
                                $key = $integration->getKey();
                                $enabled = $integration->isEnabled();
                                $hasConfig = count($integration->getConfigFields()) > 0;
                                $settingsRoute = $integration->getSettingsRoute();
                                $status = $integration->getStatus();
                                $statusColors = [
                                    'ok' => 'success',
                                    'warning' => 'warning',
                                    'error' => 'danger',
                                    'unknown' => 'gray',
                                ];
                                $statusIcons = [
                                    'ok' => 'heroicon-m-check-circle',
                                    'warning' => 'heroicon-m-exclamation-triangle',
                                    'error' => 'heroicon-m-x-circle',
                                    'unknown' => 'heroicon-m-question-mark-circle',
                                ];
                                $sc = $statusColors[$status['level']] ?? $statusColors['unknown'];
                                $scIcon = $statusIcons[$status['level']] ?? $statusIcons['unknown'];
                            @endphp

                            <div class="relative rounded-xl bg-white p-5 shadow-sm ring-1 transition hover:shadow-md flex flex-col dark:bg-white/5 {{ $enabled ? 'ring-2 ring-primary-500/40' : 'ring-gray-950/5 dark:ring-white/10' }}">
                                {{-- Status indicator dot (top right) --}}
                                <div class="absolute top-4 right-4">
                                    <span class="inline-block w-3 h-3 rounded-full {{ $enabled ? 'bg-success-500' : 'bg-gray-300 dark:bg-gray-600' }}"></span>
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
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3 line-clamp-2" style="flex:1 1 0%">
                                    {{ $integration->getDescription() }}
                                </p>

                                {{-- Status badge --}}
                                <div class="mb-3">
                                    <x-filament::badge :color="$sc" :icon="$scIcon">
                                        {{ $status['message'] }}
                                    </x-filament::badge>
                                </div>

                                {{-- Actions --}}
                                <div class="flex items-center justify-between gap-2 pt-3 border-t border-gray-100 dark:border-white/10">
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
                                            <x-filament::button
                                                wire:click="testTelegram"
                                                color="info"
                                                size="sm"
                                                outlined
                                            >
                                                Тест
                                            </x-filament::button>
                                        @endif

                                        @php
                                            $genericUrl = method_exists($integration, 'getGenericConfigUrl') ? $integration->getGenericConfigUrl() : null;
                                            $settingsUrl = $settingsRoute ? route($settingsRoute) : $genericUrl;
                                        @endphp
                                        @if ($settingsUrl)
                                            <x-filament::button
                                                tag="a"
                                                :href="$settingsUrl"
                                                wire:navigate
                                                color="primary"
                                                size="sm"
                                                icon="heroicon-m-cog-6-tooth"
                                            >
                                                Налаштувати
                                            </x-filament::button>
                                        @elseif ($hasConfig)
                                            <x-filament::button
                                                wire:click="openConfig('{{ $key }}')"
                                                color="gray"
                                                size="sm"
                                                icon="heroicon-m-cog-6-tooth"
                                            >
                                                Налаштувати
                                            </x-filament::button>
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
                                    class="fi-input mt-1 block w-full rounded-lg border-none bg-white px-3 py-1.5 text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 transition focus:ring-2 focus:ring-inset focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/10"
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
