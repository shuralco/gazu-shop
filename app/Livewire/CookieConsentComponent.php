<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Cookie;
use Livewire\Component;

class CookieConsentComponent extends Component
{
    public bool $show = true;

    public function mount(): void
    {
        $this->show = ! request()->cookie('cookie_consent');
    }

    public function accept(): void
    {
        Cookie::queue('cookie_consent', 'accepted', 60 * 24 * 365);
        $this->show = false;
    }

    public function render()
    {
        return view('livewire.cookie-consent-component');
    }
}
