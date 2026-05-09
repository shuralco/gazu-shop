<div>
    @section('metatags')
        <title>{{ shopName() . ' :: ' . __('general.cart') }}</title>
        <meta name="description" content="{{ __('general.cart') }}">
    @endsection

    <!-- Brutal Cart Page -->
    <div class="cart-page-container" style="padding-top: 120px;">
    <style>
        @media (min-width: 768px) {
            .cart-page-container {
                padding-top: 140px !important;
            }
        }
    </style>
        <div class="max-w-screen-xl mx-auto px-4 md:px-8 py-8 md:py-16">
            <!-- Breadcrumb -->
            <nav class="mb-8">
                <div class="flex items-center space-x-2 text-sm font-bold">
                    <a wire:navigate href="{{ locale_route('home') }}" class="text-black hover:underline">{{ __('general.home') }}</a>
                    <span class="text-black">→</span>
                    <span class="text-gray-600">{{ __('general.cart') }}</span>
                </div>
            </nav>
            
            <!-- Page Title -->
            <div class="mb-8 md:mb-12">
                <h1 class="text-4xl md:text-6xl font-black text-black mb-4">{{ __('general.cart') }}</h1>
                <div class="w-20 md:w-32 h-1 bg-black"></div>
            </div>

            @if($cart = \App\Helpers\Cart\Cart::getCart())
                <div class="grid lg:grid-cols-3 gap-8 lg:gap-12">
                    <!-- Cart Items -->
                    <div class="lg:col-span-2">
                        <div class="space-y-6">
                            @foreach($cart as $id => $item)
                                <div class="cart-item-card" wire:key="{{ $id }}">
                                    <div class="flex gap-4 md:gap-6">
                                        <!-- Product Image -->
                                        <div class="cart-item-image-container">
                                            <img src="{{ asset($item['image']) }}" alt="{{ $item['title'] }}" 
                                                 class="cart-page-image">
                                        </div>
                                        
                                        <!-- Product Details -->
                                        <div class="flex-1 min-w-0">
                                            <h3 class="cart-item-title-page">
                                                <a wire:navigate href="{{ locale_url($item['slug']) }}" class="hover:underline">
                                                    {{ $item['title'] }}
                                                </a>
                                            </h3>
                                            
                                            <div class="flex flex-col md:flex-row md:items-center md:justify-between mt-4 gap-4">
                                                <div class="flex items-center gap-4">
                                                    <span class="text-2xl font-black text-black">{{ number_format($item['price'], 0, ',', ' ') }} ₴</span>
                                                </div>
                                                
                                                <!-- Quantity Controls -->
                                                <div class="flex items-center gap-3" x-data="{ qty: {{ $item['quantity'] }} }">
                                                    <span class="text-sm font-bold">{{ __('general.cart_quantity_label') }}</span>
                                                    <div class="flex items-center border-2 border-black">
                                                        <button class="w-10 h-10 bg-black text-white font-bold hover:bg-gray-800" 
                                                                x-on:click="qty = Math.max(1, qty - 1)">−</button>
                                                        <input type="number" x-model="qty" min="1" 
                                                               class="w-16 h-10 text-center border-0 font-bold focus:outline-none">
                                                        <button class="w-10 h-10 bg-black text-white font-bold hover:bg-gray-800" 
                                                                x-on:click="qty++">+</button>
                                                    </div>
                                                    <button class="btn-black-sm" 
                                                            x-on:click="$wire.updateItemQuantity({{ $id }}, qty)"
                                                            wire:loading.attr="disabled">
                                                        {{ __('general.cart_update') }}
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <div class="flex justify-between items-center mt-4 pt-4 border-t-2 border-gray-200">
                                                <span class="text-lg font-bold">{{ __('general.cart_subtotal') }} {{ number_format($item['quantity'] * $item['price'], 0, ',', ' ') }} ₴</span>
                                                <button class="cart-remove-btn" wire:click="removeFromCart({{ $id }})" wire:loading.attr="disabled">
                                                    {{ __('general.cart_remove') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <!-- Clear Cart -->
                        <div class="mt-8">
                            <button class="cart-clear-btn" wire:click="clearCart" wire:loading.attr="disabled">
                                {{ __('general.clear_cart') }}
                            </button>
                        </div>
                    </div>
                    
                    <!-- Cart Summary -->
                    <div class="lg:col-span-1">
                        <div class="cart-summary-card">
                            <h2 class="cart-summary-title">{{ __('general.cart_summary') }}</h2>
                            
                            <div class="cart-summary-row">
                                <span>{{ __('general.cart_items_count') }}</span>
                                <span>{{ \App\Helpers\Cart\Cart::getCartQuantityItems() }}</span>
                            </div>
                            
                            <div class="cart-summary-row">
                                <span>{{ __('general.cart_units_count') }}</span>
                                <span>{{ \App\Helpers\Cart\Cart::getCartQuantityTotal() }}</span>
                            </div>
                            
                            <div class="cart-summary-total">
                                <span>{{ __('general.cart_grand_total') }}</span>
                                <span>{{ formatPrice(\App\Helpers\Cart\Cart::getCartTotal()) }}</span>
                            </div>
                            
                            <a wire:navigate href="{{ locale_route('checkout') }}" class="btn-black w-full mt-6">
                                {{ __('general.place_order') }}
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <div class="cart-empty-state">
                    <div class="text-center py-16 md:py-24">
                        <div class="text-8xl md:text-9xl mb-8">🛒</div>
                        <h2 class="text-3xl md:text-4xl font-black text-black mb-4">{{ __('general.cart_empty') }}</h2>
                        <p class="text-lg text-gray-600 mb-8">{{ __('general.cart_add_items') }}</p>
                        <a wire:navigate href="{{ locale_route('home') }}" class="btn-black">
                            {{ __('general.continue_shopping') }}
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

@push('styles')
<style>
/* Brutal Cart Page Styles */
.cart-page-container {
    min-height: 100vh;
    padding-top: 120px;
    background: white;
}

.cart-item-card {
    background: white;
    border: 3px solid black;
    padding: 20px;
    margin-bottom: 20px;
}

.cart-item-image-container {
    width: 120px;
    height: 120px;
    border: 2px solid black;
    overflow: hidden;
    flex-shrink: 0;
}

.cart-page-image {
    width: 120px !important;
    height: 120px !important;
    max-width: 120px !important;
    max-height: 120px !important;
    min-width: 120px !important;
    min-height: 120px !important;
    object-fit: cover !important;
    display: block !important;
}

.cart-item-title-page {
    font-size: 20px;
    font-weight: 900;
    color: black;
    margin: 0;
    line-height: 1.4;
}

.cart-remove-btn {
    background: white;
    border: 2px solid black;
    color: black;
    padding: 8px 16px;
    font-weight: 900;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.cart-remove-btn:hover {
    background: black;
    color: white;
}

.cart-clear-btn {
    background: white;
    border: 2px solid red;
    color: red;
    padding: 12px 24px;
    font-weight: 900;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.cart-clear-btn:hover {
    background: red;
    color: white;
}

.cart-summary-card {
    background: white;
    border: 3px solid black;
    padding: 24px;
    position: sticky;
    top: 140px;
}

.cart-summary-title {
    font-size: 24px;
    font-weight: 900;
    color: black;
    margin: 0 0 20px 0;
    border-bottom: 2px solid black;
    padding-bottom: 12px;
}

.cart-summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
    font-weight: 600;
}

.cart-summary-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 0;
    border-top: 2px solid black;
    font-size: 20px;
    font-weight: 900;
    color: black;
    margin-top: 16px;
}

.cart-empty-state {
    text-align: center;
    padding: 80px 20px;
}

/* Override any conflicting styles */
.cart-item-card img,
.cart-page-image {
    width: 120px !important;
    height: 120px !important;
}

@media (max-width: 768px) {
    .cart-item-image-container {
        width: 80px;
        height: 80px;
    }
    
    .cart-page-image {
        width: 80px !important;
        height: 80px !important;
        max-width: 80px !important;
        max-height: 80px !important;
        min-width: 80px !important;
        min-height: 80px !important;
    }
}
</style>
@endpush

</div>
