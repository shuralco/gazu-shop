{{-- Recently Viewed Module --}}
@php
    $limit = (int) $module->getSetting('limit', 8);
    $recentProducts = app(\App\Services\RecentlyViewedService::class)->getProducts($limit);
@endphp

@if($recentProducts->isNotEmpty())
<section class="py-16 md:py-24 bg-white">
    <div class="max-w-screen-2xl mx-auto px-4 md:px-8">
        @if($module->title)
            <h2 class="text-3xl md:text-6xl font-black text-black mb-8 md:mb-16 text-center">{{ \App\Models\HomepageModule::translateValue($module->title) }}</h2>
        @else
            <h2 class="text-3xl md:text-6xl font-black text-black mb-8 md:mb-16 text-center">{{ __('general.recently_viewed') }}</h2>
        @endif

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
            @foreach($recentProducts as $product)
            <a wire:navigate href="{{ locale_url($product->getLocalizedSlug()) }}" class="border-2 border-gray-200 hover:border-black p-3 md:p-4 transition-colors group">
                <div class="aspect-square bg-gray-100 mb-2 flex items-center justify-center">
                    @if($product->image)
                    <img src="{{ asset($product->getImage()) }}" alt="{{ $product->title }}" class="max-h-full max-w-full object-contain" loading="lazy">
                    @else
                    <span class="text-gray-300 text-3xl">📷</span>
                    @endif
                </div>
                <div class="font-bold text-sm truncate group-hover:underline">{{ $product->title }}</div>
                <div class="font-black text-lg">{{ number_format($product->price, 0) }} ₴</div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif
