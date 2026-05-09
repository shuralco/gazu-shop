<?php

namespace App\Livewire\User;

use App\Services\WishlistService;
use Livewire\Component;
use Livewire\WithPagination;

class WishlistComponent extends Component
{
    use WithPagination;

    public function removeFromWishlist(int $productId): void
    {
        app(WishlistService::class)->toggle(auth()->user(), $productId);
        $this->js("toastr.success('Товар видалено зі списку бажань')");
    }

    public function render()
    {
        return view('livewire.user.wishlist-component', [
            'title' => 'Список бажань',
            'products' => app(WishlistService::class)->getProducts(auth()->user()),
        ]);
    }
}
