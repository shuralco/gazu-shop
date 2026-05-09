{{-- Products Grid Module --}}
@php
    $filter = $module->getSetting('filter', 'hits');
    $limit = (int) $module->getSetting('limit', 8);
    $columns = (int) $module->getSetting('columns', 4);

    $query = \App\Models\Product::query()
        ->select(['id', 'title', 'slug', 'price', 'old_price', 'image', 'is_hit', 'is_new', 'brand_id'])
        ->with(['brandModel:id,name,logo,slug', 'filters.filterGroup:id,title'])
        ->where('is_active', true);

    if ($filter === 'hits') {
        $query->where('is_hit', true);
    } elseif ($filter === 'new') {
        $query->where('is_new', true);
    } elseif ($filter === 'specials') {
        $query->where('old_price', '>', 0);
    }

    $products = $query->orderBy('id', 'desc')->take($limit)->get();

    $gridClass = match ($columns) {
        3 => 'grid-cols-2 md:grid-cols-3',
        5 => 'grid-cols-2 md:grid-cols-3 lg:grid-cols-5',
        default => 'grid-cols-2 md:grid-cols-3 lg:grid-cols-4',
    };

    $bgClass = $loop->even ? 'bg-gray-100' : 'bg-white';
@endphp

@if($products->isNotEmpty())
<section class="py-16 md:py-24 {{ $bgClass }}">
    <div class="max-w-screen-2xl mx-auto px-4 md:px-8">
        @if($module->title)
            <h2 class="text-3xl md:text-6xl font-black text-black mb-8 md:mb-16">{{ \App\Models\HomepageModule::translateValue($module->title) }}</h2>
        @endif

        <div class="product-grid">
            @foreach($products as $product)
                <div wire:key="module-{{ $module->id }}-product-{{ $product->id }}">
                    <x-ui.product-card :product="$product" />
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
