@props(['qty' => 0, 'status' => null])
@php
    // Явний статус наявності з довідника StockStatus (key). Якщо заданий —
    // він перекриває стару логіку від кількості. Інакше — fallback на qty.
    $st = $status ? \App\Models\StockStatus::byKey($status) : null;
    $colorVar = [
        'success' => '--gazu-success',
        'warning' => '--gazu-warn',
        'danger'  => '--gazu-danger',
        'info'    => '--gazu-primary',
        'primary' => '--gazu-primary',
        'gray'    => '--gazu-muted',
    ][$st->color ?? 'gray'] ?? '--gazu-muted';
@endphp
@if($st)
    <span class="text-xs inline-flex items-center gap-1 whitespace-nowrap" style="color:var({{ $colorVar }})">
        <span class="w-1.5 h-1.5 rounded-full shrink-0" style="background:var({{ $colorVar }})"></span> {{ $st->label }}
    </span>
@elseif($qty > 5)
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
