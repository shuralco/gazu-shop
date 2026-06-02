<x-filament-panels::page>
    @php
        $module = $this->getModuleStatus();
        $statusBadgeColors = [
            'ok' => 'success',
            'warning' => 'warning',
            'error' => 'danger',
            'unknown' => 'gray',
        ];
        $statusColor = $statusBadgeColors[$module['level']] ?? 'gray';
        $allModulesUrl = \Illuminate\Support\Facades\Route::has('filament.admin.pages.modules')
            ? route('filament.admin.pages.modules')
            : (\Illuminate\Support\Facades\Route::has('filament.admin.pages.integrations-page')
                ? route('filament.admin.pages.integrations-page')
                : null);
    @endphp

    {{-- Module status header --}}
    <x-filament::section icon="heroicon-o-truck">
        <x-slot name="heading">Модуль «Нова Пошта»</x-slot>

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3">
                <x-filament::badge :color="$statusColor">
                    {{ $module['message'] }}
                </x-filament::badge>

                @if($allModulesUrl)
                    <x-filament::link
                        :href="$allModulesUrl"
                        wire:navigate
                        size="sm"
                        icon="heroicon-o-arrow-left"
                    >
                        Усі модулі
                    </x-filament::link>
                @endif
            </div>

            <div class="flex items-center gap-3">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ $module['enabled'] ? 'Увімкнено' : 'Вимкнено' }}
                </span>
                <x-filament::button
                    wire:click="toggleModule"
                    :color="$module['enabled'] ? 'danger' : 'success'"
                    :icon="$module['enabled'] ? 'heroicon-o-x-mark' : 'heroicon-o-check'"
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
            <x-filament::button type="submit" size="lg">
                Зберегти налаштування
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
