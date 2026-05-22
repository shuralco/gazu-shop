@props(['items' => []])
{{-- Компактний trail: один рядок з горизонтальним скролом (без переносу),
     менший шрифт/відступ на мобілці. Останній елемент обрізається, щоб довга
     назва товару не «розтягувала» крихти на кілька рядків. --}}
<div class="text-[11px] sm:text-[13px] text-[var(--gazu-graphite)] py-2.5 sm:py-4 flex items-center gap-1 sm:gap-2 flex-nowrap whitespace-nowrap gazu-scroll-x">
    @foreach($items as $i => $it)
        @php $isLast = $i === count($items) - 1; @endphp
        @if($i > 0)
            <span class="text-[var(--gazu-line-2)] shrink-0">/</span>
        @endif
        @if(is_array($it))
            <a wire:navigate href="{{ $it[1] }}" class="no-underline shrink-0 {{ $isLast ? 'text-[var(--gazu-ink)]' : 'text-[var(--gazu-graphite)] hover:text-[var(--gazu-ink)]' }}">{{ $it[0] }}</a>
        @else
            <span class="{{ $isLast ? 'text-[var(--gazu-ink)] truncate max-w-[55vw] sm:max-w-none' : 'text-[var(--gazu-graphite)] shrink-0' }}">{{ $it }}</span>
        @endif
    @endforeach
</div>
