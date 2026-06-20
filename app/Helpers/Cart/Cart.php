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

        // Підсумкова кількість цієї лінії — потрібна для порогу гуртової ціни
        // (min_quantity): ціна за одиницю залежить від загальної к-сті в лінії.
        $existing = session("cart.{$cartKey}");
        $newQty = (int) ($existing['quantity'] ?? 0) + $quantity;

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
        $image = $product->getImage();

        // База в грн: варіант має власну ціну, інакше — базова ціна товару.
        $variantBase = null;
        if ($variantId) {
            $variant = \App\Models\ProductVariant::with('optionValues')->find($variantId);
            if ($variant) {
                $title .= ' (' . $variant->getDisplayName() . ')';
                $variantBase = (float) $variant->getEffectivePrice();
                if ($variant->image) {
                    $image = $variant->image;
                }
            }
        }

        // Ефективна ціна за одиницю в грн: мультивалюта + ціна складу + персональна
        // гуртова ціна групи (з урахуванням newQty для порогу min_quantity).
        $price = self::unitPriceUah($product, $newQty, $warehouseId, $variantBase);

        // Існуюча лінія — оновлюємо кількість І перераховуємо ціну (поріг гурту
        // міг бути щойно досягнутий → ціна за одиницю змінюється).
        if ($existing) {
            session([
                "cart.{$cartKey}.quantity" => $newQty,
                "cart.{$cartKey}.price" => $price,
            ]);

            return true;
        }

        session(["cart.{$cartKey}" => [
            'product_id' => $productId,
            'title' => $title,
            'slug' => $product->getLocalizedSlug(),
            'image' => $image,
            'price' => $price,
            'quantity' => $quantity,
            'variant_id' => $variantId,
            'variant_base' => $variantBase,
            'warehouse_id' => $warehouseId,
        ]]);

        return true;
    }

    /**
     * Ефективна ціна за одиницю В ГРН: база (варіант/товар) → ціна складу
     * (display_price конвертує валюту) → персональна гуртова ціна групи
     * (effectivePriceForUser). Гуртова ціна групи головніша за ціну складу;
     * %-знижка групи застосовується поверх. qty потрібен для порогу min_quantity.
     */
    private static function unitPriceUah(Product $product, int $qty, ?int $warehouseId, ?float $variantBaseUah): float
    {
        $baseUah = $variantBaseUah ?? (float) $product->display_price;

        if ($warehouseId) {
            $inv = \App\Models\Inventory::where('product_id', $product->id)
                ->where('warehouse_id', $warehouseId)
                ->first();
            if ($inv && $inv->price !== null) {
                $baseUah = (float) $inv->display_price;
            }
        }

        return $product->effectivePriceForUser(auth()->user(), max(1, $qty), $baseUah);
    }

    /**
     * Перерахунок ціни лінії за збереженими даними (товар/склад/варіант) під
     * нову кількість — поріг гуртової ціни (min_quantity) залежить від qty.
     */
    private static function recomputeLinePrice(string $cartKey, int $quantity): void
    {
        $line = session("cart.{$cartKey}");
        if (! $line) {
            return;
        }
        $productId = (int) ($line['product_id'] ?? explode('_', $cartKey)[0]);
        $product = Product::query()->select('id', 'price', 'price_currency')->find($productId);
        if (! $product) {
            return;
        }
        session(["cart.{$cartKey}.price" => self::unitPriceUah(
            $product,
            $quantity,
            $line['warehouse_id'] ?? null,
            isset($line['variant_base']) ? (float) $line['variant_base'] : null,
        )]);
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
            self::recomputeLinePrice($key, $quantity);
            return true;
        }

        $cart = session('cart') ?: [];
        $updated = false;
        foreach (array_keys($cart) as $cartKey) {
            $cartKey = (string) $cartKey;
            if ($cartKey === $key || str_starts_with($cartKey, $key.'_')) {
                session(["cart.{$cartKey}.quantity" => $quantity]);
                self::recomputeLinePrice($cartKey, $quantity);
                $updated = true;
            }
        }

        return $updated;
    }
}
