<?php

namespace App\Livewire;

use App\Helpers\Traits\CartTrait;
use App\Models\HomepageModule;
use App\Traits\BrandDisplayTrait;
use Livewire\Component;

class HomeComponent extends Component
{
    use BrandDisplayTrait, CartTrait;

    public function render()
    {
        $modules = cache()->remember('homepage_modules', 1800, function () {
            return HomepageModule::active()->ordered()->get();
        });

        return view('livewire.home-component', [
            'modules' => $modules,
            'title' => 'SIMPLESHOP - Сучасний магазин',
            'desc' => 'Якісні товари, швидка доставка, простий сервіс. Понад 10,000 товарів від світових брендів.',
        ]);
    }

    public function goToCategory($categorySlug)
    {
        return redirect(locale_url($categorySlug));
    }
}
