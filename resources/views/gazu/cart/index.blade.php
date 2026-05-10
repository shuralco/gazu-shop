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
                        $warehouse = $warehouseId
                            ? \App\Models\MerchantWarehouse::find($warehouseId)
                            : null;
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
                        <div class="flex items-center border border-[var(--gazu-line)] rounded-md">
                            <button type="button" @click="setQty(qty - 1)" :disabled="busy || qty <= 1"
                                    class="w-9 h-9 border-0 bg-transparent text-[var(--gazu-ink)] cursor-pointer flex items-center justify-center disabled:opacity-40 disabled:cursor-not-allowed">
                                <x-gazu.icon name="minus" size="14"/>
                            </button>
                            <span class="w-10 text-center py-2 text-sm gazu-mono font-medium" :class="busy && 'opacity-50'" x-text="qty">{{ $qty }}</span>
                            <button type="button" @click="setQty(qty + 1)" :disabled="busy"
                                    class="w-9 h-9 border-0 bg-transparent text-[var(--gazu-ink)] cursor-pointer flex items-center justify-center disabled:opacity-40">
                                <x-gazu.icon name="plus" size="14"/>
                            </button>
                        </div>
                        <div class="text-right">
                            <div class="gazu-display text-lg font-bold text-[var(--gazu-ink)] gazu-count-up" x-text="fmt(lineTotal) + ' ₴'">{{ number_format($price * $qty, 0, '.', ' ') }} ₴</div>
                            <div class="text-[11px] text-[var(--gazu-graphite)]"><span x-text="fmt(price)">{{ number_format($price, 0, '.', ' ') }}</span> ₴ × <span x-text="qty">{{ $qty }}</span></div>
                        </div>
                        <button type="button" @click="remove()" :disabled="busy"
                                class="w-9 h-9 bg-transparent text-[var(--gazu-graphite)] border-0 cursor-pointer flex items-center justify-center hover:text-[var(--gazu-danger)] disabled:opacity-40">
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

            <div class="flex justify-between items-baseline mb-4 pt-3 border-t border-[var(--gazu-line)]">
                <span class="text-[var(--gazu-ink)] font-medium">До сплати</span>
                <span x-ref="grandEl" class="gazu-display text-2xl font-bold text-[var(--gazu-ink)] gazu-count-up">{{ number_format($shipping['grand_total'], 0, '.', ' ') }} ₴</span>
            </div>
            <a wire:navigate href="{{ route('gazu.checkout') }}" class="gazu-btn-primary w-full no-underline">Оформити замовлення →</a>
        </div>
    </div>
</div>
@endsection
