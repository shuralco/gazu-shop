<!-- Brutal Cart Modal - Full Screen Overlay -->
<div id="cartModal" class="cart-modal" style="display: none;" wire:ignore.self>
    <div class="cart-content" wire:ignore.self>
        <!-- Header -->
        <div class="bg-black text-white p-6 flex justify-between items-center">
            <h2 class="text-2xl font-black">{{ __('general.your_cart') }}</h2>
            <button id="closeCart" wire:click="closeModal" onclick="closeCartModal()" class="text-white hover:text-gray-300 transition-colors">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <!-- Cart Items -->
        <div id="cartItems" class="flex-1 overflow-y-auto" style="max-height: calc(80vh - 200px);">
            @if($cart = \App\Helpers\Cart\Cart::getCart())
                @foreach($cart as $id => $item)
                    <div class="cart-item p-4 border-b-2 border-gray-200 last:border-b-0" wire:key="{{ $id }}" data-id="{{ $id }}">
                        <div class="flex items-start gap-4">
                            <!-- Product Image -->
                            <div class="w-16 h-16 bg-gray-100 border-2 border-black flex items-center justify-center flex-shrink-0">
                                @if($item['image'])
                                    <img src="{{ asset($item['image']) }}" alt="{{ $item['title'] }}" class="w-full h-full object-cover">
                                @else
                                    <span class="text-2xl">📦</span>
                                @endif
                            </div>
                            
                            <!-- Product Info -->
                            <div class="flex-1 min-w-0">
                                <h3 class="text-base font-bold text-black leading-tight mb-1">{{ $item['title'] }}</h3>
                                <p class="text-lg font-black text-black mb-3">{{ number_format($item['price'], 0, ',', ' ') }} ₴</p>
                                
                                <!-- Controls Row -->
                                <div class="flex justify-end items-center gap-2">
                                    <!-- Quantity Controls -->
                                    <div class="flex items-center gap-0">
                                        <button class="w-8 h-8 bg-white border-2 border-black text-black font-black text-lg flex items-center justify-center hover:bg-black hover:text-white transition-colors" 
                                                wire:click="decreaseQuantity({{ $id }})"
                                                wire:loading.attr="disabled">−</button>
                                        <input type="number" class="w-12 h-8 border-2 border-l-0 border-r-0 border-black text-center font-bold bg-white text-black focus:outline-none" value="{{ $item['quantity'] }}" readonly id="qty-{{ $id }}">
                                        <button class="w-8 h-8 bg-white border-2 border-black text-black font-black text-lg flex items-center justify-center hover:bg-black hover:text-white transition-colors" 
                                                wire:click="increaseQuantity({{ $id }})"
                                                wire:loading.attr="disabled">+</button>
                                    </div>
                                    
                                    <!-- Delete Button -->
                                    <button class="w-8 h-8 bg-white border-2 border-black text-black font-black text-lg flex items-center justify-center hover:bg-black hover:text-white transition-colors" 
                                            wire:click="removeFromCart({{ $id }})"
                                            wire:loading.attr="disabled">×</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
        
        <!-- Empty Cart Message -->
        <div id="emptyCart" class="@if(\App\Helpers\Cart\Cart::getCart()) hidden @endif flex-1 flex items-center justify-center p-8 text-center">
            <div>
                <div class="w-20 h-20 border-4 border-black mx-auto mb-4 flex items-center justify-center">
                    <span class="text-3xl">🛒</span>
                </div>
                <h3 class="text-xl font-black text-black mb-2">{{ __('general.cart_empty') }}</h3>
                <p class="text-base font-medium text-black mb-4">{{ __('general.cart_add_items_checkout') }}</p>
                <button class="btn-black" wire:click="closeModal" onclick="closeCartModal()">{{ __('general.continue_shopping') }}</button>
            </div>
        </div>
        
        <!-- Cart Summary -->
        <div id="cartSummary" class="@if(!\App\Helpers\Cart\Cart::getCart()) hidden @endif border-t-2 border-black bg-gray-50 p-6">
            <div class="flex justify-between items-center mb-4">
                <span class="text-lg font-black text-black">{{ __('general.cart_total') }}</span>
                <span class="text-xl font-black text-black">{{ number_format(\App\Helpers\Cart\Cart::getCartTotal(), 0, ',', ' ') }} ₴</span>
            </div>
            
            <!-- Single full-width checkout button -->
            <button class="btn-black w-full text-base py-4 font-black mb-4" onclick="goToCheckout()">{{ __('general.place_order') }}</button>
            
            <p class="text-xs text-gray-600 text-center">
                {{ __('general.cart_free_delivery_note') }}
            </p>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Cart Modal - Full Screen Overlay */
