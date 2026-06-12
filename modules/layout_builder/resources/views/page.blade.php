<x-filament-panels::page>
    {{-- Без власного дубль-заголовка — назву сторінки вже рендерить Filament
         ($title), повтор виглядав як «дубль» на сторінці. --}}
    <x-filament::section icon="heroicon-o-information-circle" icon-color="info" collapsible collapsed>
        <x-slot name="heading">Довідка по зонах</x-slot>
        <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
            <div>Призначайте блоки у іменовані зони storefront.</div>
            <ul class="list-disc list-inside text-xs space-y-0.5">
                <li><code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">layout.home.top</code> — верх головної</li>
                <li><code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">layout.home.bottom</code> — низ головної</li>
                <li><code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">layout.product.sidebar</code> — сайдбар картки товару</li>
                <li><code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">layout.page.top / layout.page.bottom</code> — CMS-сторінки (/page/{slug}); ключ <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">pages</code> обмежує блок конкретними slug</li>
                <li>Дані зберігаються у <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">layout_blocks</code>, рендеряться через <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">&#64;hookAction</code> у темі</li>
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
            href="/"
            target="_blank"
            color="gray"
            icon="heroicon-o-arrow-top-right-on-square">
            Відкрити storefront
        </x-filament::button>
    </div>
</x-filament-panels::page>
