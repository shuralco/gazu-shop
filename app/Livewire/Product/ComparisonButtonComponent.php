<?php

namespace App\Livewire\Product;

use App\Services\ComparisonService;
use Livewire\Component;

class ComparisonButtonComponent extends Component
{
    public int $productId;
    public bool $isInComparison = false;

    public function mount(int $productId): void
    {
        $this->productId = $productId;
        $this->isInComparison = app(ComparisonService::class)->isInComparison($productId);
    }

    public function toggle(): void
    {
        $service = app(ComparisonService::class);

        if ($this->isInComparison) {
            $service->remove($this->productId);
            $this->isInComparison = false;
        } else {
            $added = $service->add($this->productId);
            $this->isInComparison = $added;
            if (!$added) {
                $this->dispatch('notify', message: 'Максимум 4 товари для порівняння');
            }
        }

        $this->dispatch('comparison-updated');
    }

    public function render()
    {
        return view('livewire.product.comparison-button-component');
    }
}
