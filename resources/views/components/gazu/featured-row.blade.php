@props(['title' => '', 'badge' => null, 'items' => []])
<section class="gazu-container py-8">
    <div class="flex items-baseline gap-3.5 mb-5">
        <h2 class="gazu-display text-[28px] font-semibold text-[var(--gazu-ink)] m-0">{{ $title }}</h2>
        @if($badge)
            <span class="text-[11px] px-2 py-0.5 bg-[var(--gazu-danger)] text-white rounded gazu-mono tracking-wider">{{ $badge }}</span>
        @endif
        <span class="flex-1"></span>
        <a wire:navigate href="{{ route('gazu.catalog') }}" class="text-[13px] text-[var(--gazu-blue)] no-underline">Дивитись усі →</a>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3.5">
        @foreach($items as $p)
            <x-gazu.product-card :p="$p"/>
        @endforeach
    </div>
</section>
