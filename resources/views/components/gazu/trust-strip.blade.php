@props(['items' => null])
@php
    $items = $items ?? ($gazuSettings['gazu_trust_items'] ?? null);
    if (empty($items) || !is_array($items)) {
        $items = [
            ['icon' => 'truck',  'title' => 'Доставка по Україні', 'desc' => '1–3 дні · Нова Пошта · Укрпошта'],
            ['icon' => 'shield', 'title' => 'Гарантія на запчастини', 'desc' => 'Від 6 до 24 місяців'],
            ['icon' => 'return', 'title' => 'Повернення', 'desc' => '14 днів без пояснення причин'],
            ['icon' => 'wrench', 'title' => 'Допомога з підбором', 'desc' => 'Менеджер передзвонить за 5 хв'],
        ];
    }
@endphp
@if(! empty($items))
<div class="bg-[var(--gazu-bone)] border-y border-[var(--gazu-line)]">
    <div class="gazu-container py-5 grid grid-cols-2 md:grid-cols-{{ min(count($items), 4) }} gap-6">
        @foreach($items as $it)
            <div class="flex gap-3.5 items-start">
                <div class="w-10 h-10 shrink-0 rounded-lg bg-white border border-[var(--gazu-line)] flex items-center justify-center text-[var(--gazu-blue)]">
                    <x-gazu.icon name="{{ $it['icon'] ?? 'shield' }}" size="22"/>
                </div>
                <div class="min-w-0">
                    <div class="text-sm font-medium text-[var(--gazu-ink)] mb-0.5">{{ $it['title'] ?? '' }}</div>
                    <div class="text-xs text-[var(--gazu-graphite)] leading-snug">{{ $it['desc'] ?? '' }}</div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif
