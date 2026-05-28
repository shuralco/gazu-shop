@extends('gazu.layout')

@section('title', ($p->name ?? 'Товар') . ' — GAZU')
@section('description', 'Купити '.($p->name ?? 'товар').' за '.number_format((float)($p->price ?? 0), 0, '.', ' ').' ₴. '
    .(is_object($p) && $p->brand ? 'Бренд: '.$p->brand.'. ' : '')
    .'Артикул: '.($p->sku ?? '—').'. Доставка Новою Поштою, гарантія, повернення 14 днів.')
@section('og_type', 'product')

@php
    $name = is_object($p) ? ($p->name ?? '') : ($p['name'] ?? '');
    $oem = is_object($p) ? ($p->oem ?? '') : ($p['oem'] ?? '');
    $brand = is_object($p) ? ($p->brand ?? '') : ($p['brand'] ?? '');
    $kind = is_object($p) ? ($p->image_kind ?? 'filter') : ($p['image_kind'] ?? 'filter');
    $price = is_object($p) ? (float)($p->price ?? 0) : (float)($p['price'] ?? 0);
    $oldPrice = is_object($p) ? ($p->old_price ?? null) : ($p['old_price'] ?? null);
    $discount = is_object($p) ? ($p->discount ?? null) : ($p['discount'] ?? null);
    $qty = is_object($p) ? (int)($p->qty ?? 0) : (int)($p['qty'] ?? 0);
    $rating = is_object($p) ? (float)($p->rating ?? 0) : (float)($p['rating'] ?? 0);
    $reviews = is_object($p) ? (int)($p->reviews ?? 0) : (int)($p['reviews'] ?? 0);
    $condition = is_object($p) ? ($p->condition ?? 'Новий') : ($p['condition'] ?? 'Новий');
    $fits = is_object($p) ? ($p->fits ?? null) : ($p['fits'] ?? null);

    // Specifications: з БД (Product->specifications). Fallback — лише базові поля товару.
    $rawSpecs = is_object($p) ? ($p->specifications ?? null) : ($p['specifications'] ?? null);
    if (is_array($rawSpecs) && ! empty($rawSpecs)) {
        $specs = [];
        foreach ($rawSpecs as $k => $v) {
            $isMono = preg_match('/^\d|[\.,×]|^[A-Z]\d/', (string) $v); // мономо для кодів/розмірів
            $specs[] = [(string) $k, (string) $v, (bool) $isMono];
        }
    } else {
        $specs = [
            ['Виробник', $brand ?: '—', false],
            ['Артикул', $oem ?: '—', true],
            ['Стан', $condition, false],
            ['Гарантія', $gazuSettings['gazu_default_warranty'] ?? '12 місяців', false],
        ];
    }

    // Compatibility — SINGLE SOURCE OF TRUTH: pivot product_compatibility
    // (та сама data що використовує car-selector filter та apiCompatCheck).
    // Fallback на JSON column products.compatibility для legacy records.
    $compat = [];
    if (is_object($p) && method_exists($p, 'compatibleEngines')) {
        try {
            $engines = $p->compatibleEngines()
                ->with(['model.make'])
                ->limit(100)
                ->get();
            foreach ($engines as $eng) {
                $makeModel = $eng->model->make ?? null;
                $makeName  = $makeModel->name ?? '—';
                $makeLogo  = $makeModel?->logo_url;
                $modelName = $eng->model->name ?? '—';
                $years = '';
                if (! empty($eng->model->year_from) || ! empty($eng->model->year_to)) {
                    $years = (string) ($eng->model->year_from ?? '') . '–' . (string) ($eng->model->year_to ?? '');
                    $years = trim($years, '–') ?: '—';
                }
                $engineLabel = trim(($eng->label ?? '') . ' ' . ($eng->code ?? ''));
                $compat[] = [$makeName, $modelName, $years ?: '—', $engineLabel ?: '—', $makeLogo];
            }
        } catch (\Throwable $e) { /* relation might not exist on mock $p */ }
    }
    // Fallback: legacy JSON column для старих products без pivot rows.
    if (empty($compat)) {
        $rawCompat = is_object($p) ? ($p->compatibility ?? null) : ($p['compatibility'] ?? null);
        if (is_array($rawCompat) && ! empty($rawCompat)) {
            foreach ($rawCompat as $row) {
                if (is_array($row)) {
                    $compat[] = [$row['make'] ?? '—', $row['model'] ?? '—', $row['years'] ?? '—', $row['engine'] ?? '—', null];
                }
            }
        }
    }
@endphp

