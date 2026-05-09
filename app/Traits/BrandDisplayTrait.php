<?php

namespace App\Traits;

use App\Models\DisplaySetting;

trait BrandDisplayTrait
{
    public function shouldShowBrand(): bool
    {
        $currentRoute = request()->route()->getName();
        $currentPath = request()->path();

        // Визначаємо тип сторінки за маршрутом або шляхом
        if ($currentRoute === 'home' || $currentPath === '/') {
            return DisplaySetting::get('show_brands_on_homepage', false);
        }

        if (str_contains($currentPath, '/search') || $currentRoute === 'search') {
            return DisplaySetting::get('show_brands_in_search', false);
        }

        if (str_contains($currentPath, '/specials') || str_contains($currentPath, '/hits') || str_contains($currentPath, '/new-products')) {
            return DisplaySetting::get('show_brands_in_specials', false);
        }

        // За замовчуванням для каталогу товарів та категорій
        return DisplaySetting::get('show_brands_in_catalog', false);
    }
}
