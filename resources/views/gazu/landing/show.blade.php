@extends('gazu.layout')

@section('title', $landing->meta_title ?: ($landing->title.' — GAZU'))

@push('head')
    @if($landing->meta_description)
        <meta name="description" content="{{ $landing->meta_description }}">
    @endif
@endpush

@section('content')
<div class="gazu-container">
    <x-gazu.breadcrumbs :items="[
        ['Головна', route('gazu.home')],
        ['Каталог', route('gazu.catalog')],
        $landing->title,
    ]"/>

    {{-- HEADER --}}
    <header class="mb-6 sm:mb-8 max-w-3xl">
        <h1 class="gazu-display text-3xl sm:text-4xl font-semibold text-[var(--gazu-ink)] m-0 mb-2 leading-tight">
            {{ $landing->h1 ?: $landing->title }}
        </h1>

        @if($landing->intro_html)
            <div class="prose prose-sm mt-4 text-[var(--gazu-graphite)] max-w-none">
                {!! $landing->intro_html !!}
            </div>
        @endif
    </header>

    {{-- APPLIED FILTERS chips --}}
    @if($appliedFilters->count() > 0)
        <div class="mb-5 flex flex-wrap items-center gap-2 text-sm">
            <span class="text-[var(--gazu-graphite)]">Фільтри:</span>
            @foreach($appliedFilters as $f)
                @php
                    $g = $f->filterGroup;
                    $gTitle = $g ? (is_array($g->title) ? ($g->title['uk'] ?? '') : $g->title) : null;
                    $fTitle = is_array($f->title) ? ($f->title['uk'] ?? '') : $f->title;
                @endphp
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-[var(--gazu-paper)] ring-1 ring-[var(--gazu-line)] text-[var(--gazu-ink)]">
                    @if($gTitle)
                        <span class="text-xs text-[var(--gazu-graphite)]">{{ $gTitle }}:</span>
                    @endif
                    <span class="font-medium">{{ $fTitle }}</span>
                </span>
            @endforeach
            @if($landing->category)
                <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-[var(--gazu-ink)]/5 ring-1 ring-[var(--gazu-line)] text-[var(--gazu-ink)]">
                    <span class="text-xs text-[var(--gazu-graphite)]">Категорія:</span>
                    <span class="font-medium ml-1">{{ is_array($landing->category->title) ? ($landing->category->title['uk'] ?? '') : $landing->category->title }}</span>
                </span>
            @endif
            @if($landing->brand)
                <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-[var(--gazu-ink)]/5 ring-1 ring-[var(--gazu-line)] text-[var(--gazu-ink)]">
                    <span class="text-xs text-[var(--gazu-graphite)]">Бренд:</span>
                    <span class="font-medium ml-1">{{ is_array($landing->brand->name) ? ($landing->brand->name['uk'] ?? '') : $landing->brand->name }}</span>
                </span>
            @endif
        </div>
    @endif

    {{-- PRODUCTS --}}
    @if($products->count() === 0)
        <div class="bg-white border border-[var(--gazu-line)] rounded-xl p-10 text-center">
            <p class="text-[var(--gazu-graphite)]">За цими фільтрами товарів не знайдено.</p>
        </div>
    @else
        <p class="text-sm text-[var(--gazu-graphite)] mb-4">Знайдено: <strong>{{ $products->total() }}</strong> товарів</p>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
            @foreach($products as $p)
                <x-gazu.product-card :p="$p"/>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $products->withQueryString()->links() }}
        </div>
    @endif

    {{-- OUTRO (SEO text below) --}}
    @if($landing->outro_html)
        <article class="mt-10 pt-6 border-t border-[var(--gazu-line)] prose prose-sm max-w-3xl">
            {!! $landing->outro_html !!}
        </article>
    @endif
</div>
@endsection
