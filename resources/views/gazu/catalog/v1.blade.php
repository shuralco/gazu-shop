@extends('gazu.layout')

@php
    // Pretty-URL contexts (/novynky, /khity, /akcii) have правильні title
    // замість generic 'Каталог'. Перевіряємо query string що ставить роут.
    $carSeo = $carSeo ?? null;
    $contextTitle = null;
    if (request('new') == 1) { $contextTitle = 'Новинки'; }
    elseif (request('hits') == 1) { $contextTitle = 'Хіти продажу'; }
    elseif (request('promo') == 1) { $contextTitle = 'Акції та знижки'; }
    elseif ($carSeo && ! empty($carSeo['contextTitle'])) { $contextTitle = $carSeo['contextTitle']; }
    $title = $category->title ?? ($searchQuery ? 'Пошук: '.$searchQuery : ($contextTitle ?? 'Каталог'));
    $crumbs = [['Головна', route('gazu.home')]];
    if ($category) {
        $crumbs[] = ['Каталог', route('gazu.catalog')];
        // Ancestor chain: показуємо повний drill-down до поточної категорії.
        foreach (($ancestors ?? collect()) as $anc) {
            $crumbs[] = [(string) ($anc->title ?? '—'), url('/'.$anc->slug)];
        }
        $crumbs[] = (string) ($category->title ?? 'Категорія');
    } elseif ($contextTitle) {
        $crumbs[] = ['Каталог', route('gazu.catalog')];
        $crumbs[] = $contextTitle;
    } else {
        $crumbs[] = 'Каталог';
    }
@endphp

@php
    // SEO-шаблони таксономій: пер-категорійний meta_title має пріоритет,
    // далі carSeo (підбір по авто), далі базові шаблони (category/search/car).
    $catalogCount = plural_uk_count($totalCount ?? 0, 'товар', 'товари', 'товарів');
    $catalogSeoTitle = null;
    if ($carSeo && ! empty($carSeo['metaTitle'])) {
        $catalogSeoTitle = $carSeo['metaTitle'];
    } elseif ($carSeo && ! empty($carSeo['contextTitle'])) {
        $catalogSeoTitle = \App\Support\SeoTemplates::title('car', ['car' => $carSeo['contextTitle'], 'count' => $catalogCount]);
    } elseif (! empty($searchQuery)) {
        $catalogSeoTitle = \App\Support\SeoTemplates::title('search', ['query' => $searchQuery, 'count' => $catalogCount]);
    } elseif ($category) {
        $catalogSeoTitle = ! empty($category->meta_title)
            ? (is_array($category->meta_title) ? ($category->meta_title['uk'] ?? '') : (string) $category->meta_title)
            : '';
        if ($catalogSeoTitle === '') {
            $catalogSeoTitle = \App\Support\SeoTemplates::title('category', ['name' => $title, 'count' => $catalogCount]);
        }
    }
@endphp
@section('title', $catalogSeoTitle ?: $title . ' — GAZU')

{{-- OG image: перший товар з listing або category fallback --}}
@php
    $catalogOgImage = null;
    if (isset($products) && $products->isNotEmpty()) {
        $firstP = $products->first();
        if (is_object($firstP)) {
            $kindOg = $firstP->image_kind ?? 'filter';
            $poolDirOg = public_path("img/parts/{$kindOg}");
            $filesOg = is_dir($poolDirOg) ? glob($poolDirOg.'/*.webp') : [];
            sort($filesOg);
            if (! empty($filesOg)) {
                $catalogOgImage = url("/img/parts/{$kindOg}/".basename($filesOg[abs((int) ($firstP->id ?? 0)) % count($filesOg)]));
            }
        }
    }
@endphp
@if($catalogOgImage)
    @section('og_image', $catalogOgImage)
@endif

