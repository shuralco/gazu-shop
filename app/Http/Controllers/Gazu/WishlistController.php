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
    /**
     * Client-side hydration: повертає список product_id у wishlist поточного user.
     * НЕ кешується ResponseCache (per-request data), читається JS після завантаження
     * сторінки → синхронізує heart-state на cached HTML.
     */
    public function ids(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['ids' => []])->header('Cache-Control', 'no-store, private');
        }
        $ids = \DB::table('wishlists')->where('user_id', $user->id)->pluck('product_id')->all();
        return response()->json(['ids' => $ids])->header('Cache-Control', 'no-store, private');
    }

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
                ->with(['brand', 'category'])
                ->whereHas('wishlistedBy', fn ($q) => $q->where('user_id', $user->id))
                ->limit(48)
                ->get()
                ->map(function (Product $p) {
                    $imageKinds = ['filter', 'pad', 'shock', 'bulb', 'oil', 'spark', 'bearing', 'wiper'];
                    // name: prefer translatable title (JSON {"uk":...}) → name col.
                    $rawTitle = $p->getRawOriginal('title');
                    $localized = is_string($rawTitle) && str_starts_with($rawTitle, '{')
                        ? (json_decode($rawTitle, true)['uk'] ?? null)
                        : $rawTitle;
                    $p->name = $localized ?: ($p->name ?? '');
                    $p->oem = $p->sku ?: '';
                    // Brand string: getRelation bypasses the legacy `brand` attribute.
                    $brandModel = $p->relationLoaded('brand') ? $p->getRelation('brand') : null;
                    $brandName = $brandModel?->name;
                    if (is_string($brandName) && str_starts_with($brandName, '{')) {
                        $brandName = json_decode($brandName, true)['uk'] ?? null;
                    }
                    $p->brand = (string) ($brandName ?: $p->getRawOriginal('brand') ?: $p->manufacturer ?: '');
                    $p->image_kind = $imageKinds[($p->id ?? 0) % count($imageKinds)];
                    $p->qty = (int) ($p->quantity ?? 0);
                    $p->reviews = (int) ($p->reviews_count ?? 0);
                    $p->fits = null;
                    $p->condition = 'Новий';
                    $p->discount = ($p->old_price && $p->price && $p->old_price > $p->price)
                        ? (int) round((($p->old_price - $p->price) / $p->old_price) * 100)
                        : null;
                    $slug = $p->getLocalizedSlug('uk') ?: $p->id;
                    $p->url = url('/'.$slug);
                    return $p;
                })
            : collect();

        return view('gazu.wishlist', [
            'items' => $items,
            'activeNav' => null,
        ]);
    }
}
