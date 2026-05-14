@props([
    'price' => 184,
    'oldPrice' => null,
    'qty' => 12,
    'discount' => null,
    'productId' => null,
    'name' => '', // product name — for the 1-click modal summary
    'warehouseStocks' => null, // Collection of Inventory rows with .warehouse loaded
    'closestWarehouseId' => null, // geo-detected warehouse ID (Phase 6)
])
@php
    $priceFmt = number_format((float) $price, 0, '.', ' ');
    $stocks = $warehouseStocks instanceof \Illuminate\Support\Collection ? $warehouseStocks : collect();
    // Prefer geo-detected warehouse if it has stock; otherwise first in-stock.
    $defaultStock = $closestWarehouseId
        ? $stocks->first(fn ($s) => $s->warehouse_id === $closestWarehouseId && $s->quantity > 0)
        : null;
    $defaultStock ??= $stocks->firstWhere(fn ($s) => $s->quantity > 0);
    $defaultWh = $defaultStock?->warehouse_id;
    $defaultPrice = $defaultStock && $defaultStock->price !== null ? (float) $defaultStock->price : (float) $price;
    // Build JS lookup: { warehouseId: { price, compare, qty, city, eta } }
    $stocksJs = $stocks->mapWithKeys(fn ($s) => [
        $s->warehouse_id => [
            'price'   => $s->price !== null ? (float) $s->price : (float) $price,
            'compare' => $s->compare_at_price !== null ? (float) $s->compare_at_price : null,
            'qty'     => max(0, $s->quantity - $s->reserved_quantity),
            'city'    => $s->warehouse->city ?: $s->warehouse->name,
            'eta'     => $s->warehouse->delivery_eta ?: '1-3 дні',
        ],
    ])->all();
