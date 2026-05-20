<?php

namespace App\Http\Controllers\Gazu;

use App\Helpers\Cart\Cart;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Реальний кошик для /gazu storefront. Використовує існуючий App\Helpers\Cart\Cart
 * (session-based), щоб /gazu і чинний /uk кошик ділилися станом.
 */
class CartController extends Controller
{
    public function add(Request $request)
    {
        $request->validate([
            'product_id'   => 'required|integer',
            'quantity'     => 'integer|min:1',
            'warehouse_id' => 'nullable|integer',
        ]);

        $added = Cart::add2Cart(
            (int) $request->input('product_id'),
            (int) $request->input('quantity', 1),
            null,
            $request->filled('warehouse_id') ? (int) $request->input('warehouse_id') : null,
        );

        if (! $added) {
            $msg = 'Товар не знайдено';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'ok'      => false,
                    'message' => $msg,
                ], 404);
            }
            return back()->withErrors(['cart' => $msg]);
        }

        $count = Cart::getCartQuantityItems();
        $msg = "Додано в кошик · усього $count позицій";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'ok'       => true,
                'count'    => $count,
                'qtyTotal' => Cart::getCartQuantityTotal(),
                'total'    => Cart::getCartTotal(),
                'message'  => $msg,
            ]);
        }

        return back()->with('cart_message', $msg);
    }

    /**
     * Full cart contents as JSON for the slide-in mini-cart drawer.
     * Returns each line (id, title, slug, price, qty, lineTotal, image, warehouse)
     * + aggregate totals. Image resolution mirrors cart/index.blade.php:
     * real product image → part-image webp pool (deterministic by product id) → null.
     */
    public function contents(Request $request)
    {
        $cart = Cart::getCart();
        $kinds = ['filter', 'pad', 'shock', 'bulb', 'oil', 'spark', 'bearing', 'wiper'];

        $items = [];
        foreach ($cart as $key => $item) {
            $productId = (int) (is_numeric($key) ? $key : explode('_', (string) $key)[0]);
            $title = is_array($item['title'] ?? null) ? ($item['title']['uk'] ?? '—') : ($item['title'] ?? '—');
            $slug = is_array($item['slug'] ?? null) ? ($item['slug']['uk'] ?? null) : ($item['slug'] ?? null);
            $price = (float) ($item['price'] ?? 0);
            $qty = (int) ($item['quantity'] ?? 1);

            // Image: real product image (Product::getImage returns a root-relative
            // public path like "/assets/img/products/x.jpg"), else deterministic
            // part-image webp. The "default-product" placeholder counts as "no image".
            $image = null;
            $stored = $item['image'] ?? null;
            $isDefault = ! $stored || \Illuminate\Support\Str::contains((string) $stored, 'default-product');
            if (! $isDefault) {
                $image = \Illuminate\Support\Str::startsWith($stored, 'http') ? $stored : url('/'.ltrim((string) $stored, '/'));
            } else {
                $kind = $kinds[$productId % count($kinds)];
                $dir = public_path("img/parts/{$kind}");
                $files = is_dir($dir) ? glob($dir.'/*.webp') : [];
                sort($files);
                if (! empty($files)) {
                    $image = asset("img/parts/{$kind}/".basename($files[$productId % count($files)]));
                } elseif (is_file(public_path("img/parts/{$kind}.webp"))) {
                    $image = asset("img/parts/{$kind}.webp");
                }
            }

            $items[] = [
                'key'       => (string) $key,
                'id'        => $productId,
                'title'     => $title,
                'slug'      => $slug,
                'url'       => $slug ? url('/'.$slug) : null,
                'price'     => $price,
                'qty'       => $qty,
                'lineTotal' => $price * $qty,
                'image'     => $image,
            ];
        }

        return response()->json([
            'ok'       => true,
            'items'    => $items,
            'count'    => Cart::getCartQuantityItems(),
            'qtyTotal' => Cart::getCartQuantityTotal(),
            'total'    => Cart::getCartTotal(),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
            'quantity'   => 'required|integer|min:1',
        ]);

        $productId = (int) $request->input('product_id');
        $qty = (int) $request->input('quantity');
        Cart::updateItemQuantity($productId, $qty);

        if ($request->wantsJson() || $request->ajax()) {
            $cart = Cart::getCart();
            $item = collect($cart)->first(fn ($v, $k) => (int) (is_numeric($k) ? $k : explode('_', (string) $k)[0]) === $productId);
            $price = (float) ($item['price'] ?? 0);

            return response()->json([
                'ok' => true,
                'qty' => $qty,
                'lineTotal' => $price * $qty,
                'total' => Cart::getCartTotal(),
                'count' => Cart::getCartQuantityItems(),
                'qtyTotal' => Cart::getCartQuantityTotal(),
            ]);
        }

        return back();
    }

    public function remove(Request $request)
    {
        $request->validate(['product_id' => 'required|integer']);
        Cart::removeProductFromCart((int) $request->input('product_id'));

        return $request->wantsJson()
            ? response()->json(['ok' => true, 'count' => Cart::getCartQuantityItems()])
            : back();
    }

    public function clear()
    {
        Cart::clearCart();
        return back()->with('cart_message', 'Кошик очищено');
    }

    public function applyCoupon(Request $request)
    {
        $request->validate(['code' => 'required|string|max:50']);
        $code = strtoupper(trim((string) $request->input('code')));

        $coupon = \App\Models\Coupon::query()
            ->where('code', $code)
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('valid_until')->orWhere('valid_until', '>=', now()))
            ->where(fn ($q) => $q->whereNull('valid_from')->orWhere('valid_from', '<=', now()))
            ->first();

        if (! $coupon) {
            return response()->json([
                'ok' => false,
                'message' => 'Промокод не знайдено або термін дії минув',
            ], 404);
        }

        $cartTotal = Cart::getCartTotal();
        if ($coupon->minimum_amount && $cartTotal < (float) $coupon->minimum_amount) {
            return response()->json([
                'ok' => false,
                'message' => "Мінімальна сума замовлення для цього купона: " . number_format($coupon->minimum_amount, 0, '.', ' ') . ' ₴',
            ], 422);
        }

        $discount = match ($coupon->type) {
            \App\Models\Coupon::TYPE_PERCENTAGE => round($cartTotal * ((float) $coupon->value / 100)),
            \App\Models\Coupon::TYPE_FIXED_AMOUNT => min((float) $coupon->value, $cartTotal),
            \App\Models\Coupon::TYPE_FREE_SHIPPING => 0,
            default => 0,
        };
        if ($coupon->maximum_discount && $discount > (float) $coupon->maximum_discount) {
            $discount = (float) $coupon->maximum_discount;
        }

        session([
            'gazu.coupon.code' => $coupon->code,
            'gazu.coupon.discount' => (int) $discount,
            'gazu.coupon.type' => $coupon->type,
        ]);

        return response()->json([
            'ok' => true,
            'code' => $coupon->code,
            'type' => $coupon->type,
            'discount' => (int) $discount,
            'total' => max(0, $cartTotal - (int) $discount),
            'count' => Cart::getCartQuantityItems(),
            'qtyTotal' => Cart::getCartQuantityTotal(),
            'message' => $coupon->type === \App\Models\Coupon::TYPE_FREE_SHIPPING
                ? 'Безкоштовна доставка активована'
                : 'Знижка ' . number_format($discount, 0, '.', ' ') . ' ₴ застосована',
        ]);
    }

    public function removeCoupon(Request $request)
    {
        session()->forget(['gazu.coupon.code', 'gazu.coupon.discount', 'gazu.coupon.type']);
        return response()->json(['ok' => true]);
    }
}
