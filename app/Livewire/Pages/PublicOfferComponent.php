<?php

namespace App\Livewire\Pages;

use Livewire\Component;

class PublicOfferComponent extends Component
{
    public function render()
    {
        return view('livewire.pages.public-offer', ['title' => 'Публічна оферта']);
    }
}
