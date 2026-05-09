<x-filament-panels::page>
    <div class="rounded-lg bg-blue-50 dark:bg-blue-900/20 p-4 border border-blue-200 dark:border-blue-800 mb-4">
        <div class="flex gap-3">
            <x-filament::icon icon="heroicon-o-information-circle" class="h-5 w-5 text-blue-600 shrink-0 mt-0.5"/>
            <div class="text-sm text-blue-900 dark:text-blue-100 space-y-1">
                <div><strong>Візуальні блоки GAZU storefront.</strong> Усі тексти, які раніше були хардкоджені у шаблоні, тепер редагуються тут.</div>
                <ul class="list-disc list-inside text-xs space-y-0.5">
                    <li>Зміни одразу видно на <code>/gazu</code> (без перебудови CSS)</li>
                    <li>Дані зберігаються у <code>display_settings</code> (live, кешуються)</li>
                    <li>Якщо щось вилучити (порожнє значення) — застосовується дефолт</li>
                </ul>
            </div>
        </div>
    </div>

    {{ $this->form }}

    <div class="mt-6 flex gap-2 sticky bottom-0 bg-white dark:bg-gray-900 py-3 border-t border-gray-200 dark:border-gray-700 -mx-4 px-4 z-10">
        <button type="button" wire:click="save"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 px-4 py-2 bg-success-600 hover:bg-success-700 text-white rounded-md font-medium text-sm">
            <x-filament::icon icon="heroicon-o-check" class="h-4 w-4"/>
            <span wire:loading.remove>Зберегти</span>
            <span wire:loading>Збереження…</span>
        </button>
        <a href="/gazu" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 text-gray-900 dark:text-white rounded-md font-medium text-sm no-underline">
            <x-filament::icon icon="heroicon-o-arrow-top-right-on-square" class="h-4 w-4"/>
            Відкрити /gazu
        </a>
    </div>
</x-filament-panels::page>
