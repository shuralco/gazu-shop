@extends('gazu.layout')

@section('title', ($p->name ?? 'Товар') . ' — GAZU')
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
            ['Гарантія', '12 місяців', false],
        ];
    }

    // Compatibility: array of [make, model, years, engine] objects from БД.
    // No fallback demo data — empty array means tab shows "not available" hint.
    $rawCompat = is_object($p) ? ($p->compatibility ?? null) : ($p['compatibility'] ?? null);
    $compat = [];
    if (is_array($rawCompat) && ! empty($rawCompat)) {
        foreach ($rawCompat as $row) {
            if (is_array($row)) {
                $compat[] = [$row['make'] ?? '—', $row['model'] ?? '—', $row['years'] ?? '—', $row['engine'] ?? '—'];
            }
        }
    }
@endphp

@php
    $jsonldProduct = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $name,
        'sku' => $oem,
        'description' => $fits ?: $name,
        'offers' => [
            '@type' => 'Offer',
            'price' => number_format($price, 2, '.', ''),
            'priceCurrency' => 'UAH',
            'availability' => $qty > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            'url' => url()->current(),
        ],
    ];
    if (! empty($brand)) {
        $jsonldProduct['brand'] = ['@type' => 'Brand', 'name' => $brand];
    }
    if ($rating > 0 && $reviews > 0) {
        $jsonldProduct['aggregateRating'] = [
            '@type' => 'AggregateRating',
            'ratingValue' => (string) $rating,
            'reviewCount' => $reviews,
        ];
    }
@endphp

