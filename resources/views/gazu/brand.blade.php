@extends('gazu.layout')
@php
    $brandSeoVars = [
        'name' => $brand->name ?? 'Бренд',
        'count' => plural_uk_count($productsCount ?? 0, 'товар', 'товари', 'товарів'),
    ];
@endphp
@section('title', \App\Support\SeoTemplates::title('brand', $brandSeoVars))
@section('description', \App\Support\SeoTemplates::description('brand', $brandSeoVars))

@section('content')
<div class="gazu-container">
    <x-gazu.breadcrumbs :items="[
        ['Головна', route('gazu.home')],
        ['Бренди', route('gazu.brand')],
        $brand->name,
    ]"/>

    <section class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-xl p-5 sm:p-8 mb-7 gazu-grid-brand-hero">
        <div class="w-24 h-24 sm:w-32 sm:h-32 bg-[var(--gazu-paper)] rounded-lg flex items-center justify-center gazu-display text-2xl sm:text-3xl font-bold text-[var(--gazu-ink)]">
            @if($brand->logo)
                <img src="{{ Str::startsWith($brand->logo, 'http') ? $brand->logo : asset('storage/'.$brand->logo) }}"
                     alt="{{ $brand->name }}" class="max-w-full max-h-full object-contain">
            @else
                {{ $brand->name }}
            @endif
        </div>
        <div>
            <h1 class="gazu-display text-3xl font-semibold m-0 mb-2">{{ $brand->name }}</h1>
            @if(! empty($brand->description))
                <p class="text-sm text-[var(--gazu-graphite)] leading-relaxed m-0">{{ $brand->description }}</p>
            @else
                <p class="text-sm text-[var(--gazu-graphite)] leading-relaxed m-0">
                    {{ $brand->name }} — {{ $gazuSettings['gazu_brand_fallback_description'] ?? 'один з виробників, представлених у каталозі GAZU. Перейдіть до повного списку товарів цієї марки нижче.' }}
                </p>
            @endif
        </div>
        <div class="flex flex-col gap-2 text-center">
            <div class="gazu-display text-3xl font-bold text-[var(--gazu-ink)]">{{ number_format($productsCount, 0, '.', ' ') }}</div>
            <div class="text-xs text-[var(--gazu-graphite)]">{{ plural_uk($productsCount, 'артикул', 'артикули', 'артикулів') }} у каталозі</div>
            <a wire:navigate href="{{ route('gazu.catalog', ['brand' => [$brand->name]]) }}" class="gazu-btn-primary mt-2 no-underline">Дивитись каталог</a>
        </div>
    </section>

    @if($brandCategories->isNotEmpty())
        <h2 class="gazu-display text-2xl font-semibold m-0 mb-4">{{ $brand->name }} за категоріями</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-7">
            @foreach($brandCategories as $cat)
                @php
                    $catSlug = $cat->slug ?: $cat->id;
                    $catName = $cat->title ?? $cat->name ?? 'Категорія';
                @endphp
                <a wire:navigate href="{{ url('/'.$catSlug).'?brand[]='.urlencode($brand->name) }}"
                   class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-4 no-underline text-[var(--gazu-ink)] hover:border-[var(--gazu-line-2)]">
                    <div class="font-medium">{{ $catName }}</div>
                </a>
            @endforeach
        </div>
    @endif

    <h2 class="gazu-display text-2xl font-semibold m-0 mb-4">
        @if($products->count() > 0)
            Топ товари {{ $brand->name }}
        @else
            Каталог {{ $brand->name }} порожній
        @endif
    </h2>

    @if($products->isEmpty())
        <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-10 text-center">
            <div class="gazu-display text-xl font-semibold mb-2">Зараз немає товарів</div>
            <p class="text-sm text-[var(--gazu-graphite)] mb-4">Скоро тут зʼявляться оновлення асортименту.</p>
            <a wire:navigate href="{{ route('gazu.catalog') }}" class="gazu-btn-outline no-underline">Усі товари</a>
        </div>
    @else
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3.5">
            @foreach($products as $p)
                <x-gazu.product-card :p="$p" :compact="true"/>
            @endforeach
        </div>
        @if($productsCount > $products->count())
            <div class="text-center mt-6">
                <a wire:navigate href="{{ route('gazu.catalog', ['brand' => [$brand->name]]) }}" class="gazu-btn-outline no-underline">
                    Усі {{ number_format($productsCount, 0, '.', ' ') }} {{ plural_uk($productsCount, 'товар', 'товари', 'товарів') }} {{ $brand->name }} →
                </a>
            </div>
        @endif
    @endif
</div>
@endsection
