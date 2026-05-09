<div>
    @section('metatags')
        <x-seo-meta 
            :model="$category"
            :pageType="'product.category'"
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
                @foreach($breadcrumbs as $breadcrumb_slug => $breadcrumb_title)
                    <span class="text-black font-black">/</span>
                    @if($loop->last)
                        <span class="font-black text-black uppercase">{{ $breadcrumb_title }}</span>
                    @else
                        <a wire:navigate href="{{ locale_url($breadcrumb_slug) }}" class="hover:underline font-bold uppercase">{{ $breadcrumb_title }}</a>
                    @endif
                @endforeach
            </nav>
        </div>
        
        <!-- Page Title -->
        <div class="max-w-screen-2xl mx-auto px-4 md:px-8 pb-8">
            <h1 class="text-4xl md:text-6xl font-black text-black mb-2">{{ strtoupper($category->title) }}</h1>
            <p class="text-lg font-medium hidden md:block">{{ __("general.found_products", ["count" => $totalProducts]) }}</p>
            
            <!-- SEO Quick Manager for Admins -->
            @auth
                @if(auth()->user()->is_admin && \App\Models\DisplaySetting::get('seo_manager_category_visible', false))
                    <div class="mt-6">
                        <livewire:admin.seo-quick-manager :model="$category" />
                    </div>
                @endif
            @endauth
        </div>
        
        <div class="max-w-screen-2xl mx-auto px-4 md:px-8">
            

            <!-- Filter Button and Selected Filters -->
            <div class="mb-6 md:mb-8">
                <!-- Mobile Layout -->
                <div class="flex md:hidden flex-row items-end justify-between gap-4 mb-4">
                    <!-- Sort Control -->
                    <div class="flex flex-col gap-1 flex-1">
                        <span class="font-black text-xs">{{ __("general.sort") }}:</span>
                        <select wire:model.live.debounce.200ms="sort" wire:change="changeSort"
                                class="border-2 border-black px-2 py-1 text-xs font-bold bg-white w-full">
                            @foreach($this->sortList as $k => $item)
                                <option value="{{ $k }}" wire:key="{{ $k }}">{{ $item['title'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Show Control -->
                    <div class="flex flex-col gap-1 flex-1">
                        <span class="font-black text-xs">{{ __("general.show") }}:</span>
                        <select wire:model.live.debounce.200ms="limit" wire:change="changeLimit"
                                class="border-2 border-black px-2 py-1 text-xs font-bold bg-white w-full">
                            @foreach($this->limitList as $item)
                                <option value="{{ $item }}" wire:key="{{ $item }}">{{ $item }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Filter Button -->
                    <div class="relative flex-shrink-0">
                        <button id="filter-btn"
                                onclick="openFilterModal()"
                                class="bg-black text-white font-black text-xs px-2 py-2 hover:bg-white hover:text-black border-2 border-black transition-colors flex items-center justify-center min-w-[80px]">
                            <span class="uppercase tracking-wide">{{ __("general.filters") }}</span>
                        </button>
                        @if(count($selected_filters) > 0 || count($selected_brands) > 0)
                            <div class="filter-badge">{{ count($selected_filters) + count($selected_brands) }}</div>
                        @endif
                    </div>
                </div>

                <!-- Desktop Layout -->
                <div class="hidden md:flex flex-row items-center justify-between gap-4 mb-6">
                    <div class="flex flex-row items-center gap-4">
                        <div class="flex items-center gap-2">
                            <span class="font-black text-lg">{{ __("general.sort") }}:</span>
                            <select wire:model.live.debounce.200ms="sort" wire:change="changeSort"
                                    class="border-2 border-black px-3 py-2 text-base font-bold bg-white">
                                @foreach($this->sortList as $k => $item)
                                    <option value="{{ $k }}" wire:key="{{ $k }}">{{ $item['title'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-black text-lg">{{ __("general.show") }}:</span>
                            <select wire:model.live.debounce.200ms="limit" wire:change="changeLimit"
                                    class="border-2 border-black px-3 py-2 text-base font-bold bg-white">
                                @foreach($this->limitList as $item)
                                    <option value="{{ $item }}" wire:key="{{ $item }}">{{ $item }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="relative">
                        <button id="filter-btn"
                                onclick="openFilterModal()"
                                class="bg-black text-white font-black text-lg px-8 py-4 hover:bg-white hover:text-black border-2 border-black transition-colors flex items-center justify-center gap-2 min-w-[140px]">
                            <span class="uppercase tracking-wide">{{ __("general.filters") }}</span>
                        </button>
                        @if(count($selected_filters) > 0 || count($selected_brands) > 0)
                            <div class="filter-badge">{{ count($selected_filters) + count($selected_brands) }}</div>
                        @endif
                    </div>
                </div>

                @if($selected_filters || $selected_brands)
                    <!-- Selected Filters Display -->
                    <div class="border-4 border-red-600 bg-red-50 p-3 md:p-4 mb-4 md:mb-6">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-black text-sm sm:text-base">{{ __("general.active_filters") }}:</span>
                            
                            @foreach($available_brands as $brand)
                                @if(in_array($brand->id, $selected_brands))
                                    <button wire:click="removeBrand({{ $brand->id }})" 
                                            wire:key="selected-brand-{{ $brand->id }}"
                                            class="bg-blue-600 text-white px-2 sm:px-3 py-1 text-xs sm:text-sm font-bold hover:bg-blue-700 transition-colors">
                                        ✕ {{ $brand->name }}
                                    </button>
                                @endif
                            @endforeach
                            
                            @foreach($filter_groups as $filter_group)
                                @foreach($filter_group as $filter)
                                    @if(in_array($filter->filter_id, $selected_filters))
                                        <button wire:click="removeFilter({{ $filter->filter_id }})" 
                                                wire:key="selected-{{ $filter->filter_id }}"
                                                class="bg-red-600 text-white px-2 sm:px-3 py-1 text-xs sm:text-sm font-bold hover:bg-red-700 transition-colors">
                                            ✕ {{ $filter->filter_title }}
                                        </button>
                                    @endif
                                @endforeach
                            @endforeach
                            <button wire:click="clearFilters" class="bg-red-600 text-white px-2 sm:px-3 py-1 text-xs sm:text-sm font-bold hover:bg-red-700 transition-colors">
                                {{ __('general.clear_all') }}
                            </button>
                        </div>
                    </div>
                @endif
            </div>



            <!-- Products Grid - ALWAYS VISIBLE -->
            <div class="grid @if($mobile_products_per_row == 1) grid-cols-1 @else grid-cols-2 @endif md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 @if($mobile_grid_gap == 2) gap-2 @elseif($mobile_grid_gap == 6) gap-6 @else gap-4 @endif mb-12" id="products">
                @if(count($products) > 0)
                    @foreach($products as $product)
                        <div wire:key="{{ $product->id }}">
                            @include('incs.brutal-product-card')
                        </div>
                    @endforeach
                @else
                    <div class="col-span-full text-center p-8">
                        <h3 class="text-2xl font-bold">{{ __('general.products_not_found') }}</h3>
                        <p>Фільтри: Min {{ $min_price }} - Max {{ $max_price }}</p>
                    </div>
                @endif
            </div>
                
                <!-- Combined Pagination + Load More (Desktop: inline, Mobile: stacked) -->
                @if($hasPages || $showLoadMoreContainer)
                <div class="flex justify-between items-center gap-8 bg-white border-4 border-black p-6 mb-12 max-md:flex-col max-md:gap-4">
                    
                    <!-- Traditional Pagination (Left side on desktop, centered on mobile) -->
                    @if($hasPages)
                    <div class="flex items-center gap-2 max-md:justify-center">
                        {{-- Previous Page Link --}}
                        @if ($currentPage <= 1)
                            <span class="pagination-btn opacity-50 cursor-not-allowed border-4 border-gray-300 bg-gray-100 text-gray-400 px-4 py-2 font-black min-w-12 h-12 flex items-center justify-center max-md:px-3 max-md:text-sm">
                                ←
                            </span>
                        @else
                            <button wire:click="previousPage" rel="prev" class="pagination-btn border-4 border-black bg-white text-black px-4 py-2 font-black hover:bg-black hover:text-white transition-all min-w-12 h-12 flex items-center justify-center max-md:px-3 max-md:text-sm">
                                ←
                            </button>
                        @endif

                        {{-- Page Numbers --}}
                        @foreach ($pageRange as $page => $url)
                            @if ($page == $currentPage)
                                <span class="pagination-btn active bg-black text-white border-4 border-black px-4 py-2 font-black min-w-12 h-12 flex items-center justify-center max-md:px-3 max-md:text-sm">{{ $page }}</span>
                            @else
                                <button wire:click="gotoPage({{ $page }})" class="pagination-btn border-4 border-black bg-white text-black px-4 py-2 font-black hover:bg-black hover:text-white transition-all min-w-12 h-12 flex items-center justify-center max-md:px-3 max-md:text-sm">{{ $page }}</button>
                            @endif
                        @endforeach

                        {{-- Next Page Link --}}
                        @if ($hasMorePages)
                            <button wire:click="nextPage" rel="next" class="pagination-btn border-4 border-black bg-white text-black px-4 py-2 font-black hover:bg-black hover:text-white transition-all min-w-12 h-12 flex items-center justify-center max-md:px-3 max-md:text-sm">
                                →
                            </button>
                        @else
                            <span class="pagination-btn opacity-50 cursor-not-allowed border-4 border-gray-300 bg-gray-100 text-gray-400 px-4 py-2 font-black min-w-12 h-12 flex items-center justify-center max-md:px-3 max-md:text-sm">
                                →
                            </span>
                        @endif
                    </div>
                    @endif
                    
                    <!-- Load More Button (Right side on desktop, full width on mobile) -->
                    @if($showLoadMoreContainer)
                    <div class="load-more-container max-md:w-full">
                        @if($showLoadMore)
                            <button wire:click="loadMore" 
                                    wire:loading.attr="disabled"
                                    wire:target="loadMore"
                                    class="load-more-btn bg-black text-white border-4 border-black px-8 py-3 font-black text-lg hover:bg-white hover:text-black transition-all disabled:opacity-50 max-md:w-full">
                                <span wire:loading.remove wire:target="loadMore">{{ __("general.show_more") }}</span>
                                <span wire:loading wire:target="loadMore">{{ __('general.loading') }}</span>
                            </button>
                        @else
                            <!-- Skeleton placeholder for load more button -->
                            <div class="load-more-btn bg-gray-100 text-gray-400 border-4 border-gray-300 px-8 py-3 font-black text-lg cursor-not-allowed max-md:w-full">
                                {{ __('general.all_products_loaded') }}
                            </div>
                        @endif
                    </div>
                    @endif
                </div>
                @endif
        </div>
    </div>
    
    <!-- Filter Modal -->
    <div id="filter-modal" 
         class="filter-modal-overlay" 
         style="display: none; z-index: 10001 !important;">
        <div class="filter-modal-content">
            <!-- Modal Header -->
            <div class="bg-black text-white p-6 flex items-center justify-between">
                <h2 class="font-black text-xl lg:text-2xl">{{ __('general.filter_products') }}</h2>
                <button id="close-filter-modal"
                        onclick="closeFilterModal()"
                        class="font-black text-2xl hover:bg-white hover:text-black px-3 py-1 transition-colors">×</button>
            </div>
            
            <!-- Modal Body - Two Column Layout -->
            <div class="flex-1 overflow-hidden">
                <!-- Loading overlay -->
                <div wire:loading wire:target="toggleFilter,toggleBrand,min_price,max_price" 
                     class="absolute inset-0 bg-white bg-opacity-95 flex items-center justify-center z-50">
                    <div class="bg-black text-white px-6 py-3 font-bold text-lg">
                        ОНОВЛЕННЯ ФІЛЬТРІВ...
                    </div>
                </div>
                
                <!-- Responsive filters layout -->
                <div class="p-4 md:p-6 max-h-[55vh] overflow-y-auto">
                    <div class="block md:columns-2 md:gap-8 space-y-6">
                        <!-- Price Range -->
                        <div class="break-inside-avoid mb-6">
                            <h3 class="font-black text-xl mb-4 border-b-2 border-black pb-2 tracking-wide">
                                {{ __('general.price_label') }}
                            </h3>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold mb-1 text-gray-700 uppercase">{{ __('general.price_from') }}:</label>
                                    <input type="number" placeholder="2799" 
                                           wire:model.live.debounce.500ms="min_price" 
                                           value="{{ $min_price }}"
                                           class="w-full border-2 border-black px-3 py-2 font-bold text-center text-base rounded-none focus:outline-none focus:ring-1 focus:ring-black">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold mb-1 text-gray-700 uppercase">{{ __('general.price_to') }}:</label>
                                    <input type="number" placeholder="3499" 
                                           wire:model.live.debounce.500ms="max_price" 
                                           value="{{ $max_price }}"
                                           class="w-full border-2 border-black px-3 py-2 font-bold text-center text-base rounded-none focus:outline-none focus:ring-1 focus:ring-black">
                                </div>
                            </div>
                        </div>

                        <!-- Brands Filter -->
                        @if($available_brands->count() > 0)
                        <div class="break-inside-avoid mb-6">
                            <h3 class="font-black text-xl mb-4 border-b-2 border-black pb-2 tracking-wide">
                                {{ __('general.brands') }}
                            </h3>
                            <div class="space-y-2">
                                @foreach($available_brands as $brand)
                                    <div wire:key="brand-filter-{{ $brand->id }}" 
                                         class="flex items-center p-3 hover:bg-gray-50 transition-colors border border-gray-200 hover:border-black">
                                        <input type="checkbox" 
                                               id="brand-checkbox-{{ $brand->id }}"
                                               wire:click="toggleBrand({{ $brand->id }})"
                                               @checked(in_array($brand->id, $selected_brands))
                                               class="mr-4">
                                        <label for="brand-checkbox-{{ $brand->id }}" 
                                               class="cursor-pointer font-bold flex-1 select-none text-base uppercase tracking-wide">
                                            {{ $brand->name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Filter Groups -->
                        @foreach($filter_groups as $k => $filter_group)
                            <div wire:key="group-{{ $k }}" class="break-inside-avoid mb-6">
                                <h3 class="font-black text-xl mb-4 border-b-2 border-black pb-2 tracking-wide">
                                    {{ strtoupper($filter_group[0]->title) }}
                                </h3>
                                <div class="space-y-2">
                                    @php
                                        $showAll = count($filter_group) <= 4;
                                        $visibleFilters = $showAll ? $filter_group : array_slice($filter_group, 0, 4);
                                        $hiddenFilters = $showAll ? [] : array_slice($filter_group, 4);
                                    @endphp
                                    
                                    <!-- Always visible filters (first 4) -->
                                    @foreach($visibleFilters as $filter)
                                        <div wire:key="filter-{{ $filter->filter_id }}" 
                                             class="flex items-center p-3 hover:bg-gray-50 transition-colors border border-gray-200 hover:border-black">
                                            <input type="checkbox" 
                                                   id="filter-checkbox-{{ $filter->filter_id }}"
                                                   wire:click="toggleFilter({{ $filter->filter_id }})"
                                                   @checked(in_array($filter->filter_id, $selected_filters))
                                                   class="mr-4">
                                            <label for="filter-checkbox-{{ $filter->filter_id }}" 
                                                   class="cursor-pointer font-bold flex-1 select-none text-base uppercase tracking-wide">
                                                {{ $filter->filter_title }}
                                            </label>
                                        </div>
                                    @endforeach
                                    
                                    <!-- Collapsible filters (5+) -->
                                    @if(!$showAll)
                                        <div id="hidden-filters-{{ $k }}" class="hidden space-y-2">
                                            @foreach($hiddenFilters as $filter)
                                                <div wire:key="filter-{{ $filter->filter_id }}" 
                                                     class="flex items-center p-3 hover:bg-gray-50 transition-colors border border-gray-200 hover:border-black">
                                                    <input type="checkbox" 
                                                           id="filter-checkbox-{{ $filter->filter_id }}"
                                                           wire:click="toggleFilter({{ $filter->filter_id }})"
                                                           @checked(in_array($filter->filter_id, $selected_filters))
                                                           class="mr-4">
                                                    <label for="filter-checkbox-{{ $filter->filter_id }}" 
                                                           class="cursor-pointer font-bold flex-1 select-none text-base uppercase tracking-wide">
                                                        {{ $filter->filter_title }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                        
                                        <!-- Show More/Less button -->
                                        <button type="button"
                                                onclick="toggleFilterGroup({{ $k }})"
                                                id="toggle-btn-{{ $k }}"
                                                class="w-full mt-3 py-2 px-4 bg-gray-100 hover:bg-gray-200 border-2 border-gray-300 hover:border-black font-bold text-sm uppercase tracking-wide transition-all">
                                            <span id="toggle-text-{{ $k }}">Показати ще +{{ count($hiddenFilters) }}</span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="border-t-4 border-black p-6 bg-gray-50">
                <!-- Results Counter -->
                <div class="text-center mb-4">
                    <span class="text-sm font-medium text-gray-500 tracking-wide hidden md:inline">
                        {{ __("general.found_products", ["count" => $totalProducts]) }}
                    </span>
                </div>
                
                <!-- Action Buttons -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <button wire:click="clearFilters" 
                            class="border-3 border-black font-black py-4 px-6 text-lg uppercase tracking-wide hover:bg-black hover:text-white transition-all duration-200 transform hover:scale-105">
                        {{ __('general.clear_all') }}
                    </button>
                    <button id="apply-filter-modal"
                            onclick="closeFilterModal()"
                            class="bg-black text-white font-black py-4 px-6 text-lg uppercase tracking-wide hover:bg-green-600 border-3 border-black transition-all duration-200 transform hover:scale-105">
                        {{ __('general.apply_filters') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Hide scrollbar for Chrome, Safari and Opera */
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

/* Hide scrollbar for IE, Edge and Firefox */
.scrollbar-hide {
    -ms-overflow-style: none;  /* IE and Edge */
    scrollbar-width: none;  /* Firefox */
}

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

.filter-checkbox {
    appearance: none;
    width: 20px;
    height: 20px;
    border: 2px solid black;
    position: relative;
    cursor: pointer;
    background: white;
}

.filter-checkbox:checked {
    background: black;
}

.filter-checkbox:checked::after {
    content: '✓';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-weight: 900;
    font-size: 14px;
}

.product-card {
    background: white;
    border: 2px solid black;
    overflow: hidden;
    transition: all 0.2s ease;
    height: 100%;
}

.product-card:hover {
    transform: translateY(-4px);
    box-shadow: 8px 8px 0 black;
}

.filter-btn {
    background: white;
    border: 2px solid black;
    color: black;
    padding: 12px 24px;
    font-weight: 900;
    font-size: 14px;
    transition: all 0.2s ease;
    cursor: pointer;
}

.filter-btn:hover {
    background: black;
    color: white;
}

.filter-btn.active {
    background: black;
    color: white;
}

.btn-red {
    background: white;
    border: 2px solid red;
    color: red;
    padding: 8px 16px;
    font-weight: 900;
    font-size: 12px;
    transition: all 0.2s ease;
}

.btn-red:hover {
    background: red;
    color: white;
}

/* Filter Modal - Vanilla JS */
.filter-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.8);
    z-index: 10001 !important;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 0;
}

@media (max-width: 767px) {
    .filter-modal-overlay {
        align-items: stretch;
        justify-content: stretch;
        padding: 0;
    }
}

/* Hide navigation when filter modal is open */
body.filter-modal-open nav:not([style*="z-index: 10000"]) {
    display: none !important;
}

.filter-modal-content {
    background: white;
    border: 4px solid black;
    width: 90%;
    max-width: 900px;
    max-height: 85vh;
    overflow: hidden;
    overflow-x: hidden;
    z-index: 10001 !important;
    display: flex;
    flex-direction: column;
    animation: modalFadeIn 0.3s ease;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
    border-radius: 8px;
}

@keyframes modalFadeIn {
    from { opacity: 0; transform: scale(0.9); }
    to { opacity: 1; transform: scale(1); }
}

/* CSS Columns styling for desktop only */
@media (min-width: 768px) {
    .filter-modal-content .md\:columns-2 {
        column-count: 2;
        column-gap: 2rem;
    }
}

/* Mobile responsive adjustments */
@media (max-width: 767px) {
    .filter-modal-content {
        width: 100% !important;
        height: 100vh !important;
        max-height: 100vh !important;
        max-width: none !important;
        margin: 0 !important;
        border-radius: 0 !important;
        border-width: 0 !important;
        border: none !important;
    }
    
    /* Force single column on mobile */
    .filter-modal-content .block {
        display: block !important;
        column-count: 1 !important;
    }
    
    /* Mobile specific spacing */
    .filter-modal-content .space-y-6 > * {
        margin-bottom: 1.5rem !important;
    }
    
    /* Larger touch targets on mobile */
    .filter-modal-content input[type="checkbox"] {
        width: 28px !important;
        height: 28px !important;
    }
    
    .filter-modal-content input[type="checkbox"]:checked::after {
        font-size: 18px;
        top: -1px;
        left: 4px;
    }
    
    .filter-modal-content label {
        font-size: 1rem !important;
        padding: 0.75rem !important;
    }
    
    .filter-modal-content h3 {
        font-size: 1.125rem !important;
        margin-bottom: 0.75rem !important;
    }
    
    /* Price inputs on mobile */
    .filter-modal-content input[type="number"] {
        padding: 0.75rem !important;
        font-size: 1rem !important;
    }
    
    /* Better button spacing on mobile */
    .filter-modal-content .grid.grid-cols-1.sm\:grid-cols-2 {
        grid-template-columns: 1fr 1fr !important;
        gap: 0.75rem;
    }
    
    /* Modal body padding on mobile */
    .filter-modal-content .p-4 {
        padding: 1rem !important;
    }
    
    .filter-modal-content .max-h-\[55vh\] {
        max-height: calc(100vh - 200px) !important;
    }
    
    /* Prevent horizontal scroll on mobile */
    .filter-modal-content * {
        box-sizing: border-box;
    }
    
    .filter-modal-content .grid {
        gap: 0.5rem !important;
    }
    
    /* Ensure inputs don't overflow */
    .filter-modal-content input[type="number"] {
        min-width: 0;
        max-width: 100%;
    }
    
    /* Header responsive */
    .filter-modal-content h2 {
        font-size: 1.25rem !important;
    }
    
    /* Fixed footer on mobile */
    .filter-modal-content .border-t-4.border-black {
        position: fixed !important;
        bottom: 0 !important;
        left: 0 !important;
        right: 0 !important;
        margin: 0 !important;
        border-radius: 0 !important;
        z-index: 10001 !important;
    }
    
    /* Adjust modal body to account for fixed footer */
    .filter-modal-content .max-h-\[55vh\] {
        max-height: calc(100vh - 180px) !important;
        padding-bottom: 20px !important;
    }
    
    /* Make buttons larger and more touch-friendly */
    .filter-modal-content .grid.grid-cols-1.sm\:grid-cols-2 button {
        padding: 1rem 1.5rem !important;
        font-size: 1rem !important;
        min-height: 60px !important;
    }
}

/* Custom checkbox styling for brutal design */
.filter-modal-content input[type="checkbox"] {
    appearance: none;
    width: 24px;
    height: 24px;
    border: 3px solid black;
    background: white;
    cursor: pointer;
    position: relative;
    transition: all 0.2s ease;
}

.filter-modal-content input[type="checkbox"]:checked {
    background: black;
    border-color: black;
}

.filter-modal-content input[type="checkbox"]:checked::after {
    content: '✓';
    position: absolute;
    top: -2px;
    left: 3px;
    color: white;
    font-weight: bold;
    font-size: 16px;
}

.filter-modal-content input[type="checkbox"]:hover {
    border-color: #333;
    transform: scale(1.1);
}

.filter-modal-content input[type="checkbox"]:focus {
    outline: 2px solid black;
    outline-offset: 2px;
}

/* Improved typography */
.filter-modal-content h3 {
    font-family: system-ui, -apple-system, sans-serif;
    letter-spacing: 0.05em;
    text-shadow: 1px 1px 0 rgba(0, 0, 0, 0.1);
}

.filter-modal-content label {
    font-family: system-ui, -apple-system, sans-serif;
    line-height: 1.4;
    transition: color 0.2s ease;
}

/* Active filter indication */
.filter-modal-content input[type="checkbox"]:checked + label {
    color: #059669;
    font-weight: 900;
}

/* Scrollbar styling */
.filter-modal-content .overflow-y-auto::-webkit-scrollbar {
    width: 8px;
}

.filter-modal-content .overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.filter-modal-content .overflow-y-auto::-webkit-scrollbar-thumb {
    background: black;
    border-radius: 4px;
}

.filter-modal-content .overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #333;
}

/* Enhanced input fields */
.filter-modal-content input[type="number"] {
    transition: all 0.2s ease;
    font-family: 'Courier New', monospace;
}

.filter-modal-content input[type="number"]:focus {
    transform: scale(1.02);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    background: #f9f9f9;
}

/* Better section spacing and visual hierarchy */
.filter-modal-content h3 {
    margin-bottom: 1rem;
}

/* Improved filter item styling */
.filter-modal-content .space-y-2 > div {
    border-radius: 4px;
    transition: all 0.15s ease;
}

.filter-modal-content .space-y-2 > div:hover {
    background: #f8f9fa;
    border-color: black;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* Action buttons enhancement */
.filter-modal-content button {
    font-family: system-ui, -apple-system, sans-serif;
    border-radius: 4px;
    letter-spacing: 0.05em;
}

.filter-modal-content button:active {
    transform: scale(0.98);
}

.filter-badge {
    position: absolute;
    top: -6px;
    right: -6px;
    background: white;
    color: black;
    border: 2px solid black;
    font-size: 11px;
    font-weight: 900;
    padding: 2px 6px;
    min-width: 18px;
    text-align: center;
    line-height: 1;
    border-radius: 50%;
    z-index: 10;
}

/* Mobile adjustments for filter badge and button */
@media (max-width: 767px) {
    .filter-badge {
        top: -4px;
        right: -4px;
        font-size: 9px;
        padding: 1px 4px;
        min-width: 14px;
        border-width: 1px;
    }
    
    #filter-btn {
        font-size: 0.75rem !important;
        padding: 0.5rem 0.75rem !important;
        min-width: 85px !important;
        gap: 0.25rem !important;
    }
    
    #filter-btn span {
        font-size: 0.75rem !important;
        letter-spacing: 0.025em;
    }
    
    /* Ensure button container fits properly */
    .relative {
        flex-shrink: 0;
    }
    
    /* Sort and filter row on mobile */
    .flex.flex-col.md\:flex-row.items-start.md\:items-center.justify-between {
        gap: 0.75rem !important;
        align-items: flex-start !important;
    }
    
    /* Sort and Show controls container on mobile */
    .flex.flex-row.md\:flex-row.items-start {
        gap: 0.75rem !important;
        width: 100% !important;
    }
    
    /* Individual sort/show controls on mobile */
    .flex.flex-col.gap-1.flex-1 {
        min-width: 0;
        flex: 1;
    }
    
    /* Filter button positioning on mobile */
    .relative {
        flex-shrink: 0;
        align-self: flex-end;
    }
}

/* Removed custom checkbox styles to fix Livewire compatibility */

@media (max-width: 768px) {
    .filter-modal-content {
        width: 95%;
        max-height: 95vh;
    }
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

// Filter Modal Controls - Compatible with Lazy Loading
function initFilterModal() {
    window.openFilterModal = function() {
        const filterModal = document.getElementById('filter-modal');
        if (filterModal) {
            filterModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            document.body.classList.add('filter-modal-open');
        }
    };

    window.closeFilterModal = function() {
        const filterModal = document.getElementById('filter-modal');
        if (filterModal) {
            filterModal.style.display = 'none';
            document.body.style.overflow = 'auto';
            document.body.classList.remove('filter-modal-open');
        }
    };

    // Toggle filter groups with more than 4 items
    window.toggleFilterGroup = function(groupId) {
        const hiddenFilters = document.getElementById('hidden-filters-' + groupId);
        const toggleBtn = document.getElementById('toggle-btn-' + groupId);
        const toggleText = document.getElementById('toggle-text-' + groupId);
        
        if (hiddenFilters && toggleBtn && toggleText) {
            const isHidden = hiddenFilters.classList.contains('hidden');
            
            if (isHidden) {
                hiddenFilters.classList.remove('hidden');
                toggleText.textContent = 'Показати менше';
                toggleBtn.classList.add('bg-black', 'text-white');
                toggleBtn.classList.remove('bg-gray-100');
            } else {
                hiddenFilters.classList.add('hidden');
                const hiddenCount = hiddenFilters.children.length;
                toggleText.textContent = 'Показати ще +' + hiddenCount;
                toggleBtn.classList.remove('bg-black', 'text-white');
                toggleBtn.classList.add('bg-gray-100');
            }
        }
    };

    // Event listeners
    document.addEventListener('click', function(e) {
        // Close on overlay click
        if (e.target.id === 'filter-modal') {
            window.closeFilterModal();
        }
    });

    // Close on Escape key  
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            window.closeFilterModal();
        }
    });
}

// Initialize when DOM is ready OR when Livewire loads component
function ensureModalInit() {
    setTimeout(initFilterModal, 100); // Delay to ensure DOM is fully ready
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', ensureModalInit);
} else {
    ensureModalInit();
}

// Re-initialize after Livewire navigation
if (window.Livewire) {
    window.Livewire.on('navigated', initFilterModal);
}

// Listen for Livewire events
if (window.Livewire) {
    window.Livewire.on('page-updated', (data) => {
        if (data && data.title) {
            document.title = data.title;
        }
    });
    
    // Scroll to top when pagination changes
    window.Livewire.on('scroll-to-top', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    
    // Load more items without scrolling
    window.Livewire.on('items-loaded', () => {
        // Just a placeholder for future functionality if needed
        // Content will load without any scrolling behavior
    });
}
</script>
@endpush
