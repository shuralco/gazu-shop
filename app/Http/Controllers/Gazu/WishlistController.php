<?php

namespace App\Http\Controllers\Gazu;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

/**
 * Wishlist для GAZU storefront. Використовує існуючу wishlists pivot таблицю
 * через Product::wishlistedBy() (BelongsToMany з User).
 */
class WishlistController extends Controller
{
    public function toggle(Request $request)
    {
        $request->validate(['product_id' => 'required|integer']);

        if (! $request->user()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'ok' => false,
                    'redirect' => route('gazu.auth'),
                    'message' => 'Увійдіть, щоб додати в обране',
                ], 401);
            }
            $request->session()->put('url.intended', $request->headers->get('referer') ?: route('gazu.home'));
            return redirect()->route('gazu.auth')->with('flash_message', 'Увійдіть, щоб додати в обране');
        }

        $product = Product::findOrFail($request->integer('product_id'));
        $user = $request->user();

        $action = $product->wishlistedBy()->toggle($user->id);
        $added = ! empty($action['attached'] ?? []);

        $count = \DB::table('wishlists')->where('user_id', $user->id)->count();

        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'in_wishlist' => $added,
                'count' => $count,
            ]);
        }

        return back()->with('flash_message', $added ? 'Додано в обране ❤' : 'Видалено з обраного');
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $items = $user
            ? Product::query()
                ->where('is_active', true)
                ->whereHas('wishlistedBy', fn ($q) => $q->where('user_id', $user->id))
                ->limit(48)
                ->get()
                ->map(function (Product $p) {
                    $imageKinds = ['filter', 'pad', 'shock', 'bulb', 'oil', 'spark', 'bearing', 'wiper'];
                    $p->oem = $p->sku ?: '';
                    $p->brand = $p->brand?->name ?? $p->manufacturer ?? '';
                    $p->image_kind = $imageKinds[($p->id ?? 0) % count($imageKinds)];
                    $p->qty = (int) ($p->quantity ?? 0);
                    $p->reviews = (int) ($p->reviews_count ?? 0);
                    $p->fits = $p->excerpt ?? null;
                    $p->condition = 'Новий';
                    $p->discount = ($p->old_price && $p->price && $p->old_price > $p->price)
                        ? (int) round((($p->old_price - $p->price) / $p->old_price) * 100)
                        : null;
                    $p->url = route('gazu.product.show', ['slug' => $p->slug ?? $p->id]);
                    return $p;
                })
            : collect();

        return view('gazu.wishlist', [
            'items' => $items,
            'activeNav' => null,
        ]);
    }
}
