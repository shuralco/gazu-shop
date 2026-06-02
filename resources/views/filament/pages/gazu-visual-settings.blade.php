<x-filament-panels::page>
    <x-filament::section icon="heroicon-o-information-circle" icon-color="info">
        <x-slot name="heading">Візуальні блоки GAZU storefront</x-slot>

        <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
            <p>Усі тексти, які раніше були хардкоджені у шаблоні, тепер редагуються тут.</p>
            <ul class="list-disc list-inside space-y-0.5">
                <li>Зміни одразу видно на <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">/gazu</code> (без перебудови CSS)</li>
                <li>Дані зберігаються у <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">display_settings</code> (live, кешуються)</li>
                <li>Якщо щось вилучити (порожнє значення) — застосовується дефолт</li>
            </ul>
        </div>
    </x-filament::section>

    {{ $this->form }}

    <div class="mt-6 flex gap-2 sticky bottom-0 bg-white dark:bg-gray-900 py-3 border-t border-gray-200 dark:border-gray-700 -mx-4 px-4 z-10">
        <x-filament::button
            type="button"
            wire:click="save"
            wire:loading.attr="disabled"
            color="success"
            icon="heroicon-o-check">
            <span wire:loading.remove>Зберегти</span>
            <span wire:loading>Збереження…</span>
        </x-filament::button>
        <x-filament::button
            tag="a"
            href="/gazu"
            target="_blank"
            color="gray"
            icon="heroicon-o-arrow-top-right-on-square">
            Відкрити /gazu
        </x-filament::button>
    </div>
</x-filament-panels::page>
