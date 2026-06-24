@props([
    'warehouseStocks' => null,    // Collection of Inventory rows with .warehouse loaded
    'closestWarehouseId' => null, // geo-detected warehouse ID
    'price' => 0,                 // base product price (fallback when a row has no own price)
    'brand' => null,              // brand name — shown at the top of this column
    'brandUrl' => null,           // optional catalog-filter link for the brand
    'article' => null,            // SKU / OEM article number
    'condition' => 'Новий',       // condition badge — shown at the top of this column
    'availabilityLabel' => null,  // статус наявності (напр. «Під замовлення») — для no-stock картки
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
    // warehouse_id => available qty — lets the availability line react to `sel`.
    $stocksJs = $stocks->mapWithKeys(fn ($s) => [
        $s->warehouse_id => max(0, $s->quantity - $s->reserved_quantity),
    ])->all();
@endphp
@if($stocks->isNotEmpty())
    {{-- Central column: product meta (brand · article · availability) + the
         warehouse picker. Owns its `sel` state; the buy-panel listens for the
         `warehouse-selected` window event to sync price / qty / warehouse_id. --}}
    <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-[10px] p-5 font-text"
         x-data="{
            sel: {{ $defaultWh ? (int) $defaultWh : 'null' }},
            expanded: false,
            stocks: {{ \Illuminate\Support\Js::from($stocksJs) }},
            get available() { return this.sel != null && this.stocks[this.sel] != null ? this.stocks[this.sel] : 0; }
         }"
         role="radiogroup" aria-label="Вибір складу для доставки">

        {{-- Product meta — one consistent label/value style across the whole block.
             Labels: 11px uppercase semibold graphite, fixed width so values align.
             Values: 13px medium ink. --}}
        <div class="pb-4 mb-4 border-b border-[var(--gazu-line)]">
            @if($condition)
                <div class="mb-3"><x-gazu.condition-badge value="{{ $condition }}"/></div>
            @endif
            <dl class="flex flex-col gap-2 m-0">
                @if($brand)
                    <div class="flex items-baseline gap-3">
                        <dt class="w-20 shrink-0 text-[11px] uppercase tracking-wide font-semibold text-[var(--gazu-graphite)]">Бренд</dt>
                        <dd class="m-0 text-[13px] font-medium text-[var(--gazu-ink)]">
                            @if($brandUrl)
                                <a wire:navigate href="{{ $brandUrl }}" class="text-[var(--gazu-ink)] no-underline hover:text-[var(--gazu-blue)] transition-colors">{{ $brand }}</a>
                            @else
                                {{ $brand }}
                            @endif
                        </dd>
                    </div>
                @endif
                @if($article)
                    <div class="flex items-baseline gap-3">
                        <dt class="w-20 shrink-0 text-[11px] uppercase tracking-wide font-semibold text-[var(--gazu-graphite)]">Артикул</dt>
                        <dd class="m-0">
                            {{-- click to copy the article number to the clipboard --}}
                            <button type="button"
                                    x-data="{ copied: false }"
                                    @click="navigator.clipboard.writeText(@js($article)).then(() => {
                                        copied = true;
                                        window.gazuToast && window.gazuToast('Артикул скопійовано', 'success');
                                        setTimeout(() => copied = false, 1500);
                                    }).catch(() => window.gazuToast && window.gazuToast('Не вдалося скопіювати', 'error'))"
                                    title="Скопіювати артикул"
                                    class="text-[13px] font-medium gazu-mono text-[var(--gazu-ink)] inline-flex items-center gap-1.5 cursor-pointer bg-transparent border-0 p-0 hover:text-[var(--gazu-blue)] transition-colors">
                                <span>{{ $article }}</span>
                                <svg x-show="!copied" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-55 shrink-0"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                <svg x-show="copied" x-cloak width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="var(--gazu-success)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M20 6 9 17l-5-5"/></svg>
                            </button>
                        </dd>
                    </div>
                @endif
                <div class="flex items-baseline gap-3">
                    <dt class="w-20 shrink-0 text-[11px] uppercase tracking-wide font-semibold text-[var(--gazu-graphite)]">Наявність</dt>
                    <dd class="m-0">
                        <span x-text="available > 0 ? (available + ' шт') : 'Немає'"
                              :class="available > 0 ? 'text-[var(--gazu-success)]' : 'text-[var(--gazu-danger)]'"
                              class="text-[13px] font-medium">—</span>
                    </dd>
                </div>
            </dl>
        </div>

        <div class="text-[11px] uppercase tracking-wide font-semibold text-[var(--gazu-graphite)] mb-3">Доставка зі складу</div>
        <div class="flex flex-col gap-1.5">
            @foreach($stocks as $idx => $s)
                @php
                    $available = max(0, $s->quantity - $s->reserved_quantity);
                    // Ціна складу в грн (конверсія за валютою рядка через Currency::toBase).
                    $sPrice = $s->price !== null ? (float) ($s->display_price ?? $s->price) : (float) $price;
                    $sCompare = $s->compare_at_price !== null ? (float) ($s->display_compare_at_price ?? $s->compare_at_price) : null;
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
                    :class="sel === {{ (int) $s->warehouse_id }} ? 'border-[var(--gazu-ink)] bg-[var(--gazu-ink)] text-[var(--gazu-on-brand)]' : 'border-[var(--gazu-line)] bg-[var(--gazu-surface)] text-[var(--gazu-ink)] hover:border-[var(--gazu-graphite)]'"
                    class="w-full flex items-center justify-between gap-3 px-3 py-2.5 border rounded-md transition-colors text-left min-h-[44px]
                        @if($available <= 0) opacity-50 cursor-not-allowed @endif">
                    <div class="flex items-center gap-2.5 min-w-0">
                        {{-- Warehouse icon: коробка (склад) — змінюється на check при виборі --}}
                        <div class="w-6 h-6 rounded-md flex items-center justify-center flex-shrink-0"
                             :class="sel === {{ (int) $s->warehouse_id }} ? 'bg-[var(--gazu-surface)] text-[var(--gazu-ink)]' : 'bg-[var(--gazu-mist)] text-[var(--gazu-blue)]'">
                            <svg x-show="sel !== {{ (int) $s->warehouse_id }}" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                            <svg x-show="sel === {{ (int) $s->warehouse_id }}" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                        </div>
                        <div class="min-w-0">
                            <div class="font-medium text-[13px] truncate inline-flex items-center gap-1.5">
                                <span>{{ $whCity }}</span>
                                @if($closestWarehouseId && $s->warehouse_id === $closestWarehouseId)
                                    <span class="text-[9px] gazu-mono px-1 py-0.5 rounded uppercase tracking-wider"
                                          :class="sel === {{ (int) $s->warehouse_id }} ? 'bg-[var(--gazu-surface)]/15 text-[var(--gazu-on-brand)]' : 'bg-[var(--gazu-blue-bg,#E0EBFF)] text-[var(--gazu-blue)]'">
                                        {{ ($gazuSettings ?? [])['gazu_warehouse_closest_label'] ?? 'найшвидша відправка' }}
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
@else
    {{-- Немає складських залишків (товар «під замовлення»): замість порожньої
         колонки показуємо метадані + блок постачання. Не дублюємо праву панель
         (доставка/гарантія) — фокус на самому товарі та процесі замовлення. --}}
    @php
        $availLabel = $availabilityLabel ?: 'Під замовлення';
    @endphp
    <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-[10px] p-5 font-text">
        <div class="pb-4 mb-4 border-b border-[var(--gazu-line)]">
            @if($condition)
                <div class="mb-3"><x-gazu.condition-badge value="{{ $condition }}"/></div>
            @endif
            <dl class="flex flex-col gap-2 m-0">
                @if($brand)
                    <div class="flex items-baseline gap-3">
                        <dt class="w-20 shrink-0 text-[11px] uppercase tracking-wide font-semibold text-[var(--gazu-graphite)]">Бренд</dt>
                        <dd class="m-0 text-[13px] font-medium text-[var(--gazu-ink)]">
                            @if($brandUrl)
                                <a wire:navigate href="{{ $brandUrl }}" class="text-[var(--gazu-ink)] no-underline hover:text-[var(--gazu-blue)] transition-colors">{{ $brand }}</a>
                            @else
                                {{ $brand }}
                            @endif
                        </dd>
                    </div>
                @endif
                @if($article)
                    <div class="flex items-baseline gap-3">
                        <dt class="w-20 shrink-0 text-[11px] uppercase tracking-wide font-semibold text-[var(--gazu-graphite)]">Артикул</dt>
                        <dd class="m-0">
                            <button type="button"
                                    x-data="{ copied: false }"
                                    @click="navigator.clipboard.writeText(@js($article)).then(() => {
                                        copied = true;
                                        window.gazuToast && window.gazuToast('Артикул скопійовано', 'success');
                                        setTimeout(() => copied = false, 1500);
                                    }).catch(() => window.gazuToast && window.gazuToast('Не вдалося скопіювати', 'error'))"
                                    title="Скопіювати артикул"
                                    class="text-[13px] font-medium gazu-mono text-[var(--gazu-ink)] inline-flex items-center gap-1.5 cursor-pointer bg-transparent border-0 p-0 hover:text-[var(--gazu-blue)] transition-colors">
                                <span>{{ $article }}</span>
                                <svg x-show="!copied" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-55 shrink-0"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                <svg x-show="copied" x-cloak width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="var(--gazu-success)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M20 6 9 17l-5-5"/></svg>
                            </button>
                        </dd>
                    </div>
                @endif
                <div class="flex items-baseline gap-3">
                    <dt class="w-20 shrink-0 text-[11px] uppercase tracking-wide font-semibold text-[var(--gazu-graphite)]">Наявність</dt>
                    <dd class="m-0 text-[13px] font-medium text-[var(--gazu-blue)]">{{ $availLabel }}</dd>
                </div>
            </dl>
        </div>

        <div class="text-[11px] uppercase tracking-wide font-semibold text-[var(--gazu-graphite)] mb-3">Постачання під замовлення</div>
        <ul class="flex flex-col gap-3 m-0 p-0 list-none">
            <li class="flex items-start gap-2.5">
                <span class="w-6 h-6 rounded-md bg-[var(--gazu-mist)] text-[var(--gazu-blue)] flex items-center justify-center flex-shrink-0 mt-px">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                </span>
                <span class="text-[13px] text-[var(--gazu-ink)] leading-snug">Привеземо <b>під замовлення</b> — додамо позицію до найближчого постачання</span>
            </li>
            <li class="flex items-start gap-2.5">
                <span class="w-6 h-6 rounded-md bg-[var(--gazu-mist)] text-[var(--gazu-blue)] flex items-center justify-center flex-shrink-0 mt-px">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                </span>
                <span class="text-[13px] text-[var(--gazu-ink)] leading-snug">Менеджер <b>зателефонує</b> й підтвердить наявність і точний термін</span>
            </li>
            <li class="flex items-start gap-2.5">
                <span class="w-6 h-6 rounded-md bg-[var(--gazu-mist)] text-[var(--gazu-blue)] flex items-center justify-center flex-shrink-0 mt-px">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                </span>
                <span class="text-[13px] text-[var(--gazu-ink)] leading-snug">Без передоплати — оформте замовлення або «Купити в один клік»</span>
            </li>
        </ul>
    </div>
@endif
