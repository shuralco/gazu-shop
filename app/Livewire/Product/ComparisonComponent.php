<?php

namespace App\Livewire\Product;

use App\Services\ComparisonService;
use Livewire\Component;

class ComparisonComponent extends Component
{
    public bool $showDifferencesOnly = false;

    public function removeProduct(int $productId): void
    {
        app(ComparisonService::class)->remove($productId);
    }

    public function clearAll(): void
    {
        app(ComparisonService::class)->clear();
    }

    public function render()
    {
        $service = app(ComparisonService::class);
        $data = $service->getComparisonData();

        // Filter to show only differences if toggled
        if ($this->showDifferencesOnly && !empty($data['attributes'])) {
            $data['attributes'] = array_filter($data['attributes'], function ($attr) {
                $values = array_values($attr['values']);
                return count(array_unique($values)) > 1;
            });
        }

        return view('livewire.product.comparison-component', [
            'title' => 'Порівняння товарів',
            'products' => $data['products'],
            'attributes' => $data['attributes'],
            'count' => $service->getCount(),
        ]);
    }
}
