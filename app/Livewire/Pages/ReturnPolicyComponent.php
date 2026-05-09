<?php

namespace App\Livewire\Pages;

use Livewire\Component;

class ReturnPolicyComponent extends Component
{
    public function render()
    {
        return view('livewire.pages.return-policy', ['title' => 'Повернення та обмін']);
    }
}
