<div>
    @section('metatags')
        <x-seo-meta 
            :seo="$seo"
            :pageType="'brands_index'"
            :language="'uk'"
        />
    @endsection

    <!-- Scroll Progress Bar -->
    <div class="scroll-progress" id="scrollProgress"></div>

    <!-- Main Content -->
    <div class="pt-4 md:pt-6">
        
        <!-- Breadcrumbs -->
        <div class="max-w-screen-2xl mx-auto px-2 md:px-8 mb-1 md:mb-2">
            <nav class="flex items-center gap-2 text-sm font-medium">
                <a wire:navigate href="{{ locale_route('home') }}" class="hover:underline font-bold">{{ __('general.home') }}</a>
                <span class="text-black font-black">/</span>
                <span class="font-black text-black uppercase">{{ __('general.all_brands') }}</span>
            </nav>
        </div>
        
        <!-- Page Title -->
        <div class="max-w-screen-2xl mx-auto px-4 md:px-8 pb-8">
            <h1 class="text-4xl md:text-6xl font-black text-black mb-2">{{ __('general.all_brands') }}</h1>
            <p class="text-lg font-medium">{{ __('general.found_brands', ['count' => $brands->flatten()->count()]) }}</p>
        </div>
        
        <div class="max-w-screen-2xl mx-auto px-4 md:px-8">
            
            @if($brands->count() > 0)
                @foreach($brands as $letter => $letterBrands)
                    <!-- Letter Section -->
                    <div class="mb-12">
                        <h2 class="text-2xl md:text-4xl font-black text-black mb-6 border-b-4 border-black pb-2">{{ $letter }}</h2>
                        
                        <!-- Brands Grid -->
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
                            @foreach($letterBrands as $brand)
                                <a wire:navigate href="{{ locale_route('brand', ['brand' => $brand->slug]) }}" wire:key="{{ $brand->id }}" 
                                   class="brand-card group">
                                    <div class="aspect-square bg-white border-2 border-black overflow-hidden transition-all duration-200 group-hover:transform group-hover:translate-y-[-4px] group-hover:shadow-[8px_8px_0_black]">
                                        @if($brand->logo)
                                            <img src="{{ Storage::url($brand->logo) }}" 
                                                 alt="{{ $brand->name }}" 
                                                 class="w-full h-full object-contain p-4">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center p-4">
                                                <span class="font-black text-lg md:text-xl text-black text-center leading-tight">
                                                    {{ strtoupper($brand->name) }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="mt-2 text-center">
                                        <h3 class="font-bold text-sm md:text-base text-black group-hover:underline">
                                            {{ $brand->name }}
                                        </h3>
                                        @if($brand->products_count > 0)
                                            <p class="text-xs md:text-sm text-gray-600 font-medium">
                                                {{ $brand->products_count }} {{ __('general.products_count_label') }}
                                            </p>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center py-12 md:py-16 lg:py-24">
                    <div class="text-6xl sm:text-8xl md:text-9xl mb-6 md:mb-8">🏷️</div>
                    <h2 class="text-2xl sm:text-3xl md:text-4xl font-black text-black mb-3 md:mb-4 px-4">{{ __('general.brands_not_found') }}</h2>
                    <p class="text-base sm:text-lg text-gray-600 mb-6 md:mb-8 px-4">{{ __('general.no_brands_message') }}</p>
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
.scroll-progress {
    position: fixed;
    right: 0;
    top: 0;
    width: 4px;
    height: 0%;
    background: black;
    z-index: 9999;
    transition: height 0.1s ease;
}

.brand-card {
    transition: all 0.2s ease;
}
</style>
@endpush

@push('scripts')
<script>
// Scroll Progress
window.addEventListener('scroll', () => {
    const scrollHeight = document.documentElement.scrollHeight - window.innerHeight;
    const scrollPosition = window.scrollY;
    const progress = (scrollPosition / scrollHeight) * 100;
    const progressBar = document.getElementById('scrollProgress');
    if (progressBar) {
        progressBar.style.height = progress + '%';
    }
});
</script>
@endpush