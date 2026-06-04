<x-filament-panels::page>
    <x-filament::section icon="heroicon-o-information-circle" icon-color="info">
        <x-slot name="heading">Персональне меню</x-slot>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Вимкніть пункти, які не хочете бачити у своєму бічному меню. Це впливає лише на ВАШ акаунт
            і не змінює прав доступу — приховані розділи лишаються доступними за прямим посиланням.
            Згортання груп памʼятається браузером автоматично.
        </p>
    </x-filament::section>

    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6 flex items-center gap-3">
            <x-filament::button type="submit" icon="heroicon-o-check">
                Зберегти
            </x-filament::button>
            <x-filament::button type="button" color="gray" wire:click="resetMenu" icon="heroicon-o-arrow-path">
                Показати всі
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
