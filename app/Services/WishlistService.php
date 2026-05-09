<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class WishlistService
{
    /**
     * Додати/видалити товар зі списку бажань
     *
     * @return bool true = додано, false = видалено
     */
    public function toggle(User $user, int $productId): bool
    {
        $existing = Wishlist::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            $existing->delete();

            return false;
        }

        Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $productId,
        ]);

        return true;
    }

    /**
     * Перевірити чи товар у списку бажань
     */
    public function isInWishlist(User $user, int $productId): bool
    {
        return Wishlist::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->exists();
    }

    /**
     * Отримати кількість товарів у списку бажань
     */
    public function getCount(User $user): int
    {
        return Wishlist::where('user_id', $user->id)->count();
    }

    /**
     * Отримати товари зі списку бажань (з пагінацією)
     */
    public function getProducts(User $user): LengthAwarePaginator
    {
        return $user->wishlistProducts()
            ->with('brandModel:id,name')
            ->orderByPivot('created_at', 'desc')
            ->paginate(12);
    }
}
