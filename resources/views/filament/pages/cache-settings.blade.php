<x-filament-panels::page>
    @php $stats = $this->getHitStats(); @endphp

    {{-- Live Redis hit/miss stats --}}
    @if (! isset($stats['error']))
        @php
            $h = (int) $stats['keyspace_hits'];
            $m = (int) $stats['keyspace_misses'];
            $total = $h + $m;
            $rate = $total > 0 ? round(($h / $total) * 100, 1) : 0;
        @endphp
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-3 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="text-[10px] uppercase tracking-wider text-gray-500">Hit rate</div>
                <div class="text-2xl font-bold mt-1 {{ $rate >= 90 ? 'text-green-600' : ($rate >= 70 ? 'text-yellow-600' : 'text-red-600') }}">
                    {{ $rate }}%
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-3 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="text-[10px] uppercase tracking-wider text-gray-500">Hits</div>
                <div class="text-2xl font-bold mt-1 text-green-600">{{ number_format($h, 0, '.', ' ') }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-3 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="text-[10px] uppercase tracking-wider text-gray-500">Misses</div>
                <div class="text-2xl font-bold mt-1 text-red-500">{{ number_format($m, 0, '.', ' ') }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-3 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="text-[10px] uppercase tracking-wider text-gray-500">Commands</div>
                <div class="text-2xl font-bold mt-1 text-gray-700 dark:text-gray-300">{{ number_format($stats['total_commands_processed'], 0, '.', ' ') }}</div>
            </div>
        </div>
    @else
        <div class="text-red-600 text-sm mb-6">Redis connection error: {{ $stats['error'] }}</div>
    @endif

    {{-- Form з settings --}}
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex gap-3">
            <x-filament::button type="submit">Зберегти</x-filament::button>
        </div>
    </form>

    {{-- Help block --}}
    <x-filament::section collapsible collapsed class="mt-6">
        <x-slot name="heading">Як інтерпретувати hit rate</x-slot>
        <div class="text-sm text-gray-700 dark:text-gray-300 space-y-2">
            <p><strong>Hit rate ≥ 90%</strong> — кеш працює відмінно, більшість запитів повертаються з Redis.</p>
            <p><strong>70-89%</strong> — нормально для активного магазину з частими оновленнями товарів.</p>
            <p><strong>&lt; 70%</strong> — варто збільшити TTL для статичних доменів (categories, brands, info) або перевірити чи observers не flush'ять занадто часто.</p>
            <p class="text-gray-500 text-xs mt-2">Hits/misses — кумулятивні з моменту останнього restart Redis. Для «миттєвої» статистики дивитись keys count + memory.</p>
        </div>
    </x-filament::section>

    @script
        <script>
            // Auto-refresh hit stats every 60s
            setInterval(() => { $wire.$refresh(); }, 60000);
        </script>
    @endscript
</x-filament-panels::page>
