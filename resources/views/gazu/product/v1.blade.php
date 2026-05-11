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

    // Specifications: з БД (Product->specifications: associative array). Fallback — демо.
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

    // Compatibility: array of [make, model, years, engine] objects from БД
    $rawCompat = is_object($p) ? ($p->compatibility ?? null) : ($p['compatibility'] ?? null);
    if (is_array($rawCompat) && ! empty($rawCompat)) {
        $compat = [];
        foreach ($rawCompat as $row) {
            if (is_array($row)) {
                $compat[] = [$row['make'] ?? '—', $row['model'] ?? '—', $row['years'] ?? '—', $row['engine'] ?? '—'];
            }
        }
    } else {
        $compat = [
            ['Volkswagen', 'Passat (B7, B8)', '2010–2024', '1.6 TDI · 2.0 TDI'],
            ['Volkswagen', 'Golf (VI, VII)', '2008–2020', '1.6 TDI · 2.0 TDI'],
            ['Audi', 'A4 (B8, B9)', '2007–2024', '2.0 TDI'],
        ];
    }
@endphp

@php
    $jsonldProduct = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $name,
        'sku' => $oem,
        'brand' => ['@type' => 'Brand', 'name' => $brand ?: 'GAZU'],
        'description' => $fits ?: $name,
        'offers' => [
            '@type' => 'Offer',
            'price' => number_format($price, 2, '.', ''),
            'priceCurrency' => 'UAH',
            'availability' => $qty > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            'url' => url()->current(),
        ],
    ];
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
        <x-gazu.breadcrumbs :items="[
            ['Головна', route('gazu.home')],
            ['Каталог', route('gazu.catalog')],
            'Двигун',
            'Фільтри',
            $brand . ' ' . $oem,
        ]"/>

        <div class="gazu-grid-buy">
            <div>
                <div class="flex items-center gap-2.5 mb-2 flex-wrap">
                    <x-gazu.condition-badge value="Новий"/>
                    <span class="gazu-display font-semibold text-[var(--gazu-ink)] text-sm">{{ $brand }}</span>
                    <span class="text-[11px] text-[var(--gazu-line-2)]">·</span>
                    <div class="flex items-center gap-1 whitespace-nowrap">
                        <div class="flex gap-px text-[var(--gazu-warn)]">
                            @for($i = 1; $i <= 5; $i++)
                                <x-gazu.icon name="star" size="12" fill="{{ $i <= floor($rating) ? 'var(--gazu-warn)' : 'none' }}" stroke="var(--gazu-warn)"/>
                            @endfor
                        </div>
                        <span class="text-xs text-[var(--gazu-graphite)]">{{ number_format($rating, 1) }} · {{ $reviews }} відгуки · 312 продано</span>
                    </div>
                </div>
                <h1 class="gazu-display text-[32px] font-semibold text-[var(--gazu-ink)] m-0 mb-2 leading-tight">{{ $name }}</h1>
                <div class="flex gap-4.5 text-[13px] text-[var(--gazu-graphite)] gazu-mono mb-7 flex-wrap">
                    <span class="whitespace-nowrap">Артикул: <span class="text-[var(--gazu-ink)]">{{ $oem ?: 'F 026 407 023' }}</span></span>
                    <span class="whitespace-nowrap">OEM: <span class="text-[var(--gazu-ink)]">06A 115 561 B</span></span>
                </div>

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

                @include('gazu.partials.product-tabs', ['active' => 'compat'])

                <div class="mt-6">
                    <div class="gazu-display text-lg font-semibold mb-3">Сумісність з автомобілями</div>
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
                    <button type="button" class="mt-3 bg-transparent border-0 text-[var(--gazu-blue)] text-[13px] cursor-pointer">Показати ще 24 моделі →</button>
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
