<?php

namespace App\Livewire;

use App\Services\Currency\CurrencyService;
use Livewire\Component;

class CurrencySwitcherComponent extends Component
{
    public string $currency;

    public function mount(): void
    {
        $this->currency = app(CurrencyService::class)->getCurrent();
    }

    public function switchCurrency(string $code): void
    {
        app(CurrencyService::class)->setCurrent($code);
        $this->currency = $code;
        $this->redirect(request()->header('Referer', '/'));
    }

    public function render()
    {
        return view('livewire.currency-switcher-component', [
            'currencies' => app(CurrencyService::class)->getAvailable(),
        ]);
    }
}
