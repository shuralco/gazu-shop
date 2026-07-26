<x-filament-panels::page>

    {{-- Як це працює — щоб не треба було лізти в довідку --}}
    <x-filament::section>
        <x-slot name="heading">Як це працює</x-slot>
        <x-slot name="description">Одне правило на весь магазин.</x-slot>

        <div class="text-sm space-y-2">
            <p>
                <strong>Ціна в картці товару — базова</strong> (ваша закупка/собівартість).
                Покупець її ніколи не бачить.
            </p>
            <p>
                Клієнт бачить <strong>базову + % націнки своєї групи</strong>.
                Неавторизований покупець і клієнт, якому не змінювали групу,
                отримують ту групу, що позначена нижче як <strong>«Стандартна»</strong>.
            </p>
            <p class="text-gray-500 dark:text-gray-400">
                Приклад: базова 1 000 ₴, націнка стандартної групи 35 % → покупець бачить 1 350 ₴.
                Для гуртової групи з націнкою 10 % той самий товар — 1 100 ₴.
            </p>
        </div>
    </x-filament::section>

    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6 flex justify-end">
            <x-filament::button type="submit" size="lg">
                Зберегти націнки
            </x-filament::button>
        </div>
    </form>

    {{-- Живий калькулятор: одразу видно ціну для кожної групи --}}
    <x-filament::section class="mt-6">
        <x-slot name="heading">Перевірка на прикладі</x-slot>
        <x-slot name="description">Введіть базову ціну — покажемо, скільки заплатить кожна група. Рахується тією самою формулою, що й на сайті.</x-slot>

        <div class="mb-4 flex items-end gap-3">
            <div>
                <label class="block text-sm font-medium mb-1" for="sample-price">Базова ціна, ₴</label>
                <input id="sample-price" type="number" min="0" step="1"
                       wire:model.live.debounce.400ms="sample"
                       class="fi-input block w-40 rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-white/10 dark:bg-white/5">
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 pb-2">
                Зміни в таблиці враховуються ще до збереження.
            </p>
        </div>

        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-white/10">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5">
                    <tr class="text-left">
                        <th class="px-4 py-3 font-semibold">Група клієнтів</th>
                        <th class="px-4 py-3 font-semibold text-right">Націнка</th>
                        <th class="px-4 py-3 font-semibold text-right">Ціна для клієнта</th>
                        <th class="px-4 py-3 font-semibold text-right">Різниця</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($this->previewRows() as $row)
                        <tr>
                            <td class="px-4 py-3">
                                {{ $row['name'] }}
                                @if($row['is_default'])
                                    <x-filament::badge color="info" class="ml-2 inline-flex">стандартна · для гостей</x-filament::badge>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-mono">
                                {{ $row['percent'] > 0 ? '+' : '' }}{{ rtrim(rtrim(number_format($row['percent'], 2, '.', ''), '0'), '.') }} %
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-semibold">
                                {{ number_format($row['price'], 2, ',', ' ') }} ₴
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-gray-500 dark:text-gray-400">
                                {{ $row['price'] - (float) $sample > 0 ? '+' : '' }}{{ number_format($row['price'] - (float) $sample, 2, ',', ' ') }} ₴
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                Немає активних груп — додайте хоча б одну вище.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>

</x-filament-panels::page>
