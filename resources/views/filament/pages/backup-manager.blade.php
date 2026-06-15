<x-filament-panels::page>
    <div class="space-y-4">
        <div class="text-sm text-gray-500 dark:text-gray-400">
            Повні дампи бази даних (структура + дані всіх таблиць, .sql.gz). Зберігаються на сервері у
            <code>storage/app/backups</code>. Натисніть «Створити бекап» угорі, щоб зробити новий.
            ⚠️ Завантажуйте важливі копії до себе — файли на сервері можуть стиратись при оновленні.
        </div>

        @php($backups = $this->getBackups())

        <div class="fi-ta rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5">
                    <tr class="text-left">
                        <th class="px-4 py-3 font-semibold">Файл</th>
                        <th class="px-4 py-3 font-semibold">Розмір</th>
                        <th class="px-4 py-3 font-semibold">Дата</th>
                        <th class="px-4 py-3 font-semibold text-right">Дії</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($backups as $b)
                        <tr>
                            <td class="px-4 py-3 font-mono text-xs">{{ $b['name'] }}</td>
                            <td class="px-4 py-3">{{ $b['size'] }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $b['date'] }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <x-filament::button size="sm" color="gray" icon="heroicon-o-arrow-down-tray"
                                        wire:click="download('{{ $b['name'] }}')">
                                        Завантажити
                                    </x-filament::button>
                                    <x-filament::button size="sm" color="danger" icon="heroicon-o-trash"
                                        wire:click="deleteBackup('{{ $b['name'] }}')"
                                        wire:confirm="Видалити цей бекап?">
                                        Видалити
                                    </x-filament::button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-gray-500">
                                Бекапів ще немає. Натисніть «Створити бекап».
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