@section('jsonld')
<script type="application/ld+json">{!! json_encode($jsonldProduct, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endsection

@section('content')
    <div class="gazu-container">
        @include('gazu.partials.product-breadcrumbs', compact('p', 'brand', 'oem', 'name'))

        <div class="gazu-grid-buy">
            <div>
                @php
                    // Brand badge link → catalog filter (same logic as spec rows below).
                    $brandHeaderSlug = null;
                    if (is_object($p) && $p->relationLoaded('brand') && ($b = $p->getRelation('brand'))) {
                        $brandHeaderSlug = $b->slug ?: \Illuminate\Support\Str::slug((string) $b->getRawOriginal('name'));
                    }
                    if (! $brandHeaderSlug && is_object($p) && $p->manufacturer) {
                        $brandHeaderSlug = \Illuminate\Support\Str::slug((string) $p->manufacturer);
                    }
                @endphp
                <div class="flex items-center gap-2.5 mb-2 flex-wrap">
                    <x-gazu.condition-badge value="Новий"/>
                    @if($brandHeaderSlug)
                        <a wire:navigate href="{{ route('gazu.catalog', ['brand' => [$brandHeaderSlug]]) }}"
                           class="gazu-display font-semibold text-[var(--gazu-ink)] text-sm no-underline hover:text-[var(--gazu-blue)] transition-colors">{{ $brand }}</a>
                    @else
                        <span class="gazu-display font-semibold text-[var(--gazu-ink)] text-sm">{{ $brand }}</span>
                    @endif
                    @php
                        $soldCount = is_object($p) ? (int) ($p->sold_count ?? 0) : 0;
                    @endphp
                    @if($rating > 0 || $reviews > 0 || $soldCount > 0)
                        <span class="text-[11px] text-[var(--gazu-line-2)]">·</span>
                        <div class="flex items-center gap-1 whitespace-nowrap">
                            @if($rating > 0)
                                <div class="flex gap-px text-[var(--gazu-warn)]">
                                    @for($i = 1; $i <= 5; $i++)
                                        <x-gazu.icon name="star" size="12" fill="{{ $i <= floor($rating) ? 'var(--gazu-warn)' : 'none' }}" stroke="var(--gazu-warn)"/>
                                    @endfor
                                </div>
                            @endif
                            <span class="text-xs text-[var(--gazu-graphite)]">
                                @if($rating > 0){{ number_format($rating, 1) }}@endif
                                @if($reviews > 0) · {{ $reviews }} {{ \plural_uk_count($reviews, 'відгук', 'відгуки', 'відгуків') }}@endif
                                @if($soldCount > 0) · {{ $soldCount }} продано @endif
                            </span>
                        </div>
                    @endif
                </div>
                <h1 class="gazu-display text-[32px] font-semibold text-[var(--gazu-ink)] m-0 mb-2 leading-tight">{{ $name }}</h1>
                @php
                    $oemReal = $oem ?: (is_object($p) ? ($p->sku ?? '') : '');
                    $barcode = is_object($p) ? ($p->barcode ?? null) : null;
                @endphp
                @if($oemReal || $barcode)
                    <div class="flex gap-4.5 text-[13px] text-[var(--gazu-graphite)] gazu-mono mb-7 flex-wrap">
                        @if($oemReal)
                            <span class="whitespace-nowrap">Артикул: <span class="text-[var(--gazu-ink)]">{{ $oemReal }}</span></span>
                        @endif
                        @if($barcode && $barcode !== $oemReal)
                            <span class="whitespace-nowrap">Артикул: <span class="text-[var(--gazu-ink)]">{{ $barcode }}</span></span>
                        @endif
                    </div>
                @endif

                <div class="gazu-grid-product-tabs">
                    {{-- Gallery --}}
                    <div class="grid grid-cols-[60px_1fr] gap-3">
                        <div class="flex flex-col gap-2">
                            @for($i = 0; $i < 4; $i++)
                                <div class="aspect-square bg-[var(--gazu-paper)] rounded-md flex items-center justify-center cursor-pointer" style="border: 1.5px solid {{ $i === 0 ? 'var(--gazu-ink)' : 'var(--gazu-line)' }};">
                                    <x-gazu.part-image kind="{{ $kind }}" size="42"/>
                                </div>
                            @endfor
                            <div class="aspect-square bg-[var(--gazu-paper)] rounded-md flex items-center justify-center cursor-pointer text-[var(--gazu-graphite)] text-[11px] gazu-mono" style="border: 1.5px solid var(--gazu-line);">
                                +6
                            </div>
                        </div>
                        <div class="aspect-square bg-white border border-[var(--gazu-line)] rounded-[10px] relative overflow-hidden">
                            <div class="absolute inset-0 gazu-grid-pattern"></div>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <x-gazu.part-image kind="{{ $kind }}" size="400"/>
                            </div>
                            <div class="absolute top-3.5 left-3.5 px-2.5 py-1.5 bg-white border border-[var(--gazu-line)] gazu-mono text-[11px] text-[var(--gazu-ink)] tracking-wider rounded">
                                1 / 8
                            </div>
                            <button type="button" class="absolute top-3.5 right-3.5 w-9 h-9 border border-[var(--gazu-line)] bg-white rounded-lg cursor-pointer inline-flex items-center justify-center text-[var(--gazu-graphite)]">
                                <x-gazu.icon name="heart" size="18"/>
                            </button>
                        </div>
                    </div>

                    <div>
                        <div class="gazu-display text-base font-semibold mb-3.5">Ключові характеристики</div>
                        <div>
                            @foreach(array_slice($specs, 0, 7) as [$k, $v, $mono])
                                <div class="grid grid-cols-2 py-2.5 border-b border-[var(--gazu-line)] text-[13px]">
                                    <span class="text-[var(--gazu-graphite)]">{{ $k }}</span>
                                    <span class="text-[var(--gazu-ink)] {{ $mono ? 'gazu-mono font-medium' : '' }}">{{ $v }}</span>
                                </div>
                            @endforeach
                        </div>
                        @if(module('gazu_garage')->enabled())
                            @php $primaryCar = auth()->check() ? auth()->user()->primaryCar : null; @endphp
                            @if($primaryCar)
                                <div class="mt-4.5 p-3.5 bg-[var(--gazu-success-bg)] rounded-lg flex gap-2.5">
                                    <x-gazu.icon name="check" size="18" stroke="var(--gazu-success)" class="shrink-0"/>
                                    <div class="text-[13px] text-[var(--gazu-ink)] leading-relaxed">
                                        Підходить для вашого <span class="font-semibold">{{ $primaryCar->display_name }}@if($primaryCar->engine), {{ $primaryCar->engine }}@endif</span>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

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
                    $deliveryText = $gazuSettings['gazu_product_delivery_text']
                        ?? 'Нова Пошта по Україні · Доставка наступного дня для замовлень до 16:00 · Безкоштовно від 1500 ₴.';
                    $paymentText = $gazuSettings['gazu_product_payment_text']
                        ?? 'Visa / Mastercard, Apple Pay, Google Pay, готівка при отриманні (накладений платіж), безпечна оплата через LiqPay.';
                @endphp
                <div x-data="{ tab: 'spec' }" class="mt-2">
                    <div role="tablist" aria-label="Інформація про товар"
                         class="border-b border-[var(--gazu-line)] flex gap-1 font-text mt-3 overflow-x-auto whitespace-nowrap">
                        @foreach([
                            ['spec', 'Характеристики'],
                            ['compat', 'Сумісність'],
                            ['analogs', 'Аналоги'],
                            ['reviews', 'Відгуки'],
                            ['delivery', 'Доставка та оплата'],
                        ] as [$k, $l])
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
                                        $href = route('gazu.catalog', ['brand' => [$brandSlug]]);
                                    } elseif ($k === 'Категорія' && $catSlug) {
                                        $href = route('gazu.catalog', ['cat' => $catSlug]);
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
                                                <td class="px-3.5 py-3 gazu-display font-semibold text-[var(--gazu-ink)]">{{ $r[0] }}</td>
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
                </div>
            </div>

            <div class="lg:sticky lg:top-4 lg:self-start" id="buy-panel-anchor">
                <x-gazu.buy-panel
                    :price="$price"
                    :oldPrice="$oldPrice"
                    :qty="$qty"
                    :discount="$discount"
                    :productId="is_object($p) ? ($p->id ?? null) : null"
                    :warehouseStocks="$warehouseStocks ?? collect()"
                    :closestWarehouseId="$closestWarehouseId ?? null"
                />
            </div>
        </div>

        <x-gazu.featured-row title="Часто купують разом" :items="$related"/>
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
@endsection
