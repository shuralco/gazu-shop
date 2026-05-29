<x-filament-panels::page>
    @php
        $module = $this->getModuleStatus();
        $statusColors = [
            'ok' => ['bg' => 'bg-green-50 dark:bg-green-900/30', 'text' => 'text-green-700 dark:text-green-400', 'dot' => 'bg-green-500', 'border' => 'border-green-200 dark:border-green-800'],
            'warning' => ['bg' => 'bg-yellow-50 dark:bg-yellow-900/30', 'text' => 'text-yellow-700 dark:text-yellow-400', 'dot' => 'bg-yellow-500', 'border' => 'border-yellow-200 dark:border-yellow-800'],
            'error' => ['bg' => 'bg-red-50 dark:bg-red-900/30', 'text' => 'text-red-700 dark:text-red-400', 'dot' => 'bg-red-500', 'border' => 'border-red-200 dark:border-red-800'],
            'unknown' => ['bg' => 'bg-gray-100 dark:bg-gray-700', 'text' => 'text-gray-600 dark:text-gray-300', 'dot' => 'bg-gray-400', 'border' => 'border-gray-200 dark:border-gray-700'],
        ];
        $sc = $statusColors[$module['level']] ?? $statusColors['unknown'];
    @endphp

    {{-- Module status header --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border {{ $sc['border'] }} p-5 mb-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="text-3xl">📮</div>
                <div>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">
                        Модуль «УкрПошта»
                    </h2>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 text-xs font-medium rounded-md {{ $sc['bg'] }} {{ $sc['text'] }}">
                            <span class="inline-block w-1.5 h-1.5 rounded-full {{ $sc['dot'] }}"></span>
                            {{ $module['message'] }}
                        </span>
                        <a
                            href="{{ \Illuminate\Support\Facades\Route::has('filament.admin.pages.modules') ? route('filament.admin.pages.modules') : (\Illuminate\Support\Facades\Route::has('filament.admin.pages.integrations-page') ? route('filament.admin.pages.integrations-page') : '#') }}"
                            wire:navigate
                            class="text-xs text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400 underline"
                        >
                            ← Усі модулі
                        </a>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ $module['enabled'] ? 'Увімкнено' : 'Вимкнено' }}
                </span>
                <button
                    wire:click="toggleModule"
                    type="button"
                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 {{ $module['enabled'] ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-600' }}"
                    role="switch"
                    aria-checked="{{ $module['enabled'] ? 'true' : 'false' }}"
                    aria-label="{{ $module['enabled'] ? 'Вимкнути' : 'Увімкнути' }} модуль УкрПошта"
                >
                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $module['enabled'] ? 'translate-x-5' : 'translate-x-0' }}"></span>
                </button>
            </div>
        </div>
    </div>

    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6 flex justify-end">
            <x-filament::button type="submit" size="lg">
                Зберегти налаштування
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
