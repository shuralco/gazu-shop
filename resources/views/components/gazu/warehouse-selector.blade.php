@props([
    'warehouseStocks' => null,    // Collection of Inventory rows with .warehouse loaded
    'closestWarehouseId' => null, // geo-detected warehouse ID
    'price' => 0,                 // base product price (fallback when a row has no own price)
])
@php
    $stocks = $warehouseStocks instanceof \Illuminate\Support\Collection ? $warehouseStocks : collect();
    $defaultStock = $closestWarehouseId
        ? $stocks->first(fn ($s) => $s->warehouse_id === $closestWarehouseId && $s->quantity > 0)
        : null;
    $defaultStock ??= $stocks->firstWhere(fn ($s) => $s->quantity > 0);
    $defaultWh = $defaultStock?->warehouse_id;
    $visible = 4;
    $hasMore = $stocks->count() > $visible;
@endphp
@if($stocks->isNotEmpty())
    {{-- Standalone warehouse picker. Owns only its visual `sel` state; the
         buy-panel listens for the `warehouse-selected` window event to sync
         price / availability / the hidden warehouse_id input. --}}
    <div class="bg-white border border-[var(--gazu-line)] rounded-[10px] p-5 font-text"
         x-data="{ sel: {{ $defaultWh ? (int) $defaultWh : 'null' }}, expanded: false }"
         role="radiogroup" aria-label="Вибір складу для доставки">
        <div class="text-[11px] uppercase tracking-wide font-bold text-[var(--gazu-graphite)] mb-3">Доставка зі складу</div>
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
                    :aria-checked="sel === {{ (int) $s->warehouse_id }}"
                    aria-label="{{ $ariaLabel }}"
                    @click="sel = {{ (int) $s->warehouse_id }}; $dispatch('warehouse-selected', { id: {{ (int) $s->warehouse_id }} })"
                    @if($idx >= $visible) x-show="expanded" x-transition.opacity.duration.150ms @endif
                    @disabled($available <= 0)
                    :class="sel === {{ (int) $s->warehouse_id }} ? 'border-[var(--gazu-ink)] bg-[var(--gazu-ink)] text-white' : 'border-[var(--gazu-line)] bg-white text-[var(--gazu-ink)] hover:border-[var(--gazu-graphite)]'"
                    class="w-full flex items-center justify-between gap-3 px-3 py-2.5 border rounded-md transition-colors text-left min-h-[44px]
                        @if($available <= 0) opacity-50 cursor-not-allowed @endif">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-3.5 h-3.5 rounded-full border-2 flex-shrink-0"
                             :class="sel === {{ (int) $s->warehouse_id }} ? 'border-white bg-white' : 'border-[var(--gazu-graphite)]'">
                            <div x-show="sel === {{ (int) $s->warehouse_id }}" class="w-1.5 h-1.5 rounded-full bg-[var(--gazu-ink)] m-auto mt-[3px]"></div>
                        </div>
                        <div class="min-w-0">
                            <div class="font-medium text-[13px] truncate inline-flex items-center gap-1.5">
                                <span>{{ $whCity }}</span>
                                @if($closestWarehouseId && $s->warehouse_id === $closestWarehouseId)
                                    <span class="text-[9px] gazu-mono px-1 py-0.5 rounded uppercase tracking-wider"
                                          :class="sel === {{ (int) $s->warehouse_id }} ? 'bg-white/15 text-white' : 'bg-[var(--gazu-blue-bg,#E0EBFF)] text-[var(--gazu-blue)]'">
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
