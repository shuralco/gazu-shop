<?php

namespace App\View\Composers;

use App\Helpers\FragmentCache;
use Illuminate\View\View;

class ProductCardComposer
{
    public function compose(View $view): void
    {
        if ($view->getName() === 'incs.product-card' && isset($view->getData()['product'])) {
            $product = $view->getData()['product'];

            $cacheKey = "product_card_{$product->id}_{$product->updated_at->timestamp}";
            $tags = ['products', 'product_cards', "category_{$product->category_id}"];

            $cachedHtml = FragmentCache::remember($cacheKey, $tags, 3600, function () use ($view) {
                return $view->render();
            });

            // Якщо є кешована версія, використовуємо її
            if ($cachedHtml) {
                $view->with('_cached_content', $cachedHtml);
            }
        }
    }
}
