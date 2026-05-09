<?php

namespace App\Livewire\Product;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class ProductFiltersComponent extends Component
{
    public int $categoryId;

    public array $selectedFilters = [];

    public array $selectedBrands = [];

    public ?float $minPrice = null;

    public ?float $maxPrice = null;

    public function mount(int $categoryId, array $selectedFilters = [], array $selectedBrands = [], ?float $minPrice = null, ?float $maxPrice = null)
    {
        $this->categoryId = $categoryId;
        $this->selectedFilters = $selectedFilters;
        $this->selectedBrands = $selectedBrands;
        $this->minPrice = $minPrice;
        $this->maxPrice = $maxPrice;
    }

    #[Computed(cache: true)]
    public function filterGroups(): array
    {
        return Cache::remember("category_filters_{$this->categoryId}", 1800, function () {
            $categories = Category::where('id', $this->categoryId)
                ->with(['filterGroups.filters'])
                ->get();

            $filter_groups = [];
            foreach ($categories as $categoryItem) {
                foreach ($categoryItem->filterGroups as $filterGroup) {
                    foreach ($filterGroup->filters as $filter) {
                        $filter_groups[$filterGroup->id][] = (object) [
                            'filter_group_id' => $filterGroup->id,
                            'title' => $filterGroup->title,
                            'filter_id' => $filter->id,
                            'filter_title' => $filter->title,
                        ];
                    }
                }
            }

            return $filter_groups;
        });
    }

    #[Computed(cache: true)]
    public function availableBrands(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember("category_brands_{$this->categoryId}", 1800, function () {
            return Brand::query()
                ->select(['id', 'name', 'logo'])
                ->whereHas('products', function (Builder $query) {
                    $query->where('category_id', $this->categoryId);
                })
                ->orderBy('name')
                ->get();
        });
    }

    public function toggleFilter($filterId): void
    {
        $filterId = (int) $filterId;
        $key = array_search($filterId, $this->selectedFilters);

        if ($key !== false) {
            unset($this->selectedFilters[$key]);
            $this->selectedFilters = array_values($this->selectedFilters);
        } else {
            $this->selectedFilters[] = $filterId;
        }

        $this->dispatch('filters-updated', filters: $this->selectedFilters);
    }

    public function toggleBrand($brandId): void
    {
        $key = array_search($brandId, $this->selectedBrands);

        if ($key !== false) {
            unset($this->selectedBrands[$key]);
            $this->selectedBrands = array_values($this->selectedBrands);
        } else {
            $this->selectedBrands[] = $brandId;
        }

        $this->dispatch('brands-updated', brands: $this->selectedBrands);
    }

    public function clearFilters(): void
    {
        $this->selectedFilters = [];
        $this->selectedBrands = [];
        $this->minPrice = null;
        $this->maxPrice = null;
        $this->dispatch('filters-cleared');
    }

    public function placeholder()
    {
        // Check if skeleton loaders are enabled in admin settings
        if (! \App\Models\DisplaySetting::get('show_skeleton_loaders', true)) {
            return <<<'HTML'
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-center py-4">
                    <div class="text-lg font-bold">Завантаження фільтрів...</div>
                </div>
            </div>
            HTML;
        }

        return <<<'HTML'
        <div class="animate-pulse bg-white rounded-lg shadow p-4">
            <div class="h-6 bg-gray-200 rounded mb-4"></div>
            <div class="space-y-2">
                @for($i = 0; $i < 5; $i++)
                <div class="h-4 bg-gray-200 rounded"></div>
                @endfor
            </div>
        </div>
        HTML;
    }

    public function render()
    {
        return view('livewire.product.product-filters-component');
    }
}