@php
    // Schema.org Product — enriched для rich snippets у Google.
    // Включає: mpn, image, itemCondition, priceValidUntil, hasMerchantReturnPolicy,
    // shippingDetails. Це дає Google показувати ціну/наявність/рейтинг прямо у SERP.
    $conditionMap = [
        'new'         => 'https://schema.org/NewCondition',
        'Новий'       => 'https://schema.org/NewCondition',
        'used'        => 'https://schema.org/UsedCondition',
        'Б/у'         => 'https://schema.org/UsedCondition',
        'refurbished' => 'https://schema.org/RefurbishedCondition',
        'Відновлений' => 'https://schema.org/RefurbishedCondition',
    ];
    $productImageUrl = is_object($p) ? ($p->image ?? null) : null;
    if ($productImageUrl && ! \Illuminate\Support\Str::startsWith($productImageUrl, ['http://','https://'])) {
        $productImageUrl = url('/storage/'.$productImageUrl);
    }
    // Fallback на part-image webp pool (same algorithm як у product card).
    if (! $productImageUrl) {
        $kindForJsonLd = is_object($p) ? ($p->image_kind ?? 'filter') : 'filter';
        $poolDir = public_path("img/parts/{$kindForJsonLd}");
        $poolFiles = is_dir($poolDir) ? glob($poolDir.'/*.webp') : [];
        sort($poolFiles);
        if (! empty($poolFiles)) {
            $seedForLd = is_object($p) ? (int) ($p->id ?? 0) : 0;
            $productImageUrl = url("/img/parts/{$kindForJsonLd}/".basename($poolFiles[abs($seedForLd) % count($poolFiles)]));
        }
    }

    $jsonldProduct = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $name,
        'sku' => (string) $oem,
        'mpn' => (string) $oem,
        'description' => $fits ?: $name,
        'image' => $productImageUrl ?: url('/og-default.svg'),
        'url' => url()->current(),
        'offers' => [
            '@type' => 'Offer',
            'price' => number_format($price, 2, '.', ''),
            'priceCurrency' => 'UAH',
            'availability' => $qty > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            'itemCondition' => $conditionMap[$condition ?? 'new'] ?? 'https://schema.org/NewCondition',
            'url' => url()->current(),
            'priceValidUntil' => now()->addYear()->format('Y-m-d'),
            'seller' => [
                '@type' => 'Organization',
                'name' => 'GAZU',
            ],
            'hasMerchantReturnPolicy' => [
                '@type' => 'MerchantReturnPolicy',
                'applicableCountry' => 'UA',
                'returnPolicyCategory' => 'https://schema.org/MerchantReturnFiniteReturnWindow',
                'merchantReturnDays' => 14,
                'returnMethod' => 'https://schema.org/ReturnByMail',
                'returnFees' => 'https://schema.org/FreeReturn',
            ],
            'shippingDetails' => [
                '@type' => 'OfferShippingDetails',
                'shippingDestination' => ['@type' => 'DefinedRegion', 'addressCountry' => 'UA'],
                'deliveryTime' => [
                    '@type' => 'ShippingDeliveryTime',
                    'businessDays' => ['@type' => 'OpeningHoursSpecification', 'dayOfWeek' => ['https://schema.org/Monday','https://schema.org/Tuesday','https://schema.org/Wednesday','https://schema.org/Thursday','https://schema.org/Friday','https://schema.org/Saturday']],
                    'handlingTime' => ['@type' => 'QuantitativeValue', 'minValue' => 0, 'maxValue' => 1, 'unitCode' => 'DAY'],
                    'transitTime'  => ['@type' => 'QuantitativeValue', 'minValue' => 1, 'maxValue' => 3, 'unitCode' => 'DAY'],
                ],
            ],
        ],
    ];
    if (! empty($brand)) {
        $jsonldProduct['brand'] = ['@type' => 'Brand', 'name' => $brand];
    }
    // SEO мікророзмітка з aggregateRating — тільки якщо reviews модуль УВімкнено
    if (module('reviews')->enabled() && $rating > 0 && $reviews > 0) {
        $jsonldProduct['aggregateRating'] = [
            '@type' => 'AggregateRating',
            'ratingValue' => (string) $rating,
            'reviewCount' => $reviews,
            'bestRating' => '5',
            'worstRating' => '1',
        ];
    }
@endphp

