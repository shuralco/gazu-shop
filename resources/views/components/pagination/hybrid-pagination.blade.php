@props([
    'products' => null,
    'paginationInfo' => []
])

<div class="hybrid-pagination-container">
    @if($products && method_exists($products, 'hasPages'))
        
        <!-- Desktop: Traditional Pagination + Load More in one line -->
        <div class="hidden md:flex justify-between items-center gap-6 bg-white border-4 border-black p-4 mb-8">
            
            <!-- Traditional Pagination (Left Side) -->
            <div class="flex items-center gap-2">
                @if($products->onFirstPage())
                    <span class="pagination-btn border-2 border-gray-300 px-4 py-2 text-gray-400 cursor-not-allowed font-bold">
                        ← {{ __('general.prev') }}
                    </span>
                @else
                    <button wire:click="previousPage" 
                            wire:loading.attr="disabled"
                            class="pagination-btn border-2 border-black px-4 py-2 hover:bg-black hover:text-white font-bold transition-all">
                        ← {{ __('general.prev') }}
                    </button>
                @endif
                
                @foreach($products->getUrlRange(1, min(5, $products->lastPage())) as $page => $url)
                    @if($page == $products->currentPage())
                        <span class="pagination-btn bg-black text-white border-2 border-black px-4 py-2 font-bold">
                            {{ $page }}
                        </span>
                    @else
                        <button wire:click="gotoPage({{ $page }})" 
                                wire:loading.attr="disabled"
                                class="pagination-btn border-2 border-black px-4 py-2 hover:bg-black hover:text-white font-bold transition-all">
                            {{ $page }}
                        </button>
                    @endif
                @endforeach
                
                @if($products->lastPage() > 5)
                    <span class="px-2 font-bold">...</span>
                    <button wire:click="gotoPage({{ $products->lastPage() }})" 
                            wire:loading.attr="disabled"
                            class="pagination-btn border-2 border-black px-4 py-2 hover:bg-black hover:text-white font-bold transition-all">
                        {{ $products->lastPage() }}
                    </button>
                @endif
                
                @if($products->hasMorePages())
                    <button wire:click="nextPage" 
                            wire:loading.attr="disabled"
                            class="pagination-btn border-2 border-black px-4 py-2 hover:bg-black hover:text-white font-bold transition-all">
                        {{ __('general.next') }} →
                    </button>
                @else
                    <span class="pagination-btn border-2 border-gray-300 px-4 py-2 text-gray-400 cursor-not-allowed font-bold">
                        {{ __('general.next') }} →
                    </span>
                @endif
            </div>
            
            <!-- Load More Section (Right Side) -->
            <div class="flex items-center gap-4">
                <!-- Progress Info -->
                <div class="text-sm font-bold text-gray-600">
                    {{ __('general.showing_of', ['shown' => $paginationInfo['currentItemsCount'], 'total' => $paginationInfo['totalItems']]) }}
                </div>
                
                <!-- Load More Button -->
                @if($paginationInfo['showLoadMore'])
                    <button wire:click="loadMorePage" 
                            wire:loading.attr="disabled"
                            wire:target="loadMorePage"
                            class="bg-black text-white border-2 border-black px-6 py-3 hover:bg-white hover:text-black font-black transition-all disabled:opacity-50">
                        <span wire:loading.remove wire:target="loadMorePage">{{ __('general.show_more_count', ['count' => 25]) }}</span>
                        <span wire:loading wire:target="loadMorePage">{{ __('general.loading') }}</span>
                    </button>
                @else
                    <div class="text-sm font-bold text-gray-500">
                        {{ __('general.all_loaded') }}
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Mobile: Stacked Layout -->
        <div class="flex md:hidden flex-col gap-4 bg-white border-4 border-black p-4 mb-8">
            
            <!-- Page Info & Progress -->
            <div class="flex justify-between items-center bg-black text-white py-2 px-4 font-bold text-sm">
                <span>{{ __('general.page_of', ['current' => $products->currentPage(), 'total' => $products->lastPage()]) }}</span>
                <span>{{ $paginationInfo['currentItemsCount'] }}/{{ $paginationInfo['totalItems'] }}</span>
            </div>
            
            <!-- Navigation Controls -->
            <div class="flex justify-between items-center gap-2">
                @if($products->onFirstPage())
                    <span class="border-2 border-gray-300 px-3 py-2 text-gray-400 cursor-not-allowed font-bold text-sm">
                        ← {{ __('general.prev') }}
                    </span>
                @else
                    <button wire:click="previousPage" 
                            class="border-2 border-black px-3 py-2 hover:bg-black hover:text-white font-bold transition-all text-sm">
                        ← {{ __('general.prev') }}
                    </button>
                @endif
                
                <!-- Quick page numbers -->
                <div class="flex items-center gap-1">
                    @foreach($products->getUrlRange(1, min(3, $products->lastPage())) as $page => $url)
                        @if($page == $products->currentPage())
                            <span class="bg-black text-white border-2 border-black px-2 py-1 font-bold text-xs">
                                {{ $page }}
                            </span>
                        @else
                            <button wire:click="gotoPage({{ $page }})" 
                                    class="border-2 border-black px-2 py-1 hover:bg-black hover:text-white font-bold transition-all text-xs">
                                {{ $page }}
                            </button>
                        @endif
                    @endforeach
                </div>
                
                @if($products->hasMorePages())
                    <button wire:click="nextPage" 
                            class="border-2 border-black px-3 py-2 hover:bg-black hover:text-white font-bold transition-all text-sm">
                        {{ __('general.next') }} →
                    </button>
                @else
                    <span class="border-2 border-gray-300 px-3 py-2 text-gray-400 cursor-not-allowed font-bold text-sm">
                        {{ __('general.next') }} →
                    </span>
                @endif
            </div>
            
            <!-- Load More Button (Full Width) -->
            @if($paginationInfo['showLoadMore'])
                <button wire:click="loadMorePage" 
                        wire:loading.attr="disabled"
                        wire:target="loadMorePage"
                        class="bg-black text-white border-2 border-black py-3 hover:bg-white hover:text-black font-black transition-all w-full disabled:opacity-50">
                    <span wire:loading.remove wire:target="loadMorePage">{{ __('general.show_more_products', ['count' => 25]) }}</span>
                    <span wire:loading wire:target="loadMorePage">{{ __('general.loading') }}</span>
                </button>
            @else
                <div class="text-center text-gray-500 font-bold py-2">
                    {{ __('general.all_products_loaded') }}
                </div>
            @endif
        </div>
        
    @endif
</div>

<style>
    .pagination-btn {
        min-width: 44px;
        min-height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .hybrid-pagination-container .pagination-btn:hover {
        transform: translateY(-2px);
        box-shadow: 4px 4px 0 black;
    }
    
    @media (max-width: 768px) {
        .pagination-btn {
            min-width: 40px;
            min-height: 40px;
            font-size: 14px;
        }
    }
</style>