@props(['p', 'compact' => false])
@php
    // $p — Product модель або mock-об'єкт/array з overlay-полями
    $name = is_object($p) ? ($p->name ?? '') : ($p['name'] ?? '');
    $oem = is_object($p) ? ($p->oem ?? $p->sku ?? '') : ($p['oem'] ?? '');
    $brand = is_object($p) ? ($p->brand ?? $p->manufacturer ?? '') : ($p['brand'] ?? '');
    $image = is_object($p) ? ($p->image_kind ?? 'filter') : ($p['image_kind'] ?? 'filter');
    $price = is_object($p) ? (float) ($p->price ?? 0) : (float) ($p['price'] ?? 0);
    $oldPrice = is_object($p) ? ($p->old_price ?? null) : ($p['old_price'] ?? null);
    $oldPrice = ((float) $oldPrice > (float) $price) ? $oldPrice : null; // ignore 0 / ≤ price
    $discount = is_object($p) ? ($p->discount ?? null) : ($p['discount'] ?? null);
    $condition = is_object($p) ? ($p->condition ?? 'Новий') : ($p['condition'] ?? 'Новий');
    $qty = is_object($p) ? (int) ($p->qty ?? $p->quantity ?? 0) : (int) ($p['qty'] ?? 0);
    $rating = is_object($p) ? (float) ($p->rating ?? 0) : (float) ($p['rating'] ?? 0);
    $reviews = is_object($p) ? (int) ($p->reviews ?? $p->reviews_count ?? 0) : (int) ($p['reviews'] ?? 0);
    $fits = is_object($p) ? ($p->fits ?? null) : ($p['fits'] ?? null);
    $url = is_object($p) ? ($p->url ?? '#') : ($p['url'] ?? '#');
    $productId = is_object($p) ? ($p->id ?? null) : ($p['id'] ?? null);
