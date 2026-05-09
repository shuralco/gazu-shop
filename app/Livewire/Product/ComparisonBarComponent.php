<?php

namespace App\Livewire\Product;

use App\Services\ComparisonService;
use Livewire\Attributes\On;
use Livewire\Component;

class ComparisonBarComponent extends Component
{
    #[On('comparison-updated')]
    public function refresh(): void {}

    public function removeProduct(int $productId): void
    {
        app(ComparisonService::class)->remove($productId);
    }

    public function render()
    {
        $service = app(ComparisonService::class);
        return view('livewire.product.comparison-bar-component', [
            'count' => $service->getCount(),
            'products' => $service->getProducts(),
        ]);
    }
}
