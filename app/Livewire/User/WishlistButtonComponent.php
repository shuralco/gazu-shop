<?php

namespace App\Livewire\User;

use App\Services\WishlistService;
use Livewire\Component;

class WishlistButtonComponent extends Component
{
    public int $productId;

    public bool $isInWishlist = false;

    public function mount(int $productId): void
    {
        $this->productId = $productId;
        if (auth()->check()) {
            $this->isInWishlist = app(WishlistService::class)->isInWishlist(auth()->user(), $productId);
        }
    }

    public function toggle(): void
    {
        if (! auth()->check()) {
            $this->redirect(locale_route('login'));

            return;
        }

        $this->isInWishlist = app(WishlistService::class)->toggle(auth()->user(), $this->productId);

        $message = $this->isInWishlist
            ? 'Товар додано до списку бажань'
            : 'Товар видалено зі списку бажань';

        $this->js("toastr.success('{$message}')");
    }

    public function render()
    {
        return view('livewire.user.wishlist-button-component');
    }
}
