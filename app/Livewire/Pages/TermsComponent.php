<?php

namespace App\Livewire\Pages;

use Livewire\Component;

class TermsComponent extends Component
{
    public function render()
    {
        return view('livewire.pages.terms', ['title' => 'Умови використання']);
    }
}
