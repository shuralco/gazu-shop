<?php

namespace App\Helpers\Cart;

use App\Models\Product;

class Cart
{
    // add product to cart - optimized version with variant support
    public static function add2Cart(int $productId, int $quantity = 1, ?int $variantId = null, ?int $warehouseId = null): bool
    {
        // Per-warehouse cart key — same product from different warehouses are separate cart lines.
        $cartKey = (string) $productId;
        if ($variantId) {
            $cartKey .= "_v{$variantId}";
        }
        if ($warehouseId) {
            $cartKey .= "_w{$warehouseId}";
        }

        // Fast path: if same (product, variant, warehouse) already in cart, bump quantity.
        if (session()->has("cart.{$cartKey}")) {
            session(["cart.{$cartKey}.quantity" => session("cart.{$cartKey}.quantity") + $quantity]);

            return true;
        }

        $cacheKey = "cart_product_{$productId}";
        $product = cache()->remember($cacheKey, 3600, function () use ($productId) {
            // price_currency потрібен, щоб display_price конвертував у грн.
            return Product::query()
                ->select('id', 'title', 'slug', 'image', 'price', 'price_currency')
                ->find($productId);
        });

        if (! $product) {
            return false;
        }

        $title = $product->title;
        // У кошику ЗАВЖДИ грн: товар може бути заведений у USD/EUR — display_price
        // конвертує за курсом /admin/currencies (accessor рахується «на льоту»,
        // тож навіть закешований об'єкт бере свіжий курс). Без цього USD-товар
        // продавався б за числом 200 «грн» замість 8 333.
        $price = (float) $product->display_price;
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

        // Per-warehouse price override.
        if ($warehouseId) {
            $inv = \App\Models\Inventory::where('product_id', $productId)
                ->where('warehouse_id', $warehouseId)
                ->first();
            if ($inv && $inv->price !== null) {
                // display_price конвертує ціну складу у грн за валютою рядка.
                $price = (float) $inv->display_price;
            }
        }

        session(["cart.{$cartKey}" => [
            'title' => $title,
            'slug' => $product->getLocalizedSlug(),
            'image' => $image,
            'price' => $price,
            'quantity' => $quantity,
            'variant_id' => $variantId,
            'warehouse_id' => $warehouseId,
        ]]);

        return true;
    }

    /**
     * Remove a single cart line by full cart key (productId, productId_v{var},
     * productId_v{var}_w{wh}, productId_w{wh}). For backwards compat with
     * callers that pass a plain productId, also strips every "{productId}_*"
     * line so users get the expected "remove product" behaviour.
     */
    public static function removeProductFromCart(int|string $key): bool
    {
        $key = (string) $key;

        if (session()->has("cart.{$key}")) {
            session()->forget("cart.{$key}");
            return true;
        }

        // Plain productId — also strip per-warehouse / per-variant lines for that product.
        $cart = session('cart') ?: [];
        $found = false;
        foreach (array_keys($cart) as $cartKey) {
            $cartKey = (string) $cartKey;
            if ($cartKey === $key || str_starts_with($cartKey, $key.'_')) {
                session()->forget("cart.{$cartKey}");
                $found = true;
            }
        }

        return $found;
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
    public static function hasProductInCart(int|string $key): bool
    {
        return session()->has('cart.'.$key);
    }

    /**
     * Update quantity for a single cart line. Accepts full cart key
     * (productId, productId_v{var}, productId_w{wh}, productId_v{var}_w{wh}).
     * Backwards-compat: plain productId targets every line of that product.
     */
    public static function updateItemQuantity(int|string $key, int $quantity): bool
    {
        $key = (string) $key;

        if (session()->has("cart.{$key}")) {
            session(["cart.{$key}.quantity" => $quantity]);
            return true;
        }

        $cart = session('cart') ?: [];
        $updated = false;
        foreach (array_keys($cart) as $cartKey) {
            $cartKey = (string) $cartKey;
            if ($cartKey === $key || str_starts_with($cartKey, $key.'_')) {
                session(["cart.{$cartKey}.quantity" => $quantity]);
                $updated = true;
            }
        }

        return $updated;
    }
}
