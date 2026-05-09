<?php

namespace App\Helpers\Cart;

use App\Models\Product;

class Cart
{
    // add product to cart - optimized version with variant support
    public static function add2Cart(int $productId, int $quantity = 1, ?int $variantId = null): bool
    {
        $cartKey = $variantId ? "{$productId}_v{$variantId}" : (string) $productId;

        // Fast path: if product already in cart, just update quantity
        if (session()->has("cart.{$cartKey}")) {
            session(["cart.{$cartKey}.quantity" => session("cart.{$cartKey}.quantity") + $quantity]);

            return true;
        }

        // Ultra-fast cache with 1-hour TTL for instant cart operations
        $cacheKey = "cart_product_{$productId}";
        $product = cache()->remember($cacheKey, 3600, function () use ($productId) {
            return Product::query()
                ->select('id', 'title', 'slug', 'image', 'price')
                ->find($productId);
        });

        if ($product) {
            $title = $product->title;
            $price = $product->price;
            $image = $product->getImage();

            if ($variantId) {
                $variant = \App\Models\ProductVariant::with('optionValues')->find($variantId);
                if ($variant) {
                    $title .= ' (' . $variant->getDisplayName() . ')';
                    $price = $variant->getEffectivePrice();
                    if ($variant->image) {
                        $image = $variant->image;
                    }
                }
            }

            $new_product = [
                'title' => $title,
                'slug' => $product->getLocalizedSlug(),
                'image' => $image,
                'price' => $price,
                'quantity' => $quantity,
                'variant_id' => $variantId,
            ];
            session(["cart.{$cartKey}" => $new_product]);

            return true;
        }

        return false;
    }

    // remove product from cart
    public static function removeProductFromCart(int $productId): bool
    {
        if (self::hasProductInCart($productId)) {
            session()->forget("cart.{$productId}");

            return true;
        }

        return false;
    }

    // get cart
    public static function getCart(): array
    {
        return session('cart') ?: [];
    }

    // clear cart
    public static function clearCart()
    {
        session()->forget('cart');
    }

    // get cart total sum
    public static function getCartTotal(): int
    {
        $total = 0;
        $cart = self::getCart();
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        return $total;
    }

    // get cart items
    public static function getCartQuantityItems(): int
    {
        return count(self::getCart());
    }

    // get cart quantity
    public static function getCartQuantityTotal(): int
    {
        $cart = self::getCart();

        return array_sum(array_column($cart, 'quantity'));
    }

    // has product in cart
    public static function hasProductInCart(int $productId): bool
    {
        return session()->has("cart.$productId");
    }

    // update item quantity
    public static function updateItemQuantity(int $productId, int $quantity): bool
    {
        $updated = false;
        if (self::hasProductInCart($productId)) {
            session(["cart.{$productId}.quantity" => $quantity]);
            $updated = true;
        }

        return $updated;
    }
}