@endphp
<div class="gazu-card-anim bg-white border border-[var(--gazu-line)] rounded-lg flex flex-col overflow-hidden font-text relative transition-all duration-200 hover:border-[var(--gazu-ink)] hover:shadow-[0_8px_24px_-12px_rgba(14,27,44,0.25)] hover:-translate-y-0.5">
    @if($discount)
        <div class="absolute top-2 left-2 px-2 py-0.5 bg-[var(--gazu-danger)] text-white text-[11px] font-semibold rounded gazu-mono z-10">−{{ $discount }}%</div>
    @endif
    @if($productId)
        @php
            $inWishlist = auth()->check() && \DB::table('wishlists')->where('user_id', auth()->id())->where('product_id', $productId)->exists();
        @endphp
        <button type="button"
                x-data="{ active: {{ $inWishlist ? 'true' : 'false' }}, busy: false, burst: false }"
                @click.prevent="
                    if (busy) return;
                    busy = true;
                    fetch('{{ route('gazu.wishlist.toggle') }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                        body: new URLSearchParams({ product_id: '{{ $productId }}' })
                    }).then(r => r.json()).then(d => {
                        if (d.ok) {
                            active = d.in_wishlist;
                            if (active) { burst = true; setTimeout(() => burst = false, 600); }
                            window.gazuToast && window.gazuToast(active ? 'Додано в обране ❤' : 'Видалено з обраного', active ? 'success' : 'info');
                        } else if (d.redirect) { window.location = d.redirect; }
                    }).catch(() => { window.location = '{{ route('gazu.auth') }}'; })
                      .finally(() => { busy = false; });
                "
                :title="active ? 'Прибрати з обраного' : 'Додати в обране'"
                :class="active ? 'text-[var(--gazu-danger)]' : 'text-[var(--gazu-graphite)] hover:text-[var(--gazu-danger)]'"
                class="absolute top-2 right-2 w-8 h-8 rounded-md border-0 bg-white/85 cursor-pointer flex items-center justify-center z-10 transition-colors">
            <svg :class="burst ? 'gazu-heart-burst' : ''" width="16" height="16" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" :fill="active ? 'currentColor' : 'none'">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78Z"/>
            </svg>
        </button>
    @endif

    <a wire:navigate href="{{ $url }}" class="aspect-square bg-[var(--gazu-paper)] flex items-center justify-center border-b border-[var(--gazu-line)] no-underline relative overflow-hidden p-2 sm:p-3">
        <x-gazu.part-image kind="{{ $image }}" :seed="$productId" fit/>
        @if($oem)
            {{-- bottom-left so it never collides with the discount badge (top-left) or wishlist heart (top-right) --}}
            <span class="absolute bottom-1.5 left-1.5 px-1.5 py-0.5 gazu-mono text-[10px] text-[var(--gazu-graphite)] bg-white/90 border border-[var(--gazu-line)] rounded">{{ $oem }}</span>
        @endif
    </a>

    <div class="{{ $compact ? 'p-2.5 sm:p-3.5' : 'p-3.5' }} flex flex-col gap-1.5 sm:gap-2">
        <div class="flex items-center gap-1.5 min-w-0">
            <x-gazu.condition-badge value="{{ $condition }}"/>
            <span class="gazu-mono text-[11px] text-[var(--gazu-graphite)] truncate">{{ $brand }}</span>
        </div>

        <a wire:navigate href="{{ $url }}" class="text-[13px] text-[var(--gazu-ink)] leading-snug font-medium no-underline line-clamp-2" style="min-height: 36px;">
            {{ $name }}
        </a>

        @if($fits)
            <div class="text-[11px] text-[var(--gazu-graphite)] leading-snug px-2 py-1.5 bg-[var(--gazu-mist)] rounded flex gap-1.5 items-start">
                <x-gazu.icon name="check" size="12" stroke="var(--gazu-blue)" class="shrink-0 mt-0.5"/>
                <span class="line-clamp-2">{{ $fits }}</span>
            </div>
        @endif

        @if($rating > 0 && $reviews > 0)
            <div class="flex items-center gap-1.5 mt-0.5">
                <div class="flex gap-px text-[var(--gazu-warn)] shrink-0">
                    @for($i = 1; $i <= 5; $i++)
                        <x-gazu.icon name="star" size="12" fill="{{ $i <= floor($rating) ? 'var(--gazu-warn)' : 'none' }}" stroke="var(--gazu-warn)"/>
                    @endfor
                </div>
                <span class="text-[11px] text-[var(--gazu-graphite)] whitespace-nowrap">{{ number_format($rating, 1) }} ({{ $reviews }})</span>
            </div>
        @endif

        <x-gazu.stock qty="{{ $qty }}"/>

        <div class="flex items-end gap-2 mt-1 flex-wrap">
            @if($oldPrice)
                <span class="text-xs text-[var(--gazu-muted)] line-through">{{ number_format((float)$oldPrice, 0, '.', ' ') }} ₴</span>
            @endif
            <span class="gazu-display text-[19px] sm:text-[22px] font-bold text-[var(--gazu-ink)] leading-none">
                {{ number_format($price, 0, '.', ' ') }} <span class="text-sm font-medium text-[var(--gazu-graphite)]">₴</span>
            </span>
        </div>

        <div class="flex gap-1.5 mt-1">
            @if($productId && $qty > 0)
                <button type="button"
                        x-data="{ busy: false, added: false }"
                        @click.prevent="
                            if (busy) return;
                            busy = true;
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
                        :class="added ? 'bg-[var(--gazu-success)] scale-[0.97]' : (busy ? 'bg-[var(--gazu-ink)] opacity-80' : 'bg-[var(--gazu-ink)] hover:bg-[var(--gazu-ink-2)] active:scale-[0.97]')"
                        :disabled="busy"
                        class="flex-1 min-w-0 py-2.5 text-white border-0 rounded-md text-[13px] font-medium cursor-pointer inline-flex items-center justify-center gap-1.5 whitespace-nowrap transition-all duration-200">
                    <span x-show="!busy && !added" class="inline-flex items-center gap-1.5">
                        <x-gazu.icon name="cart" size="14"/> У кошик
                    </span>
                    <svg x-show="busy" x-cloak class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25" stroke-width="3"/>
                        <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                    </svg>
                    <span x-show="added" x-cloak x-transition.duration.150ms class="inline-flex items-center gap-1.5">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                        Додано
                    </span>
                </button>
            @elseif($qty <= 0)
                <button type="button" disabled class="flex-1 min-w-0 py-2.5 bg-[var(--gazu-line-2)] text-[var(--gazu-graphite)] border-0 rounded-md text-[13px] font-medium cursor-not-allowed inline-flex items-center justify-center gap-1.5 whitespace-nowrap">
                    Під замовлення
                </button>
            @else
                <a wire:navigate href="{{ $url }}" class="flex-1 min-w-0 py-2.5 bg-[var(--gazu-ink)] text-white border-0 rounded-md text-[13px] font-medium cursor-pointer inline-flex items-center justify-center gap-1.5 whitespace-nowrap hover:bg-[var(--gazu-ink-2)] no-underline">
                    <x-gazu.icon name="cart" size="14"/> Деталі
                </a>
            @endif
            @if($productId && $qty > 0)
                <button type="button"
                        title="Купити в 1 клік"
                        x-data
                        @click.prevent="$dispatch('gazu:one-click', { productId: '{{ $productId }}', productName: @js($name), productPrice: {{ (float) $price }} })"
                        class="w-8 sm:w-9 shrink-0 border border-[var(--gazu-line)] rounded-md bg-white text-[var(--gazu-ink)] hover:border-[var(--gazu-ink)] cursor-pointer inline-flex items-center justify-center transition-colors">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h7l-1 8 10-12h-7l1-8z"/></svg>
                </button>
            @endif
        </div>
    </div>
</div>