.cart-modal {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    background: rgba(0, 0, 0, 0.8) !important;
    z-index: 1000000 !important;
    display: none !important;
    opacity: 0 !important;
    transition: opacity 0.3s ease !important;
    align-items: center !important;
    justify-content: center !important;
    isolation: isolate !important;
}

.cart-modal.active {
    display: flex !important;
    opacity: 1 !important;
}

.cart-content {
    position: relative !important;
    background: white !important;
    border: 4px solid black !important;
    max-width: 600px !important;
    width: 90% !important;
    min-height: 300px !important;
    max-height: 80vh !important;
    transform: scale(0.9) !important;
    transition: transform 0.3s ease !important;
    overflow: hidden !important;
    display: flex !important;
    flex-direction: column !important;
    z-index: 999999 !important;
}

.cart-modal.active .cart-content {
    transform: scale(1) !important;
}

/* Cart Items */
.cart-item.removing {
    opacity: 0;
    transform: translateX(-100%);
}

/* Remove old quantity styles - now using inline Tailwind */

/* Button Styles */
.btn-black { 
    background: black; 
    color: white; 
    border: 2px solid black; 
    padding: 12px 24px; 
    font-weight: 700; 
    font-size: 16px; 
    transition: all 0.2s ease; 
    cursor: pointer; 
    text-decoration: none;
    display: inline-block;
}
.btn-black:hover { 
    background: white; 
    color: black; 
}

.btn-white { 
    background: white; 
    color: black; 
    border: 2px solid black; 
    padding: 12px 24px; 
    font-weight: 700; 
    font-size: 16px; 
    transition: all 0.2s ease; 
    cursor: pointer; 
    text-decoration: none;
    display: inline-block;
}
.btn-white:hover { 
    background: black; 
    color: white; 
}
</style>
@endpush

@push('scripts')
<script>
// Track modal state
if (typeof cartModalIsOpen === 'undefined') {
    var cartModalIsOpen = false;
}

// Keep modal open after Livewire updates
document.addEventListener('livewire:init', () => {
    Livewire.hook('commit', ({ component, commit, respond }) => {
        respond(() => {
            if (cartModalIsOpen) {
                const modal = document.getElementById('cartModal');
                if (modal) {
                    modal.classList.add('active');
                    modal.style.display = 'flex';
                    modal.style.opacity = '1';
                }
            }
        });
    });
});

// Livewire події
window.addEventListener('open-cart', event => {
    cartModalIsOpen = true;
    if (typeof window.openCartModal === 'function') {
        window.openCartModal();
    }
});

// Track close events
window.addEventListener('click', (e) => {
    const modal = document.getElementById('cartModal');
    if (e.target === modal || e.target.id === 'closeCart') {
        cartModalIsOpen = false;
    }
});

// Keep track of modal state when opening
if (typeof window.openCartModal === 'undefined') {
    window.openCartModal = function() {
        cartModalIsOpen = true;
        // Dispatch Livewire event to notify components
        Livewire.dispatch('cart-modal-opened');
        const modal = document.getElementById('cartModal');
        if (modal) {
            modal.classList.add('active');
            modal.style.display = 'flex';
            modal.style.opacity = '1';
        }
    };
}

// Keep track of modal state when closing
if (typeof window.closeCartModal === 'undefined') {
    window.closeCartModal = function() {
        cartModalIsOpen = false;
        // Dispatch Livewire event to notify components
        Livewire.dispatch('cart-modal-closed');
        const modal = document.getElementById('cartModal');
        if (modal) {
            modal.classList.remove('active');
            setTimeout(() => {
                if (!cartModalIsOpen) {
                    modal.style.display = 'none';
                }
            }, 300);
        }
    };
}
</script>
@endpush
