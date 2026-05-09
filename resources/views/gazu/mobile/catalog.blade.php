@extends('gazu.layout')
@section('title', 'Каталог · mobile')

@php
    $pills = $gazuSettings['gazu_mobile_filter_pills'] ?? ['Усі', 'Bosch', 'Mahle', 'Mann', 'TRW', 'KYB'];
    $selectedBrand = request('brand') ?? null;
    if (is_array($selectedBrand)) $selectedBrand = $selectedBrand[0] ?? null;
@endphp
@section('content')
<div class="max-w-[420px] mx-auto py-4 px-4 pb-20">
    <h1 class="gazu-display text-xl font-semibold mb-2">{{ $category->title ?? 'Каталог' }}</h1>
    <div class="text-xs text-[var(--gazu-graphite)] mb-3">{{ plural_uk_count((int) ($totalCount ?? $products->count()), 'товар', 'товари', 'товарів') }}</div>
    <div class="flex gap-2 mb-3 overflow-x-auto whitespace-nowrap">
        @foreach((array) $pills as $i => $pill)
            @php
                $isAll = $i === 0;
                $isActive = $isAll ? ! $selectedBrand : $selectedBrand === $pill;
                $url = $isAll
                    ? request()->fullUrlWithQuery(['brand' => null])
                    : request()->fullUrlWithQuery(['brand' => [$pill]]);
            @endphp
            <a href="{{ $url }}" class="px-3 py-1.5 rounded-full text-xs whitespace-nowrap no-underline {{ $isActive ? 'bg-[var(--gazu-ink)] text-white' : 'bg-white border border-[var(--gazu-line)] text-[var(--gazu-graphite)]' }}">
                {{ $pill }}
            </a>
        @endforeach
    </div>
    <div class="flex justify-between items-center mb-3">
        <button type="button" class="gazu-btn-outline text-xs py-1.5 px-3"><x-gazu.icon name="filter" size="14"/> Фільтри</button>
        <select class="text-xs border border-[var(--gazu-line)] bg-white rounded px-2 py-1.5">
            <option>За популярністю</option>
            <option>За ціною</option>
        </select>
    </div>
    <div class="grid grid-cols-2 gap-2.5">
        @foreach($products as $p)
            <x-gazu.product-card :p="$p" :compact="true"/>
        @endforeach
    </div>
</div>
@include('gazu.partials.mobile-nav', ['active' => 'catalog'])
@endsection
