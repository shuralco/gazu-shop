<x-filament-panels::page>
    <div class="space-y-6">
        <div class="rounded-lg bg-blue-50 dark:bg-blue-900/20 p-4 border border-blue-200 dark:border-blue-800">
            <div class="flex gap-3">
                <x-filament::icon icon="heroicon-o-information-circle" class="h-5 w-5 text-blue-600 shrink-0 mt-0.5"/>
                <div class="text-sm text-blue-900 dark:text-blue-100 space-y-1">
                    <div><strong>Швидке наповнення</strong> — натхненне Excel-таблицею: один рядок = один товар.</div>
                    <ul class="list-disc list-inside text-xs space-y-0.5">
                        <li>Введіть закупку у валюті постачальника (CNY/USD), система перерахує у ₴ за курсом і застосує націнку.</li>
                        <li>Курси редагуються через DisplaySetting (<code>fx_cny_uah</code>, <code>fx_usd_uah</code>, <code>default_markup</code>) або у файлі <code>config/.env</code> поки що.</li>
                        <li>SKU має бути унікальним — дублі пропускаються з помилкою.</li>
                        <li>Після збереження товар отримує статус <em>активний</em>, <em>в наявності</em> якщо k-ть більше 0.</li>
                    </ul>
                </div>
            </div>
        </div>

        {{ $this->form }}

        <div class="flex flex-wrap gap-2 sticky bottom-0 bg-white dark:bg-gray-900 py-3 border-t border-gray-200 dark:border-gray-700 -mx-4 px-4 z-10">
            <button type="button"
                    wire:click="saveAll"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-success-600 hover:bg-success-700 text-white rounded-md font-medium text-sm disabled:opacity-60">
                <x-filament::icon icon="heroicon-o-check" class="h-4 w-4"/>
                <span wire:loading.remove wire:target="saveAll">Зберегти всі товари</span>
                <span wire:loading wire:target="saveAll">Збереження…</span>
            </button>

            <span class="flex-1"></span>

            <a href="{{ \App\Filament\Resources\ProductResource::getUrl('index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 text-gray-900 dark:text-white rounded-md font-medium text-sm">
                <x-filament::icon icon="heroicon-o-arrow-top-right-on-square" class="h-4 w-4"/>
                До списку товарів
            </a>
        </div>

        <div class="rounded-lg bg-gray-50 dark:bg-gray-900/50 p-4 border border-gray-200 dark:border-gray-700 text-xs">
            <div class="font-bold text-gray-900 dark:text-white mb-2">Формула розрахунку ціни</div>
            <code class="text-xs text-gray-700 dark:text-gray-300">
                Ціна продажу = закупка × курс × (1 + націнка / 100)
            </code>
            <div class="mt-2 text-gray-600 dark:text-gray-400">
                Приклад: 100 ¥ × 4.0 ₴/¥ × (1 + 100% / 100) = <strong>800 ₴</strong>
            </div>
        </div>
    </div>
</x-filament-panels::page>
