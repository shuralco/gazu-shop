@php $active = $active ?? 'spec'; @endphp
<div class="border-b border-[var(--gazu-line)] flex gap-1 font-text mt-3 overflow-x-auto whitespace-nowrap">
    @foreach([
        ['spec', 'Характеристики', 13],
        ['compat', 'Сумісність', 8],
        ['analogs', 'Аналоги', 4],
        ['reviews', 'Відгуки', 42],
        ['delivery', 'Доставка та оплата', null],
    ] as [$k, $l, $c])
        <button type="button"
                class="px-4.5 py-3.5 bg-transparent border-0 text-sm cursor-pointer inline-flex items-center gap-1.5 {{ $active === $k ? 'text-[var(--gazu-ink)] font-semibold' : 'text-[var(--gazu-graphite)]' }}"
                style="border-bottom: 2px solid {{ $active === $k ? 'var(--gazu-ink)' : 'transparent' }};">
            {{ $l }}
            @if($c !== null)<span class="text-[11px] text-[var(--gazu-muted)] gazu-mono">{{ $c }}</span>@endif
        </button>
    @endforeach
</div>
