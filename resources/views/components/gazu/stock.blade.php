@props(['qty' => 0])
@if($qty > 5)
    <span class="text-xs text-[var(--gazu-success)] inline-flex items-center gap-1 whitespace-nowrap">
        <span class="w-1.5 h-1.5 rounded-full bg-[var(--gazu-success)] shrink-0"></span> В наявності
    </span>
@elseif($qty > 0)
    <span class="text-xs text-[var(--gazu-warn)] inline-flex items-center gap-1 whitespace-nowrap">
        <span class="w-1.5 h-1.5 rounded-full bg-[var(--gazu-warn)] shrink-0"></span> Залишилось {{ $qty }}
    </span>
@else
    <span class="text-xs text-[var(--gazu-muted)] inline-flex items-center gap-1 whitespace-nowrap">
        <span class="w-1.5 h-1.5 rounded-full bg-[var(--gazu-muted)] shrink-0"></span> Під замовлення
    </span>
@endif
