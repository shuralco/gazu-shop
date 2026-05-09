<?php

namespace App\Livewire\Cart;

use Livewire\Attributes\On;
use Livewire\Component;

class CartIconComponent extends Component
{
    /**
     * Fix for Livewire 3 toJSON error.
     * This method is called by JavaScript when trying to serialize the component.
     */
    public function toJSON(): string
    {
        return json_encode([
            'cartItemsCount' => \App\Helpers\Cart\Cart::getCartQuantityTotal(),
            'componentName' => 'cart-icon-component',
        ]);
    }

    #[On('cart-updated')]
    public function render()
    {
        return view('livewire.cart.cart-icon-component');
    }
}
