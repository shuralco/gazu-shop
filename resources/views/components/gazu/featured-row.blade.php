@props(['title' => '', 'badge' => null, 'items' => [], 'viewAll' => null])
@if(! empty($items))
{{-- `py` lighter than before so the row doesn't look bloated next to the
     surrounding blocks on the product page; still leaves breathing room
     between sections on the homepage. --}}
<section class="gazu-container pt-4 pb-2">
    <div class="flex items-baseline gap-3.5 mb-4">
        <h2 class="gazu-display text-[28px] font-semibold text-[var(--gazu-ink)] m-0">{{ $title }}</h2>
        @if($badge)
            <span class="text-[11px] px-2 py-0.5 bg-[var(--gazu-danger)] text-white rounded gazu-mono tracking-wider">{{ $badge }}</span>
        @endif
        <span class="flex-1"></span>
        <a wire:navigate href="{{ $viewAll ?: route('gazu.catalog') }}" class="text-[13px] text-[var(--gazu-blue)] no-underline">Дивитись усі →</a>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3.5">
        @foreach($items as $p)
            <x-gazu.product-card :p="$p"/>
        @endforeach
    </div>
</section>
@endif
