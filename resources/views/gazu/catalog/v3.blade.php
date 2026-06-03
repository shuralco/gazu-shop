@extends('gazu.layout')

@section('title', 'Каталог · Rich list — GAZU')

@section('content')
    <div class="gazu-container">
        <x-gazu.breadcrumbs :items="[
            ['Головна', route('gazu.home')],
            ['Каталог', route('gazu.catalog')],
            'Двигун',
            'Фільтри',
            'Масляні фільтри',
        ]"/>

        <h1 class="gazu-display text-4xl font-semibold text-[var(--gazu-ink)] m-0">Масляні фільтри</h1>
        <div class="text-sm text-[var(--gazu-graphite)] mb-4.5 mt-1">Розширений вигляд зі специфікаціями та сумісністю.</div>
        @include('gazu.partials.active-filters')

        <div class="gazu-grid-sidebar">
            <x-gazu.filter-panel
                :priceRange="$priceRange"
                :availableBrands="$availableBrands"
                :selectedBrands="$selectedBrands"
                :availableConditions="$availableConditions ?? null"
                :selectedConditions="$selectedConditions ?? []"
                :inStockOnly="$inStockOnly"
                :searchQuery="$searchQuery"
                :category="$category"/>
            <div class="min-w-0">
                @include('gazu.partials.sort-bar', ['count' => $totalCount, 'view' => 'list', 'currentSort' => $currentSort])
                <div class="flex flex-col gap-3 mt-4">
                    @foreach($products as $p)
                        @php
                            $name = is_object($p) ? ($p->name ?? '') : ($p['name'] ?? '');
                            $oem = is_object($p) ? ($p->oem ?? '') : ($p['oem'] ?? '');
                            $brand = is_object($p) ? ($p->brand ?? '') : ($p['brand'] ?? '');
                            $kind = is_object($p) ? ($p->image_kind ?? 'filter') : ($p['image_kind'] ?? 'filter');
                            $price = is_object($p) ? (float)($p->price ?? 0) : (float)($p['price'] ?? 0);
                            $oldPrice = is_object($p) ? ($p->old_price ?? null) : ($p['old_price'] ?? null);
                            $condition = is_object($p) ? ($p->condition ?? 'Новий') : ($p['condition'] ?? 'Новий');
                            $qty = is_object($p) ? (int)($p->qty ?? 0) : (int)($p['qty'] ?? 0);
                            $rating = is_object($p) ? (float)($p->rating ?? 0) : (float)($p['rating'] ?? 0);
                            $reviews = is_object($p) ? (int)($p->reviews ?? 0) : (int)($p['reviews'] ?? 0);
                            $fits = is_object($p) ? ($p->fits ?? '') : ($p['fits'] ?? '');
                            $url = is_object($p) ? ($p->url ?? '#') : ($p['url'] ?? '#');
                            $warranty = $gazuSettings['gazu_default_warranty'] ?? '12 місяців';
                            $analogsArr = is_object($p) ? ($p->analogs ?? null) : ($p['analogs'] ?? null);
                            $analogsCount = is_array($analogsArr) ? count($analogsArr) : 0;
                        @endphp
                        <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-4 gazu-grid-list font-text">
                            <a wire:navigate href="{{ $url }}" class="bg-[var(--gazu-paper)] rounded-md flex items-center justify-center" style="aspect-ratio:1;">
                                <x-gazu.part-image kind="{{ $kind }}" size="140"/>
                            </a>
                            <div class="flex flex-col gap-2 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <x-gazu.condition-badge value="{{ $condition }}"/>
                                    <span class="gazu-display font-semibold text-sm text-[var(--gazu-ink)]">{{ $brand }}</span>
                                    <span class="flex-1"></span>
                                    <div class="flex items-center gap-1 whitespace-nowrap">
                                        <div class="flex gap-px text-[var(--gazu-warn)]">
                                            @for($i = 1; $i <= 5; $i++)
                                                <x-gazu.icon name="star" size="12" fill="{{ $i <= floor($rating) ? 'var(--gazu-warn)' : 'none' }}" stroke="var(--gazu-warn)"/>
                                            @endfor
                                        </div>
                                        <span class="text-[11px] text-[var(--gazu-graphite)]">{{ number_format($rating, 1) }} ({{ $reviews }})</span>
                                    </div>
                                </div>
                                <a wire:navigate href="{{ $url }}" class="gazu-display text-[17px] font-semibold text-[var(--gazu-ink)] no-underline">{{ $name }}</a>
                                <div class="flex gap-3.5 text-xs text-[var(--gazu-graphite)] flex-wrap">
                                    <span class="whitespace-nowrap"><span class="text-[var(--gazu-muted)]">Артикул:</span> <span class="gazu-mono text-[var(--gazu-ink)]">{{ $oem }}</span></span>
                                    <span class="whitespace-nowrap"><span class="text-[var(--gazu-muted)]">Гарантія:</span> {{ $warranty }}</span>
                                </div>
                                @if($fits)
                                    <div class="text-xs text-[var(--gazu-graphite)] px-2.5 py-2 bg-[var(--gazu-mist)] rounded flex gap-2">
                                        <x-gazu.icon name="check" size="14" stroke="var(--gazu-blue)" class="shrink-0 mt-0.5"/>
                                        <span><span class="text-[var(--gazu-ink)] font-medium">Сумісність:</span> {{ $fits }}</span>
                                    </div>
                                @endif
                                <div class="flex gap-2.5 mt-1">
                                    <button type="button" class="bg-transparent border-0 p-0 text-[var(--gazu-blue)] text-xs cursor-pointer">Технічні характеристики</button>
                                    <span class="text-[var(--gazu-line-2)]">·</span>
                                    <button type="button" class="bg-transparent border-0 p-0 text-[var(--gazu-blue)] text-xs cursor-pointer">Аналоги{{ $analogsCount ? ' ('.$analogsCount.')' : '' }}</button>
                                    <span class="text-[var(--gazu-line-2)]">·</span>
                                    <button type="button" class="bg-transparent border-0 p-0 text-[var(--gazu-blue)] text-xs cursor-pointer">Інструкція</button>
                                </div>
                            </div>
                            <div class="flex flex-col gap-2.5 justify-between border-l border-[var(--gazu-line)] pl-5">
                                <div>
                                    @if($oldPrice)<div class="text-xs text-[var(--gazu-muted)] line-through">{{ number_format((float)$oldPrice, 0, '.', ' ') }} ₴</div>@endif
                                    <div class="gazu-display text-[28px] font-bold text-[var(--gazu-ink)] leading-none">{{ number_format($price, 0, '.', ' ') }} ₴</div>
                                    <div class="mt-2"><x-gazu.stock qty="{{ $qty }}"/></div>
                                    <div class="text-xs text-[var(--gazu-graphite)] mt-1 inline-flex gap-1 items-center">
                                        <x-gazu.icon name="truck" size="14"/> Доставка завтра
                                    </div>
                                </div>
                                <div class="flex gap-1.5">
                                    <button type="button" class="flex-1 py-3 bg-[var(--gazu-ink)] text-[var(--gazu-on-brand)] border-0 rounded-md text-[13px] font-medium cursor-pointer inline-flex items-center justify-center gap-1.5">
                                        <x-gazu.icon name="cart" size="14"/> У кошик
                                    </button>
                                    <button type="button" class="w-10 bg-[var(--gazu-surface)] text-[var(--gazu-graphite)] border border-[var(--gazu-line)] rounded-md cursor-pointer flex items-center justify-center">
                                        <x-gazu.icon name="heart" size="16"/>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <x-gazu.pagination :paginator="$paginator ?? null" :current="1" :total="12"/>
            </div>
        </div>
    </div>
@endsection
