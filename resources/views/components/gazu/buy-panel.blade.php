@props([
    'price' => 184,
    'oldPrice' => null,
    'qty' => 12,
    'discount' => null,
    'productId' => null,
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
        fmt(n) { return Math.round(n).toLocaleString('uk-UA').replace(/,/g, ' '); }
     }">
    <div class="flex items-baseline gap-3 mb-1">
        <span class="gazu-display font-bold text-[var(--gazu-ink)] leading-none" style="font-size: 40px;">
            <span x-text="fmt(price * q)">{{ $priceFmt }}</span> <span class="text-2xl font-medium text-[var(--gazu-graphite)]">₴</span>
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
        </div>
    </div>
    <div class="text-[11px] text-[var(--gazu-graphite)] mb-2" x-show="q > 1" x-cloak>
        <span x-text="fmt(price)"></span> ₴ × <span x-text="q"></span> шт.
    </div>
    <div class="mt-1">
        <span x-text="available > 0 ? ('У наявності · ' + available + ' шт') : 'Немає в наявності'"
              :class="available > 0 ? 'text-[var(--gazu-success)] font-medium' : 'text-[var(--gazu-danger)] font-medium'"
              class="text-[13px]">У наявності</span>
    </div>

    @if($stocks->isNotEmpty())
        @php $visible = 3; $hasMore = $stocks->count() > $visible; @endphp
        <div class="mt-4" x-data="{ expanded: false }" role="radiogroup" aria-label="Вибір складу для доставки">
            <div class="text-[11px] uppercase tracking-wide font-bold text-[var(--gazu-graphite)] mb-2">Доставка зі складу</div>
            <div class="flex flex-col gap-1.5">
                @foreach($stocks as $idx => $s)
                    @php
                        $available = max(0, $s->quantity - $s->reserved_quantity);
                        $sPrice = $s->price !== null ? (float) $s->price : (float) $price;
                        $sCompare = $s->compare_at_price !== null ? (float) $s->compare_at_price : null;
                        $whCity = $s->warehouse->city ?: $s->warehouse->name;
                        $whEta = $s->warehouse->delivery_eta ?: '1-3 дні';
                        $ariaLabel = sprintf(
                            '%s, %s, %s, %s ₴',
                            $whCity, $whEta,
                            $available > 0 ? $available.' шт у наявності' : 'немає в наявності',
                            number_format($sPrice, 0, '.', ' ')
                        );
                    @endphp
                    <button type="button"
                        role="radio"
                        :aria-checked="warehouseId === {{ (int) $s->warehouse_id }}"
                        aria-label="{{ $ariaLabel }}"
                        @click="warehouseId = {{ (int) $s->warehouse_id }}"
                        @if($idx >= $visible) x-show="expanded" x-transition.opacity.duration.150ms @endif
                        @disabled($available <= 0)
                        :class="warehouseId === {{ (int) $s->warehouse_id }} ? 'border-[var(--gazu-ink)] bg-[var(--gazu-ink)] text-white' : 'border-[var(--gazu-line)] bg-white text-[var(--gazu-ink)] hover:border-[var(--gazu-graphite)]'"
                        class="w-full flex items-center justify-between gap-3 px-3 py-2.5 border rounded-md transition-colors text-left min-h-[44px]
                            @if($available <= 0) opacity-50 cursor-not-allowed @endif">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-3.5 h-3.5 rounded-full border-2 flex-shrink-0"
                                 :class="warehouseId === {{ (int) $s->warehouse_id }} ? 'border-white bg-white' : 'border-[var(--gazu-graphite)]'">
                                <div x-show="warehouseId === {{ (int) $s->warehouse_id }}" class="w-1.5 h-1.5 rounded-full bg-[var(--gazu-ink)] m-auto mt-[3px]"></div>
                            </div>
                            <div class="min-w-0">
                                <div class="font-medium text-[13px] truncate inline-flex items-center gap-1.5">
                                    <span>{{ $whCity }}</span>
                                    @if($closestWarehouseId && $s->warehouse_id === $closestWarehouseId)
                                        <span class="text-[9px] gazu-mono px-1 py-0.5 rounded uppercase tracking-wider"
                                              :class="warehouseId === {{ (int) $s->warehouse_id }} ? 'bg-white/15 text-white' : 'bg-[var(--gazu-blue-bg, #E0EBFF)] text-[var(--gazu-blue)]'">
                                            ближче вам
                                        </span>
                                    @endif
                                </div>
                                <div class="text-[11px] opacity-70 truncate">
                                    {{ $whEta }}
                                    @if($available > 0) · {{ $available }} шт @else · немає @endif
                                </div>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            @if($sCompare && $sCompare > $sPrice)
                                <div class="text-[10px] line-through opacity-60">{{ number_format($sCompare, 0, '.', ' ') }} ₴</div>
                            @endif
                            <div class="font-semibold text-[13px] gazu-mono">{{ number_format($sPrice, 0, '.', ' ') }} ₴</div>
                        </div>
                    </button>
                @endforeach
            </div>
            @if($hasMore)
                <button type="button" @click="expanded = !expanded"
                    :aria-expanded="expanded"
                    aria-label="Показати більше складів"
                    class="w-full mt-2 py-2.5 text-[13px] font-medium text-[var(--gazu-ink)] bg-[var(--gazu-mist)] border border-[var(--gazu-line)] rounded-md cursor-pointer hover:bg-[var(--gazu-line-2)] inline-flex items-center justify-center gap-2 transition-colors min-h-[44px]">
                    <span x-show="!expanded" class="inline-flex items-center gap-1.5">
                        <x-gazu.icon name="plus" size="14"/>
                        Показати ще {{ $stocks->count() - $visible }} {{ $stocks->count() - $visible === 1 ? 'склад' : 'склади' }}
                    </span>
                    <span x-show="expanded" x-cloak class="inline-flex items-center gap-1.5">
                        <x-gazu.icon name="minus" size="14"/>
                        Сховати
                    </span>
                </button>
            @endif
        </div>
    @endif

    <div class="h-px bg-[var(--gazu-line)] my-5"></div>

    <form action="{{ route('gazu.cart.add') }}" method="POST">
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
                    <button type="button" @click.prevent="$dispatch('open-oneclick', { productId: {{ $productId }}, qty: q })"
                            class="w-full h-12 bg-white text-[var(--gazu-ink)] border-[1.5px] border-[var(--gazu-ink)] rounded-lg text-[14px] font-medium cursor-pointer inline-flex items-center justify-center gap-2 hover:bg-[var(--gazu-mist)] transition-colors">
                        <x-gazu.icon name="phone" size="16"/>
                        {{ $oneClickLabel }}
                    </button>
                @endif
            </div>
        @endif
    </form>

    {{-- 1-клік модалка (Alpine listens for 'open-oneclick' event) --}}
    @if($oneClickEnabled && $productId)
        <div x-data="{ open: false, productId: null, qty: 1 }"
             x-on:open-oneclick.window="open = true; productId = $event.detail.productId; qty = $event.detail.qty || 1"
             x-show="open" x-cloak x-transition.opacity
             class="fixed inset-0 bg-black/45 z-[60] flex items-center justify-center p-4"
             @click.self="open = false">
            <div class="bg-white rounded-xl max-w-md w-full p-6" @click.stop>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="gazu-display text-xl font-semibold m-0">{{ $oneClickLabel }}</h3>
                    <button type="button" @click="open = false" class="bg-transparent border-0 cursor-pointer text-[var(--gazu-graphite)]">
                        <x-gazu.icon name="close" size="20"/>
                    </button>
                </div>
                <p class="text-sm text-[var(--gazu-graphite)] mb-4">{{ $oneClickMessage }}</p>
                <form action="{{ route('gazu.checkout.one-click') }}" method="POST">
                    @csrf
                    <input type="hidden" name="product_id" :value="productId">
                    <input type="hidden" name="quantity" :value="qty">
                    <label class="block mb-3">
                        <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Ваш телефон <span class="text-[var(--gazu-danger)]">*</span></span>
                        <input type="tel" name="phone" required value="{{ auth()->user()?->phone }}" placeholder="+380 67 123 45 67"
                               class="w-full px-3 py-3 border border-[var(--gazu-line)] rounded-md outline-none focus:border-[var(--gazu-ink)] gazu-mono">
                    </label>
                    <div class="flex gap-2">
                        <button type="submit" class="gazu-btn-primary flex-1">Замовити дзвінок</button>
                        <button type="button" @click="open = false" class="gazu-btn-outline">Скасувати</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

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
            Не впевнені в підборі? <span class="text-[var(--gazu-blue)] font-medium">Запитайте менеджера</span> — відповість за 5 хв.
        </div>
    </div>
</div>
