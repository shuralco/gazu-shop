@extends('gazu.layout')

@php
    $title = $category->title ?? ($searchQuery ? 'Пошук: '.$searchQuery : 'Каталог');
    $crumbs = [['Головна', route('gazu.home')]];
    if ($category) {
        $crumbs[] = ['Каталог', route('gazu.catalog')];
        // Ancestor chain: показуємо повний drill-down до поточної категорії.
        foreach (($ancestors ?? collect()) as $anc) {
            $crumbs[] = [(string) ($anc->title ?? '—'), url('/'.$anc->slug)];
        }
        $crumbs[] = (string) ($category->title ?? 'Категорія');
    } else {
        $crumbs[] = 'Каталог';
    }
@endphp

@section('title', $title . ' — GAZU')
@section('description', $category && $category->meta_description
    ? $category->meta_description
    : ($category
        ? 'Купити '.$category->title.' для китайських авто (BYD, Chery, Geely, Haval). У наявності '.\plural_uk_count($totalCount, 'товар', 'товари', 'товарів').'. Доставка Новою Поштою, гарантія.'
        : 'Каталог автозапчастин · '.\plural_uk_count($totalCount ?? 0, 'товар', 'товари', 'товарів').' · доставка по Україні'))

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

        {{-- Subcategories drilldown — клікабельні плитки L2/L3 під поточною категорією --}}
        @if(! empty($subcategories) && $subcategories->isNotEmpty())
            <div class="bg-white border border-[var(--gazu-line)] rounded-lg p-4 mb-5">
                <div class="gazu-mono text-[10px] text-[var(--gazu-muted)] tracking-widest uppercase mb-3">Підкатегорії</div>
                <div class="grid gap-2" style="grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));">
                    @foreach($subcategories as $sub)
                        <a wire:navigate href="{{ url('/'.$sub->slug) }}"
                           class="flex items-center justify-between gap-2 px-3 py-2.5 bg-[var(--gazu-paper)] hover:bg-[var(--gazu-mist)] border border-[var(--gazu-line)] rounded-md no-underline text-[var(--gazu-ink)] transition-colors">
                            <span class="text-[13px] font-medium truncate">{{ $sub->title }}</span>
                            <span class="gazu-mono text-[10px] text-[var(--gazu-muted)] whitespace-nowrap">{{ $sub->products_count ?? 0 }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

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
                @php $currentView = request('view') === 'list' ? 'list' : 'grid'; @endphp
                @include('gazu.partials.sort-bar', ['count' => $totalCount, 'view' => $currentView, 'currentSort' => $currentSort])

                @if($products->isEmpty())
                    <div class="bg-white border border-[var(--gazu-line)] rounded-lg p-10 text-center mt-4">
                        <div class="gazu-display text-2xl font-semibold mb-2">Нічого не знайдено</div>
                        <p class="text-sm text-[var(--gazu-graphite)] mb-4">Спробуйте змінити фільтри або скинути всі.</p>
                        <a wire:navigate href="{{ route('gazu.catalog') }}" class="gazu-btn-outline no-underline">Скинути фільтри</a>
                    </div>
                @elseif($currentView === 'list')
                    <div class="flex flex-col gap-2 mt-4">
                        @foreach($products as $p)
                            <x-gazu.product-row :p="$p"/>
                        @endforeach
                    </div>
                    <x-gazu.pagination :paginator="$paginator"/>
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
