<x-filament-panels::page>
    <div class="rounded-lg bg-blue-50 dark:bg-blue-900/20 p-4 border border-blue-200 dark:border-blue-800 mb-4">
        <div class="flex gap-3">
            <x-filament::icon icon="heroicon-o-information-circle" class="h-5 w-5 text-blue-600 shrink-0 mt-0.5"/>
            <div class="text-sm text-blue-900 dark:text-blue-100 space-y-1">
                <div><strong>Конструктор зон layout (OpenCart-стиль).</strong> Призначайте блоки у іменовані зони storefront.</div>
                <ul class="list-disc list-inside text-xs space-y-0.5">
                    <li><code>layout.home.top</code> — верх головної</li>
                    <li><code>layout.home.bottom</code> — низ головної</li>
                    <li><code>layout.product.sidebar</code> — сайдбар картки товару</li>
                    <li>Дані зберігаються у <code>layout_blocks</code>, рендеряться через <code>&#64;hookAction</code> у темі</li>
                </ul>
            </div>
        </div>
    </div>

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
            href="/"
            target="_blank"
            color="gray"
            icon="heroicon-o-arrow-top-right-on-square">
            Відкрити storefront
        </x-filament::button>
    </div>
</x-filament-panels::page>