@section('jsonld')
<script type="application/ld+json">{!! json_encode($jsonldProduct, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endsection

{{-- OG image — real product photo для share-previews у соцмережах. --}}
@if(! empty($productImageUrl))
    @section('og_image'){{ $productImageUrl }}@endsection
@endif

@section('content')
    <div class="gazu-container">
        @include('gazu.partials.product-breadcrumbs', compact('p', 'brand', 'oem', 'name'))

        @php
            // Brand link + article are passed down to the central column's
            // <x-gazu.warehouse-selector> — no longer rendered in this header.
            $brandHeaderSlug = null;
            if (is_object($p) && $p->relationLoaded('brand') && ($b = $p->getRelation('brand'))) {
                $brandHeaderSlug = $b->slug ?: \Illuminate\Support\Str::slug((string) $b->getRawOriginal('name'));
            }
            if (! $brandHeaderSlug && is_object($p) && $p->manufacturer) {
                $brandHeaderSlug = \Illuminate\Support\Str::slug((string) $p->manufacturer);
            }
            // SEO-friendly: /brand/{slug} (brand profile) замість /catalog filter.
            $brandUrl = $brandHeaderSlug ? route('gazu.brand', ['slug' => $brandHeaderSlug]) : null;
            $oemReal = $oem ?: (is_object($p) ? ($p->sku ?? '') : '');
            $soldCount = is_object($p) ? (int) ($p->sold_count ?? 0) : 0;
            // Etap 51: $productId був визначений нижче (line 480) — підняли наверх
            // для heart button у gallery section (line ~130). Інакше Undefined variable.
            $productId = is_object($p) ? ($p->id ?? null) : null;
        @endphp
        {{-- Product top — outer grid: gallery | right-hand side.
             RHS = title (over) + nested grid [ info+warehouse | buy-panel ]. --}}
        <div class="gazu-grid-product-main mt-1">
            {{-- Gallery: big main image + active-thumb grid (4 ракурси, hover/click swap) --}}
            @php
                $gallerySeed = is_object($p) ? (int) ($p->id ?? 0) : 0;
                $variants = [
                    $gallerySeed,
                    $gallerySeed + 1001,
                    $gallerySeed + 2002,
                    $gallerySeed + 3003,
                ];
            @endphp
            <div class="flex flex-col gap-3" x-data="{ idx: 0, zoom: false }" @keydown.escape.window="zoom = false">
                <div class="aspect-square bg-white rounded-lg relative overflow-hidden cursor-zoom-in group/main"
                     @click="zoom = true" title="Натисніть щоб збільшити">
                    <div class="absolute inset-0 gazu-grid-pattern"></div>
                    @foreach($variants as $i => $seed)
                        <div class="absolute inset-0 transition-opacity duration-200"
                             :class="idx === {{ $i }} ? 'opacity-100' : 'opacity-0 pointer-events-none'">
                            <x-gazu.part-image kind="{{ $kind }}" :seed="$seed" fit/>
                        </div>
                    @endforeach
                    {{-- AJAX variant-switch overlay. Default opacity-0 + display:none.
                         AJAX handler ставить src+display:block, потім onload→opacity-1.
                         Поверх gallery-grid (z-3) щоб не блокувати hover/zoom за відсутності. --}}
                    <img data-gazu-product-image
                         alt=""
                         style="display:none; opacity:0; transition: opacity .2s ease;"
                         class="absolute inset-0 w-full h-full object-contain bg-white z-[3]"
                         onload="this.style.opacity='1';"
                         onerror="this.style.display='none';"/>
                    <div class="absolute top-3.5 left-3.5 px-2.5 py-1.5 bg-white border border-[var(--gazu-line)] gazu-mono text-[11px] text-[var(--gazu-ink)] tracking-wider rounded z-[1]">
                        <span x-text="idx + 1">1</span> / {{ count($variants) }}
                    </div>
                    {{-- Zoom hint icon — top-right поряд з heart, видно тільки при hover --}}
                    <div class="absolute bottom-3.5 left-3.5 w-9 h-9 rounded-lg bg-white/90 backdrop-blur border border-[var(--gazu-line)] inline-flex items-center justify-center text-[var(--gazu-ink)] opacity-0 group-hover/main:opacity-100 transition-opacity z-[1] pointer-events-none">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                    </div>
                    @if($productId)
                        {{-- Heart wired to wishlist toggle, hydrated client-side --}}
                        <button type="button"
                                data-wishlist-pid="{{ $productId }}"
                                x-data="{ active: false, busy: false }"
                                x-init="if (window.GAZU_WISHLIST_IDS && window.GAZU_WISHLIST_IDS.has({{ (int) $productId }})) active = true;
                                        window.addEventListener('gazu:wishlist-ids-loaded', () => { if (window.GAZU_WISHLIST_IDS && window.GAZU_WISHLIST_IDS.has({{ (int) $productId }})) active = true; });"
                                @click.prevent.stop="
                                    if (busy) return; busy = true;
                                    Promise.resolve(window.gazuWishlistToggle({{ (int) $productId }})).then(inWl => { active = inWl; }).finally(() => busy = false);"
                                :title="active ? 'Прибрати з обраного' : 'Додати в обране'"
                                :class="active ? 'text-[var(--gazu-danger)] border-[var(--gazu-danger)]' : 'text-[var(--gazu-graphite)] border-[var(--gazu-line)] hover:text-[var(--gazu-danger)]'"
                                class="absolute top-3.5 right-3.5 w-9 h-9 border bg-white rounded-lg cursor-pointer inline-flex items-center justify-center transition-colors z-[2]">
                            <svg width="18" height="18" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" :fill="active ? 'currentColor' : 'none'">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78Z"/>
                            </svg>
                        </button>
                    @endif
                </div>

                {{-- Fullscreen lightbox: arrows навігація, ESC + click backdrop close --}}
                <div x-show="zoom" x-cloak x-transition.opacity
                     class="fixed inset-0 z-[90] flex items-center justify-center p-4 sm:p-8"
                     style="background: rgba(14,27,44,0.92);"
                     @click.self="zoom = false">
                    <button type="button" @click="zoom = false" aria-label="Закрити"
                            class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 text-white border-0 cursor-pointer inline-flex items-center justify-center transition-colors z-[1]">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                    <button type="button"
                            @click.stop="idx = (idx - 1 + {{ count($variants) }}) % {{ count($variants) }}"
                            aria-label="Попереднє"
                            class="absolute left-4 sm:left-8 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-white/10 hover:bg-white/20 text-white border-0 cursor-pointer inline-flex items-center justify-center transition-colors z-[1]">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                    </button>
                    <button type="button"
                            @click.stop="idx = (idx + 1) % {{ count($variants) }}"
                            aria-label="Наступне"
                            class="absolute right-4 sm:right-8 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-white/10 hover:bg-white/20 text-white border-0 cursor-pointer inline-flex items-center justify-center transition-colors z-[1]">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                    </button>
                    <div class="relative w-full max-w-[90vw] max-h-[85vh] aspect-square bg-white rounded-2xl overflow-hidden flex items-center justify-center" @click.stop>
                        @foreach($variants as $i => $seed)
                            <div class="absolute inset-0 flex items-center justify-center p-8 transition-opacity"
                                 :class="idx === {{ $i }} ? 'opacity-100' : 'opacity-0 pointer-events-none'">
                                <x-gazu.part-image kind="{{ $kind }}" :seed="$seed" fit/>
                            </div>
                        @endforeach
                        <div class="absolute bottom-4 left-1/2 -translate-x-1/2 px-3 py-1.5 bg-black/70 text-white gazu-mono text-[12px] rounded">
                            <span x-text="idx + 1">1</span> / {{ count($variants) }}
                        </div>
                    </div>
                </div>
                {{-- Real thumbnails (4 variants) — клік/hover для перемикання головної. --}}
                <div class="grid grid-cols-4 gap-2">
                    @foreach($variants as $i => $seed)
                        <button type="button"
                                @click="idx = {{ $i }}" @mouseover="idx = {{ $i }}"
                                :class="idx === {{ $i }} ? 'ring-2 ring-[var(--gazu-blue)] ring-offset-1' : 'opacity-80 hover:opacity-100'"
                                class="aspect-square bg-[var(--gazu-paper)] rounded-md overflow-hidden cursor-pointer transition-all">
                            <x-gazu.part-image kind="{{ $kind }}" :seed="$seed" fit class="w-full h-full object-cover"/>
                        </button>
                    @endforeach
                </div>
            </div>{{-- /gallery --}}

            {{-- Right-hand side: product title spanning the two columns below it --}}
            <div>
                <h1 data-gazu-product-title class="gazu-display text-[28px] sm:text-[32px] font-semibold text-[var(--gazu-ink)] m-0 leading-tight">{{ $name }}</h1>
                @php
                    // Reviews/rating показуються тільки якщо модуль reviews УВімкнено.
                    // soldCount — це окрема метрика (не reviews-модуль), не гейтиться.
                    $showReviews = module('reviews')->enabled();
                @endphp
                @if(($showReviews && ($rating > 0 || $reviews > 0)) || $soldCount > 0)
                    <div class="flex items-center gap-1 whitespace-nowrap mt-2">
                        @if($showReviews && $rating > 0)
                            <div class="flex gap-px text-[var(--gazu-warn)]">
                                @for($i = 1; $i <= 5; $i++)
                                    <x-gazu.icon name="star" size="12" fill="{{ $i <= floor($rating) ? 'var(--gazu-warn)' : 'none' }}" stroke="var(--gazu-warn)"/>
                                @endfor
                            </div>
                        @endif
                        <span class="text-xs text-[var(--gazu-graphite)]">
                            @if($showReviews && $rating > 0){{ number_format($rating, 1) }}@endif
                            @if($showReviews && $reviews > 0) · {{ $reviews }} {{ \plural_uk_count($reviews, 'відгук', 'відгуки', 'відгуків') }}@endif
                            @if($soldCount > 0) · {{ $soldCount }} продано @endif
                        </span>
                    </div>
                @endif

                {{-- Nested grid: central info+warehouse column · buy-panel --}}
                <div class="gazu-grid-product-rhs mt-4">
                    {{-- Central column — condition · brand · article · availability
                         + warehouse picker. Syncs the buy-panel via `warehouse-selected`. --}}
                    <div>
                        <x-gazu.warehouse-selector
                            :warehouseStocks="$warehouseStocks ?? collect()"
                            :closestWarehouseId="$closestWarehouseId ?? null"
                            :price="$price"
                            :brand="$brand"
                            :brandUrl="$brandUrl"
                            :article="$oemReal"/>
                    </div>

                    {{-- buy-panel --}}
                    <div class="lg:sticky lg:top-4 lg:self-start" id="buy-panel-anchor">
                        <x-gazu.buy-panel
                            :price="$price"
                            :oldPrice="$oldPrice"
                            :qty="$qty"
                            :discount="$discount"
                            :productId="is_object($p) ? ($p->id ?? null) : null"
                            :name="$name"
                            :warehouseStocks="$warehouseStocks ?? collect()"
                            :closestWarehouseId="$closestWarehouseId ?? null"/>
                    </div>
                </div>
            </div>
        </div>{{-- /gazu-grid-product-main --}}

        {{-- 4D: Compat-check — перевірити чи запчастина підходить вашому авто.
             Reference: chery911.com.ua/products/aftermarket-yj026280-30376.html.
             Логіка: марка→модель→двигун, перевірити по product_compatibility. --}}
        @if(is_object($p) && $p instanceof \App\Models\Product)
            <x-gazu.compat-check :product-id="$p->id"/>
        @endif

        {{-- Hook-point: модулі підписуються на 'product.page.variants' і
             повертають HTML для рендера тут. related_products видає variant
             picker; інші модулі можуть додати свої блоки (compatibility,
             cross-sell, etc.) без редагування цього файлу. --}}
        @hookAction('product.page.variants', $p)

        {{-- Класичні опції товару (Колір / Розмір / Об'єм) — radio pills,
             color swatches або dropdown залежно від option.type. На зміну
             dispatch'имо gazu:variant-switched (повторно використовуємо
             AJAX listener вище для price/image/sku/qty). --}}
        @if(is_object($p) && $p instanceof \App\Models\Product)
            @php
                $productOptions = $p->options()->where('is_active', true)->orderBy('sort_order')->with(['values' => fn($q) => $q->where('is_active', true)->orderBy('sort_order')])->get();
            @endphp
            @if($productOptions->isNotEmpty())
                <section class="bg-white border border-[var(--gazu-line)] rounded-lg p-4 sm:p-5 mt-4 mb-4"
                         x-data="{
                            picks: {},
                            busy: false,
                            async sync() {
                                const ids = Object.values(this.picks).filter(Boolean);
                                if (ids.length === 0) return;
                                this.busy = true;
                                try {
                                    const url = new URL('/api/products/{{ (int) $p->id }}/variant-by-options', window.location.origin);
                                    ids.forEach(id => url.searchParams.append('option_value_ids[]', id));
                                    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                                    if (!res.ok) throw new Error('http '+res.status);
                                    const data = await res.json();
                                    window.dispatchEvent(new CustomEvent('gazu:variant-switched', { detail: data }));
                                } catch (e) { console.warn('[options] fetch failed', e); }
                                finally { this.busy = false; }
                            }
                         }">
                    @foreach($productOptions as $opt)
                        <div class="mb-4 last:mb-0">
                            <div class="flex items-baseline gap-2 mb-2">
                                <span class="text-sm font-semibold text-[var(--gazu-ink)]">{{ $opt->name }}:</span>
                                <span class="text-sm text-[var(--gazu-graphite)]" x-text="picks[{{ $opt->id }}] ? '{{ $opt->id }}' : ''" x-cloak></span>
                            </div>

                            @if($opt->type === 'color')
                                <div class="flex flex-wrap gap-2">
                                    @foreach($opt->values as $v)
                                        <button type="button"
                                                title="{{ $v->value }}"
                                                @click="picks[{{ $opt->id }}] = {{ $v->id }}; sync();"
                                                :disabled="busy"
                                                :class="picks[{{ $opt->id }}] === {{ $v->id }} ? 'ring-2 ring-[var(--gazu-ink)] ring-offset-2' : 'ring-1 ring-[var(--gazu-line)] hover:ring-[var(--gazu-graphite)]'"
                                                style="background-color: {{ $v->color_hex ?: '#ddd' }}"
                                                class="w-9 h-9 rounded-full transition-all disabled:opacity-50 disabled:cursor-wait">
                                        </button>
                                    @endforeach
                                </div>
                            @elseif($opt->type === 'image')
                                <div class="flex flex-wrap gap-2">
                                    @foreach($opt->values as $v)
                                        <button type="button"
                                                title="{{ $v->value }}"
                                                @click="picks[{{ $opt->id }}] = {{ $v->id }}; sync();"
                                                :disabled="busy"
                                                :class="picks[{{ $opt->id }}] === {{ $v->id }} ? 'ring-2 ring-[var(--gazu-ink)] ring-offset-1' : 'ring-1 ring-[var(--gazu-line)] hover:ring-[var(--gazu-ink)] opacity-90'"
                                                class="w-16 h-16 rounded-md overflow-hidden bg-[var(--gazu-paper)] transition-all disabled:opacity-50 disabled:cursor-wait">
                                            @if($v->image)
                                                <img src="{{ \Illuminate\Support\Str::startsWith($v->image, ['http','/']) ? $v->image : '/storage/'.$v->image }}" alt="{{ $v->value }}" class="w-full h-full object-cover"/>
                                            @else
                                                <span class="block w-full h-full flex items-center justify-center text-xs">{{ $v->value }}</span>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            @elseif($opt->type === 'select')
                                <select @change="picks[{{ $opt->id }}] = parseInt($event.target.value); sync();"
                                        :disabled="busy"
                                        class="w-full max-w-xs px-3 py-2 text-sm rounded-md border border-[var(--gazu-line)] bg-white text-[var(--gazu-ink)] focus:outline-none focus:border-[var(--gazu-ink)] disabled:opacity-50">
                                    <option value="">— Оберіть {{ mb_strtolower($opt->name) }} —</option>
                                    @foreach($opt->values as $v)
                                        <option value="{{ $v->id }}">{{ $v->value }}@if($v->price_modifier != 0) ({{ $v->price_modifier > 0 ? '+' : '' }}{{ (int) $v->price_modifier }} ₴)@endif</option>
                                    @endforeach
                                </select>
                            @else
                                {{-- type=text → radio-pills (default «класичний» вигляд) --}}
                                <div class="flex flex-wrap gap-2">
                                    @foreach($opt->values as $v)
                                        <button type="button"
                                                @click="picks[{{ $opt->id }}] = {{ $v->id }}; sync();"
                                                :disabled="busy"
                                                :class="picks[{{ $opt->id }}] === {{ $v->id }} ? 'bg-[var(--gazu-ink)] text-white ring-[var(--gazu-ink)]' : 'bg-white text-[var(--gazu-ink)] ring-[var(--gazu-line)] hover:ring-[var(--gazu-ink)] hover:bg-[var(--gazu-paper)]'"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-md ring-1 transition-colors disabled:opacity-50 disabled:cursor-wait">
                                            <span>{{ $v->value }}</span>
                                            @if($v->price_modifier != 0)
                                                <span class="text-xs opacity-70">{{ $v->price_modifier > 0 ? '+' : '' }}{{ (int) $v->price_modifier }} ₴</span>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </section>
            @endif
        @endif

        @php
                    $analogList = ($analogs ?? null) instanceof \Illuminate\Support\Collection
                        ? $analogs : collect();
                    $tabCounts = [
                        'spec'     => count($specs),
                        'compat'   => count($compat),
                        'analogs'  => $analogList->count(),
                        'reviews'  => $reviews,
                        'delivery' => null,
                    ];
                    $tabDefs = [
                        'spec'     => 'Характеристики',
                        'compat'   => 'Сумісність',
                        'analogs'  => 'Аналоги',
                        'reviews'  => 'Відгуки',
                        'delivery' => 'Доставка та оплата',
                    ];
                    $deliveryText = $gazuSettings['gazu_product_delivery_text']
                        ?? 'Нова Пошта по Україні · Доставка наступного дня для замовлень до 16:00 · Безкоштовно від 1500 ₴.';
                    $paymentText = $gazuSettings['gazu_product_payment_text']
                        ?? 'Visa / Mastercard, Apple Pay, Google Pay, готівка при отриманні (накладений платіж), безпечна оплата через LiqPay.';
                @endphp
                <div class="mt-2" x-data="{ tab: 'spec' }">
                    {{-- Mobile — sticky tab strip: every tab sits in a row; the
                         ← / → buttons appear only when the row overflows and
                         nudge it sideways (the row itself is also swipe-scrollable). --}}
                    {{-- Sticky element has no margins — margins on a sticky element
                         cause jumpiness during the in-flow → pinned transition.
                         Sticky's parent (the .mt-2 tabs container) is tall — it
                         holds the active tabpanel content — giving sticky enough
                         room to pin while the user reads through that panel. --}}
                    <div class="md:hidden sticky top-2 z-30 mt-3"
                         x-data="{
                            canL: false, canR: false,
                            upd() {
                                const e = this.$refs.strip;
                                this.canL = e.scrollLeft > 4;
                                this.canR = e.scrollLeft + e.clientWidth < e.scrollWidth - 4;
                            },
                            nudge(d) { this.$refs.strip.scrollBy({ left: d * 150, behavior: 'smooth' }); }
                         }"
                         x-init="$nextTick(() => upd())"
                         @resize.window.debounce.150ms="upd()">
                        <div class="flex items-stretch bg-white border border-[var(--gazu-line)] rounded-xl overflow-hidden shadow-[0_6px_20px_-6px_rgba(14,27,44,0.22)]">
                            <button type="button" @click="nudge(-1)" x-show="canL" x-cloak x-transition.opacity
                                    aria-label="Прокрутити вкладки вліво"
                                    class="w-9 shrink-0 bg-white border-r border-[var(--gazu-line)] text-[var(--gazu-ink)] inline-flex items-center justify-center cursor-pointer active:scale-90 transition-transform">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                            </button>
                            <div x-ref="strip" @scroll.passive="upd()" role="tablist" aria-label="Інформація про товар"
                                 class="flex gap-1 gazu-scroll-x flex-1 px-1">
                                @foreach($tabDefs as $k => $l)
                                    <button type="button" role="tab"
                                            :aria-selected="tab === '{{ $k }}'"
                                            @click="tab = '{{ $k }}'; $el.scrollIntoView({ inline: 'center', block: 'nearest', behavior: 'smooth' })"
                                            :class="tab === '{{ $k }}'
                                                ? 'text-[var(--gazu-ink)] font-semibold border-b-2 border-[var(--gazu-ink)]'
                                                : 'text-[var(--gazu-graphite)] border-b-2 border-transparent'"
                                            class="px-3.5 py-3 -mb-px bg-transparent cursor-pointer inline-flex items-center gap-1.5 text-[13px] shrink-0 whitespace-nowrap transition-colors">
                                        {{ $l }}
                                        @if($tabCounts[$k] !== null && $tabCounts[$k] > 0)
                                            <span class="text-[10px] text-[var(--gazu-muted)] gazu-mono">{{ $tabCounts[$k] }}</span>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                            <button type="button" @click="nudge(1)" x-show="canR" x-cloak x-transition.opacity
                                    aria-label="Прокрутити вкладки вправо"
                                    class="w-9 shrink-0 bg-white border-l border-[var(--gazu-line)] text-[var(--gazu-ink)] inline-flex items-center justify-center cursor-pointer active:scale-90 transition-transform">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                            </button>
                        </div>
                    </div>

                    {{-- Desktop — classic horizontal tablist --}}
                    <div role="tablist" aria-label="Інформація про товар"
                         class="border-b border-[var(--gazu-line)] hidden md:flex gap-1 font-text mt-3 gazu-scroll-x whitespace-nowrap">
                        @foreach($tabDefs as $k => $l)
                            <button type="button" role="tab"
                                    :aria-selected="tab === '{{ $k }}'"
                                    :tabindex="tab === '{{ $k }}' ? 0 : -1"
                                    @click="tab = '{{ $k }}'"
                                    :class="tab === '{{ $k }}'
                                        ? 'text-[var(--gazu-ink)] font-semibold border-b-2 border-[var(--gazu-ink)]'
                                        : 'text-[var(--gazu-graphite)] border-b-2 border-transparent hover:text-[var(--gazu-ink)]'"
                                    class="px-4.5 py-3.5 -mb-px bg-transparent cursor-pointer inline-flex items-center gap-1.5 text-sm transition-colors">
                                {{ $l }}
                                @if($tabCounts[$k] !== null && $tabCounts[$k] > 0)
                                    <span class="text-[11px] text-[var(--gazu-muted)] gazu-mono">{{ $tabCounts[$k] }}</span>
                                @endif
                            </button>
                        @endforeach
                    </div>

                    {{-- spec --}}
                    <div role="tabpanel" x-show="tab === 'spec'" x-cloak class="mt-6">
                        <div class="gazu-display text-lg font-semibold mb-3">Характеристики</div>
                        <div class="bg-white border border-[var(--gazu-line)] rounded-lg overflow-hidden">
                            @php
                                // Clickable spec rows → catalog filter:
                                //   "Виробник" → brand slug (lower-cased name with hyphens)
                                //   "Категорія" → cat slug (if Product has a category relation)
                                $brandSlug = null;
                                if (is_object($p) && $p->relationLoaded('brand') && ($b = $p->getRelation('brand'))) {
                                    $brandSlug = $b->slug ?: \Illuminate\Support\Str::slug((string) $b->getRawOriginal('name'));
                                }
                                if (! $brandSlug && is_object($p) && $p->manufacturer) {
                                    $brandSlug = \Illuminate\Support\Str::slug((string) $p->manufacturer);
                                }
                                $catSlug = null;
                                if (is_object($p) && $p->relationLoaded('category') && ($cat = $p->getRelation('category'))) {
                                    $raw = $cat->getRawOriginal('slug');
                                    if (is_string($raw) && str_starts_with($raw, '{')) {
                                        $decoded = json_decode($raw, true);
                                        $catSlug = $decoded['uk'] ?? $decoded['en'] ?? null;
                                    } else {
                                        $catSlug = $raw ?: ($cat->slug ?? null);
                                    }
                                }
                            @endphp
                            @foreach($specs as [$k, $v, $mono])
                                @php
                                    $href = null;
                                    if ($k === 'Виробник' && $brandSlug && $v !== '—') {
                                        $href = route('gazu.brand', ['slug' => $brandSlug]);
                                    } elseif ($k === 'Категорія' && $catSlug) {
                                        $href = url('/'.$catSlug);
                                    }
                                @endphp
                                <div class="grid grid-cols-2 px-4 py-2.5 text-[13px] @if(!$loop->last) border-b border-[var(--gazu-line)] @endif">
                                    <span class="text-[var(--gazu-graphite)]">{{ $k }}</span>
                                    @if($href)
                                        <a wire:navigate href="{{ $href }}" class="text-[var(--gazu-blue)] {{ $mono ? 'gazu-mono font-medium' : '' }} no-underline hover:underline inline-flex items-center gap-1">
                                            {{ $v }}
                                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-70"><path d="M7 17 17 7"/><path d="M7 7h10v10"/></svg>
                                        </a>
                                    @else
                                        <span class="text-[var(--gazu-ink)] {{ $mono ? 'gazu-mono font-medium' : '' }}">{{ $v }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- compat --}}
                    <div role="tabpanel" x-show="tab === 'compat'" x-cloak class="mt-6">
                        <div class="gazu-display text-lg font-semibold mb-3">Сумісність з автомобілями</div>
                        @if(! empty($compat))
                            <div class="bg-white border border-[var(--gazu-line)] rounded-lg overflow-hidden overflow-x-auto">
                                <table class="w-full text-left font-text text-[13px]">
                                    <thead class="bg-[var(--gazu-bone)] gazu-mono text-[11px] text-[var(--gazu-graphite)] tracking-wider uppercase">
                                        <tr>
                                            <th class="px-3.5 py-3 font-medium">Марка</th>
                                            <th class="px-3.5 py-3 font-medium">Модель</th>
                                            <th class="px-3.5 py-3 font-medium">Роки</th>
                                            <th class="px-3.5 py-3 font-medium">Двигун</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($compat as $r)
                                            <tr class="border-t border-[var(--gazu-line)]">
                                                <td class="px-3.5 py-3 gazu-display font-semibold text-[var(--gazu-ink)]">
                                                    <span class="inline-flex items-center gap-2">
                                                        <span class="w-6 h-6 rounded overflow-hidden inline-flex items-center justify-center shrink-0 {{ ($r[4] ?? null) ? '' : 'bg-[var(--gazu-mist)] text-[9px] gazu-mono text-[var(--gazu-blue)]' }}">
                                                            @if($r[4] ?? null)<img src="{{ $r[4] }}" alt="{{ $r[0] }}" class="w-full h-full object-cover" loading="lazy">@else{{ mb_substr($r[0], 0, 2) }}@endif
                                                        </span>
                                                        <span>{{ $r[0] }}</span>
                                                    </span>
                                                </td>
                                                <td class="px-3.5 py-3 text-[var(--gazu-ink)]">{{ $r[1] }}</td>
                                                <td class="px-3.5 py-3 text-[var(--gazu-graphite)] gazu-mono text-xs">{{ $r[2] }}</td>
                                                <td class="px-3.5 py-3 text-[var(--gazu-graphite)] gazu-mono text-xs">{{ $r[3] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="bg-white border border-[var(--gazu-line)] rounded-lg p-6 text-center">
                                <p class="text-[13px] text-[var(--gazu-graphite)]">Список сумісних авто для цієї деталі поки не заповнено. Зв'яжіться з менеджером для уточнення.</p>
                            </div>
                        @endif
                    </div>

                    {{-- analogs --}}
                    <div role="tabpanel" x-show="tab === 'analogs'" x-cloak class="mt-6">
                        <div class="gazu-display text-lg font-semibold mb-3">Аналоги</div>
                        @if($analogList->isNotEmpty())
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                @foreach($analogList as $r)
                                    <x-gazu.product-card :p="$r"/>
                                @endforeach
                            </div>
                        @else
                            <p class="text-[13px] text-[var(--gazu-graphite)]">Поки що немає підібраних аналогів для цього товару.</p>
                        @endif
                    </div>

                    {{-- reviews --}}
                    <div role="tabpanel" x-show="tab === 'reviews'" x-cloak class="mt-6">
                        <div class="flex items-center justify-between mb-3 gap-3 flex-wrap">
                            <div class="gazu-display text-lg font-semibold">Відгуки покупців</div>
                            @auth
                                <a href="#review-form"
                                   @click.prevent="document.getElementById('review-form')?.scrollIntoView({ behavior: 'smooth', block: 'center' })"
                                   class="text-[13px] font-medium text-[var(--gazu-ink)] border border-[var(--gazu-ink)] rounded-md px-3 py-1.5 hover:bg-[var(--gazu-mist)] transition-colors no-underline inline-block">
                                    Залишити відгук
                                </a>
                            @else
                                <a wire:navigate href="{{ route('gazu.auth') }}"
                                   class="text-[13px] font-medium text-[var(--gazu-ink)] border border-[var(--gazu-ink)] rounded-md px-3 py-1.5 hover:bg-[var(--gazu-mist)] transition-colors no-underline inline-block">
                                    Увійти, щоб залишити відгук
                                </a>
                            @endauth
                        </div>
                        @if(is_object($p) && method_exists($p, 'approvedReviews') && ($reviewList = $p->approvedReviews()->latest()->take(3)->get())->isNotEmpty())
                            <div class="flex flex-col gap-3">
                                @foreach($reviewList as $rev)
                                    <article class="bg-white border border-[var(--gazu-line)] rounded-lg p-4">
                                        <header class="flex items-center justify-between gap-3 mb-2">
                                            <div class="flex items-center gap-2">
                                                <span class="font-semibold text-[var(--gazu-ink)] text-[14px]">{{ $rev->author_name ?? $rev->user?->name ?? 'Анонім' }}</span>
                                                <span class="text-[11px] text-[var(--gazu-muted)] gazu-mono">{{ optional($rev->created_at)->format('d.m.Y') }}</span>
                                            </div>
                                            <div class="flex gap-px text-[var(--gazu-warn)]">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <x-gazu.icon name="star" size="12" fill="{{ $i <= (int) ($rev->rating ?? 0) ? 'var(--gazu-warn)' : 'none' }}" stroke="var(--gazu-warn)"/>
                                                @endfor
                                            </div>
                                        </header>
                                        @if(!empty($rev->title))
                                            <div class="text-[14px] font-semibold text-[var(--gazu-ink)] mb-1">{{ $rev->title }}</div>
                                        @endif
                                        <p class="text-[13px] text-[var(--gazu-graphite)] leading-relaxed m-0">{{ $rev->body ?? $rev->comment ?? '' }}</p>
                                    </article>
                                @endforeach
                            </div>
                        @else
                            <p class="text-[13px] text-[var(--gazu-graphite)]">Будьте першим, хто залишить відгук на цей товар.</p>
                        @endif
                    </div>

                    {{-- delivery --}}
                    <div role="tabpanel" x-show="tab === 'delivery'" x-cloak class="mt-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-white border border-[var(--gazu-line)] rounded-lg p-4 flex gap-3 items-start">
                                <x-gazu.icon name="truck" size="22" stroke="var(--gazu-blue)" class="shrink-0"/>
                                <div>
                                    <div class="gazu-display font-semibold text-[var(--gazu-ink)] mb-1">Доставка</div>
                                    <div class="text-[13px] text-[var(--gazu-graphite)] leading-relaxed">{{ $deliveryText }}</div>
                                </div>
                            </div>
                            <div class="bg-white border border-[var(--gazu-line)] rounded-lg p-4 flex gap-3 items-start">
                                <x-gazu.icon name="shield" size="22" stroke="var(--gazu-blue)" class="shrink-0"/>
                                <div>
                                    <div class="gazu-display font-semibold text-[var(--gazu-ink)] mb-1">Оплата</div>
                                    <div class="text-[13px] text-[var(--gazu-graphite)] leading-relaxed">{{ $paymentText }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>{{-- /tabs --}}

        <x-gazu.featured-row title="Часто купують разом" :items="$related" bare/>
    </div>

    {{-- Mobile sticky add-to-cart bar — shows only when buy-panel scrolled off-screen --}}
    @php
        $productId = is_object($p) ? ($p->id ?? null) : null;
        $stocks = ($warehouseStocks ?? collect());
        $defaultStock = $closestWarehouseId
            ? $stocks->first(fn ($s) => $s->warehouse_id === $closestWarehouseId && $s->quantity > 0)
            : null;
        $defaultStock ??= $stocks->firstWhere(fn ($s) => $s->quantity > 0);
        $defaultWh = $defaultStock?->warehouse_id;
        $defaultPrice = $defaultStock && $defaultStock->price !== null ? (float) $defaultStock->price : (float) $price;
    @endphp
    @if($productId)
        <div x-data="{
                show: false,
                init() {
                    const anchor = document.getElementById('buy-panel-anchor');
                    if (!anchor || !('IntersectionObserver' in window)) return;
                    const io = new IntersectionObserver(
                        ([entry]) => { this.show = !entry.isIntersecting; },
                        { rootMargin: '-80px 0px 0px 0px', threshold: 0 }
                    );
                    io.observe(anchor);
                }
             }"
             x-show="show" x-cloak x-transition.opacity.duration.200ms
             class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-[var(--gazu-line)] shadow-[0_-4px_12px_-2px_rgba(0,0,0,0.08)] px-4 py-3"
             role="region" aria-label="Швидкий кошик">
            <form action="{{ route('gazu.cart.add') }}" method="POST" class="flex items-center gap-3">
                @csrf
                <input type="hidden" name="product_id" value="{{ $productId }}">
                <input type="hidden" name="quantity" value="1">
                <input type="hidden" name="warehouse_id" value="{{ $defaultWh }}">
                <div class="flex-1 min-w-0">
                    <div class="text-[11px] text-[var(--gazu-graphite)] truncate">{{ is_object($p) ? ($p->name ?? '') : '' }}</div>
                    <div class="gazu-display font-bold text-[var(--gazu-ink)] gazu-mono">
                        {{ number_format($defaultPrice, 0, '.', ' ') }} ₴
                    </div>
                </div>
                <button type="submit"
                    class="h-12 px-5 bg-[var(--gazu-ink)] text-white border-0 rounded-lg text-[14px] font-semibold cursor-pointer inline-flex items-center justify-center gap-2 hover:bg-[var(--gazu-ink-2)] whitespace-nowrap"
                    aria-label="Додати в кошик за {{ number_format($defaultPrice, 0, '.', ' ') }} грн">
                    <x-gazu.icon name="cart" size="18"/>
                    <span>У кошик</span>
                </button>
            </form>
        </div>
    @endif

    {{-- Track product visit + recently viewed block --}}
    @php $currentPid = is_object($p) ? (int) ($p->id ?? 0) : 0; @endphp
    @if($currentPid)
        <script>
            // Trigger через wire:navigate (livewire:navigated) + initial DOMContentLoaded
            (function () {
                var t = function () { if (window.gazuTrackProduct) window.gazuTrackProduct({{ $currentPid }}); };
                document.addEventListener('DOMContentLoaded', t, { once: true });
                document.addEventListener('livewire:navigated', t);
                t(); // immediate if script late
            })();
        </script>
        <x-gazu.recently-viewed :exclude-id="$currentPid"/>
    @endif
@endsection
