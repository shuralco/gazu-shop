@php
    $active = $active ?? 'spec';
    $tabs = $tabs ?? [
        ['spec', 'Характеристики', null],
        ['compat', 'Сумісність', null],
        ['analogs', 'Аналоги', null],
        ['reviews', 'Відгуки', null],
        ['delivery', 'Доставка та оплата', null],
    ];
@endphp
<div class="border-b border-[var(--gazu-line)] flex gap-1 font-text mt-3 overflow-x-auto whitespace-nowrap">
    @foreach($tabs as [$k, $l, $c])
        <button type="button"
                class="px-4.5 py-3.5 bg-transparent border-0 text-sm cursor-pointer inline-flex items-center gap-1.5 {{ $active === $k ? 'text-[var(--gazu-ink)] font-semibold' : 'text-[var(--gazu-graphite)]' }}"
                style="border-bottom: 2px solid {{ $active === $k ? 'var(--gazu-ink)' : 'transparent' }};">
            {{ $l }}
            @if($c !== null && $c > 0)<span class="text-[11px] text-[var(--gazu-muted)] gazu-mono">{{ $c }}</span>@endif
        </button>
    @endforeach
</div>
