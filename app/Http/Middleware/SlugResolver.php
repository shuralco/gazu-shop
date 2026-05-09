<?php

namespace App\Http\Middleware;

use App\Models\Category;
use App\Models\Product;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SlugResolver
{
    public function handle(Request $request, Closure $next): Response
    {
        $slug = $request->route('slug');

        if (! $slug) {
            return $next($request);
        }

        // Спочатку перевіряємо категорії
        $category = Category::findBySlug($slug);
        if ($category) {
            $request->route()->setParameter('component', \App\Livewire\Product\CategoryComponent::class);

            return $next($request);
        }

        // Потім перевіряємо товари
        $product = Product::findBySlug($slug);
        if ($product) {
            $request->route()->setParameter('component', \App\Livewire\Product\ProductComponent::class);

            return $next($request);
        }

        // Якщо нічого не знайдено - 404
        abort(404);
    }
}
