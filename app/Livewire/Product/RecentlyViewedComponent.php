<?php

namespace App\Livewire\Product;

use App\Services\RecentlyViewedService;
use Livewire\Component;

class RecentlyViewedComponent extends Component
{
    public int $limit = 8;
    public ?int $excludeId = null;

    public function render()
    {
        $products = app(RecentlyViewedService::class)->getProducts($this->limit, $this->excludeId);
        return view('livewire.product.recently-viewed-component', ['products' => $products]);
    }
}
