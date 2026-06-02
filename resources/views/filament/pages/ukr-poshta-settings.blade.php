<x-filament-panels::page>
    @php
        $module = $this->getModuleStatus();
        $statusBadgeColor = [
            'ok' => 'success',
            'warning' => 'warning',
            'error' => 'danger',
            'unknown' => 'gray',
        ][$module['level']] ?? 'gray';
        $statusIcon = [
            'ok' => 'heroicon-m-check-circle',
            'warning' => 'heroicon-m-exclamation-triangle',
            'error' => 'heroicon-m-x-circle',
            'unknown' => 'heroicon-m-question-mark-circle',
        ][$module['level']] ?? 'heroicon-m-question-mark-circle';
        $modulesUrl = \Illuminate\Support\Facades\Route::has('filament.admin.pages.modules')
            ? route('filament.admin.pages.modules')
            : (\Illuminate\Support\Facades\Route::has('filament.admin.pages.integrations-page')
                ? route('filament.admin.pages.integrations-page')
                : '#');
    @endphp

    {{-- Module status header --}}
    <x-filament::section icon="heroicon-o-truck">
        <x-slot name="heading">Модуль «УкрПошта»</x-slot>

        <x-slot name="headerEnd">
            <div class="flex items-center gap-3">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ $module['enabled'] ? 'Увімкнено' : 'Вимкнено' }}
                </span>
                <button
                    wire:click="toggleModule"
                    type="button"
                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 {{ $module['enabled'] ? 'bg-primary-600' : 'bg-gray-200 dark:bg-white/10' }}"
                    role="switch"
                    aria-checked="{{ $module['enabled'] ? 'true' : 'false' }}"
                    aria-label="{{ $module['enabled'] ? 'Вимкнути' : 'Увімкнути' }} модуль УкрПошта"
                >
                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $module['enabled'] ? 'translate-x-5' : 'translate-x-0' }}"></span>
                </button>
            </div>
        </x-slot>

        <div class="flex flex-wrap items-center gap-3">
            <x-filament::badge :color="$statusBadgeColor" :icon="$statusIcon">
                {{ $module['message'] }}
            </x-filament::badge>

            <x-filament::link :href="$modulesUrl" wire:navigate size="sm">
                ← Усі модулі
            </x-filament::link>
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
