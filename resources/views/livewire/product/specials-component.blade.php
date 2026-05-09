<div>
    @section('metatags')
        @php
            $specialsSeo = \App\Models\SeoMeta::where('page_type', 'specials')->where('language', app()->getLocale())->first();
        @endphp
        
        @if($specialsSeo)
            <x-seo-meta 
                :title="$specialsSeo->meta_title"
                :description="$specialsSeo->meta_description"
                :keywords="is_array($specialsSeo->meta_keywords) ? implode(', ', $specialsSeo->meta_keywords) : $specialsSeo->meta_keywords"
                :canonical="$specialsSeo->canonical_url"
                :robots="$specialsSeo->getRobotsDirective()"
                :pageType="'website'"
                :language="app()->getLocale()"
            />
        @else
            <title>{{ shopName() . ' :: ' . __('general.meta_specials') }}</title>
            <meta name="description" content="{{ __('general.meta_specials_description') }}">
        @endif
    @endsection


    <!-- Main Content -->
    <div class="pt-4 md:pt-6">
        
        <!-- Breadcrumbs -->
        <div class="max-w-screen-2xl mx-auto px-2 md:px-8 mb-1 md:mb-2">
            <nav class="flex items-center gap-2 text-sm font-medium">
                <a wire:navigate href="{{ locale_route('home') }}" class="hover:underline font-bold">{{ __('general.home') }}</a>
                <span class="text-black font-black">/</span>
                <span class="font-black text-black uppercase">{{ __('general.specials') }}</span>
            </nav>
        </div>
        
        <!-- Page Title -->
        <div class="max-w-screen-2xl mx-auto px-4 md:px-8 pb-8">
            <h1 class="text-4xl md:text-6xl font-black text-black mb-2">{{ __('general.special_offers_title') }}</h1>
            <p class="text-lg font-medium hidden md:block">{{ __('general.found_special_products', ['count' => $products->total()]) }}</p>
            
        </div>
        
        <div class="max-w-screen-2xl mx-auto px-4 md:px-8">
            
            <!-- Loading Indicator -->
            <div wire:loading wire:target.except="add2Cart" class="border-4 border-black bg-black text-white p-4 mb-6 text-center">
                <div class="font-black">{{ __('general.loading') }}</div>
            </div>

            <!-- Filter Button and Selected Filters -->
            <div class="mb-6 md:mb-8">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-4 md:mb-6">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-4 w-full sm:w-auto">
                        <span class="font-black text-sm sm:text-lg">{{ __('general.sort_by') }}</span>
                        <select wire:model.live="sort" wire:change="changeSort"
                                class="border-2 border-black px-3 py-2 text-sm sm:text-base font-bold bg-white w-full sm:w-auto">
                            @foreach($sortList as $k => $item)
                                <option value="{{ $k }}" wire:key="{{ $k }}">{{ $item['title'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="relative">
                        <button id="filter-btn" 
                                onclick="if(typeof openFilterModal === 'function') { openFilterModal(); } else { document.getElementById('filter-modal').classList.add('active'); document.body.style.overflow = 'hidden'; }"
                                class="bg-black text-white font-black text-sm sm:text-lg px-4 sm:px-8 py-3 sm:py-4 hover:bg-white hover:text-black border-2 border-black transition-colors flex items-center gap-2 sm:gap-3">
                            <span class="text-base sm:text-lg">⚙️</span>
                            <span>{{ __('general.filters') }}</span>
                        </button>
                        @if(count($selected_filters) > 0 || count($selected_categories) > 0)
                            <div class="filter-badge">{{ count($selected_filters) + count($selected_categories) }}</div>
                        @endif
                    </div>
                </div>

                @if($selected_filters || $selected_categories)
                    <!-- Selected Filters Display -->
                    <div class="border-4 border-red-600 bg-red-50 p-3 md:p-4 mb-4 md:mb-6">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-black text-sm sm:text-base">{{ __('general.active_filters_label') }}</span>
                            @foreach($categories as $category)
                                @if(in_array($category->id, $selected_categories))
                                    <button wire:click="removeCategory({{ $category->id }})" 
                                            wire:key="selected-cat-{{ $category->id }}"
                                            class="bg-blue-600 text-white px-2 sm:px-3 py-1 text-xs sm:text-sm font-bold hover:bg-blue-700 transition-colors">
                                        ✕ {{ $category->title }}
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

            <!-- Filter Modal -->
            <div id="filter-modal" class="filter-modal-overlay">
                <div class="filter-modal-content">
                    <!-- Modal Header -->
                    <div class="bg-black text-white p-6 flex items-center justify-between">
                        <h2 class="font-black text-xl lg:text-2xl">{{ __('general.filter_specials') }}</h2>
                        <button id="close-filter-modal" 
                                onclick="if(typeof closeFilterModal === 'function') { closeFilterModal(); } else { document.getElementById('filter-modal').classList.remove('active'); document.body.style.overflow = 'auto'; }"
                                class="font-black text-2xl hover:bg-white hover:text-black px-3 py-1 transition-colors">×</button>
                    </div>
                    
                    <!-- Modal Body -->
                    <div class="p-6 space-y-8 max-h-96 overflow-y-auto position-relative">
                        <!-- Loading overlay -->
                        <div wire:loading wire:target="toggleFilter,toggleCategory" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-10">
                            <div class="text-black font-bold">{{ __('general.updating') }}</div>
                        </div>
                        
                        <!-- Categories Filter -->
                        <div>
                            <h3 class="font-black text-lg mb-4 border-b-2 border-black pb-2">{{ __('general.categories') }}</h3>
                            <div class="space-y-3">
                                @foreach($categories as $category)
                                    <div wire:key="category-{{ $category->id }}" class="flex items-center p-2">
                                        <input type="checkbox" 
                                               id="category-checkbox-{{ $category->id }}"
                                               wire:click="toggleCategory({{ $category->id }})"
                                               @checked(in_array($category->id, $selected_categories))
                                               class="w-5 h-5 border-2 border-black mr-3">
                                        <label for="category-checkbox-{{ $category->id }}" 
                                               class="cursor-pointer font-medium flex-1 select-none">
                                            {{ $category->title }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        
                        <!-- Price Range -->
                        <div>
                            <h3 class="font-black text-lg mb-4 border-b-2 border-black pb-2">{{ __('general.price_label') }}</h3>
                            <div class="flex gap-4 mb-4">
                                <input type="number" placeholder="{{ __('general.price_from') }}"
                                       wire:model.live.debounce.500ms="min_price"
                                       value="{{ $min_price }}"
                                       class="w-full border-2 border-black px-3 py-2 font-bold text-center">
                                <input type="number" placeholder="{{ __('general.price_to') }}" 
                                       wire:model.live.debounce.500ms="max_price" 
                                       value="{{ $max_price }}"
                                       class="w-full border-2 border-black px-3 py-2 font-bold text-center">
                            </div>
                        </div>

                        <!-- Filter Groups -->
                        @foreach($filter_groups as $k => $filter_group)
                            <div wire:key="group-{{ $k }}">
                                <h3 class="font-black text-lg mb-4 border-b-2 border-black pb-2">{{ strtoupper($filter_group[0]->title) }}</h3>
                                <div class="space-y-3">
                                    @foreach($filter_group as $filter)
                                        <div wire:key="filter-{{ $filter->filter_id }}" class="flex items-center p-2">
                                            <input type="checkbox" 
                                                   id="filter-checkbox-{{ $filter->filter_id }}"
                                                   wire:click="toggleFilter({{ $filter->filter_id }})"
                                                   @checked(in_array($filter->filter_id, $selected_filters))
                                                   class="w-5 h-5 border-2 border-black mr-3">
                                            <label for="filter-checkbox-{{ $filter->filter_id }}" 
                                                   class="cursor-pointer font-medium flex-1 select-none">
                                                {{ $filter->filter_title }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Modal Footer -->
                    <div class="border-t-4 border-black p-6 bg-white">
                        <div class="flex flex-col sm:flex-row gap-4">
                            <button wire:click="clearFilters" 
                                    class="flex-1 border-2 border-black font-black py-4 hover:bg-black hover:text-white transition-colors">
                                {{ __('general.clear_all') }}
                            </button>
                            <button id="apply-filter-modal" 
                                    onclick="if(typeof closeFilterModal === 'function') { closeFilterModal(); } else { document.getElementById('filter-modal').classList.remove('active'); document.body.style.overflow = 'auto'; }"
                                    class="flex-1 bg-black text-white font-black py-4 hover:bg-white hover:text-black border-2 border-black transition-colors">
                                {{ __('general.apply_filters') }}
                            </button>
                        </div>
                        <div class="text-center mt-4">
                            <span class="font-medium text-sm">{{ __('general.found_products_count') }} <span class="font-black">{{ $products->total() }}</span></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Show per page -->
            <div class="flex justify-end items-center mb-6 md:mb-8 gap-2 sm:gap-4 whitespace-nowrap">
                <span class="font-black text-sm sm:text-base whitespace-nowrap">{{ __('general.show_label') }}</span>
                <select wire:model.live="limit" wire:change="changeLimit"
                        class="border-2 border-black px-3 py-2 text-sm sm:text-base font-bold bg-white">
                    @foreach($limitList as $k => $item)
                        <option value="{{ $k }}" wire:key="{{ $k }}">{{ $item }}</option>
                    @endforeach
                </select>
            </div>
            
            @if(count($products))
                <!-- Products Grid - 4 in a row -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-12" id="products">
                    @foreach($products as $product)
                        <div wire:key="{{ $product->id }}">
                            <x-ui.product-card :product="$product" />
                        </div>
                    @endforeach
                </div>
                
                <!-- Pagination -->
                <div class="flex justify-center items-center gap-2 mb-12">
                    {{ $products->links('pagination.brutal-pagination', data: ['scrollTo' => '#products']) }}
                </div>
            @else
                <!-- No Products Found -->
                <div class="border-4 border-black bg-white p-8 md:p-16 text-center mb-12">
                    <div class="text-6xl md:text-8xl mb-6">🛍️</div>
                    <h2 class="text-2xl md:text-4xl font-black mb-4">{{ __('general.no_specials') }}</h2>
                    <p class="text-lg font-medium mb-8">{{ __('general.no_specials_message') }}</p>
                    <button wire:click="clearFilters"
                            class="bg-black text-white font-black px-8 py-4 hover:bg-white hover:text-black border-2 border-black transition-colors">
                        {{ __('general.clear_filters') }}
                    </button>
                </div>
            @endif
            
            @if($selected_filters || $selected_categories)
                <button onclick="document.getElementById('filter-modal').classList.add('active'); document.body.style.overflow = 'hidden';"
                        class="fixed bottom-6 right-6 bg-black text-white font-black px-6 py-4 hover:bg-white hover:text-black border-2 border-black transition-colors z-40 shadow-lg">
                    {{ __('general.filters') }} ({{ count($selected_filters) + count($selected_categories) }})
                </button>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
/* Hide scrollbar for Chrome, Safari and Opera */
.hide-scrollbar::-webkit-scrollbar {
    display: none;
}

/* Hide scrollbar for IE, Edge and Firefox */
.hide-scrollbar {
    -ms-overflow-style: none;  /* IE and Edge */
    scrollbar-width: none;  /* Firefox */
}


/* Filter Modal */
.filter-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    z-index: 10001 !important;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.filter-modal-overlay.active {
    display: flex;
}

.filter-modal-content {
    background: white;
    border: 4px solid black;
    max-width: 800px;
    width: 100%;
    max-height: 90vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

/* Filter Badge */
.filter-badge {
    position: absolute;
    top: -8px;
    right: -8px;
    background: red;
    color: white;
    border: 2px solid white;
    font-size: 12px;
    font-weight: 900;
    padding: 2px 8px;
    min-width: 20px;
    text-align: center;
}

/* Mobile filter styles */
@media (max-width: 768px) {
    .filter-modal-content {
        max-height: 95vh;
        margin: 10px;
    }
    
    .filter-badge {
        top: -6px;
        right: -6px;
        padding: 1px 6px;
        font-size: 10px;
    }
}

/* Special product badges */
.special-badge-hit {
    background: linear-gradient(45deg, #ff6b6b, #ff5252);
    color: white;
    border: 2px solid white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

.special-badge-new {
    background: linear-gradient(45deg, #4caf50, #2e7d32);
    color: white;
    border: 2px solid white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

.special-badge-discount {
    background: linear-gradient(45deg, #ff9800, #f57c00);
    color: white;
    border: 2px solid white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.3);
}
</style>
@endpush

@push('scripts')
<script>

    // Filter modal functions
    function openFilterModal() {
        document.getElementById('filter-modal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeFilterModal() {
        document.getElementById('filter-modal').classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeFilterModal();
        }
    });

    // Close modal on overlay click
    document.getElementById('filter-modal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeFilterModal();
        }
    });
</script>
@endpush