{{-- Categories Module --}}
@php
    $limit = (int) $module->getSetting('limit', 6);
    $style = $module->getSetting('style', 'grid');

    $categories = \App\Models\Category::query()
        ->select(['id', 'title', 'slug', 'parent_id'])
        ->whereNull('parent_id')
        ->withCount('products')
        ->take($limit)
        ->get();
@endphp

@if($categories->isNotEmpty())
<section id="categories" class="py-16 md:py-24 bg-gray-100">
    <div class="max-w-screen-2xl mx-auto px-4 md:px-8">
        @if($module->title)
            <h2 class="text-3xl md:text-6xl font-black text-black mb-8 md:mb-16 text-center">{{ \App\Models\HomepageModule::translateValue($module->title) }}</h2>
        @endif

        @if($style === 'grid')
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
                @foreach($categories as $category)
                    <div class="category-wall-item" wire:click="goToCategory('{{ $category->slug }}')">
                        <div class="category-icon">{{ $category->icon ?? '📦' }}</div>
                        <h3 class="text-xl font-black">{{ mb_strtoupper($category->title) }}</h3>
                        <p class="text-sm mt-2">{{ $category->products_count ?? 0 }} {{ __('general.products_count_label') }}</p>
                    </div>
                @endforeach
            </div>
        @else
            <div class="space-y-3">
                @foreach($categories as $category)
                    <a wire:navigate href="{{ locale_url($category->getLocalizedSlug()) }}"
                       class="flex items-center justify-between p-4 md:p-6 border-4 border-black bg-white hover:bg-black hover:text-white transition-all group">
                        <div class="flex items-center gap-4">
                            <span class="text-2xl">{{ $category->icon ?? '📦' }}</span>
                            <h3 class="text-xl md:text-2xl font-black">{{ mb_strtoupper($category->title) }}</h3>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-bold">{{ $category->products_count ?? 0 }} {{ __('general.products_count_label') }}</span>
                            <span class="text-2xl font-black group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endif
