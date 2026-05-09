<?php

namespace App\Livewire\Pages;

use Livewire\Component;

class PrivacyPolicyComponent extends Component
{
    public function render()
    {
        return view('livewire.pages.privacy-policy', ['title' => 'Політика конфіденційності']);
    }
}
