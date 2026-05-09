<div>
    @section('metatags')
        <x-seo-meta 
            :seo="$seo"
            :pageType="'brand'"
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
                <a wire:navigate href="{{ locale_route('brands') }}" class="hover:underline font-bold uppercase">{{ __('general.all_brands') }}</a>
                <span class="text-black font-black">/</span>
                <span class="font-black text-black uppercase">{{ $brand->name }}</span>
            </nav>
        </div>
        
        <!-- Brand Header -->
        <div class="max-w-screen-2xl mx-auto px-4 md:px-8 pb-8">
            <div class="flex flex-col md:flex-row items-start md:items-center gap-6 mb-6">
                @if($brand->logo)
                    <div class="w-24 h-24 md:w-32 md:h-32 bg-white border-2 border-black flex items-center justify-center">
                        <img src="{{ Storage::url($brand->logo) }}" 
                             alt="{{ $brand->name }}" 
                             class="w-full h-full object-contain p-2">
                    </div>
                @endif
                <div class="flex-1">
                    <h1 class="text-4xl md:text-6xl font-black text-black mb-2">{{ strtoupper($brand->name) }}</h1>
                    @if($brand->description)
                        <p class="text-lg font-medium text-gray-700 mb-4">{{ $brand->description }}</p>
                    @endif
                    <p class="text-lg font-medium hidden md:block">{{ __('general.found_products', ['count' => $products->total()]) }}</p>
                </div>
            </div>
        </div>
        
        <div class="max-w-screen-2xl mx-auto px-4 md:px-8">
            
            <!-- Sort Controls -->
            <div class="mb-6 md:mb-8">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-4 md:mb-6">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-4 w-full sm:w-auto">
                        <div class="flex items-center gap-2">
                            <span class="font-black text-sm sm:text-lg">{{ __('general.sort_by') }}</span>
                            <select wire:model.live="sortBy"
                                    class="border-2 border-black px-3 py-2 text-sm sm:text-base font-bold bg-white">
                                <option value="created_at">{{ __('general.sort_by_date') }}</option>
                                <option value="title">{{ __('general.sort_by_name') }}</option>
                                <option value="price">{{ __('general.sort_by_price') }}</option>
                                <option value="is_hit">{{ __('general.sort_by_popularity') }}</option>
                            </select>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-black text-sm sm:text-lg">{{ __('general.order_by') }}</span>
                            <select wire:model.live="sortDirection"
                                    class="border-2 border-black px-3 py-2 text-sm sm:text-base font-bold bg-white">
                                <option value="desc">{{ __('general.sort_desc') }}</option>
                                <option value="asc">{{ __('general.sort_asc') }}</option>
                            </select>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-black text-sm sm:text-lg">{{ __('general.show_label') }}</span>
                            <select wire:model.live="perPage" 
                                    class="border-2 border-black px-3 py-2 text-sm sm:text-base font-bold bg-white">
                                <option value="12">12</option>
                                <option value="24">24</option>
                                <option value="48">48</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            @if($products->count() > 0)
                <!-- Products Grid -->
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-12">
                    @foreach($products as $product)
                        <div wire:key="{{ $product->id }}">
                            @include('incs.brutal-product-card')
                        </div>
                    @endforeach
                </div>
                
                <!-- Pagination -->
                <div class="flex justify-center">
                    {{ $products->links() }}
                </div>
            @else
                <div class="text-center py-12 md:py-16 lg:py-24">
                    <div class="text-6xl sm:text-8xl md:text-9xl mb-6 md:mb-8">📦</div>
                    <h2 class="text-2xl sm:text-3xl md:text-4xl font-black text-black mb-3 md:mb-4 px-4">{{ __('general.products_not_found') }}</h2>
                    <p class="text-base sm:text-lg text-gray-600 mb-6 md:mb-8 px-4">{{ __('general.no_brand_products') }}</p>
                    <a wire:navigate href="{{ locale_route('brands') }}" class="bg-black text-white font-bold px-6 py-3 hover:bg-white hover:text-black border-2 border-black transition-colors">
                        {{ __('general.view_other_brands') }}
                    </a>
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