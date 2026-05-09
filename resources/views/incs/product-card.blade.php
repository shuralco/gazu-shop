<div class="product-card">
    <div class="product-card-offer">
        @if($product->is_hit)
            <div class="offer-hit">Hit</div>
        @endif
        @if($product->is_new)
            <div class="offer-new">New</div>
        @endif
    </div>
    <div class="product-thumb relative" style="aspect-ratio: 1/1; overflow: hidden;">
        <!-- Skeleton for image -->
        <div class="skeleton-shimmer absolute inset-0 z-10" wire:loading wire:target="$refresh"></div>
        
        <a href="{{ locale_url($product->getLocalizedSlug()) }}" wire:navigate wire:loading.class="hidden" wire:target="$refresh">
            <img src="{{ asset($product->getImage()) }}" 
                 alt="{{ $product->title }}"
                 onload="this.style.opacity='1'; this.parentElement.previousElementSibling.style.display='none';"
                 style="opacity: 0; transition: opacity 0.3s ease; width: 100%; height: 100%; object-fit: cover;">
        </a>
    </div>
    <div class="product-details">        
        <h4>
            <a href="{{ locale_url($product->getLocalizedSlug()) }}" wire:navigate>{{ $product->title }}</a>
        </h4>
        <div class="product-bottom-details d-flex justify-content-between">
            <div class="product-price">
                <div>
                    @if($product->old_price)
                        <small><s>{{ formatPrice($product->old_price) }}</s></small>
                    @endif
                    <strong>{{ formatPrice($product->price) }}</strong>
                </div>
            </div>
            @if(\App\Models\DisplaySetting::get('show_add_to_cart_buttons', true))
            <div class="product-links">                
                <button wire:click="add2Cart({{ $product->id }})" 
                        onclick="this.style.background='#22c55e'; this.style.color='white'; this.innerHTML='✅'; setTimeout(() => { this.style.background=''; this.style.color=''; this.innerHTML='<i class=\'fas fa-shopping-cart\'></i>'; }, 400);"
                        class="btn btn-outline-secondary add-to-cart">
                    <i class="fas fa-shopping-cart"></i>
                </button>
            </div>
            @endif
        </div>
    </div>
</div>
