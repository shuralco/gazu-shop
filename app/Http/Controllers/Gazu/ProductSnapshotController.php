<?php

namespace App\Http\Controllers\Gazu;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\PartImage;
use Illuminate\Http\JsonResponse;

/**
 * Lightweight JSON snapshot of a Product for the AJAX variant picker
 * on the storefront product card. Used by the pills inside the
 * "4E: Variants picker" section in gazu/product/v1.blade.php.
 */
class ProductSnapshotController extends Controller
{
    public function show(int $id): JsonResponse
    {
        $p = Product::with(['category', 'brand'])->where('is_active', true)->findOrFail($id);

        $title = is_array($p->title) ? ($p->title['uk'] ?? '') : (string) $p->title;
        $slug = is_array($p->slug) ? ($p->slug['uk'] ?? '') : (string) $p->slug;

        $image = $p->image;
        if (! $image) {
            $catTitle = $p->category?->title;
            if (is_array($catTitle)) $catTitle = $catTitle['uk'] ?? '';
            $kind = PartImage::kindFromCategory((string) $catTitle);
            $image = PartImage::resolve(null, $kind, $p->id, $title);
        }

        $specs = is_array($p->specifications)
            ? $p->specifications
            : (json_decode((string) $p->specifications, true) ?: []);

        $qty = method_exists($p, 'totalAvailableQuantity')
            ? (int) $p->totalAvailableQuantity()
            : (int) ($p->quantity ?? 0);

        return response()->json([
            'id' => $p->id,
            'slug' => $slug,
            'title' => $title,
            'price' => (float) ($p->price ?? 0),
            'old_price' => $p->old_price ? (float) $p->old_price : null,
            'image' => $image,
            'qty' => $qty,
            'in_stock' => $qty > 0,
            'sku' => $p->sku ?: null,
            'specs' => $specs,
            'url' => '/'.$slug,
        ]);
    }
}
