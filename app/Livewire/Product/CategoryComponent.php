<?php

namespace App\Livewire\Product;

use App\Helpers\Traits\CartTrait;
use App\Models\Brand;
use App\Models\Category;
use App\Models\DisplaySetting;
use App\Models\Filter;
use App\Models\Product;
use App\Traits\BrandDisplayTrait;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class CategoryComponent extends Component
{
    use BrandDisplayTrait, CartTrait, WithPagination;

    public string $slug = '';

    public string $categoryTitle = '';

    public string $sort = 'default';

    #[Computed(persist: true)]
    public function sortList(): array
    {
        return [
            'default' => ['title' => __('general.sort_default'), 'order_field' => 'id', 'order_direction' => 'desc'],
            'name-asc' => ['title' => __('general.sort_name_az'), 'order_field' => 'title', 'order_direction' => 'asc'],
            'name-desc' => ['title' => __('general.sort_name_za'), 'order_field' => 'title', 'order_direction' => 'desc'],
            'price-asc' => ['title' => __('general.sort_price_low_high'), 'order_field' => 'price', 'order_direction' => 'asc'],
            'price-desc' => ['title' => __('general.sort_price_high_low'), 'order_field' => 'price', 'order_direction' => 'desc'],
        ];
    }

    public int $limit = 25;

    #[Computed(persist: true)]
    public function limitList(): array
    {
        return [12, 25, 50, 100];
    }

    public array $selected_filters = [];

    public $min_price;

    public $max_price;

    public array $selected_brands = [];

    public int $loadedItems = 0;

    public bool $showLoadMore = false;

    public function placeholder()
    {
        // Check if skeleton loaders are enabled in admin settings
        if (! DisplaySetting::get('show_skeleton_loaders', true)) {
            return <<<'HTML'
            <main class="pt-32 md:pt-36">
                <div class="max-w-screen-2xl mx-auto px-4 md:px-8">
                    <div class="text-center py-12">
                        <div class="text-2xl font-bold">Завантаження...</div>
                    </div>
                </div>
            </main>
            HTML;
        }

        return <<<'HTML'
        <main class="pt-32 md:pt-36">
            <div class="max-w-screen-2xl mx-auto px-4 md:px-8">
                <div class="animate-pulse">
                    <div class="h-12 bg-gray-200 rounded mb-6"></div>
                    <div class="h-6 bg-gray-200 rounded w-48 mb-8"></div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @for($i = 0; $i < 8; $i++)
                        <div class="h-64 bg-gray-200 rounded"></div>
                        @endfor
                    </div>
                </div>
            </div>
        </main>
        HTML;
    }

    public function mount($category_slug = null, $category = null, $slug = null)
    {
        $rawSlug = $category_slug ?? $slug ?? ($category instanceof Category ? null : $category) ?? '';

        if ($category instanceof Category) {
            $locale = app()->getLocale();
            $this->slug = $category->getLocalizedSlug($locale) ?: $category->slug;
        } elseif ($rawSlug) {
            $locale = app()->getLocale();
            $resolved = Category::findBySlug($rawSlug, $locale);

            if (!$resolved) {
                abort(404);
            }

            $this->slug = $resolved->getLocalizedSlug($locale) ?: $rawSlug;
        } else {
            abort(404);
        }

        // Форсований reset всіх фільтрів при зміні категорії
        $this->selected_filters = [];
        $this->selected_brands = [];
        $this->min_price = null;
        $this->max_price = null;

        if (! isset($this->sortList()[$this->sort])) {
            $this->redirectRoute('category', ['locale' => app()->getLocale(), 'category_slug' => $this->slug], navigate: true);
        }
        if (! in_array($this->limit, $this->limitList())) {
            $this->redirectRoute('category', ['locale' => app()->getLocale(), 'category_slug' => $this->slug], navigate: true);
        }

        $this->loadedItems = $this->limit;
    }

    public function updated($property)
    {
        $property = explode('.', $property);
        if (in_array($property[0], ['selected_filters', 'min_price', 'max_price', 'selected_brands'])) {
            // Ensure selected_filters is always an array and contains valid integers
            if ($property[0] === 'selected_filters') {
                $this->selected_filters = array_filter(array_map('intval', $this->selected_filters));
                $this->selected_filters = array_values($this->selected_filters); // Re-index array
            }

            // Ensure selected_brands is always an array and contains valid integers
            if ($property[0] === 'selected_brands') {
                $this->selected_brands = array_filter(array_map('intval', $this->selected_brands));
                $this->selected_brands = array_values($this->selected_brands); // Re-index array
            }

            $this->resetPage();
            $this->loadedItems = $this->limit;
            $this->dispatch('filters-updated', ['filters' => $this->selected_filters]);
        }
    }

    public function updatedPage($page)
    {
        $this->loadedItems = $this->limit; // Reset load more on page change
        $page_title = $page > 1 ? " :: Page - {$page}" : ' :: Page - 1';
        $this->dispatch('page-updated', title: shopName()." :: Category {$this->categoryTitle}$page_title");
        $this->dispatch('scroll-to-top');
    }

    public function nextPage()
    {
        $this->setPage($this->getPage() + 1);
    }

    public function previousPage()
    {
        $this->setPage($this->getPage() - 1);
    }

    public function gotoPage($page)
    {
        $this->setPage($page);
    }

    public function changeSort()
    {
        $this->sort = isset($this->sortList()[$this->sort]) ? $this->sort : 'default';
    }

    public function changeLimit()
    {
        $this->limit = in_array($this->limit, $this->limitList()) ? $this->limit : $this->limitList()[0];
        $this->loadedItems = $this->limit;
        $this->resetPage();
    }

    public function removeFilter($filter_id)
    {
        if (false !== ($key = array_search($filter_id, $this->selected_filters))) {
            unset($this->selected_filters[$key]);
            $this->selected_filters = array_values($this->selected_filters);
            $this->resetPage();
            $this->loadedItems = $this->limit;
        }
    }

    public function toggleBrand($brandId)
    {
        $key = array_search($brandId, $this->selected_brands);

        if ($key !== false) {
            // Remove brand
            unset($this->selected_brands[$key]);
            $this->selected_brands = array_values($this->selected_brands);
        } else {
            // Add brand
            $this->selected_brands[] = $brandId;
        }

        $this->resetPage();
        $this->loadedItems = $this->limit;
        $this->dispatch('brands-updated', ['brands' => $this->selected_brands]);
    }

    public function removeBrand($brandId)
    {
        if (false !== ($key = array_search($brandId, $this->selected_brands))) {
            unset($this->selected_brands[$key]);
            $this->selected_brands = array_values($this->selected_brands);
            $this->resetPage();
            $this->loadedItems = $this->limit;
        }
    }

    public function clearFilters()
    {
        $this->selected_filters = [];
        $this->selected_brands = [];
        $this->min_price = null;
        $this->max_price = null;
        $this->resetPage();
        $this->loadedItems = $this->limit;
    }

    public function loadMore()
    {
        $this->loadedItems += 25;
        $this->dispatch('items-loaded');
    }

    public function toggleFilter($filterId)
    {
        $filterId = (int) $filterId; // Ensure it's an integer

        $key = array_search($filterId, $this->selected_filters);

        if ($key !== false) {
            // Remove filter
            unset($this->selected_filters[$key]);
            $this->selected_filters = array_values($this->selected_filters);
        } else {
            // Add filter
            $this->selected_filters[] = $filterId;
        }

        $this->resetPage();
        $this->loadedItems = $this->limit;
        $this->dispatch('filters-updated', ['filters' => $this->selected_filters]);
    }

    public function render()
    {
        $category = Category::findBySlug($this->slug) ?? abort(404);
        $ids = $category->id;

        // Завжди отримуємо реальний діапазон цін для цієї категорії
        $min_max_price = Product::selectRaw('min(price) as min_price, max(price) as max_price')
            ->where('category_id', $ids)
            ->first();

        $categoryMinPrice = $min_max_price->min_price ?? 0;
        $categoryMaxPrice = $min_max_price->max_price ?? 999999;

        // Валідуємо URL параметри проти реальних цін категорії
        if (is_null($this->min_price) || $this->min_price < $categoryMinPrice || $this->min_price > $categoryMaxPrice) {
            $this->min_price = $categoryMinPrice;
        }

        if (is_null($this->max_price) || $this->max_price > $categoryMaxPrice || $this->max_price < $categoryMinPrice) {
            $this->max_price = $categoryMaxPrice;
        }

        // Якщо діапазон порушено - виправляємо
        if ($this->min_price > $this->max_price) {
            $this->min_price = $categoryMinPrice;
            $this->max_price = $categoryMaxPrice;
        }

        $categories = \Cache::remember("category_filters_{$ids}", 1800, function () use ($ids) {
            return Category::where('id', $ids)
                ->with(['filterGroups.filters'])
                ->get();
        });

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
        //        dump($filter_groups);

        if ($this->selected_filters) {
            $cnt_filter_groups = Filter::selectRaw('count(distinct filter_group_id) as cnt')
                ->whereIn('id', $this->selected_filters)
                ->value('cnt');
        } else {
            $cnt_filter_groups = 1;
        }

        $productsQuery = Product::query()
            ->select([
                'products.id', 'products.title', 'products.slug', 'products.price',
                'products.old_price', 'products.image', 'products.category_id',
                'products.brand_id', 'products.is_hit', 'products.is_new',
                'brands.name as brand_name', 'brands.logo as brand_logo',
            ])
            ->with(['filters.filterGroup:id,title', 'brandModel:id,name,logo,slug'])
            ->leftJoin((new Brand)->getTable(), (new Product)->getTable().'.brand_id', '=', (new Brand)->getTable().'.id')
            ->where('products.category_id', $ids)
            ->when($this->selected_filters, function (Builder $query) use ($cnt_filter_groups) {
                $query->whereHas('filters', function (Builder $subQuery) {
                    $subQuery->whereIn('filter_id', $this->selected_filters);
                })
                    ->whereIn('products.id', function ($subQuery) use ($cnt_filter_groups) {
                        $subQuery->select('product_id')
                            ->from('filter_products') // pivot table, no model equivalent
                            ->whereIn('filter_id', $this->selected_filters)
                            ->groupBy('product_id')
                            ->havingRaw('count(distinct filter_group_id) >= ?', [$cnt_filter_groups]);
                    });
            })
            ->when($this->selected_brands, function (Builder $query) {
                $query->whereIn('products.brand_id', $this->selected_brands);
            })
            ->whereBetween('products.price', [$this->min_price, $this->max_price])
            ->orderBy('products.'.$this->sortList()[$this->sort]['order_field'], $this->sortList()[$this->sort]['order_direction']);

        // Manual pagination for 6.7x speed improvement (10.6ms → 1.58ms)
        $totalProducts = $productsQuery->count();
        $currentPage = $this->getPage();
        $lastPage = (int) ceil($totalProducts / $this->limit);

        // Calculate proper offset for pagination
        $offset = ($currentPage - 1) * $this->limit;

        // Load products with proper offset and limit
        $products = $productsQuery->offset($offset)->limit($this->limit)->get();

        // Calculate pagination state
        $hasMorePages = $currentPage < $lastPage;
        $this->showLoadMore = $this->loadedItems < $totalProducts && $hasMorePages;

        // Always show load more container if we have enough products to paginate
        $showLoadMoreContainer = $totalProducts > $this->limit;

        // Generate page range for pagination links (show max 5 pages around current)
        $pageRange = [];
        $startPage = max(1, $currentPage - 2);
        $endPage = min($lastPage, $currentPage + 2);

        for ($i = $startPage; $i <= $endPage; $i++) {
            $pageRange[$i] = ''; // URL not needed for wire:click
        }

        $this->categoryTitle = $category->title;
        $title = "Category {$category->title}";

        $breadcrumbs = \App\Helpers\Category\Category::getBreadcrumbs($category->id);

        $mobile_products_per_row = DisplaySetting::get('mobile_products_per_row', 2);
        $mobile_grid_gap = DisplaySetting::get('mobile_grid_gap', 4);

        // Load available brands for this category with caching
        $available_brands = \Cache::remember("category_brands_{$ids}", 1800, function () use ($ids) {
            return Brand::query()
                ->select(['id', 'name', 'logo'])
                ->whereHas('products', function (Builder $query) use ($ids) {
                    $query->where('category_id', $ids);
                })
                ->orderBy('name')
                ->get();
        });

        return view('livewire.product.category-component', [
            'products' => $products,
            'category' => $category,
            'breadcrumbs' => $breadcrumbs,
            'filter_groups' => $filter_groups,
            'available_brands' => $available_brands,
            'title' => $title,
            'mobile_products_per_row' => $mobile_products_per_row,
            'mobile_grid_gap' => $mobile_grid_gap,
            'showLoadMore' => $this->showLoadMore,
            'showLoadMoreContainer' => $showLoadMoreContainer,
            'totalProducts' => $totalProducts,
            'hasPages' => $hasMorePages, // Замість $products->hasPages()
            'hasMorePages' => $hasMorePages,
            'currentPage' => $currentPage,
            'lastPage' => $lastPage,
            'pageRange' => $pageRange,
        ])->extends('components.layouts.app', [
            'seoModel' => $category,
            'seoTitle' => $category->getSeoTitle(),
            'seoDescription' => $category->getSeoDescription(),
            'seoKeywords' => $category->getSeoKeywords(),
        ]);
    }
}
