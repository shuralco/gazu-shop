<?php

namespace App\Http\Controllers\Gazu;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\PartImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

    /**
     * Find a ProductVariant matching the picked option-value combination.
     * Used by the storefront options block (Колір/Розмір/Об'єм) for AJAX swap.
     *
     * Query: option_value_ids[] = 12, 45, 78
     * Returns the same snapshot shape as show() so the existing JS handler reuses.
     */
    public function variantByOptions(Request $request, int $productId): JsonResponse
    {
        $product = Product::with('options.values')->findOrFail($productId);
        $picked = collect($request->input('option_value_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->sort()
            ->values()
            ->all();

        if (empty($picked)) {
            return response()->json(['error' => 'no option_value_ids picked'], 422);
        }

        // Find variant that has EXACTLY this set of option_value_ids.
        $variant = ProductVariant::query()
            ->where('product_id', $product->id)
            ->where('is_active', true)
            ->whereHas('optionValues', fn ($q) => $q->whereIn('product_option_values.id', $picked), '=', count($picked))
            ->with('optionValues')
            ->get()
            ->first(function ($v) use ($picked) {
                $ids = $v->optionValues->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();
                return $ids === $picked;
            });

        $title = is_array($product->title) ? ($product->title['uk'] ?? '') : (string) $product->title;
        $slug = is_array($product->slug) ? ($product->slug['uk'] ?? '') : (string) $product->slug;

        // Fall back to base product fields if no exact-match variant exists yet.
        $image = $variant?->image ?: $product->image;
        if (! $image) {
            $catTitle = $product->category?->title;
            if (is_array($catTitle)) $catTitle = $catTitle['uk'] ?? '';
            $kind = PartImage::kindFromCategory((string) $catTitle);
            $image = PartImage::resolve(null, $kind, $variant?->id ?: $product->id, $title);
        }

        $basePrice = (float) ($product->price ?? 0);
        $price = $variant?->price !== null
            ? (float) $variant->price
            : $basePrice + collect($picked)
                ->map(fn ($id) => (float) optional(\App\Models\ProductOptionValue::find($id))->price_modifier)
                ->sum();

        $qty = $variant ? (int) $variant->quantity : (
            method_exists($product, 'totalAvailableQuantity')
                ? (int) $product->totalAvailableQuantity()
                : (int) ($product->quantity ?? 0)
        );

        return response()->json([
            'id' => $product->id,
            'variant_id' => $variant?->id,
            'slug' => $slug,
            'title' => $title,
            'price' => $price,
            'old_price' => $variant?->old_price ? (float) $variant->old_price : ($product->old_price ? (float) $product->old_price : null),
            'image' => $image,
            'qty' => $qty,
            'in_stock' => $qty > 0,
            'sku' => $variant?->sku ?: ($product->sku ?: null),
            'has_variant' => $variant !== null,
            'url' => '/'.$slug,
        ]);
    }
}
