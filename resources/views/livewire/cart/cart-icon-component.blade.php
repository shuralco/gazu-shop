<button class="cart-btn-brutal bg-black text-white border-2 border-black px-6 py-3 font-black text-lg uppercase hover:bg-white hover:text-black transition-colors flex items-center space-x-2 relative" onclick="console.log('Cart button clicked!'); if(typeof openCartModal === 'function') { openCartModal(); } else { alert('openCartModal function not found!'); }" type="button">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
        <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
    </svg>
    <span>{{ __('general.cart') }}</span>
    
    @if(\App\Helpers\Cart\Cart::getCartQuantityTotal() > 0)
        <div class="absolute -top-2 -right-2 bg-black text-white border-2 border-white font-black text-xs min-w-6 h-6 flex items-center justify-center">
            {{ \App\Helpers\Cart\Cart::getCartQuantityTotal() }}
        </div>
    @endif
</button>
