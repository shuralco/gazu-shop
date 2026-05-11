@extends('gazu.layout')
@section('title', 'Кошик — GAZU')

@section('content')
<div class="gazu-container">
    <x-gazu.breadcrumbs :items="[['Головна', route('gazu.home')], 'Кошик']"/>
    <h1 class="gazu-display text-4xl font-semibold text-[var(--gazu-ink)] m-0 mb-2">Кошик</h1>
    @php
        $itemsCount = array_sum(array_column($cart, 'quantity'));
        $positionsCount = count($cart);
    @endphp
    <div class="text-sm text-[var(--gazu-graphite)] mb-6">
        {{ plural_uk_count($positionsCount, 'позиція', 'позиції', 'позицій') }}
        @if($itemsCount !== $positionsCount)
            · {{ $itemsCount }} шт.
        @endif
    </div>

    @if(session('cart_message'))
        <div class="bg-[var(--gazu-success-bg)] text-[var(--gazu-success)] px-4 py-2 rounded-md mb-4 text-sm">
            {{ session('cart_message') }}
        </div>
    @endif

    @if($errors->any())
        <div role="alert" class="bg-[var(--gazu-danger-bg)] text-[var(--gazu-danger)] px-4 py-3 rounded-md mb-4 text-sm flex items-start gap-2">
            <x-gazu.icon name="close" size="16" stroke="var(--gazu-danger)" class="shrink-0 mt-0.5"/>
            <div>
                @foreach($errors->all() as $msg)
                    <div>{{ $msg }}</div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="gazu-grid-cart"
         x-data="{
            total: {{ (float) $cartTotal }},
            count: {{ (int) $positionsCount }},
            qtyTotal: {{ (int) $itemsCount }},
            fmt(n) { return Math.round(n).toLocaleString('uk-UA').replace(/,/g,' '); },
            flash(refName) {
                const el = this.$refs[refName];
                if (!el) return;
                el.setAttribute('data-changed', '0');
                void el.offsetWidth;
                el.setAttribute('data-changed', '1');
                setTimeout(() => el.setAttribute('data-changed', '0'), 450);
            },
            init() {
                window.addEventListener('cart-updated', (e) => {
                    if (e.detail.total !== undefined) this.total = e.detail.total;
                    if (e.detail.count !== undefined) this.count = e.detail.count;
                    if (e.detail.qtyTotal !== undefined) this.qtyTotal = e.detail.qtyTotal;
                });
                this.$watch('total', () => { this.flash('grandEl'); this.flash('subEl'); });
            }
         }">
        @php
            // Eager-load all warehouses referenced by the cart in ONE query
            // instead of MerchantWarehouse::find() per cart line (N+1).
            $cartWarehouseIds = collect($cart)->pluck('warehouse_id')->filter()->unique()->all();
            $cartWarehouses = $cartWarehouseIds
                ? \App\Models\MerchantWarehouse::query()->whereIn('id', $cartWarehouseIds)->get()->keyBy('id')
                : collect();
        @endphp
        <div>
            <div class="bg-white border border-[var(--gazu-line)] rounded-lg overflow-hidden">
                @foreach($cart as $key => $item)
                    @php
                        $productId = is_numeric($key) ? (int) $key : (int) explode('_', (string) $key)[0];
                        $title = is_array($item['title'] ?? null) ? ($item['title']['uk'] ?? '—') : ($item['title'] ?? '—');
                        $slug = is_array($item['slug'] ?? null) ? ($item['slug']['uk'] ?? null) : ($item['slug'] ?? null);
                        $price = (float) ($item['price'] ?? 0);
                        $qty = (int) ($item['quantity'] ?? 1);
                        $img = $item['image'] ?? null;
                        $warehouseId = (int) ($item['warehouse_id'] ?? 0);
                        $warehouse = $warehouseId ? ($cartWarehouses[$warehouseId] ?? null) : null;
                        $kinds = ['filter','pad','shock','bulb','oil','spark','bearing','wiper'];
                        $kind = $kinds[$productId % count($kinds)];
                    @endphp
                    @php
                        $hasRealImg = ! empty($img) && (str_starts_with($img, 'http') || str_starts_with($img, '/storage/') || file_exists(public_path($img)));
                        $imgUrl = $hasRealImg ? (str_starts_with($img, 'http') ? $img : asset('storage/'.ltrim($img, '/storage/'))) : null;
                    @endphp
                    <div class="gazu-grid-cart-row {{ $loop->index ? 'border-t border-[var(--gazu-line)]' : '' }}"
                         x-data="{
                            qty: {{ $qty }},
                            price: {{ $price }},
                            busy: false,
                            removing: false,
                            get lineTotal() { return this.price * this.qty; },
                            async setQty(newQty) {
                                if (this.busy || newQty < 1) return;
                                const prev = this.qty;
                                this.qty = newQty;
                                this.busy = true;
                                try {
                                    const r = await fetch('{{ route('gazu.cart.update') }}', {
                                        method: 'POST',
                                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                                        body: new URLSearchParams({ product_id: '{{ $productId }}', quantity: String(newQty) })
                                    });
                                    const d = await r.json();
                                    if (d.ok) {
                                        window.dispatchEvent(new CustomEvent('cart-updated', { detail: d }));
                                    } else { this.qty = prev; }
                                } catch (e) {
                                    this.qty = prev;
                                    window.gazuToast && window.gazuToast('Помилка з\'єднання', 'error');
                                } finally { this.busy = false; }
                            },
                            async remove() {
                                if (this.busy) return;
                                this.busy = true; this.removing = true;
                                try {
                                    const r = await fetch('{{ route('gazu.cart.remove') }}', {
                                        method: 'POST',
                                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                                        body: new URLSearchParams({ product_id: '{{ $productId }}' })
                                    });
                                    const d = await r.json();
                                    if (d.ok) {
                                        $el.style.transition = 'opacity .25s, max-height .25s, padding .25s';
                                        $el.style.maxHeight = $el.offsetHeight + 'px';
                                        requestAnimationFrame(() => { $el.style.maxHeight = '0'; $el.style.opacity = '0'; $el.style.padding = '0'; });
                                        setTimeout(() => $el.remove(), 250);
                                        window.dispatchEvent(new CustomEvent('cart-updated', { detail: d }));
                                        window.gazuToast && window.gazuToast('Видалено з кошика', 'info');
                                    }
                                } catch (e) {
                                    this.removing = false;
                                    window.gazuToast && window.gazuToast('Помилка', 'error');
                                } finally { this.busy = false; }
                            }
                         }">
                        <div class="bg-[var(--gazu-paper)] rounded-md aspect-square flex items-center justify-center overflow-hidden">
                            @if($imgUrl)
                                <img src="{{ $imgUrl }}" alt="" class="w-20 h-20 object-contain"
                                     onerror="this.style.display='none'; this.nextElementSibling?.style.removeProperty('display');">
                                <x-gazu.part-image kind="{{ $kind }}" size="80" style="display:none"/>
                            @else
                                <x-gazu.part-image kind="{{ $kind }}" size="80"/>
                            @endif
                        </div>
                        <div class="min-w-0">
                            @if($slug)
                                <a wire:navigate href="{{ route('gazu.product.show', ['slug' => $slug]) }}" class="text-[var(--gazu-ink)] no-underline font-medium leading-snug">{{ $title }}</a>
                            @else
                                <span class="text-[var(--gazu-ink)] font-medium leading-snug">{{ $title }}</span>
                            @endif
                            @if($warehouse)
                                <div class="mt-1.5 inline-flex items-center gap-1.5 text-[11px] text-[var(--gazu-graphite)]">
                                    <x-gazu.icon name="location" size="12" stroke="var(--gazu-blue)"/>
                                    <span>{{ $warehouse->city ?: $warehouse->name }}</span>
                                    @if($warehouse->delivery_eta)
                                        <span class="text-[var(--gazu-muted)]">·</span>
                                        <span>{{ $warehouse->delivery_eta }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                        <div class="flex items-center border border-[var(--gazu-line)] rounded-md" role="group" aria-label="Кількість">
                            <button type="button" @click="setQty(qty - 1)" :disabled="busy || qty <= 1"
                                    aria-label="Зменшити"
                                    class="w-11 h-11 md:w-9 md:h-9 border-0 bg-transparent text-[var(--gazu-ink)] cursor-pointer flex items-center justify-center disabled:opacity-40 disabled:cursor-not-allowed">
                                <x-gazu.icon name="minus" size="14"/>
                            </button>
                            <span class="w-10 text-center py-2 text-sm gazu-mono font-medium" :class="busy && 'opacity-50'" x-text="qty" aria-live="polite">{{ $qty }}</span>
                            <button type="button" @click="setQty(qty + 1)" :disabled="busy"
                                    aria-label="Збільшити"
                                    class="w-11 h-11 md:w-9 md:h-9 border-0 bg-transparent text-[var(--gazu-ink)] cursor-pointer flex items-center justify-center disabled:opacity-40">
                                <x-gazu.icon name="plus" size="14"/>
                            </button>
                        </div>
                        <div class="text-right">
                            <div class="gazu-display text-lg font-bold text-[var(--gazu-ink)] gazu-count-up" x-text="fmt(lineTotal) + ' ₴'">{{ number_format($price * $qty, 0, '.', ' ') }} ₴</div>
                            <div class="text-[11px] text-[var(--gazu-graphite)]"><span x-text="fmt(price)">{{ number_format($price, 0, '.', ' ') }}</span> ₴ × <span x-text="qty">{{ $qty }}</span></div>
                        </div>
                        <button type="button" @click="remove()" :disabled="busy"
                                aria-label="Видалити з кошика"
                                class="w-11 h-11 md:w-9 md:h-9 bg-transparent text-[var(--gazu-graphite)] border-0 cursor-pointer flex items-center justify-center hover:text-[var(--gazu-danger)] disabled:opacity-40">
                            <x-gazu.icon name="trash" size="18"/>
                        </button>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 flex items-center gap-3">
                <a wire:navigate href="{{ route('gazu.catalog') }}" class="gazu-btn-outline no-underline">← Продовжити покупки</a>
                <span class="flex-1"></span>
                <form action="{{ route('gazu.cart.clear') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-[13px] text-[var(--gazu-danger)] bg-transparent border-0 cursor-pointer">Очистити кошик</button>
                </form>
            </div>
        </div>

        @php
            $shipping = app(\App\Services\Cart\ShippingCalculator::class)->breakdown($cart);
        @endphp
        <div class="bg-white border border-[var(--gazu-line)] rounded-lg p-5 self-start">
            <h3 class="gazu-display text-xl font-semibold m-0 mb-4">Підсумок</h3>
            <div class="flex justify-between mb-2 text-sm">
                <span class="text-[var(--gazu-graphite)]" x-text="count + ' ' + (count === 1 ? 'позиція' : (count >= 2 && count <= 4 ? 'позиції' : 'позицій'))">{{ plural_uk_count($positionsCount, 'позиція', 'позиції', 'позицій') }}</span>
                <span x-ref="subEl" class="text-[var(--gazu-ink)] gazu-count-up" x-text="fmt(total) + ' ₴'">{{ number_format($shipping['subtotal'], 0, '.', ' ') }} ₴</span>
            </div>

            {{-- Per-warehouse shipping breakdown --}}
            @if(count($shipping['groups']) > 0)
                <div class="my-3 pt-3 border-t border-[var(--gazu-line)]">
                    <div class="text-[11px] uppercase tracking-wide text-[var(--gazu-graphite)] mb-2 font-bold">Доставка</div>
                    @foreach($shipping['groups'] as $g)
                        @php
                            $w = $g['warehouse'];
                            $whName = $w?->city ?: ($w?->name ?: 'Склад');
                        @endphp
                        <div class="flex justify-between items-baseline mb-1.5 text-xs">
                            <span class="text-[var(--gazu-graphite)] inline-flex items-center gap-1">
                                <x-gazu.icon name="location" size="11" stroke="var(--gazu-blue)"/>
                                <span>{{ $whName }}</span>
                            </span>
                            <span class="gazu-mono">
                                @if($g['free'])
                                    <span class="text-[var(--gazu-success)]">безкоштовно</span>
                                @else
                                    {{ number_format($g['shipping'], 0, '.', ' ') }} ₴
                                @endif
                            </span>
                        </div>
                        @if(! $g['free'] && $w?->free_shipping_threshold)
                            @php $remaining = (float) $w->free_shipping_threshold - $g['subtotal']; @endphp
                            @if($remaining > 0)
                                <div class="text-[10px] text-[var(--gazu-muted)] mb-1.5 ml-4">
                                    + {{ number_format($remaining, 0, '.', ' ') }} ₴ до безкоштовної
                                </div>
                            @endif
                        @endif
                    @endforeach
                    <div class="flex justify-between text-sm pt-2 mt-1 border-t border-[var(--gazu-line)]">
                        <span class="text-[var(--gazu-graphite)]">Разом доставка</span>
                        <span class="font-medium gazu-mono">
                            @if($shipping['shipping_total'] == 0)
                                <span class="text-[var(--gazu-success)]">безкоштовно</span>
                            @else
                                {{ number_format($shipping['shipping_total'], 0, '.', ' ') }} ₴
                            @endif
                        </span>
                    </div>
                </div>
            @endif

            {{-- Free-shipping progress bar (threshold 1000 ₴) --}}
            @php $freeShipThreshold = 1000; @endphp
            <div class="my-3 pt-3 border-t border-[var(--gazu-line)]"
                 x-data="{ threshold: {{ $freeShipThreshold }}, get pct(){ return Math.min(100, Math.round(total / this.threshold * 100)) }, get remaining(){ return Math.max(0, this.threshold - total) } }">
                <template x-if="remaining > 0">
                    <div>
                        <div class="text-[11px] text-[var(--gazu-graphite)] mb-1.5">
                            До безкоштовної доставки ще <span class="font-semibold text-[var(--gazu-ink)] gazu-mono" x-text="fmt(remaining) + ' ₴'"></span>
                        </div>
                        <div class="w-full h-2 bg-[var(--gazu-mist)] rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-[var(--gazu-blue)] to-[var(--gazu-success)] transition-all duration-500 ease-out" :style="`width: ${pct}%`"></div>
                        </div>
                    </div>
                </template>
                <template x-if="remaining === 0">
                    <div class="text-[11px] text-[var(--gazu-success)] font-medium inline-flex items-center gap-1">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg>
                        Безкоштовна доставка
                    </div>
                </template>
            </div>

            {{-- Promo code input --}}
            <div class="my-3 pt-3 border-t border-[var(--gazu-line)]"
                 x-data="{
                    open: false, code: '', busy: false, applied: null,
                    apply() {
                        if (!this.code.trim() || this.busy) return;
                        this.busy = true;
                        fetch('{{ route('gazu.cart.coupon.apply') }}', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                            body: new URLSearchParams({ code: this.code.trim() })
                        }).then(r => r.json()).then(d => {
                            if (d.ok) {
                                this.applied = { code: this.code, discount: d.discount };
                                window.gazuToast && window.gazuToast(d.message || 'Промокод застосовано', 'success');
                                window.dispatchEvent(new CustomEvent('cart-updated', { detail: d }));
                            } else {
                                window.gazuToast && window.gazuToast(d.message || 'Промокод не знайдено', 'error');
                            }
                        }).catch(() => window.gazuToast && window.gazuToast('Помилка', 'error'))
                          .finally(() => { this.busy = false; });
                    }
                 }">
                <button type="button" @click="open = !open" class="w-full flex items-center justify-between text-sm text-[var(--gazu-ink)] bg-transparent border-0 cursor-pointer p-0" :aria-expanded="open">
                    <span class="inline-flex items-center gap-1.5">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41 13.42 20.58a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                        Промокод
                    </span>
                    <svg :class="open ? 'rotate-180' : ''" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="transition-transform"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div x-show="open" x-cloak x-collapse class="mt-3">
                    <div class="flex gap-2">
                        <input type="text" x-model="code" @keydown.enter.prevent="apply()" placeholder="Введіть код" class="flex-1 px-3 py-2 border border-[var(--gazu-line)] rounded-md text-sm focus:border-[var(--gazu-ink)] outline-none">
                        <button type="button" @click="apply()" :disabled="busy || !code.trim()" class="px-4 py-2 bg-[var(--gazu-ink)] text-white rounded-md text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed hover:bg-[var(--gazu-ink-2)] transition-colors">
                            <span x-show="!busy">Застосувати</span>
                            <svg x-show="busy" x-cloak class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25" stroke-width="3"/><path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                        </button>
                    </div>
                    <template x-if="applied">
                        <div class="mt-2 text-[12px] text-[var(--gazu-success)] inline-flex items-center gap-1">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg>
                            <span x-text="'Знижка −' + fmt(applied.discount) + ' ₴'"></span>
                        </div>
                    </template>
                </div>
            </div>

            <div class="flex justify-between items-baseline mb-4 pt-3 border-t border-[var(--gazu-line)]">
                <span class="text-[var(--gazu-ink)] font-medium">До сплати</span>
                <span x-ref="grandEl" x-text="fmt(total + ({{ (int) $shipping['shipping_total'] }})) + ' ₴'" class="gazu-display text-2xl font-bold text-[var(--gazu-ink)] gazu-count-up">{{ number_format($shipping['grand_total'], 0, '.', ' ') }} ₴</span>
            </div>
            <a wire:navigate href="{{ route('gazu.checkout') }}" class="gazu-btn-primary w-full no-underline">Оформити замовлення →</a>
        </div>
    </div>

    {{-- Recommended products (Ukrainian shop signature feature) --}}
    @if(! empty($recommended ?? []) && count($recommended) > 0)
        <section class="mt-10">
            <h2 class="gazu-display text-2xl font-semibold mb-4">Часто купують разом</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3.5 gazu-stagger">
                @foreach($recommended as $p)
                    <x-gazu.product-card :p="$p" :compact="true"/>
                @endforeach
            </div>
        </section>
    @endif
    </div>
</div>
@endsection
