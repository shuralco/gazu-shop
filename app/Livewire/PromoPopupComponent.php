<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Cookie;
use Livewire\Component;

class PromoPopupComponent extends Component
{
    public bool $show = false;

    public string $email = '';

    public function mount(): void
    {
        // Check admin toggle
        $enabled = (bool) \App\Models\DisplaySetting::get('promo_popup_enabled', true);
        if (! $enabled) {
            $this->show = false;
            return;
        }
        $this->show = ! request()->cookie('promo_popup_dismissed');
    }

    public function dismiss(): void
    {
        Cookie::queue('promo_popup_dismissed', '1', 60 * 24 * 30);
        $this->show = false;
    }

    public function subscribe(): void
    {
        $this->validate(['email' => ['required', 'email']]);

        Cookie::queue('promo_popup_dismissed', '1', 60 * 24 * 365);
        $this->show = false;

        $this->dispatch('notify', message: 'Дякуємо! Промокод надіслано на вашу пошту.');
    }

    public function render()
    {
        return view('livewire.promo-popup-component');
    }
}