@section('jsonld')
    @php
        $itemList = [];
        $pos = 1;
        foreach ($crumbs as $crumb) {
            if (is_array($crumb)) {
                $itemList[] = [
                    '@type' => 'ListItem',
                    'position' => $pos++,
                    'name' => (string) $crumb[0],
                    'item' => (string) ($crumb[1] ?? url()->current()),
                ];
            } elseif (is_string($crumb)) {
                $itemList[] = [
                    '@type' => 'ListItem',
                    'position' => $pos++,
                    'name' => $crumb,
                    'item' => url()->current(),
                ];
            }
        }
        $breadcrumbLd = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $itemList,
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($breadcrumbLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endsection
@php
    $catalogSeoDescription = null;
    if ($carSeo && ! empty($carSeo['metaDescription'])) {
        $catalogSeoDescription = $carSeo['metaDescription'];
    } elseif ($carSeo && ! empty($carSeo['contextTitle'])) {
        $catalogSeoDescription = \App\Support\SeoTemplates::description('car', ['car' => $carSeo['contextTitle'], 'count' => $catalogCount]);
    } elseif (! empty($searchQuery)) {
        $catalogSeoDescription = \App\Support\SeoTemplates::description('search', ['query' => $searchQuery, 'count' => $catalogCount]);
    } elseif ($category) {
        $catalogSeoDescription = $category->meta_description
            ? (is_array($category->meta_description) ? ($category->meta_description['uk'] ?? '') : (string) $category->meta_description)
            : '';
        if ($catalogSeoDescription === '') {
            $catalogSeoDescription = \App\Support\SeoTemplates::description('category', ['name' => $title, 'count' => $catalogCount]);
        }
    }
@endphp
@section('description', $catalogSeoDescription ?: 'Каталог автозапчастин · '.$catalogCount.' · доставка по Україні')

@section('content')
    <div class="gazu-container">
        <x-gazu.breadcrumbs :items="$crumbs"/>

        <div class="flex items-end justify-between mb-5 flex-wrap gap-2">
            <div>
                <h1 class="gazu-display text-4xl font-semibold text-[var(--gazu-ink)] m-0">{{ $title }}</h1>
                @if($category && ($category->description ?? false))
                    <div class="text-sm text-[var(--gazu-graphite)] mt-1.5 max-w-xl gazu-prose">{!! $category->description !!}</div>
                @elseif($carSeo && ! empty($carSeo['description']))
                    <div class="text-sm text-[var(--gazu-graphite)] mt-1.5 max-w-xl gazu-prose">{!! $carSeo['description'] !!}</div>
                @elseif($searchQuery)
                    <p class="text-sm text-[var(--gazu-graphite)] mt-1.5">Знайдено {{ plural_uk_count($totalCount, 'товар', 'товари', 'товарів') }}</p>
                @endif
            </div>
        </div>

        {{-- Car-selector: показуємо ТІЛЬКИ на category pages (коли $category set)
             АБО коли user вже обрав авто (preserve UX flow). На root /catalog
             без category — пошук per car робить через home hero. --}}
        @if($category || ! empty($selectedMake ?? '') || ! empty($selectedModel ?? '') || ! empty($selectedEngine ?? ''))
            <div class="mb-4">
                <x-gazu.car-selector
                    variant="catalog"
                    :selected-make="$selectedMake ?? ''"
                    :selected-model="$selectedModel ?? ''"
                    :selected-engine="$selectedEngine ?? ''"
                    :category-url="$category ? request()->url() : null"
                />
            </div>
        @endif

        {{-- Subcategories drilldown — клікабельні плитки L2/L3 під поточною категорією --}}
        @if(! empty($subcategories) && $subcategories->isNotEmpty())
            <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-4 mb-5">
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

        <div class="gazu-grid-sidebar mt-3" x-data="{ filtersOpen: false }"
             @keydown.escape.window="filtersOpen = false"
             :class="filtersOpen ? 'gazu-filters-active' : ''">
            {{-- Backdrop (mobile only) --}}
            <div x-show="filtersOpen" x-cloak x-transition.opacity
                 class="lg:hidden fixed inset-0 z-[69]" style="background: rgba(14,27,44,0.5);"
                 @click="filtersOpen = false"></div>

            {{-- Filter panel: static sidebar on desktop, off-canvas drawer on mobile --}}
            <div class="gazu-filter-panel" :data-open="filtersOpen ? '1' : '0'">
                <div class="lg:hidden flex items-center justify-between mb-3 pb-3 border-b border-[var(--gazu-line)]">
                    <span class="gazu-display text-lg font-semibold text-[var(--gazu-ink)]">Фільтри</span>
                    <button type="button" @click="filtersOpen = false"
                            class="w-8 h-8 rounded-md hover:bg-[var(--gazu-mist)] flex items-center justify-center text-[var(--gazu-graphite)] cursor-pointer" aria-label="Закрити">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
                <x-gazu.filter-panel
                    :priceRange="$priceRange"
                    :availableCategories="$availableCategories ?? collect()"
                    :availableBrands="$availableBrands"
                    :selectedBrands="$selectedBrands"
                    :availableConditions="$availableConditions ?? null"
                    :selectedConditions="$selectedConditions ?? []"
                    :inStockOnly="$inStockOnly"
                    :searchQuery="$searchQuery"
                    :category="$category"/>
            </div>
            <div class="min-w-0">
                @php
                    $currentView = request('view') === 'list' ? 'list' : 'grid';
                    $activeFilterCount = (is_array(request('brand')) ? count(request('brand')) : 0)
                        + (is_array(request('condition')) ? count(request('condition')) : 0)
                        + (request()->filled('min') || request()->filled('max') ? 1 : 0)
                        + (request('stock') === 'in' ? 1 : 0);
                @endphp
                {{-- Mobile filter trigger --}}
                <button type="button" @click="filtersOpen = true"
                        class="lg:hidden w-full mb-3 px-4 py-2.5 bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg flex items-center justify-center gap-2 text-[13px] font-medium text-[var(--gazu-ink)] cursor-pointer">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/></svg>
                    Фільтри
                    @if($activeFilterCount > 0)
                        <span class="ml-1 px-1.5 py-0.5 bg-[var(--gazu-ink)] text-[var(--gazu-on-brand)] text-[11px] rounded-full gazu-mono leading-none">{{ $activeFilterCount }}</span>
                    @endif
                </button>
                @include('gazu.partials.sort-bar', ['count' => $totalCount, 'view' => $currentView, 'currentSort' => $currentSort])

                @if($products->isEmpty())
                    <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-10 text-center mt-4">
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
                            @php
                                // Fragment-кеш картки: рендер 24 карток ≈ головна
                                // вартість cold-MISS. Ключ самоінвалідний — id +
                                // updated_at (бамп на зміну ціни/назви → новий ключ,
                                // НЕ застаріла ціна) + статус наявності + eager.
                                // Deploy чистить cache; TTL 6год доварює рідкісне
                                // (rename бренду / лічильник відгуків).
                                $eager = $loop->index < 4;
                                $cardKey = 'card:'.($p->id ?? 'x').':'.(optional($p->updated_at)->timestamp ?? '0').':'.((($p->qty ?? 0) > 0) ? 1 : 0).':c'.($eager ? 'e' : 'n');
                            @endphp
                            {!! \Illuminate\Support\Facades\Cache::remember($cardKey, 21600, fn () => \Illuminate\Support\Facades\Blade::render('<x-gazu.product-card :p="$p" :compact="true" :eager="$eager"/>', ['p' => $p, 'eager' => $eager])) !!}
                        @endforeach
                    </div>
                    <x-gazu.pagination :paginator="$paginator"/>
                @endif
            </div>
        </div>
    </div>

    <x-gazu.recently-viewed/>
@endsection