@endphp
<div class="bg-white border border-[var(--gazu-line)] rounded-[10px] p-6 font-text"
     x-data="{
        q: 1,
        warehouseId: {{ $defaultWh ? (int) $defaultWh : 'null' }},
        stocks: {{ \Illuminate\Support\Js::from($stocksJs) }},
        get price() { return this.warehouseId && this.stocks[this.warehouseId] ? this.stocks[this.warehouseId].price : {{ (float) $defaultPrice }}; },
        get compareAt() { return this.warehouseId && this.stocks[this.warehouseId] ? this.stocks[this.warehouseId].compare : null; },
        get available() { return this.warehouseId && this.stocks[this.warehouseId] ? this.stocks[this.warehouseId].qty : {{ (int) $qty }}; },
        fmt(n) { return Math.round(n).toLocaleString('uk-UA').replace(/,/g, ' '); },
        adding: false,
        async addToCart() {
            if (this.adding || this.available <= 0) return;
            this.adding = true;
            try {
                const r = await fetch('{{ route('gazu.cart.add') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: new URLSearchParams({
                        product_id: '{{ $productId }}',
                        quantity: this.q,
                        warehouse_id: this.warehouseId || '',
                    })
                });
                const d = await r.json();
                if (d.ok) {
                    window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: d.count, qtyTotal: d.qtyTotal, total: d.total } }));
                    window.gazuToast && window.gazuToast('Додано до кошика', 'success');
                } else {
                    window.gazuToast && window.gazuToast(d.message || 'Не вдалося додати', 'error');
                }
            } catch(e) {
                window.gazuToast && window.gazuToast('Помилка з\'єднання', 'error');
            } finally { this.adding = false; }
        }
     }"
     @warehouse-selected.window="warehouseId = $event.detail.id">
    <div class="flex items-baseline gap-3 mb-1 min-h-[44px]">
        <span class="gazu-display font-bold text-[var(--gazu-ink)] leading-none gazu-mono" style="font-size: 40px; font-variant-numeric: tabular-nums; min-width: 7ch; display: inline-flex; align-items: baseline; gap: .25em;">
            <span x-text="fmt(price * q)" style="display:inline-block;text-align:left">{{ $priceFmt }}</span><span class="text-2xl font-medium text-[var(--gazu-graphite)]">₴</span>
        </span>
        <div class="flex flex-col gap-0.5">
            <template x-if="compareAt && compareAt > price">
                <span class="text-sm text-[var(--gazu-muted)] line-through" x-text="fmt(compareAt) + ' ₴'"></span>
            </template>
            @if($oldPrice && !$stocks->isNotEmpty())
                <span class="text-sm text-[var(--gazu-muted)] line-through">{{ number_format((float)$oldPrice, 0, '.', ' ') }} ₴</span>
                @if($discount)
                    <span class="text-[11px] gazu-mono px-1.5 py-0.5 bg-[var(--gazu-danger-bg)] text-[var(--gazu-danger)] rounded">−{{ $discount }}%</span>
                @endif
            @endif
            {{-- per-unit × qty breakdown — to the right of the price, shown when q > 1 --}}
            <div class="text-[11px] text-[var(--gazu-graphite)] gazu-mono" style="font-variant-numeric: tabular-nums;" x-show="q > 1" x-cloak>
                <span x-text="fmt(price)"></span> ₴ × <span x-text="q"></span> шт.
            </div>
        </div>
    </div>

    {{-- Availability + warehouse picker both live in <x-gazu.warehouse-selector>
         (middle column of the product page). It syncs back here via the
         `warehouse-selected`
         window event — see the @warehouse-selected.window listener on the root. --}}

    <div class="h-px bg-[var(--gazu-line)] my-5"></div>

    <form action="{{ route('gazu.cart.add') }}" method="POST" @submit.prevent="addToCart">
        @csrf
        <input type="hidden" name="product_id" value="{{ $productId }}">
        <input type="hidden" name="quantity" :value="q">
        <input type="hidden" name="warehouse_id" :value="warehouseId">

        {{-- Quantity selector — bigger, centered, easier to tap --}}
        <div class="flex items-center justify-between gap-3 mb-4">
            <span class="text-[13px] font-medium text-[var(--gazu-graphite)]">Кількість</span>
            <div class="flex items-center bg-[var(--gazu-mist)] border border-[var(--gazu-line)] rounded-lg overflow-hidden">
                <button type="button" @click="q = Math.max(1, q-1)"
                    aria-label="Зменшити кількість"
                    class="w-11 h-11 border-0 bg-transparent cursor-pointer text-[var(--gazu-ink)] inline-flex items-center justify-center hover:bg-[var(--gazu-line-2)] active:bg-[var(--gazu-line)] transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
                    :disabled="q <= 1">
                    <x-gazu.icon name="minus" size="16"/>
                </button>
                <input x-model.number="q" type="number" min="1" :max="available || 99"
                    aria-label="Кількість"
                    class="w-14 h-11 text-center border-0 bg-white text-base gazu-mono font-semibold text-[var(--gazu-ink)] outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                <button type="button" @click="q = Math.min((available || 99), q+1)"
                    aria-label="Збільшити кількість"
                    class="w-11 h-11 border-0 bg-transparent cursor-pointer text-[var(--gazu-ink)] inline-flex items-center justify-center hover:bg-[var(--gazu-line-2)] active:bg-[var(--gazu-line)] transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
                    :disabled="available > 0 && q >= available">
                    <x-gazu.icon name="plus" size="16"/>
                </button>
            </div>
            <span x-show="available > 0" class="text-[11px] text-[var(--gazu-muted)] gazu-mono">
                макс. <span x-text="available"></span>
            </span>
        </div>

        @php
            $oneClickEnabled = ($gazuSettings['gazu_oneclick_enabled'] ?? true);
            $oneClickLabel = $gazuSettings['gazu_oneclick_label'] ?? 'Купити в один клік';
            $oneClickMessage = $gazuSettings['gazu_oneclick_message'] ?? 'Менеджер передзвонить за 5 хвилин для уточнення доставки';
        @endphp

        {{-- Primary + secondary action grid: cart fills width, 1-click is companion --}}
        @if($productId)
            <div class="grid grid-cols-1 gap-2.5">
                <button type="submit" :disabled="available <= 0"
                    :class="available <= 0 ? 'bg-[var(--gazu-line-2)] text-[var(--gazu-graphite)] cursor-not-allowed' : 'bg-[var(--gazu-ink)] text-white hover:bg-[var(--gazu-ink-2)] cursor-pointer'"
                    class="w-full h-14 border-0 rounded-lg text-[15px] font-semibold inline-flex items-center justify-center gap-2.5 transition-colors">
                    <template x-if="available > 0">
                        <span class="inline-flex items-center gap-2.5">
                            <x-gazu.icon name="cart" size="20"/>
                            <span>Додати в кошик</span>
                            <span class="opacity-70">·</span>
                            <span class="gazu-mono"><span x-text="fmt(price * q)">{{ $priceFmt }}</span> ₴</span>
                        </span>
                    </template>
                    <template x-if="available <= 0">
                        <span class="inline-flex items-center gap-2">
                            <x-gazu.icon name="clock" size="18"/>
                            Під замовлення
                        </span>
                    </template>
                </button>

                @if($oneClickEnabled)
                    <button type="button" @click.prevent="$dispatch('gazu:one-click', { productId: '{{ $productId }}', productName: @js($name ?? ''), productPrice: price * q })"
                            class="w-full h-12 bg-white text-[var(--gazu-ink)] border-[1.5px] border-[var(--gazu-ink)] rounded-lg text-[14px] font-medium cursor-pointer inline-flex items-center justify-center gap-2 hover:bg-[var(--gazu-mist)] transition-colors">
                        <x-gazu.icon name="phone" size="16"/>
                        {{ $oneClickLabel }}
                    </button>
                @endif
            </div>
        @endif
    </form>

    {{-- 1-клік: глобальна brutal-модалка (<x-gazu.one-click-modal> у layout)
         слухає подію `gazu:one-click` — окремий inline-модал більше не
         потрібен. --}}

    <div class="mt-4 p-3.5 bg-[var(--gazu-mist)] rounded-lg flex flex-col gap-2.5">
        <div class="flex gap-2.5 items-start">
            <x-gazu.icon name="truck" size="18" stroke="var(--gazu-blue)" class="shrink-0"/>
            <div>
                <div class="text-[13px] font-medium text-[var(--gazu-ink)]">Доставка завтра, {{ now()->addDay()->format('d.m') }}</div>
                <div class="text-[11px] text-[var(--gazu-graphite)]">Замовте сьогодні до 16:00 · Нова Пошта</div>
            </div>
        </div>
        <div class="flex gap-2.5 items-start">
            <x-gazu.icon name="shield" size="18" stroke="var(--gazu-blue)" class="shrink-0"/>
            <div>
                <div class="text-[13px] font-medium text-[var(--gazu-ink)]">Гарантія 12 місяців</div>
                <div class="text-[11px] text-[var(--gazu-graphite)]">Повернення коштів при дефекті</div>
            </div>
        </div>
        <div class="flex gap-2.5 items-start">
            <x-gazu.icon name="return" size="18" stroke="var(--gazu-blue)" class="shrink-0"/>
            <div>
                <div class="text-[13px] font-medium text-[var(--gazu-ink)]">14 днів на повернення</div>
                <div class="text-[11px] text-[var(--gazu-graphite)]">Без пояснення причин</div>
            </div>
        </div>
    </div>

    <div class="mt-4 p-3 border border-dashed border-[var(--gazu-line-2)] rounded-lg flex gap-2.5 items-center">
        <x-gazu.icon name="chat" size="20" stroke="var(--gazu-blue)" class="shrink-0"/>
        <div class="text-xs text-[var(--gazu-graphite)] leading-relaxed">
            Не впевнені в підборі? <a href="tel:0800751024" class="text-[var(--gazu-blue)] font-medium hover:underline no-underline">Запитайте менеджера</a> — відповість за 5 хв.
        </div>
    </div>
</div>
