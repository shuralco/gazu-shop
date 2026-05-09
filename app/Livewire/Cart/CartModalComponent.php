<?php

namespace App\Livewire\Cart;

use App\Helpers\Traits\CartTrait;
use Livewire\Attributes\On;
use Livewire\Component;

class CartModalComponent extends Component
{
    use CartTrait;

    /**
     * Fix for Livewire 3 toJSON error.
     * This method is called by JavaScript when trying to serialize the component.
     */
    public function toJSON(): string
    {
        $cart = \App\Helpers\Cart\Cart::getCart();

        return json_encode([
            'cartItems' => $cart ? array_keys($cart) : [],
            'cartTotal' => \App\Helpers\Cart\Cart::getCartTotal(),
            'cartCount' => \App\Helpers\Cart\Cart::getCartQuantityTotal(),
            'componentName' => 'cart-modal-component',
        ]);
    }

    public function openModal(): void
    {
        $this->dispatch('cart-modal-opened');
    }

    public function closeModal(): void
    {
        $this->dispatch('cart-modal-closed');
    }

    // Override the trait method to remove toastr notifications
    public function updateItemQuantity(int $productId, int $quantity)
    {
        if ($quantity <= 0) {
            $quantity = 1;
        }
        if (\App\Helpers\Cart\Cart::updateItemQuantity($productId, $quantity)) {
            $this->dispatch('cart-updated');
            // No toastr notification - keep modal open
        }
    }

    // Override removeFromCart to avoid toastr
    public function removeFromCart(int $productId): void
    {
        if (\App\Helpers\Cart\Cart::removeProductFromCart($productId)) {
            $this->dispatch('cart-updated');
            // No toastr notification - keep modal open
        }
    }

    public function increaseQuantity($id)
    {
        $cart = \App\Helpers\Cart\Cart::getCart();
        if (isset($cart[$id])) {
            $newQuantity = $cart[$id]['quantity'] + 1;
            \App\Helpers\Cart\Cart::updateItemQuantity($id, $newQuantity);
            $this->dispatch('cart-updated');
        }
    }

    public function decreaseQuantity($id)
    {
        $cart = \App\Helpers\Cart\Cart::getCart();
        if (isset($cart[$id]) && $cart[$id]['quantity'] > 1) {
            $newQuantity = $cart[$id]['quantity'] - 1;
            \App\Helpers\Cart\Cart::updateItemQuantity($id, $newQuantity);
            $this->dispatch('cart-updated');
        }
    }

    #[On('cart-updated')]
    public function render()
    {
        return view('livewire.cart.cart-modal-component');
    }
}
