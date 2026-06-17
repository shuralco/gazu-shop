<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6 flex gap-3">
            <x-filament::button type="submit">
                Зберегти
            </x-filament::button>
            <x-filament::button type="button" color="gray" icon="heroicon-o-arrow-path" wire:click="fetchFromNbu">
                Оновити з НБУ зараз
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
