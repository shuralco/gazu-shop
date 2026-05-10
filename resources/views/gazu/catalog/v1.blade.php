@extends('gazu.layout')

@php
    $title = $category->title ?? ($searchQuery ? 'Пошук: '.$searchQuery : 'Каталог');
    $crumbs = [['Головна', route('gazu.home')]];
    if ($category) {
        $crumbs[] = ['Каталог', route('gazu.catalog')];
        $crumbs[] = (string) ($category->title ?? 'Категорія');
    } else {
        $crumbs[] = 'Каталог';
    }
@endphp

@section('title', $title . ' — GAZU')

@section('content')
    <div class="gazu-container">
        <x-gazu.breadcrumbs :items="$crumbs"/>

        <div class="flex items-end justify-between mb-5 flex-wrap gap-2">
            <div>
                <h1 class="gazu-display text-4xl font-semibold text-[var(--gazu-ink)] m-0">{{ $title }}</h1>
                @if($category && $category->description ?? false)
                    <p class="text-sm text-[var(--gazu-graphite)] mt-1.5 max-w-xl">{{ $category->description }}</p>
                @elseif($searchQuery)
                    <p class="text-sm text-[var(--gazu-graphite)] mt-1.5">Знайдено {{ plural_uk_count($totalCount, 'товар', 'товари', 'товарів') }}</p>
                @endif
            </div>
        </div>

        @include('gazu.partials.active-filters', ['category' => $category])

        <div class="gazu-grid-sidebar mt-3">
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
                @include('gazu.partials.sort-bar', ['count' => $totalCount, 'view' => 'grid', 'currentSort' => $currentSort])

                @if($products->isEmpty())
                    <div class="bg-white border border-[var(--gazu-line)] rounded-lg p-10 text-center mt-4">
                        <div class="gazu-display text-2xl font-semibold mb-2">Нічого не знайдено</div>
                        <p class="text-sm text-[var(--gazu-graphite)] mb-4">Спробуйте змінити фільтри або скинути всі.</p>
                        <a wire:navigate href="{{ route('gazu.catalog') }}" class="gazu-btn-outline no-underline">Скинути фільтри</a>
                    </div>
                @else
                    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3.5 mt-4 gazu-stagger">
                        @foreach($products as $p)
                            <x-gazu.product-card :p="$p" :compact="true"/>
                        @endforeach
                    </div>
                    <x-gazu.pagination :paginator="$paginator"/>
                @endif
            </div>
        </div>
    </div>
@endsection
