<?php

namespace App\Http\Middleware;

use App\Models\Category;
use App\Models\Product;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SlugValidation
{
    public function handle(Request $request, Closure $next): Response
    {
        $slug = $request->route('slug');

        if (! $slug) {
            return $next($request);
        }

        // Список заборонених slug для уникнення конфліктів
        $reservedSlugs = ['checkout', 'admin', 'login', 'register', 'account', 'orders', 'cart', 'search', 'specials', 'hits', 'new', 'sitemap', 'robots', 'api', 'webhooks'];

        if (in_array($slug, $reservedSlugs)) {
            abort(404);
        }

        // Перевіряємо чи існує категорія або товар з таким slug
        $categoryExists = Category::findBySlug($slug) !== null;
        $productExists = Product::findBySlug($slug) !== null;

        if (! $categoryExists && ! $productExists) {
            abort(404);
        }

        // Визначаємо тип та додаємо до request для використання в компоненті
        if ($categoryExists) {
            $request->merge(['entity_type' => 'category']);
        } else {
            $request->merge(['entity_type' => 'product']);
        }

        return $next($request);
    }
}
