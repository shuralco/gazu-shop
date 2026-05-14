@props(['p'])
@php
    $name = is_object($p) ? ($p->name ?? '') : ($p['name'] ?? '');
    $oem = is_object($p) ? ($p->oem ?? $p->sku ?? '') : ($p['oem'] ?? '');
    $brand = is_object($p) ? ($p->brand ?? $p->manufacturer ?? '') : ($p['brand'] ?? '');
    $image = is_object($p) ? ($p->image_kind ?? 'filter') : ($p['image_kind'] ?? 'filter');
    $price = is_object($p) ? (float) ($p->price ?? 0) : (float) ($p['price'] ?? 0);
    $oldPrice = is_object($p) ? ($p->old_price ?? null) : ($p['old_price'] ?? null);
    $oldPrice = ((float) $oldPrice > (float) $price) ? $oldPrice : null; // ignore 0 / ≤ price
    $qty = is_object($p) ? (int) ($p->qty ?? $p->quantity ?? 0) : (int) ($p['qty'] ?? 0);
    $rating = is_object($p) ? (float) ($p->rating ?? 0) : 0;
    $reviews = is_object($p) ? (int) ($p->reviews ?? $p->reviews_count ?? 0) : 0;
    $url = is_object($p) ? ($p->url ?? '#') : ($p['url'] ?? '#');
    $productId = is_object($p) ? ($p->id ?? null) : ($p['id'] ?? null);
    $excerpt = is_object($p) ? ($p->excerpt ?? null) : null;
    if (is_array($excerpt)) $excerpt = $excerpt['uk'] ?? null;
@endphp
<div class="bg-white border border-[var(--gazu-line)] rounded-lg p-3 flex gap-3 hover:border-[var(--gazu-line-2)] transition-colors">
    <a wire:navigate href="{{ $url }}" class="shrink-0 w-24 h-24 sm:w-32 sm:h-32 bg-[var(--gazu-paper)] rounded-md flex items-center justify-center relative no-underline overflow-hidden p-1.5">
        <x-gazu.part-image kind="{{ $image }}" fit/>
        @if($oem)
            <span class="absolute top-1 left-1 px-1.5 py-0.5 gazu-mono text-[9px] text-[var(--gazu-graphite)] bg-white/90 border border-[var(--gazu-line)] rounded">{{ $oem }}</span>
        @endif
    </a>
    <div class="flex-1 min-w-0 flex flex-col gap-1">
        <div class="flex items-center gap-1.5 flex-wrap">
            <x-gazu.condition-badge value="Новий"/>
            <span class="gazu-mono text-[11px] text-[var(--gazu-graphite)]">{{ $brand }}</span>
            @if($rating > 0 && $reviews > 0)
                <span class="text-[11px] text-[var(--gazu-graphite)]">· {{ number_format($rating, 1) }} ({{ $reviews }})</span>
            @endif
        </div>
        <a wire:navigate href="{{ $url }}" class="text-[14px] text-[var(--gazu-ink)] leading-snug font-semibold no-underline line-clamp-2">{{ $name }}</a>
        @if($excerpt)
            <p class="text-[12px] text-[var(--gazu-graphite)] m-0 line-clamp-2 hidden sm:block">{{ $excerpt }}</p>
        @endif
        <x-gazu.stock qty="{{ $qty }}"/>
    </div>
    <div class="shrink-0 flex flex-col items-end gap-2 min-w-[160px]">
        <div class="flex flex-col items-end">
            @if($oldPrice)
                <span class="text-xs text-[var(--gazu-muted)] line-through">{{ number_format((float) $oldPrice, 0, '.', ' ') }} ₴</span>
            @endif
            <span class="gazu-display text-[22px] font-bold text-[var(--gazu-ink)] leading-none">{{ number_format($price, 0, '.', ' ') }} <span class="text-sm font-medium text-[var(--gazu-graphite)]">₴</span></span>
        </div>
        @if($productId && $qty > 0)
            <button type="button"
                    x-data="{ busy: false, added: false }"
                    @click.prevent="
                        if (busy) return; busy = true;
                        fetch('{{ route('gazu.cart.add') }}', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                            body: new URLSearchParams({ product_id: '{{ $productId }}', quantity: '1' })
                        }).then(r => r.json()).then(d => {
                            if (d.ok) {
                                window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: d.count, qtyTotal: d.qtyTotal, total: d.total } }));
                                added = true;
                                window.gazuToast && window.gazuToast('Додано до кошика', 'success');
                                setTimeout(() => added = false, 1500);
                            } else {
                                window.gazuToast && window.gazuToast(d.message || 'Не вдалося додати', 'error');
                            }
                        }).catch(() => window.gazuToast && window.gazuToast('Помилка з\'єднання', 'error'))
                          .finally(() => { busy = false; });
                    "
                    :class="added ? 'bg-[var(--gazu-success)]' : 'bg-[var(--gazu-ink)] hover:bg-[var(--gazu-ink-2)]'"
                    :disabled="busy"
                    class="w-full px-4 py-2 text-white border-0 rounded-md text-[13px] font-medium cursor-pointer inline-flex items-center justify-center gap-1.5 whitespace-nowrap transition-colors">
                <span x-show="!added"><x-gazu.icon name="cart" size="14"/> У кошик</span>
                <span x-show="added" x-cloak>✓ Додано</span>
            </button>
        @else
            <a wire:navigate href="{{ $url }}" class="w-full px-4 py-2 bg-[var(--gazu-ink)] text-white border-0 rounded-md text-[13px] font-medium cursor-pointer inline-flex items-center justify-center gap-1.5 whitespace-nowrap hover:bg-[var(--gazu-ink-2)] no-underline">Деталі</a>
        @endif
    </div>
</div>
