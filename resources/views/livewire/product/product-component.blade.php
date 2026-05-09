<div>

    @section('metatags')
        <x-seo-meta 
            :model="$product"
            :pageType="'product'"
            :language="app()->getLocale()"
        />
    @endsection

    <!-- Main Content -->
    <div class="pt-4 md:pt-6">
        
        <!-- Breadcrumbs -->
        <div class="max-w-screen-2xl mx-auto px-2 md:px-8 mb-1 md:mb-2">
            <nav class="flex items-center gap-2 text-sm font-medium">
                <a wire:navigate href="{{ locale_route('home') }}" class="hover:underline font-bold">{{ __('general.home') }}</a>
                @foreach($breadcrumbs as $breadcrumb_slug => $breadcrumb_title)
                    <span class="text-black font-black">/</span>
                    <a wire:navigate href="{{ locale_url($breadcrumb_slug) }}" class="hover:underline font-bold uppercase">{{ $breadcrumb_title }}</a>
                @endforeach
            </nav>
        </div>
        
        <!-- Product Details -->
        <section class="max-w-screen-2xl mx-auto px-4 md:px-8 pb-8">
            <div class="grid lg:grid-cols-2 gap-8 lg:gap-16">
                
                <!-- Product Gallery -->
                <div>
                    <!-- Main Image -->
                    <div class="border-4 border-black bg-gray-100 aspect-square flex items-center justify-center mb-6 relative overflow-hidden">
                        <!-- Image skeleton loader -->
                        <div class="skeleton-shimmer absolute inset-0 z-10" wire:loading wire:target="$refresh"></div>
                        
                        @if($product->image)
                            <img src="{{ asset($product->getImage()) }}"
                                 alt="{{ $product->title }}"
                                 class="w-full h-full object-cover"
                                 width="800"
                                 height="800"
                                 id="mainProductImage"
                                 fetchpriority="high"
                                 decoding="async"
                                 wire:loading.class="hidden"
                                 wire:target="$refresh">
                        @else
                            <span class="text-8xl md:text-9xl" 
                                  id="mainProductImage"
                                  wire:loading.class="hidden" 
                                  wire:target="$refresh">📦</span>
                        @endif
                        
                        @if($product->is_new)
                            <span class="absolute top-4 left-4 bg-black text-white px-4 py-2 text-sm font-black">{{ __('general.new_badge') }}</span>
                        @endif

                        @if($product->is_hit)
                            <span class="absolute top-4 right-4 bg-red-600 text-white px-4 py-2 text-sm font-black">{{ __('general.hit_badge') }}</span>
                        @endif
                    </div>
                    
                    <!-- Thumbnails -->
                    @if($product->gallery && count($product->gallery) > 0)
                        <div class="flex gap-4 overflow-x-auto pb-2 gallery-scroll">
                            <div class="thumbnail active border-4 border-black bg-gray-100 aspect-square flex items-center justify-center cursor-pointer flex-shrink-0 w-20 h-20" onclick="changeMainImage('{{ asset($product->getImage()) }}', this)">
                                @if($product->image)
                                    <img src="{{ asset($product->getImage()) }}" alt="{{ $product->title }}" class="w-full h-full object-cover" width="80" height="80" loading="lazy">
                                @else
                                    <span class="text-xl">📦</span>
                                @endif
                            </div>
                            @foreach($product->gallery as $index => $item)
                                @if($item)
                                <div class="thumbnail border-2 border-gray-300 bg-gray-100 aspect-square flex items-center justify-center cursor-pointer hover:border-black flex-shrink-0 w-20 h-20" onclick="changeMainImage('{{ asset($item) }}', this)">
                                    <img src="{{ asset($item) }}" alt="{{ $product->title }} - зображення {{ $index + 2 }}" class="w-full h-full object-cover" width="80" height="80" loading="lazy" decoding="async" onerror="this.style.display='none'; this.parentElement.innerHTML='<span class=\'text-xl\'>📦</span>';">
                                </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <!-- Single image thumbnail -->
                        @if($product->image)
                            <div class="flex gap-4">
                                <div class="thumbnail active border-4 border-black bg-gray-100 aspect-square flex items-center justify-center cursor-pointer w-20 h-20">
                                    <img src="{{ asset($product->getImage()) }}" alt="{{ $product->title }}" class="w-full h-full object-cover" width="80" height="80" loading="lazy">
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
                
                <!-- Product Info -->
                <div class="lg:pl-0">
                    <!-- Product Title -->
                    <h1 class="text-2xl sm:text-3xl md:text-5xl font-black text-black mb-4 md:mb-6 break-words">{{ strtoupper($product->title) }}</h1>
                    
                    <!-- Badges -->
                    <div class="flex gap-2 mb-4">
                        @if($product->is_new)
                            <span class="bg-green-600 text-white px-3 py-1 font-black text-sm">{{ __('general.new_badge') }}</span>
                        @endif
                        @if($product->is_hit)
                            <span class="bg-orange-600 text-white px-3 py-1 font-black text-sm">{{ __('general.hit_sale') }}</span>
                        @endif
                        @if($product->rating >= 4.5)
                            <span class="bg-yellow-500 text-black px-3 py-1 font-black text-sm">⭐ {{ $product->rating }}</span>
                        @endif
                    </div>
                    
                    <!-- Price + Quantity (Alpine.js for instant updates) -->
                    @php
                        $unitPrice = $variantPrice ?? (float) $product->price;
                        $oldUnitPrice = (float) ($product->old_price ?? 0);
                        $discount = $oldUnitPrice > 0 ? round(($oldUnitPrice - $unitPrice) / $oldUnitPrice * 100) : 0;
                    @endphp
                    <div x-data="{
                        qty: @entangle('quantity'),
                        up: {{ $unitPrice }},
                        op: {{ $oldUnitPrice }},
                        fmt(n) { return new Intl.NumberFormat('uk-UA', {maximumFractionDigits:0}).format(n) }
                    }">
                    <div class="mb-6 md:mb-8">
                        <div class="flex flex-wrap items-center gap-2 md:gap-4 mb-2 min-h-16 md:min-h-20">
                            <span class="text-3xl sm:text-4xl md:text-6xl font-black text-black" x-text="fmt(up * qty) + ' ₴'"></span>
                            @if($oldUnitPrice > 0)
                                <span class="text-xl sm:text-2xl md:text-3xl line-through text-gray-500 font-bold" x-text="fmt(op * qty) + ' ₴'"></span>
                                <span class="bg-red-600 text-white px-3 py-2 font-black text-sm flex-shrink-0">-{{ $discount }}%</span>
                            @else
                                <div class="h-8 md:h-10"></div>
                            @endif
                        </div>

                        <!-- Stock Status -->
                        @if($product->stock_status === 'in_stock' && $product->quantity > 0)
                            <p class="text-green-600 font-bold text-lg">{{ __('general.in_stock') }} ({{ $product->quantity }} {{ __('general.pcs') }})</p>
                        @elseif($product->stock_status === 'preorder')
                            <p class="text-blue-600 font-bold text-lg">{{ __('general.preorder') }}</p>
                        @else
                            <p class="text-red-600 font-bold text-lg">{{ __('general.out_of_stock') }}</p>
                        @endif

                        {{-- Per-warehouse availability (Phase 5 multi-warehouse) --}}
                        <x-warehouse-availability :product="$product" />

                        <div class="h-5">
                            <template x-if="qty > 1">
                                <p class="text-sm text-gray-600 font-medium">{{ __('general.price_per_unit') }}: <span x-text="fmt(up)"></span> ₴</p>
                            </template>
                        </div>
                        
                        <!-- SKU and Brand -->
                        <div class="mt-2 space-y-1">
                            @if($variantSku)
                                <p class="text-sm text-gray-600">{{ __('general.sku') }}: <span class="font-bold">{{ $variantSku }}</span></p>
                            @elseif($product->sku)
                                <p class="text-sm text-gray-600">{{ __('general.sku') }}: <span class="font-bold">{{ $product->sku }}</span></p>
                            @endif
                            @if($product->brandModel)
                                <p class="text-sm text-gray-600">{{ __('general.brand') }}: <span class="font-bold">{{ $product->brandModel->name }}</span></p>
                            @endif
                        </div>
                    </div>
                    
                    @if($product->excerpt && \App\Models\DisplaySetting::get('show_product_excerpt', true))
                        <div class="mb-8">
                            <p class="text-lg font-medium text-black leading-relaxed">{{ $product->excerpt }}</p>
                        </div>
                    @endif

                    {{-- Variant Options --}}
                    @if($product->options->count() > 0)
                    <div class="space-y-4 mb-6">
                        @foreach($product->options->where('is_active', true)->sortBy('sort_order') as $option)
                        <div>
                            <label class="font-black text-sm uppercase mb-2 block">{{ $option->name }}</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($option->values->where('is_active', true)->sortBy('sort_order') as $value)
                                    @if($option->type === 'color')
                                    <button wire:click="selectOption({{ $option->id }}, {{ $value->id }})"
                                        class="w-10 h-10 border-3 transition-all duration-100 {{ ($selectedOptions[$option->id] ?? null) == $value->id ? 'border-black ring-2 ring-black ring-offset-2' : 'border-gray-300 hover:border-black' }}"
                                        style="background-color: {{ $value->color_hex }}"
                                        title="{{ $value->value }}"
                                        aria-label="{{ $option->name }}: {{ $value->value }}">
                                    </button>
                                    @else
                                    <button wire:click="selectOption({{ $option->id }}, {{ $value->id }})"
                                        class="px-4 py-2 border-2 font-bold text-sm transition-all duration-100 {{ ($selectedOptions[$option->id] ?? null) == $value->id ? 'border-black bg-black text-white' : 'border-gray-300 hover:border-black' }}"
                                        aria-label="{{ $option->name }}: {{ $value->value }}">
                                        {{ $value->value }}
                                        @if($value->price_modifier != 0)
                                        <span class="text-xs">({{ $value->price_modifier > 0 ? '+' : '' }}{{ number_format($value->price_modifier, 0) }}₴)</span>
                                        @endif
                                    </button>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        @endforeach

                        @if($variantSku)
                        <div class="text-sm text-gray-600">{{ __('general.sku') }}: <strong>{{ $variantSku }}</strong></div>
                        @endif

                        @if(!$variantInStock)
                        <div class="bg-red-100 border-2 border-red-500 p-3 font-bold text-red-700 text-sm">{{ __('general.out_of_stock') }}</div>
                        @endif
                    </div>
                    @endif

                    <!-- Quantity Selector (Alpine.js) -->
                    <div class="mb-6 md:mb-8">
                        <div class="flex items-center">
                            <button @click="if(qty > 1) qty--" type="button" class="quantity-btn w-12 h-12 border-4 border-black bg-white text-black flex items-center justify-center font-black text-xl hover:bg-black hover:text-white transition-all duration-100 active:scale-95">−</button>
                            <div class="w-16 h-12 border-t-4 border-b-4 border-black bg-white flex items-center justify-center font-black text-xl text-black" x-text="qty"></div>
                            <button @click="qty++" type="button" class="quantity-btn w-12 h-12 border-4 border-black bg-white text-black flex items-center justify-center font-black text-xl hover:bg-black hover:text-white transition-all duration-100 active:scale-95">+</button>
                        </div>
                    </div>
                    
                    <!-- Add to Cart -->
                    <x-ui.button
                        size="lg"
                        class="w-full mb-3 md:mb-4"
                        wire:click="add2Cart({{ $product->id }}, true)"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50"
                    >
                        <span wire:loading.remove wire:target="add2Cart">{{ __('general.add_to_cart_full') }}</span>
                        <span wire:loading wire:target="add2Cart">{{ __('general.adding') }}</span>
                    </x-ui.button>

                    <!-- Quick Order -->
                    <x-ui.button
                        variant="secondary"
                        size="lg"
                        class="w-full mb-4"
                        wire:click="openQuickOrder"
                    >
                        {{ __('general.quick_order') }}
                    </x-ui.button>

                    <!-- Comparison Button -->
                    <div class="mb-4">
                        <livewire:product.comparison-button-component :product-id="$product->id" />
                    </div>
                    </div>{{-- Close Alpine price+qty scope --}}
                    <!-- Quick Info -->
                    <div class="border-4 border-black p-4 md:p-6 bg-gray-50">
                        <div class="flex">
                            <!-- Left side - advantages -->
                            <div class="flex-1 space-y-3 md:space-y-4">
                                <div class="flex items-start gap-4">
                                    <span class="text-2xl">🚚</span>
                                    <div>
                                        <p class="font-black text-black">{{ __('general.free_delivery_full') }}</p>
                                        <p class="text-sm font-medium">{{ __('general.free_delivery_from') }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-4">
                                    <span class="text-2xl">↩️</span>
                                    <div>
                                        <p class="font-black text-black">{{ __('general.return_30_days') }}</p>
                                        <p class="text-sm font-medium">{{ __('general.return_guarantee') }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-4">
                                    <span class="text-2xl">🛡️</span>
                                    <div>
                                        <p class="font-black text-black">{{ __('general.warranty_1_year') }}</p>
                                        <p class="text-sm font-medium">{{ __('general.warranty_quality') }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Right side - brand square -->
                            @if($product->brandModel)
                                <div class="flex items-center ml-6">
                                    <a wire:navigate href="{{ locale_url('brands/' . $product->brandModel->slug) }}"
                                       class="w-32 h-32 bg-white border-4 border-black flex items-center justify-center hover:bg-gray-100 transition-colors p-4"
                                       title="{{ $product->brandModel->name }}">
                                        @php
                                            $brandName = mb_strtoupper($product->brandModel->name);
                                            $nameLength = mb_strlen($brandName);
                                            $fontSize = $nameLength <= 4 ? 'text-xl' : ($nameLength <= 8 ? 'text-lg' : 'text-base');
                                        @endphp
                                        <span class="{{ $fontSize }} font-black text-black leading-tight text-center break-words hyphens-auto" style="word-break: break-word; hyphens: auto;">{{ $brandName }}</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Product Tabs -->
        <section class="max-w-screen-2xl mx-auto px-4 md:px-8 py-8 md:py-12">
            <div class="border-b-4 border-black mb-6 md:mb-8">
                <div class="flex gap-0 overflow-x-auto scrollbar-hide">
                    <button class="tab-btn px-3 sm:px-4 md:px-6 py-3 md:py-4 font-black text-sm sm:text-base text-black border-b-4 transition-all hover:border-black whitespace-nowrap {{ $activeTab === 'description' ? 'border-black' : 'border-transparent' }}" wire:click="setActiveTab('description')">{{ __('general.description') }}</button>
                    <button class="tab-btn px-3 sm:px-4 md:px-6 py-3 md:py-4 font-black text-sm sm:text-base text-black border-b-4 transition-all hover:border-black whitespace-nowrap {{ $activeTab === 'specs' ? 'border-black' : 'border-transparent' }}" wire:click="setActiveTab('specs')">{{ __('general.characteristics') }}</button>
                    <button class="tab-btn px-3 sm:px-4 md:px-6 py-3 md:py-4 font-black text-sm sm:text-base text-black border-b-4 transition-all hover:border-black whitespace-nowrap {{ $activeTab === 'reviews' ? 'border-black' : 'border-transparent' }}" wire:click="setActiveTab('reviews')">{{ __('general.reviews') }}</button>
                    <button class="tab-btn px-3 sm:px-4 md:px-6 py-3 md:py-4 font-black text-sm sm:text-base text-black border-b-4 transition-all hover:border-black whitespace-nowrap {{ $activeTab === 'delivery' ? 'border-black' : 'border-transparent' }}" wire:click="setActiveTab('delivery')">{{ __('general.delivery_tab') }}</button>
                </div>
            </div>
            
            <!-- Tab Contents -->
            <div id="description" class="tab-content {{ $activeTab === 'description' ? 'block' : 'hidden' }}">
                <div class="max-w-4xl">
                    <h2 class="text-3xl md:text-4xl font-black text-black mb-6 uppercase">{{ __('general.product_description') }}</h2>
                    
                    <!-- SEO Quick Manager for Admins -->
                    @auth
                        @if(auth()->user()->is_admin && \App\Models\DisplaySetting::get('seo_manager_visible', false))
                            <livewire:admin.seo-quick-manager :model="$product" />
                        @endif
                    @endauth
                    
                    <div class="prose prose-lg max-w-none">
                        {!! strip_tags($product->content, '<p><br><strong><em><ul><ol><li><h2><h3><h4><a><img><table><tr><td><th><thead><tbody>') !!}
                    </div>
                </div>
            </div>
            
            <div id="specs" class="tab-content {{ $activeTab === 'specs' ? 'block' : 'hidden' }}">
                <div class="max-w-4xl">
                    <h2 class="text-3xl md:text-4xl font-black text-black mb-6 uppercase">{{ __('general.characteristics') }}</h2>
                    <div class="border-4 border-black overflow-x-auto">
                        <div class="min-w-[300px]">
                        <!-- Main Characteristics -->
                        @if($product->sku)
                            <div class="flex bg-white border-b-2 border-black">
                                <div class="flex-1 p-3 md:p-4 font-black text-black text-sm md:text-base">{{ __('general.sku') }}</div>
                                <div class="flex-1 p-3 md:p-4 font-medium text-sm md:text-base">{{ $product->sku }}</div>
                            </div>
                        @endif
                        
                        @if($product->brandModel)
                            <div class="flex bg-gray-50 border-b-2 border-black">
                                <div class="flex-1 p-3 md:p-4 font-black text-black text-sm md:text-base">{{ __('general.brand') }}</div>
                                <div class="flex-1 p-3 md:p-4 font-medium text-sm md:text-base">{{ $product->brandModel->name }}</div>
                            </div>
                        @endif
                        
                        @if($product->manufacturer)
                            <div class="flex bg-white border-b-2 border-black">
                                <div class="flex-1 p-3 md:p-4 font-black text-black text-sm md:text-base">{{ __('general.manufacturer') }}</div>
                                <div class="flex-1 p-3 md:p-4 font-medium text-sm md:text-base">{{ $product->manufacturer }}</div>
                            </div>
                        @endif
                        
                        <!-- Filter Attributes -->
                        @if($attributes->isNotEmpty())
                            @foreach($attributes as $index => $attribute)
                                <div class="flex {{ $index % 2 == 0 ? 'bg-gray-50' : 'bg-white' }} border-b-2 border-black">
                                    <div class="flex-1 p-3 md:p-4 font-black text-black text-sm md:text-base">{{ $attribute->filter_groups_title }}</div>
                                    <div class="flex-1 p-3 md:p-4 font-medium text-sm md:text-base">{{ $attribute->filters_title }}</div>
                                </div>
                            @endforeach
                        @endif
                        
                        <!-- Physical Characteristics -->
                        @if($product->weight)
                            <div class="flex bg-white border-b-2 border-black">
                                <div class="flex-1 p-3 md:p-4 font-black text-black text-sm md:text-base">{{ __('general.weight') }}</div>
                                <div class="flex-1 p-3 md:p-4 font-medium text-sm md:text-base">{{ $product->weight }} {{ __('general.kg') }}</div>
                            </div>
                        @endif
                        
                        @if($product->dimensions)
                            <div class="flex bg-gray-50 border-b-2 border-black">
                                <div class="flex-1 p-3 md:p-4 font-black text-black text-sm md:text-base">{{ __('general.dimensions') }}</div>
                                <div class="flex-1 p-3 md:p-4 font-medium text-sm md:text-base">{{ $product->dimensions }}</div>
                            </div>
                        @endif
                        
                        <!-- Stock Info -->
                        <div class="flex bg-white border-b-2 border-black">
                            <div class="flex-1 p-3 md:p-4 font-black text-black text-sm md:text-base">{{ __('general.availability') }}</div>
                            <div class="flex-1 p-3 md:p-4 font-medium text-sm md:text-base">
                                @if($product->stock_status === 'in_stock')
                                    <span class="text-green-600 font-bold">{{ __('general.in_stock_qty', ['qty' => $product->quantity]) }}</span>
                                @elseif($product->stock_status === 'preorder')
                                    <span class="text-blue-600 font-bold">{{ __('general.under_order') }}</span>
                                @else
                                    <span class="text-red-600 font-bold">{{ __('general.not_available') }}</span>
                                @endif
                            </div>
                        </div>
                        
                        @if($product->min_quantity > 1)
                            <div class="flex bg-gray-50">
                                <div class="flex-1 p-4 font-black text-black">{{ __('general.min_order') }}</div>
                                <div class="flex-1 p-4 font-medium">{{ $product->min_quantity }} {{ __('general.pcs') }}</div>
                            </div>
                        @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <div id="reviews" class="tab-content {{ $activeTab === 'reviews' ? 'block' : 'hidden' }}">
                <div class="max-w-4xl">
                    <h2 class="text-3xl md:text-4xl font-black text-black mb-6 uppercase">{{ __('general.customer_reviews') }}</h2>
                    
                    <!-- Review Summary -->
                    @if($product->reviews_count > 0)
                    <div class="border-4 border-black p-6 bg-white mb-8">
                        <div class="flex items-center gap-4 mb-4">
                            <span class="text-5xl font-black text-black">{{ number_format($product->rating, 1) }}</span>
                            <div>
                                <div class="flex gap-1 mb-2">
                                    <span class="text-yellow-400 text-2xl">
                                        @for($i = 1; $i <= 5; $i++)
                                            {{ $i <= round($product->rating) ? '★' : '☆' }}
                                        @endfor
                                    </span>
                                </div>
                                <p class="text-lg font-medium text-black">{{ __('general.based_on_reviews', ['count' => $product->reviews_count]) }}</p>
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            @for($star = 5; $star >= 1; $star--)
                                @php
                                    $count = $ratingDistribution[$star] ?? 0;
                                    $percentage = $product->reviews_count > 0 ? ($count / $product->reviews_count) * 100 : 0;
                                @endphp
                                <div class="flex items-center gap-3">
                                    <span class="text-sm font-bold w-3">{{ $star }}</span>
                                    <div class="flex-1 bg-gray-200 h-3 border-2 border-black">
                                        <div class="bg-yellow-400 h-full" style="width: {{ $percentage }}%"></div>
                                    </div>
                                    <span class="text-sm font-medium w-8">{{ $count }}</span>
                                </div>
                            @endfor
                        </div>
                    </div>
                    @else
                    <div class="border-4 border-black p-6 bg-white mb-8 text-center">
                        <p class="text-lg font-medium text-black">{{ __('general.no_reviews_yet') }}</p>
                        <p class="text-sm text-gray-600 mt-2">{{ __('general.be_first_review') }}</p>
                    </div>
                    @endif
                    
                    <!-- Individual Reviews -->
                    <div class="space-y-6">
                        @forelse($reviews as $review)
                            <div class="border-4 border-black p-6 bg-white" wire:key="review-{{ $review->id }}">
                                <div class="flex items-start justify-between mb-4">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h4 class="font-black text-black text-lg">{{ strtoupper($review->author_name) }}</h4>
                                            @if($review->is_verified_purchase)
                                                <span class="bg-green-600 text-white px-2 py-1 text-xs font-bold">{{ __('general.verified_purchase') }}</span>
                                            @endif
                                        </div>
                                        <div class="flex gap-1 my-2">
                                            <span class="text-yellow-400 text-lg">
                                                @for($i = 1; $i <= 5; $i++)
                                                    {{ $i <= $review->rating ? '★' : '☆' }}
                                                @endfor
                                            </span>
                                        </div>
                                    </div>
                                    <span class="text-sm text-gray-600 font-medium">{{ $review->formatted_date }}</span>
                                </div>
                                <p class="text-black font-medium leading-relaxed">
                                    {{ $review->comment }}
                                </p>

                                {{-- Admin Reply --}}
                                @if($review->admin_reply)
                                    <div class="mt-4 ml-4 pl-4 border-l-4 border-black bg-gray-50 p-4">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="font-black text-sm text-black uppercase">{{ __('general.shop_reply') }}</span>
                                            @if($review->admin_replied_at)
                                                <span class="text-xs text-gray-500">{{ $review->admin_replied_at->format('d.m.Y') }}</span>
                                            @endif
                                        </div>
                                        <p class="text-black font-medium leading-relaxed text-sm">
                                            {{ $review->admin_reply }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="border-4 border-black p-6 bg-white text-center">
                                <p class="text-lg font-medium text-black">{{ __('general.no_reviews') }}</p>
                                <p class="text-sm text-gray-600 mt-2">{{ __('general.be_first_review_product') }}</p>
                            </div>
                        @endforelse
                        
                        <!-- Review Form -->
                        @if($showReviewForm)
                            <div class="border-4 border-black p-6 bg-gray-50">
                                <h3 class="text-xl font-black text-black mb-4 uppercase">{{ __('general.leave_review') }}</h3>
                                
                                <form wire:submit.prevent="submitReview">
                                    <!-- Rating -->  
                                    <div class="mb-4">
                                        <label class="block text-sm font-black text-black mb-2 uppercase">{{ __('general.rating') }} *</label>
                                        <div class="flex gap-2">
                                            @for($i = 1; $i <= 5; $i++)
                                                <button type="button" 
                                                        wire:click="$set('reviewRating', {{ $i }})"
                                                        class="text-3xl transition-colors {{ $reviewRating >= $i ? 'text-yellow-400' : 'text-gray-300' }}">
                                                    ★
                                                </button>
                                            @endfor
                                        </div>
                                    </div>
                                    
                                    <!-- Comment -->
                                    <div class="mb-4">
                                        <label class="block text-sm font-black text-black mb-2 uppercase">{{ __('general.comment') }} *</label>
                                        <textarea wire:model="reviewComment"
                                                  rows="4"
                                                  maxlength="1000"
                                                  placeholder="{{ __('general.review_placeholder') }}"
                                                  class="w-full px-4 py-3 border-2 border-black font-medium bg-white resize-none"></textarea>
                                        <p class="text-xs text-gray-600 mt-1">{{ __('general.min_chars') }}</p>
                                    </div>
                                    
                                    <div class="flex gap-4">
                                        <button type="submit" 
                                                wire:loading.attr="disabled"
                                                class="btn-black flex-1">
                                            <span wire:loading.remove wire:target="submitReview">{{ __('general.submit_review') }}</span>
                                            <span wire:loading wire:target="submitReview">{{ __('general.submitting') }}</span>
                                        </button>
                                        <button type="button"
                                                wire:click="toggleReviewForm"
                                                class="btn-white px-6">
                                            {{ __('general.cancel') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @else
                            <button wire:click="toggleReviewForm"
                                    class="btn-white w-full mt-6"
                                    type="button">
                                {{ __('general.leave_review') }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>
            
            <div id="delivery" class="tab-content {{ $activeTab === 'delivery' ? 'block' : 'hidden' }}">
                <div class="max-w-4xl">
                    <h2 class="text-3xl md:text-4xl font-black text-black mb-6 uppercase">{{ __('general.delivery_info') }}</h2>

                    <div class="grid md:grid-cols-2 gap-8">
                        <div>
                            <h3 class="text-2xl font-black text-black mb-4 uppercase">{{ __('general.delivery_methods') }}</h3>
                            <div class="space-y-4">
                                <div class="border-4 border-black p-6 bg-white">
                                    <h4 class="font-black text-black mb-2">🚚 {{ __('general.nova_poshta') }}</h4>
                                    <p class="font-bold">{{ __('general.nova_poshta_time') }} &bull; {{ __('general.nova_poshta_price') }}</p>
                                    <p class="text-sm font-medium mt-1">{{ __('general.nova_poshta_free') }}</p>
                                </div>
                                <div class="border-4 border-black p-6 bg-white">
                                    <h4 class="font-black text-black mb-2">📮 {{ __('general.ukrposhta') }}</h4>
                                    <p class="font-bold">{{ __('general.ukrposhta_time') }} &bull; {{ __('general.ukrposhta_price') }}</p>
                                    <p class="text-sm font-medium mt-1">{{ __('general.ukrposhta_desc') }}</p>
                                </div>
                                <div class="border-4 border-black p-6 bg-white">
                                    <h4 class="font-black text-black mb-2">🏃 {{ __('general.courier_kyiv') }}</h4>
                                    <p class="font-bold">{{ __('general.courier_time') }} &bull; {{ __('general.courier_price') }}</p>
                                    <p class="text-sm font-medium mt-1">{{ __('general.courier_desc') }}</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-2xl font-black text-black mb-4 uppercase">{{ __('general.payment_methods') }}</h3>
                            <div class="space-y-4">
                                <div class="border-4 border-black p-6 bg-white">
                                    <h4 class="font-black text-black mb-2">💳 {{ __('general.privat24') }}</h4>
                                    <p class="font-bold">{{ __('general.privat24_desc') }}</p>
                                    <p class="text-sm font-medium mt-1">{{ __('general.privat24_parts') }}</p>
                                </div>
                                <div class="border-4 border-black p-6 bg-white">
                                    <h4 class="font-black text-black mb-2">🏦 {{ __('general.monobank') }}</h4>
                                    <p class="font-bold">{{ __('general.monobank_desc') }}</p>
                                    <p class="text-sm font-medium mt-1">{{ __('general.monobank_parts') }}</p>
                                </div>
                                <div class="border-4 border-black p-6 bg-white">
                                    <h4 class="font-black text-black mb-2">💵 {{ __('general.cash') }}</h4>
                                    <p class="font-bold">{{ __('general.cash_desc') }}</p>
                                    <p class="text-sm font-medium mt-1">{{ __('general.cash_cod') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    @if(count($related_products))
        <section class="py-16 md:py-24 bg-gray-100">
            <div class="max-w-screen-2xl mx-auto px-4 md:px-8">
                <h2 class="text-3xl md:text-6xl font-black text-black mb-8 md:mb-16 text-center">{{ __('general.related_products') }}</h2>
                
                <div class="product-grid">
                    @foreach($related_products as $product)
                        <div wire:key="{{ $product->id }}">
                            @include('incs.brutal-product-card')
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <livewire:product.recently-viewed-component :exclude-id="$product->id" :limit="8" />

    <!-- Quick Order Modal -->
    <div class="fixed inset-0 bg-black bg-opacity-50 z-[9000] {{ $showQuickOrderModal ? 'flex' : 'hidden' }} items-center justify-center p-4">
        <div class="bg-white border-4 border-black max-w-md md:max-w-lg w-full p-6 md:p-8 relative">
            @if(!$quickOrderSuccess)
                <button wire:click="closeQuickOrder" class="absolute top-4 right-4 text-2xl font-black hover:text-red-600">&times;</button>
                
                <h3 class="text-2xl font-black text-black mb-6 uppercase text-center">{{ __('general.quick_order') }}</h3>
                
                <div class="border-2 border-black p-4 mb-6 bg-gray-50">
                    <h4 class="font-black text-black mb-2">{{ strtoupper($product->title) }}</h4>
                    <p class="font-bold text-black">{{ number_format($this->totalPrice, 0, ',', ' ') }} ₴</p>
                    <p class="text-sm font-medium text-gray-600">{{ __('general.quantity') }}: {{ $quantity }}</p>
                </div>
                
                <form wire:submit.prevent="quickOrder">
                    <div class="mb-4">
                        <label class="block text-sm font-black text-black mb-2 uppercase">{{ __('general.name') }} *</label>
                        <input type="text" wire:model="quickOrderName" required class="w-full px-4 py-3 border-2 border-black font-medium bg-white">
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-black text-black mb-2 uppercase">{{ __('general.phone') }} *</label>
                        <input type="tel" wire:model="quickOrderPhone" required placeholder="+38 (0XX) XXX-XX-XX" 
                               x-mask="+38 (099) 999-99-99" x-data
                               class="w-full px-4 py-3 border-2 border-black font-medium bg-white">
                    </div>
                    
                    <button type="submit" 
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-50"
                            class="btn-black w-full">
                        <span wire:loading.remove wire:target="quickOrder">{{ __('general.place_order') }}</span>
                        <span wire:loading wire:target="quickOrder">{{ __('general.processing') }}</span>
                    </button>
                    
                    <p class="text-xs text-gray-600 mt-4 text-center">
                        {{ __('general.manager_contact_short') }}
                    </p>
                </form>
            @else
                <!-- Success State -->
                <div class="text-center">
                    <!-- Success Icon with Animation -->
                    <div class="success-icon w-24 h-24 border-4 border-black mx-auto mb-6 flex items-center justify-center bg-white">
                        <span class="text-4xl">✓</span>
                    </div>
                    
                    <h3 class="text-2xl font-black text-black mb-6 uppercase">{{ __('general.order_placed') }}</h3>
                    
                    @if($successOrderId)
                    <div class="bg-black text-white px-4 py-3 text-center mb-6">
                        <p class="text-sm font-black mb-1">
                            {{ __('general.order_number') }}
                        </p>
                        <div class="flex items-center justify-center gap-3">
                            <p id="quickOrderNumber" class="text-lg font-black">
                                #SH-{{ date('Y') }}-{{ str_pad($successOrderId, 6, '0', STR_PAD_LEFT) }}
                            </p>
                            <button id="copyQuickBtn" class="bg-white text-black px-3 py-1 font-bold text-sm hover:bg-gray-100 transition-colors" onclick="copyQuickOrderNumber()">{{ __('general.copy') }}</button>
                        </div>
                    </div>
                    @endif
                    
                    <p class="text-sm font-medium text-black mb-6">
                        {{ __('general.manager_contact') }}
                    </p>

                    <button wire:click="closeQuickOrder" class="btn-black w-full">{{ __('general.close') }}</button>
                </div>
            @endif
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

/* Success Icon Animation */
.success-icon {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
}
</style>
@endpush

@push('scripts')
<script>
// Copy quick order number to clipboard
function copyQuickOrderNumber() {
    const orderNumberEl = document.getElementById('quickOrderNumber');
    const orderNumber = orderNumberEl ? orderNumberEl.textContent.trim() : '';
    const copyBtn = document.getElementById('copyQuickBtn');
    
    console.log('Copy button clicked', { orderNumberEl, orderNumber, copyBtn });
    
    if (orderNumber && copyBtn) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(orderNumber).then(() => {
                updateCopyButton(copyBtn);
                console.log('Order number copied to clipboard:', orderNumber);
            }).catch((err) => {
                console.log('Clipboard API failed, using fallback:', err);
                fallbackCopy(orderNumber, copyBtn);
            });
        } else {
            console.log('Clipboard API not available, using fallback');
            fallbackCopy(orderNumber, copyBtn);
        }
    } else {
        console.log('Missing elements for copy:', { orderNumber, copyBtn });
    }
}

function updateCopyButton(copyBtn) {
    const originalText = copyBtn.textContent;
    copyBtn.textContent = '{{ __('general.copied') }}';
    copyBtn.style.background = '#22c55e';
    copyBtn.style.color = 'white';
    
    setTimeout(() => {
        copyBtn.textContent = originalText;
        copyBtn.style.background = 'white';
        copyBtn.style.color = 'black';
    }, 2000);
}

function fallbackCopy(text, copyBtn) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    document.body.appendChild(textArea);
    textArea.select();
    document.execCommand('copy');
    document.body.removeChild(textArea);
    updateCopyButton(copyBtn);
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        // Let Livewire handle modal closing
        Livewire.dispatch('close-quick-order-modal');
    }
});

// Make function globally available
window.copyQuickOrderNumber = copyQuickOrderNumber;
</script>
@endpush
