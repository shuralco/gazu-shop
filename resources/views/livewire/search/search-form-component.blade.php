<div class="relative" x-data="{ focused: false }">
    <form wire:submit="search" class="relative">
        <input type="text"
               wire:model.live.debounce.500ms="term"
               placeholder="{{ __('general.search_placeholder') }}"
               @focus="focused = true; $wire.focus()"
               @click.away="focused = false; $wire.blur()"
               aria-label="{{ __('general.search_placeholder') }}"
               autocomplete="off"
               class="w-40 lg:w-56 px-3 py-2 bg-white border-2 border-black text-sm font-medium focus:outline-none focus:bg-gray-50">

        @if($term)
            <button type="button"
                    wire:click="clearSearch"
                    aria-label="{{ __('general.clear') }}"
                    class="absolute right-2 top-1/2 transform -translate-y-1/2 text-black hover:text-gray-600">
                <i class="fa-solid fa-xmark"></i>
            </button>
        @endif
    </form>

    {{-- Popular searches when input is empty and focused --}}
    @if(empty($term) && $showPopular && count($popularSearches ?? []))
        <div class="absolute top-full left-0 right-0 bg-white border-2 border-black border-t-0 z-50 px-3 py-2">
            <span class="font-bold text-xs text-gray-500 uppercase tracking-wide">{{ __('general.popular_searches') }}:</span>
            <div class="flex flex-wrap gap-1 mt-1">
                @foreach($popularSearches as $ps)
                    <button
                        wire:click="selectPopular({{ Js::from($ps->query) }})"
                        type="button"
                        class="text-xs px-2 py-1 bg-gray-100 hover:bg-black hover:text-white border border-gray-300 transition-colors"
                    >{{ $ps->query }}</button>
                @endforeach
            </div>
        </div>
    @endif

    @if(count($search_categories) || count($search_brands) || count($search_results))
        <div class="absolute top-full left-0 right-0 bg-white border-2 border-black border-t-0 z-50 max-h-96 overflow-y-auto">

            {{-- Categories --}}
            @if(count($search_categories))
                <div class="px-4 py-2 bg-gray-50 border-b-2 border-black">
                    <span class="text-xs font-black text-gray-500">{{ mb_strtoupper(__('general.categories')) }}</span>
                </div>
                @foreach($search_categories as $cat)
                    <a wire:navigate href="{{ locale_url($cat->getLocalizedSlug()) }}"
                       class="block px-4 py-2 hover:bg-black hover:text-white border-b border-gray-100">
                        <div class="flex justify-between items-center">
                            <span class="font-medium">{{ $cat->title }}</span>
                            <span class="text-gray-400 text-sm">{{ $cat->products_count }} {{ __('general.products_label_count') }}</span>
                        </div>
                    </a>
                @endforeach
            @endif

            {{-- Brands --}}
            @if(count($search_brands))
                <div class="px-4 py-2 bg-gray-50 border-b-2 border-black">
                    <span class="text-xs font-black text-gray-500">{{ mb_strtoupper(__('general.brands')) }}</span>
                </div>
                @foreach($search_brands as $brand)
                    <a wire:navigate href="{{ locale_route('brand', ['brand' => $brand->slug]) }}"
                       class="block px-4 py-2 hover:bg-black hover:text-white border-b border-gray-100">
                        <div class="flex justify-between items-center">
                            <span class="font-medium">{{ $brand->name }}</span>
                            <span class="text-gray-400 text-sm">{{ $brand->products_count }} {{ __('general.products_label_count') }}</span>
                        </div>
                    </a>
                @endforeach
            @endif

            {{-- Products --}}
            @if(count($search_results))
                <div class="px-4 py-2 bg-gray-50 border-b-2 border-black">
                    <span class="text-xs font-black text-gray-500">{{ mb_strtoupper(__('general.products_label')) }}</span>
                </div>
                @foreach($search_results as $product)
                    <a wire:navigate href="{{ locale_url($product->getLocalizedSlug()) }}"
                       wire:click="trackClick({{ $product->id }}, {{ Js::from($term) }})"
                       class="block px-4 py-3 hover:bg-black hover:text-white border-b border-gray-200 last:border-b-0">
                        <div class="flex justify-between items-center">
                            <span class="font-medium">{{ $product->title }}</span>
                            <span class="font-bold">{{ formatPrice($product->price) }}</span>
                        </div>
                    </a>
                @endforeach
            @endif

        </div>
    @endif
</div>
