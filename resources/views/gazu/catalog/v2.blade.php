@extends('gazu.layout')

@section('title', 'Каталог · B2B — GAZU')

@section('content')
    <div class="gazu-container">
        <x-gazu.breadcrumbs :items="[
            ['Головна', route('gazu.home')],
            ['Каталог', route('gazu.catalog')],
            'Двигун',
            'Фільтри',
            'Масляні фільтри',
        ]"/>

        <h1 class="gazu-display text-3xl font-semibold text-[var(--gazu-ink)] m-0">Масляні фільтри</h1>
        <div class="text-[13px] text-[var(--gazu-graphite)] mb-4.5 mt-1">{{ $products->count() * 24 }} артикулів · режим B2B-таблиці</div>
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
                <div class="mt-4 bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg overflow-hidden overflow-x-auto">
                    <table class="w-full text-left" style="border-collapse: collapse;">
                        <thead class="bg-[var(--gazu-bone)] gazu-mono text-[11px] text-[var(--gazu-graphite)] tracking-wider uppercase">
                            <tr class="border-b border-[var(--gazu-line)]">
                                <th class="py-2.5 px-2 font-medium"></th>
                                <th class="py-2.5 px-2 font-medium">Назва · Артикул</th>
                                <th class="py-2.5 px-2 font-medium">Бренд</th>
                                <th class="py-2.5 px-2 font-medium">Стан</th>
                                <th class="py-2.5 px-2 font-medium">Сумісність</th>
                                <th class="py-2.5 px-2 font-medium">Наявн.</th>
                                <th class="py-2.5 px-2 font-medium text-right">Ціна</th>
                                <th class="py-2.5 px-2"></th>
                            </tr>
                        </thead>
                        <tbody class="text-[13px]">
                            @foreach($products as $p)
                                @php
                                    $brand = is_object($p) ? ($p->brand ?? '') : ($p['brand'] ?? '');
                                    $oem = is_object($p) ? ($p->oem ?? '') : ($p['oem'] ?? '');
                                    $name = is_object($p) ? ($p->name ?? '') : ($p['name'] ?? '');
                                    $kind = is_object($p) ? ($p->image_kind ?? 'filter') : ($p['image_kind'] ?? 'filter');
                                    $price = is_object($p) ? (float)($p->price ?? 0) : (float)($p['price'] ?? 0);
                                    $oldPrice = is_object($p) ? ($p->old_price ?? null) : ($p['old_price'] ?? null);
                                    $condition = is_object($p) ? ($p->condition ?? 'Новий') : ($p['condition'] ?? 'Новий');
                                    $qty = is_object($p) ? (int)($p->qty ?? 0) : (int)($p['qty'] ?? 0);
                                    $fits = is_object($p) ? ($p->fits ?? '') : ($p['fits'] ?? '');
                                @endphp
                                <tr class="border-b border-[var(--gazu-line)]">
                                    <td class="py-2.5 px-2" style="width: 56px;">
                                        <div class="w-11 h-11 bg-[var(--gazu-paper)] rounded flex items-center justify-center">
                                            <x-gazu.part-image kind="{{ $kind }}" size="38"/>
                                        </div>
                                    </td>
                                    <td class="py-2.5 px-2">
                                        <div class="text-[var(--gazu-ink)] font-medium mb-0.5">{{ $name }}</div>
                                        <div class="text-[11px] text-[var(--gazu-graphite)] gazu-mono">{{ $oem }}</div>
                                    </td>
                                    <td class="py-2.5 px-2 gazu-display font-semibold text-[var(--gazu-ink)] text-xs">{{ $brand }}</td>
                                    <td class="py-2.5 px-2"><x-gazu.condition-badge value="{{ $condition }}"/></td>
                                    <td class="py-2.5 px-2 text-[var(--gazu-graphite)] text-xs" style="max-width: 160px;">{{ $fits }}</td>
                                    <td class="py-2.5 px-2 whitespace-nowrap"><x-gazu.stock qty="{{ $qty }}"/></td>
                                    <td class="py-2.5 px-2 text-right whitespace-nowrap">
                                        <div class="gazu-display font-bold text-[15px] text-[var(--gazu-ink)]">{{ number_format($price, 0, '.', ' ') }} ₴</div>
                                        @if($oldPrice)<div class="text-[11px] text-[var(--gazu-muted)] line-through">{{ number_format((float)$oldPrice, 0, '.', ' ') }} ₴</div>@endif
                                    </td>
                                    <td class="py-2.5 px-2" style="width: 92px;">
                                        <button type="button" class="px-3 py-2 bg-[var(--gazu-ink)] text-[var(--gazu-on-brand)] border-0 rounded text-xs font-medium cursor-pointer inline-flex items-center gap-1.5 whitespace-nowrap">
                                            <x-gazu.icon name="cart" size="14"/> Купити
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <x-gazu.pagination :paginator="$paginator ?? null" :current="1" :total="12"/>
            </div>
        </div>
    </div>
@endsection
