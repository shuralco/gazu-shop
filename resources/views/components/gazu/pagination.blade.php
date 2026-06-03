@props(['paginator' => null, 'current' => 1, 'total' => 1])
@php
    if ($paginator) {
        $current = $paginator->currentPage();
        $total = $paginator->lastPage();
    }
    if ($total <= 1) return '';
    $pages = [];
    $pages[] = 1;
    if ($total > 1) {
        for ($i = max(2, $current - 1); $i <= min($total - 1, $current + 1); $i++) {
            if ($i > 1 && $i < $total) $pages[] = $i;
        }
        if ($current + 1 < $total - 1) $pages[] = '...';
        if ($total > 1) $pages[] = $total;
    }
    $pages = array_values(array_unique($pages, SORT_REGULAR));
    $url = fn ($p) => $paginator ? $paginator->url($p) : request()->fullUrlWithQuery(['page' => $p]);
@endphp
<div class="flex items-center justify-center gap-1 py-8 font-text">
    @if ($current > 1)
        <a wire:navigate href="{{ $url($current - 1) }}" class="w-9 h-9 border border-[var(--gazu-line)] bg-[var(--gazu-surface)] text-[var(--gazu-ink)] rounded-md text-[13px] cursor-pointer inline-flex items-center justify-center no-underline">
            <x-gazu.icon name="arrow-l" size="14"/>
        </a>
    @else
        <span class="w-9 h-9 border border-[var(--gazu-line)] bg-[var(--gazu-surface)] text-[var(--gazu-line-2)] rounded-md text-[13px] inline-flex items-center justify-center cursor-not-allowed">
            <x-gazu.icon name="arrow-l" size="14"/>
        </span>
    @endif

    @foreach($pages as $p)
        @if($p === '...')
            <span class="w-9 h-9 inline-flex items-center justify-center text-[var(--gazu-muted)]">…</span>
        @else
            @php $active = $p == $current; @endphp
            <a wire:navigate href="{{ $url($p) }}"
               class="w-9 h-9 rounded-md text-[13px] inline-flex items-center justify-center no-underline {{ $active ? 'bg-[var(--gazu-ink)] text-[var(--gazu-on-brand)] border border-[var(--gazu-ink)] font-semibold' : 'bg-[var(--gazu-surface)] text-[var(--gazu-ink)] border border-[var(--gazu-line)]' }}">
                {{ $p }}
            </a>
        @endif
    @endforeach

    @if ($current < $total)
        <a wire:navigate href="{{ $url($current + 1) }}" class="w-9 h-9 border border-[var(--gazu-line)] bg-[var(--gazu-surface)] text-[var(--gazu-ink)] rounded-md text-[13px] cursor-pointer inline-flex items-center justify-center no-underline">
            <x-gazu.icon name="arrow-r" size="14"/>
        </a>
    @else
        <span class="w-9 h-9 border border-[var(--gazu-line)] bg-[var(--gazu-surface)] text-[var(--gazu-line-2)] rounded-md text-[13px] inline-flex items-center justify-center cursor-not-allowed">
            <x-gazu.icon name="arrow-r" size="14"/>
        </span>
    @endif
</div>
