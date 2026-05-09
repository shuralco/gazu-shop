@props(['product'])

@php
    $product->loadMissing(['brandModel', 'filters.filterGroup']);
    $url = locale_url($product->getLocalizedSlug());
    $showBadges = \App\Models\DisplaySetting::get('show_product_badges', true);
    $showBrand = \App\Models\DisplaySetting::get('show_brands_in_catalog', false);
    $showFilters = \App\Models\DisplaySetting::get('show_product_filters', true);
    $maxFilters = (int) \App\Models\DisplaySetting::get('max_product_filters_display', 3);
    $showAddToCart = \App\Models\DisplaySetting::get('show_add_to_cart_buttons', true);
@endphp

<x-ui.card class="product-card h-full flex flex-col relative" :padded="false">
    <a wire:navigate href="{{ $url }}" class="aspect-square w-full bg-gray-100 flex items-center justify-center overflow-hidden block relative">
        <div class="skeleton-shimmer absolute inset-0 z-10" wire:loading.flex wire:target="$refresh"></div>

        @if($product->image)
            <img src="{{ asset($product->getImage()) }}"
                 alt="{{ $product->title }}"
                 class="w-full h-full object-cover"
                 width="400" height="400"
                 loading="lazy" decoding="async"
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                 style="opacity: 1; transition: opacity 0.3s ease;">
            <div class="hidden items-center justify-center w-full h-full bg-gray-100">
                <span class="text-5xl md:text-6xl text-gray-400">📦</span>
            </div>
        @else
            <div class="flex items-center justify-center w-full h-full bg-gray-100">
                <span class="text-5xl md:text-6xl text-gray-400">📦</span>
            </div>
        @endif

        @if($showBadges)
            @if($product->is_new)
                <x-ui.badge variant="default" class="absolute top-4 left-4 z-10">
                    {{ __('general.new_badge') }}
                </x-ui.badge>
            @endif
            @if($product->is_hit)
                <x-ui.badge variant="danger" class="absolute top-4 right-4 z-10">
                    {{ __('general.hit_badge') }}
                </x-ui.badge>
            @endif
        @endif

        @if($showBrand && $product->brandModel)
            @php
                $brandName = mb_strtoupper($product->brandModel->name);
                $nameLength = mb_strlen($brandName);
                $fontSize = $nameLength <= 4 ? 'text-sm' : ($nameLength <= 8 ? 'text-xs' : 'text-[10px]');
            @endphp
            <a wire:navigate href="{{ locale_url('brands/' . $product->brandModel->slug) }}"
               class="absolute top-1/2 right-4 transform -translate-y-1/2 w-16 h-16 bg-white border-2 border-(--color-fg) flex items-center justify-center hover:bg-gray-100 transition-colors p-2 z-10"
               title="{{ $product->brandModel->name }}">
                <span class="{{ $fontSize }} font-black text-(--color-fg) leading-tight text-center break-words"
                      style="word-break: break-word; hyphens: auto;">{{ $brandName }}</span>
            </a>
        @endif
    </a>

    <div class="p-3 md:p-4 flex-grow flex flex-col justify-between">
        <div>
            <a wire:navigate href="{{ $url }}" class="hover:underline block mb-3">
                <h3 class="text-lg md:text-xl font-bold text-(--color-fg) line-clamp-2">{{ $product->title }}</h3>
            </a>

            @if($showFilters && $product->filters && $product->filters->count() > 0)
                <div class="mb-3 flex flex-wrap gap-2">
                    @foreach($product->filters->take($maxFilters) as $filter)
                        <span class="inline-block px-2 py-1 text-xs font-medium border border-(--color-fg)">
                            {{ $filter->filterGroup->title }}: {{ $filter->title }}
                        </span>
                    @endforeach
                    @if($product->filters->count() > $maxFilters)
                        <span class="inline-block px-2 py-1 text-xs font-medium border border-gray-400 text-gray-600">
                            +{{ $product->filters->count() - $maxFilters }}
                        </span>
                    @endif
                </div>
            @endif

            <p class="text-xl md:text-2xl font-black text-(--color-fg) mb-3">
                {{ number_format($product->price, 0, ',', ' ') }} ₴
            </p>
        </div>

        <div class="flex items-center gap-2 mt-auto">
            @if($showAddToCart)
                <x-ui.button
                    size="sm"
                    class="flex-1"
                    wire:click="add2Cart({{ $product->id }})"
                    wire:loading.attr="disabled"
                    wire:target="add2Cart({{ $product->id }})"
                >
                    <span wire:loading.remove wire:target="add2Cart({{ $product->id }})">{{ __('general.add_to_cart') }}</span>
                    <span wire:loading wire:target="add2Cart({{ $product->id }})">{{ __('general.adding') }}</span>
                </x-ui.button>
            @endif
            <livewire:product.comparison-button-component
                :product-id="$product->id"
                :wire:key="'cmp-'.$product->id" />
        </div>
    </div>
</x-ui.card>
