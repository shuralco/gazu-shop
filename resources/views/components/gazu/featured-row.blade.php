@props(['title' => '', 'badge' => null, 'items' => [], 'viewAll' => null, 'bare' => false])
@if(! empty($items))
{{-- `bare=true` пропускає gazu-container wrapper — використовуй коли блок
     рендериться ВСЕРЕДИНІ existing gazu-container (product page).
     Інакше отримуєш double padding 48px (стиснутий вигляд). --}}
<section class="{{ $bare ? 'pt-8 pb-2' : 'gazu-container pt-4 pb-2' }}">
    <div class="flex items-baseline gap-3.5 mb-4">
        <h2 class="gazu-display text-[28px] font-semibold text-[var(--gazu-ink)] m-0">{{ $title }}</h2>
        @if($badge)
            <span class="text-[11px] px-2 py-0.5 bg-[var(--gazu-danger)] text-white rounded gazu-mono tracking-wider">{{ $badge }}</span>
        @endif
        <span class="flex-1"></span>
        <a wire:navigate href="{{ $viewAll ?: route('gazu.catalog') }}" class="text-[13px] text-[var(--gazu-blue)] no-underline">Дивитись усі →</a>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3.5 sm:gap-4">
        @foreach($items as $p)
            <x-gazu.product-card :p="$p"/>
        @endforeach
    </div>
</section>
@endif
