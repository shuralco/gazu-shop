<?php

namespace App\Livewire\Product;

use App\Models\Brand;
use App\Services\SeoMetaGenerator;
use Livewire\Attributes\Layout;
use Livewire\Component;

class BrandsComponent extends Component
{
    public function mount(): void
    {
        $seoGenerator = new SeoMetaGenerator;
        $seoData = $seoGenerator->generateForPage('brands_index', [], 'uk');

        $this->seo = [
            'title' => $seoData['meta_title'],
            'description' => $seoData['meta_description'],
            'keywords' => $seoData['meta_keywords'],
            'canonical_url' => $seoGenerator->generateCanonicalUrl('brands', 'brands_index'),
        ];
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $brands = Brand::active()
            ->ordered()
            ->withCount('products')
            ->get()
            ->groupBy(function ($brand) {
                return mb_strtoupper(mb_substr($brand->name, 0, 1));
            });

        return view('livewire.product.brands-component', [
            'brands' => $brands,
            'seo' => $this->seo ?? [],
        ]);
    }
}
