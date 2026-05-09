<?php

namespace App\Livewire\Product;

use App\Helpers\Traits\CartTrait;
use App\Models\Brand;
use App\Models\Product;
use App\Services\SeoMetaGenerator;
use App\Traits\BrandDisplayTrait;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class BrandComponent extends Component
{
    use BrandDisplayTrait, CartTrait;
    use WithPagination;

    public Brand $brand;

    public $sortBy = 'created_at';

    public $sortDirection = 'desc';

    public $perPage = 24;

    public function mount(Brand $brand): void
    {
        $this->brand = $brand;

        $seoGenerator = new SeoMetaGenerator;
        $seoData = $seoGenerator->generateForBrand($brand, 'uk');

        $this->seo = [
            'title' => $seoData['meta_title'],
            'description' => $seoData['meta_description'],
            'keywords' => $seoData['meta_keywords'],
            'canonical_url' => $seoGenerator->generateCanonicalUrl($brand->slug, 'brand'),
        ];
    }

    public function sortBy($field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $products = Product::where('brand_id', $this->brand->id)
            ->where('is_active', true)
            ->with(['category:id,title'])
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.product.brand-component', [
            'products' => $products,
            'seo' => $this->seo ?? [],
        ]);
    }
}
