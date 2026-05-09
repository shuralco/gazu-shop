<?php

namespace App\Livewire\Cart;

use App\Helpers\Traits\CartTrait;
use Livewire\Attributes\On;
use Livewire\Component;

class Cart extends Component
{
    use CartTrait;

    #[On('cart-updated')]
    public function render()
    {
        return view('livewire.cart.cart', [
            'title' => 'Cart',
        ]);
    }
}
