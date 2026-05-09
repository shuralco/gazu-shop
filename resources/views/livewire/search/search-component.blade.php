<div>
    @section('metatags')
        <title>{{ shopName() . ' :: ' . __('general.search') . ': ' . $query }}</title>
        <meta name="description" content="{{ __('general.search') }}: {{ $query }}">
    @endsection

    <style>
        .scroll-progress {
            position: fixed;
            right: 0;
            top: 0;
            width: 4px;
            height: 0%;
            background: black;
            z-index: 8000;
            transition: height 0.1s ease;
        }
        
        .filter-checkbox {
            appearance: none;
            width: 20px;
            height: 20px;
            border: 2px solid black;
            position: relative;
            cursor: pointer;
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
            font-weight: 700;
        }
        
        .search-suggestion {
            padding: 12px 16px;
            border-bottom: 1px solid #e0e0e0;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .search-suggestion:hover {
            background: black;
            color: white;
        }
        
        .search-tag {
            border: 2px solid black;
            padding: 8px 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .search-tag:hover {
            background: black;
            color: white;
        }
        
        .pagination-btn {
            border: 2px solid black;
            padding: 8px 16px;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        
        .pagination-btn:hover:not(.active) {
            background: black;
            color: white;
        }
        
        .pagination-btn.active {
            background: black;
            color: white;
        }
        
        .autocomplete-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 2px solid black;
            border-top: none;
            max-height: 400px;
            overflow-y: auto;
            display: none;
            z-index: 1200;
        }
        
        .autocomplete-dropdown.show {
            display: block;
        }
        
        .product-card {
            background: white;
            border: 2px solid black;
            overflow: hidden;
            transition: all 0.2s ease;
        }
        
        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 8px 8px 0 black;
        }

        /* Filter Modal */
        .filter-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            z-index: 2000;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .filter-modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .filter-modal-content {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.9);
            background: white;
            border: 4px solid black;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow: hidden;
            z-index: 2001;
            transition: transform 0.3s ease;
        }

        .filter-modal-overlay.active .filter-modal-content {
            transform: translate(-50%, -50%) scale(1);
        }

        .filter-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: white;
            color: black;
            border: 2px solid black;
            font-size: 12px;
            font-weight: 900;
            padding: 2px 6px;
            min-width: 20px;
            text-align: center;
        }

        @media (max-width: 768px) {
            .filter-modal-content {
                width: 95%;
                max-height: 95vh;
            }
        }
    </style>
    
    <div class="scroll-progress" id="scrollProgress"></div>
    
    <!-- Search Bar Section -->
    <section class="pt-32 md:pt-40 pb-8 bg-gray-100 border-b-4 border-black">
        <div class="max-w-screen-xl mx-auto px-4 md:px-8">
            <div class="max-w-4xl mx-auto">
                <div class="relative">
                    <input 
                        type="search" 
                        wire:model.live.debounce.300ms="query"
                        id="searchInput"
                        class="w-full px-6 py-4 pr-32 text-xl font-semibold border-4 border-black bg-white focus:outline-none focus:shadow-lg"
                        placeholder="{{ __('general.search_products') }}"
                    >
                    <button wire:click="search" class="absolute right-2 top-1/2 transform -translate-y-1/2 btn-black">
                        {{ __('general.search_btn') }}
                    </button>
                    
                    @if($suggestions && count($suggestions) > 0)
                    <!-- Autocomplete Dropdown -->
                    <div class="autocomplete-dropdown show" id="autocomplete">
                        @foreach($suggestions as $suggestion)
                        <div class="search-suggestion font-semibold" wire:click="selectSuggestion({{ Js::from($suggestion) }})">
                            <span class="text-gray-500">🔍</span> {{ $suggestion }}
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                
                <!-- Search Info -->
                @if($query)
                <div class="mt-6 flex items-center justify-between">
                    <p class="text-xl font-bold hidden md:block">
                        {{ __('general.search_found_text', ['total' => $products->total(), 'query' => $query]) }}
                    </p>
                    <button wire:click="clearSearch" class="text-lg font-semibold hover:underline">❌ {{ __('general.search_clear') }}</button>
                </div>
                @endif
            </div>
        </div>
    </section>
    
    @if($query && strlen($query) > 0)
    <div class="max-w-screen-2xl mx-auto px-4 md:px-8 py-8">
        
        <!-- Loading Indicator -->
        <div wire:loading wire:target.except="addToCart" class="border-4 border-black bg-black text-white p-4 mb-6 text-center">
            <div class="font-black">{{ __('general.search_loading') }}</div>
        </div>

        <!-- Filter Button and Sort Controls -->
        <div class="mb-6 md:mb-8">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-4 md:mb-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-4 w-full sm:w-auto">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-4">
                        <div class="flex items-center gap-2">
                            <span class="font-black text-sm sm:text-lg">{{ __('general.search_sort_by') }}</span>
                            <select wire:model.live="sortBy"
                                    class="border-2 border-black px-3 py-2 text-sm sm:text-base font-bold bg-white">
                                <option value="relevance">{{ __('general.search_sort_relevance') }}</option>
                                <option value="price_asc">{{ __('general.search_sort_price_asc') }}</option>
                                <option value="price_desc">{{ __('general.search_sort_price_desc') }}</option>
                                <option value="title_asc">{{ __('general.search_sort_title') }}</option>
                                <option value="hits">{{ __('general.search_sort_hits') }}</option>
                                <option value="newest">{{ __('general.search_sort_newest') }}</option>
                            </select>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-black text-sm sm:text-lg">{{ __('general.search_view') }}</span>
                            <select wire:model.live="viewMode"
                                    class="border-2 border-black px-3 py-2 text-sm sm:text-base font-bold bg-white">
                                <option value="grid">{{ __('general.search_view_grid') }}</option>
                                <option value="list">{{ __('general.search_view_list') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="relative">
                    <button id="filter-btn" 
                            onclick="openFilterModal()"
                            class="bg-black text-white font-black text-sm sm:text-lg px-4 sm:px-8 py-3 sm:py-4 hover:bg-white hover:text-black border-2 border-black transition-colors flex items-center gap-2 sm:gap-3">
                        <span class="text-base sm:text-lg">⚙️</span>
                        <span>{{ __('general.filters') }}</span>
                    </button>
                    @if((count($selectedCategories) + count($selectedBrands) + ($priceFrom ? 1 : 0) + ($priceTo ? 1 : 0)) > 0)
                        <div class="filter-badge">{{ count($selectedCategories) + count($selectedBrands) + ($priceFrom ? 1 : 0) + ($priceTo ? 1 : 0) }}</div>
                    @endif
                </div>
            </div>

            <!-- Selected Filters Display -->
            @if((count($selectedCategories) + count($selectedBrands) + ($priceFrom ? 1 : 0) + ($priceTo ? 1 : 0)) > 0)
                <div class="border-4 border-red-600 bg-red-50 p-3 md:p-4 mb-4 md:mb-6">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-black text-sm sm:text-base">{{ __('general.search_active_filters') }}</span>
                        @foreach($selectedCategories as $categoryId)
                            @php $category = $categories->firstWhere('id', $categoryId) @endphp
                            @if($category)
                                <button wire:click="$set('selectedCategories', {{ json_encode(array_values(array_diff($selectedCategories, [$categoryId]))) }})" 
                                        wire:key="selected-cat-{{ $categoryId }}"
                                        class="bg-red-600 text-white px-2 sm:px-3 py-1 text-xs sm:text-sm font-bold hover:bg-red-700 transition-colors">
                                    ✕ {{ $category->title }}
                                </button>
                            @endif
                        @endforeach
                        @foreach($selectedBrands as $brand)
                            <button wire:click="$set('selectedBrands', {{ json_encode(array_values(array_diff($selectedBrands, [$brand]))) }})" 
                                    wire:key="selected-brand-{{ $brand }}"
                                    class="bg-red-600 text-white px-2 sm:px-3 py-1 text-xs sm:text-sm font-bold hover:bg-red-700 transition-colors">
                                ✕ {{ $brand }}
                            </button>
                        @endforeach
                        @if($priceFrom)
                            <button wire:click="$set('priceFrom', null)" 
                                    class="bg-red-600 text-white px-2 sm:px-3 py-1 text-xs sm:text-sm font-bold hover:bg-red-700 transition-colors">
                                ✕ {{ __('general.search_from') }} {{ $priceFrom }}₴
                            </button>
                        @endif
                        @if($priceTo)
                            <button wire:click="$set('priceTo', null)" 
                                    class="bg-red-600 text-white px-2 sm:px-3 py-1 text-xs sm:text-sm font-bold hover:bg-red-700 transition-colors">
                                ✕ {{ __('general.search_to') }} {{ $priceTo }}₴
                            </button>
                        @endif
                        <button wire:click="clearSearch" class="bg-red-600 text-white px-2 sm:px-3 py-1 text-xs sm:text-sm font-bold hover:bg-red-700 transition-colors">
                            {{ __('general.search_clear_all') }}
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
                    <h2 class="font-black text-xl lg:text-2xl">{{ __('general.search_filter_products') }}</h2>
                    <button id="close-filter-modal" 
                            onclick="closeFilterModal()"
                            class="font-black text-2xl hover:bg-white hover:text-black px-3 py-1 transition-colors">×</button>
                </div>
                
                <!-- Modal Body -->
                <div class="p-6 space-y-8 max-h-96 overflow-y-auto position-relative">
                    <!-- Loading overlay -->
                    <div wire:loading class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-10">
                        <div class="text-black font-bold">{{ __('general.search_updating') }}</div>
                    </div>
                    
                    <!-- Price Range -->
                    <div>
                        <h3 class="font-black text-lg mb-4 border-b-2 border-black pb-2">{{ __('general.search_price') }}</h3>
                        <div class="flex gap-4 mb-4">
                            <input type="number" placeholder="{{ __('general.search_price_from') }}" 
                                   wire:model.live.debounce.500ms="priceFrom" 
                                   class="w-full border-2 border-black px-3 py-2 font-bold text-center">
                            <input type="number" placeholder="{{ __('general.search_price_to') }}" 
                                   wire:model.live.debounce.500ms="priceTo" 
                                   class="w-full border-2 border-black px-3 py-2 font-bold text-center">
                        </div>
                    </div>

                    <!-- Categories Filter -->
                    @if($categories && count($categories) > 0)
                        <div>
                            <h3 class="font-black text-lg mb-4 border-b-2 border-black pb-2">{{ __('general.search_categories') }}</h3>
                            <div class="space-y-3">
                                @foreach($categories as $category)
                                    <div wire:key="category-{{ $category->id }}" class="flex items-center p-2">
                                        <input type="checkbox" 
                                               id="category-checkbox-{{ $category->id }}"
                                               wire:model.live="selectedCategories"
                                               value="{{ $category->id }}"
                                               class="w-5 h-5 border-2 border-black mr-3">
                                        <label for="category-checkbox-{{ $category->id }}" 
                                               class="cursor-pointer font-medium flex-1 select-none">
                                            {{ $category->title }} ({{ $category->products_count ?? 0 }})
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Brands Filter -->
                    @if($brands && count($brands) > 0)
                        <div>
                            <h3 class="font-black text-lg mb-4 border-b-2 border-black pb-2">{{ __('general.search_brands') }}</h3>
                            <div class="space-y-3">
                                @foreach($brands as $brand => $count)
                                    <div wire:key="brand-{{ $brand }}" class="flex items-center p-2">
                                        <input type="checkbox" 
                                               id="brand-checkbox-{{ $brand }}"
                                               wire:model.live="selectedBrands"
                                               value="{{ $brand }}"
                                               class="w-5 h-5 border-2 border-black mr-3">
                                        <label for="brand-checkbox-{{ $brand }}" 
                                               class="cursor-pointer font-medium flex-1 select-none">
                                            {{ $brand }} ({{ $count }})
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
                
                <!-- Modal Footer -->
                <div class="border-t-4 border-black p-6 bg-white">
                    <div class="flex flex-col sm:flex-row gap-4">
                        <button wire:click="clearSearch" 
                                class="flex-1 border-2 border-black font-black py-4 hover:bg-black hover:text-white transition-colors">
                            {{ __('general.search_clear_all') }}
                        </button>
                        <button id="apply-filter-modal" 
                                onclick="closeFilterModal()"
                                class="flex-1 bg-black text-white font-black py-4 hover:bg-white hover:text-black border-2 border-black transition-colors">
                            {{ __('general.search_apply_filters') }}
                        </button>
                    </div>
                    <div class="text-center mt-4">
                        <span class="font-medium text-sm">{{ __('general.search_found_products') }} <span class="font-black">{{ $products->total() }}</span></span>
                    </div>
                </div>
            </div>
        </div>
                
        @if($products && $products->count() > 0)
            @if($correctedQuery)
            <!-- Typo Correction Hint -->
            <div class="mb-6 text-lg">
                <span class="text-gray-500">{{ __('general.showing_results_for') }}</span>
                <span class="font-black">&laquo;{{ $correctedQuery }}&raquo;</span>
            </div>
            @endif

            <!-- Products Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-12" id="products">
                @foreach($products as $product)
                    <div wire:key="{{ $product->id }}">
                        @include('incs.brutal-product-card')
                    </div>
                @endforeach
            </div>
                
            <!-- Pagination -->
            <div class="flex justify-center items-center gap-2 mb-12">
                {{ $products->links('pagination.brutal-pagination', data: ['scrollTo' => '#products']) }}
            </div>
                
        @else
            <!-- No Results State -->
            <div class="text-center py-12 md:py-16">
                <div class="text-6xl sm:text-8xl md:text-9xl mb-6 md:mb-8">🔍</div>
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-black text-black mb-3 md:mb-4 px-4">{{ __('general.search_not_found') }}</h2>
                <p class="text-base sm:text-lg text-gray-600 mb-6 md:mb-8 px-4">{{ __('general.try_change_query') }}</p>
                <button wire:click="clearSearch" class="bg-black text-white font-black px-4 sm:px-6 py-2 sm:py-3 text-sm sm:text-base border-2 border-black hover:bg-white hover:text-black transition-colors">
                    {{ __('general.search_clear_filters') }}
                </button>
            </div>

            {{-- Expanded results (query minus last word) --}}
            @if($expandedResults && count($expandedResults) > 0)
            <div class="mb-12">
                <h3 class="text-xl sm:text-2xl font-black mb-6 border-b-4 border-black pb-3">
                    {{ __('general.results_for_short') }} &laquo;{{ $expandedQuery }}&raquo;:
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach($expandedResults as $product)
                        <div wire:key="expanded-{{ $product->id }}">
                            @include('incs.brutal-product-card')
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Popular categories --}}
            @if($popularCategories && count($popularCategories) > 0)
            <div class="mb-12">
                <h3 class="text-xl sm:text-2xl font-black mb-6 border-b-4 border-black pb-3">
                    {{ __('general.search_popular_categories') }}
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach($popularCategories as $category)
                    <button wire:click="searchByCategory({{ Js::from($category->slug) }})"
                            wire:key="pop-cat-{{ $category->id }}"
                            class="border-4 border-black p-6 hover:bg-black hover:text-white transition-all text-center">
                        <div class="text-3xl mb-2">{{ $category->icon ?? '📦' }}</div>
                        <div class="font-bold">{{ mb_strtoupper($category->title) }}</div>
                        <div class="text-sm mt-1">{{ $category->products_count ?? 0 }} {{ __('general.search_products_count') }}</div>
                    </button>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Recommended products --}}
            @if($recommendedProducts && count($recommendedProducts) > 0)
            <div class="mb-12">
                <h3 class="text-xl sm:text-2xl font-black mb-6 border-b-4 border-black pb-3">
                    {{ __('general.you_may_be_interested') }}
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach($recommendedProducts as $product)
                        <div wire:key="rec-{{ $product->id }}">
                            @include('incs.brutal-product-card')
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        @endif
    </div>
    @endif
    
    @if(!$query || strlen($query) === 0)
    <!-- Default Search State -->
    <section class="pt-32 md:pt-40 pb-16 bg-white">
        <div class="max-w-screen-xl mx-auto px-4 md:px-8 text-center">
            <span class="text-8xl block mb-6">🔍</span>
            <h1 class="text-5xl md:text-8xl font-black text-black mb-6 md:mb-8 leading-none">
                {{ __('general.search_title') }}
            </h1>
            <p class="text-lg md:text-2xl text-black mb-8 md:mb-16 font-medium max-w-3xl mx-auto">
                {{ __('general.search_subtitle') }}
            </p>
            
            <!-- Popular Categories -->
            <div class="max-w-4xl mx-auto">
                <h2 class="text-2xl font-black mb-8">{{ __('general.search_popular_categories') }}</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach($popularCategories ?? [] as $category)
                    <button wire:click="searchByCategory({{ Js::from($category->slug) }})" 
                            class="border-4 border-black p-6 hover:bg-black hover:text-white transition-all">
                        <div class="text-3xl mb-2">{{ $category->icon ?? '📦' }}</div>
                        <div class="font-bold">{{ strtoupper($category->title) }}</div>
                        <div class="text-sm mt-1">{{ $category->products_count ?? 0 }} {{ __('general.search_products_count') }}</div>
                    </button>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    @endif

    <script>
        // Scroll Progress
        window.addEventListener('scroll', () => {
            const scrollHeight = document.documentElement.scrollHeight - window.innerHeight;
            const scrollPosition = window.scrollY;
            const progress = (scrollPosition / scrollHeight) * 100;
            document.getElementById('scrollProgress').style.height = progress + '%';
        });

        // Filter Modal Controls
        window.openFilterModal = function() {
            const filterModal = document.getElementById('filter-modal');
            if (filterModal) {
                filterModal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        };

        window.closeFilterModal = function() {
            const filterModal = document.getElementById('filter-modal');
            if (filterModal) {
                filterModal.classList.remove('active');
                document.body.style.overflow = 'auto';
            }
        };

        // Event delegation for modal controls
        document.addEventListener('click', function(e) {
            // Close filter modal
            if (e.target.id === 'close-filter-modal' || e.target.id === 'apply-filter-modal') {
                e.preventDefault();
                window.closeFilterModal();
            }
            
            // Close on overlay click
            if (e.target.id === 'filter-modal' && e.target.classList.contains('filter-modal-overlay')) {
                window.closeFilterModal();
            }
        });

        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                window.closeFilterModal();
            }
        });
        
        // Search Autocomplete
        const searchInput = document.getElementById('searchInput');
        const autocomplete = document.getElementById('autocomplete');
        
        if (searchInput && autocomplete) {
            searchInput.addEventListener('focus', () => {
                if (searchInput.value.length > 0) {
                    autocomplete.classList.add('show');
                }
            });
            
            searchInput.addEventListener('input', () => {
                if (searchInput.value.length > 0) {
                    autocomplete.classList.add('show');
                } else {
                    autocomplete.classList.remove('show');
                }
            });
            
            document.addEventListener('click', (e) => {
                if (!searchInput.contains(e.target) && !autocomplete.contains(e.target)) {
                    autocomplete.classList.remove('show');
                }
            });
        }

        // Listen for Livewire events
        if (window.Livewire) {
            window.Livewire.on('page-updated', (data) => {
                if (data && data.title) {
                    document.title = data.title;
                }
            });
        }
    </script>
</div>

@if(session('success'))
    @script
    <script>
        toastr.success('{{ session('success') }}')
    </script>
    @endscript
@endif