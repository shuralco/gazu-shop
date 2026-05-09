<?php

namespace App\View\Composers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ShopSettings;
use Illuminate\View\View;

class Error404Composer
{
    public function compose(View $view): void
    {
        $view->with([
            'categories' => Category::whereNull('parent_id')->withCount('products')->take(4)->get(),
            'recommendedProducts' => Product::where('is_hit', true)->take(4)->get(),
            'errorTitle' => ShopSettings::get('error_404_title', 'СТОРІНКА НЕ ЗНАЙДЕНА'),
            'errorSubtitle' => ShopSettings::get('error_404_subtitle', 'На жаль, сторінка, яку ви шукаєте, не існує або була переміщена.'),
            'errorPhone' => ShopSettings::get('error_404_phone', '0-800-123-456'),
            'errorEmail' => ShopSettings::get('error_404_email', 'support@simpleshop.ua'),
        ]);
    }
}
