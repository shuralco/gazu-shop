<x-filament-panels::page>
    @php
        $module = $this->getModuleStatus();
        $statusColor = match ($module['level']) {
            'ok' => 'success',
            'warning' => 'warning',
            'error' => 'danger',
            default => 'gray',
        };
    @endphp

    {{-- Module status header --}}
    <x-filament::section icon-color="{{ $statusColor }}">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="text-3xl">{{ $module['icon'] ?? '🧩' }}</div>
                <div class="flex flex-col gap-1">
                    <h2 class="text-base font-semibold text-gray-950 dark:text-white">{{ $module['name'] ?? '' }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $module['description'] ?? '' }}</p>
                    <div class="flex flex-wrap items-center gap-2">
                        <x-filament::badge :color="$statusColor">
                            {{ $module['message'] }}
                        </x-filament::badge>
                        <x-filament::link
                            href="{{ route('filament.admin.pages.integrations-page') }}"
                            wire:navigate
                            size="sm"
                            color="gray"
                        >
                            ← Усі модулі
                        </x-filament::link>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ $module['enabled'] ? 'Увімкнено' : 'Вимкнено' }}
                </span>
                <x-filament::button
                    wire:click="toggleModule"
                    :color="$module['enabled'] ? 'danger' : 'success'"
                    :icon="$module['enabled'] ? 'heroicon-m-x-mark' : 'heroicon-m-check'"
                    outlined
                >
                    {{ $module['enabled'] ? 'Вимкнути' : 'Увімкнути' }}
                </x-filament::button>
            </div>
        </div>
    </x-filament::section>

    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6 flex justify-end">
            <x-filament::button type="submit" size="lg">Зберегти налаштування</x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
