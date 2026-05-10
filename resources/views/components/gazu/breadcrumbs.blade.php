@props(['items' => []])
<div class="text-[13px] text-[var(--gazu-graphite)] py-4 flex items-center gap-2 flex-wrap">
    @foreach($items as $i => $it)
        @if($i > 0)
            <span class="text-[var(--gazu-line-2)]">/</span>
        @endif
        @if(is_array($it))
            <a wire:navigate href="{{ $it[1] }}" class="no-underline {{ $i === count($items) - 1 ? 'text-[var(--gazu-ink)]' : 'text-[var(--gazu-graphite)] hover:text-[var(--gazu-ink)]' }}">{{ $it[0] }}</a>
        @else
            <span class="{{ $i === count($items) - 1 ? 'text-[var(--gazu-ink)]' : 'text-[var(--gazu-graphite)]' }}">{{ $it }}</span>
        @endif
    @endforeach
</div>
