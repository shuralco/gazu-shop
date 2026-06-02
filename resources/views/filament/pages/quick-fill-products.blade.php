<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section icon="heroicon-o-information-circle" icon-color="info">
            <x-slot name="heading">Швидке наповнення</x-slot>
            <x-slot name="description">Натхненне Excel-таблицею: один рядок = один товар.</x-slot>
            <ul class="list-disc list-inside space-y-1 text-sm text-gray-600 dark:text-gray-400">
                <li>Введіть закупку у валюті постачальника (CNY/USD), система перерахує у ₴ за курсом і застосує націнку.</li>
                <li>Курси редагуються через DisplaySetting (<code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">fx_cny_uah</code>, <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">fx_usd_uah</code>, <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">default_markup</code>) або у файлі <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">config/.env</code> поки що.</li>
                <li>SKU має бути унікальним — дублі пропускаються з помилкою.</li>
                <li>Після збереження товар отримує статус <em>активний</em>, <em>в наявності</em> якщо k-ть більше 0.</li>
            </ul>
        </x-filament::section>

        {{ $this->form }}

        <div class="flex flex-wrap gap-2 sticky bottom-0 bg-white dark:bg-gray-900 py-3 border-t border-gray-200 dark:border-gray-700 -mx-4 px-4 z-10">
            <x-filament::button
                wire:click="saveAll"
                wire:loading.attr="disabled"
                wire:target="saveAll"
                color="success"
                icon="heroicon-o-check">
                <span wire:loading.remove wire:target="saveAll">Зберегти всі товари</span>
                <span wire:loading wire:target="saveAll">Збереження…</span>
            </x-filament::button>

            <span style="flex:1 1 0%"></span>

            <x-filament::button
                tag="a"
                href="{{ \App\Filament\Resources\ProductResource::getUrl('index') }}"
                color="gray"
                icon="heroicon-o-arrow-top-right-on-square">
                До списку товарів
            </x-filament::button>
        </div>

        <x-filament::section icon="heroicon-o-calculator" icon-color="gray">
            <x-slot name="heading">Формула розрахунку ціни</x-slot>
            <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">
                Ціна продажу = закупка × курс × (1 + націнка / 100)
            </code>
            <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Приклад: 100 ¥ × 4.0 ₴/¥ × (1 + 100% / 100) = <strong>800 ₴</strong>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
