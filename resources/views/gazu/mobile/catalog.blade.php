@extends('gazu.layout')
@section('title', 'Каталог · mobile')

@php
    // Brand pills from category brands; "Усі" is always first.
    // Each pill = [slug, label]; first is sentinel for "all".
    $brandPills = collect($availableBrands ?? [])
        ->take(6)
        ->map(function ($b) {
            $slug = is_object($b) ? ($b->manufacturer ?? $b->slug ?? null) : (is_array($b) ? ($b['manufacturer'] ?? $b['slug'] ?? null) : (string) $b);
            $label = is_object($b) ? ($b->label ?? $b->name ?? $slug) : (is_array($b) ? ($b['label'] ?? $b['name'] ?? $slug) : (string) $b);
            return $slug ? [(string) $slug, (string) $label] : null;
        })
        ->filter()
        ->values()
        ->all();
    $pills = array_merge([['', 'Усі']], $brandPills);
    $selectedBrand = request('brand') ?? null;
    if (is_array($selectedBrand)) $selectedBrand = $selectedBrand[0] ?? null;
@endphp
@section('content')
<div class="max-w-[420px] mx-auto py-4 px-4 pb-20">
    <h1 class="gazu-display text-xl font-semibold mb-2">{{ $category->title ?? 'Каталог' }}</h1>
    <div class="text-xs text-[var(--gazu-graphite)] mb-3">{{ plural_uk_count((int) ($totalCount ?? $products->count()), 'товар', 'товари', 'товарів') }}</div>
    <div class="flex gap-2 mb-3 overflow-x-auto whitespace-nowrap">
        @foreach($pills as $i => [$pillSlug, $pillLabel])
            @php
                $isAll = $i === 0;
                $isActive = $isAll ? ! $selectedBrand : $selectedBrand === $pillSlug;
                $url = $isAll
                    ? request()->fullUrlWithQuery(['brand' => null])
                    : request()->fullUrlWithQuery(['brand' => [$pillSlug]]);
            @endphp
            <a wire:navigate href="{{ $url }}" class="px-3 py-1.5 rounded-full text-xs whitespace-nowrap no-underline {{ $isActive ? 'bg-[var(--gazu-ink)] text-[var(--gazu-on-brand)]' : 'bg-[var(--gazu-surface)] border border-[var(--gazu-line)] text-[var(--gazu-graphite)]' }}">
                {{ $pillLabel }}
            </a>
        @endforeach
    </div>
    <div class="flex justify-between items-center mb-3">
        <button type="button" class="gazu-btn-outline text-xs py-1.5 px-3"><x-gazu.icon name="filter" size="14"/> Фільтри</button>
        <select class="text-xs border border-[var(--gazu-line)] bg-[var(--gazu-surface)] rounded px-2 py-1.5">
            <option>За популярністю</option>
            <option>За ціною</option>
        </select>
    </div>
    <div class="grid grid-cols-2 gap-2.5">
        @foreach($products as $p)
            @php
                $eager = $loop->index < 4;
                $cardKey = 'card:'.($p->id ?? 'x').':'.(optional($p->updated_at)->timestamp ?? '0').':'.((($p->qty ?? 0) > 0) ? 1 : 0).':c'.($eager ? 'e' : 'n');
            @endphp
            {!! \Illuminate\Support\Facades\Cache::remember($cardKey, 21600, fn () => \Illuminate\Support\Facades\Blade::render('<x-gazu.product-card :p="$p" :compact="true" :eager="$eager"/>', ['p' => $p, 'eager' => $eager])) !!}
        @endforeach
    </div>
</div>
@include('gazu.partials.mobile-nav', ['active' => 'catalog'])
@endsection
