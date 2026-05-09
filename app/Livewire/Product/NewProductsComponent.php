<?php

namespace App\Livewire\Product;

use App\Helpers\Traits\CartTrait;
use App\Models\Category;
use App\Models\Filter;
use App\Models\FilterGroup;
use App\Models\Product;
use App\Traits\BrandDisplayTrait;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class NewProductsComponent extends Component
{
    use BrandDisplayTrait, CartTrait, WithPagination;

    #[Url]
    public string $sort = 'default';

    public array $sortList = [];

    private function initSortList(): void
    {
        $this->sortList = [
            'default' => ['title' => __('general.sort_newest_first'), 'order_field' => 'created_at', 'order_direction' => 'desc'],
            'name-asc' => ['title' => __('general.sort_name_az'), 'order_field' => 'title', 'order_direction' => 'asc'],
            'name-desc' => ['title' => __('general.sort_name_za'), 'order_field' => 'title', 'order_direction' => 'desc'],
            'price-asc' => ['title' => __('general.sort_price_low_high'), 'order_field' => 'price', 'order_direction' => 'asc'],
            'price-desc' => ['title' => __('general.sort_price_high_low'), 'order_field' => 'price', 'order_direction' => 'desc'],
        ];
    }

    #[Url]
    public int $limit = 25;

    public array $limitList = [12, 25, 50, 100];

    #[Url]
    public array $selected_filters = [];

    #[Url]
    public $min_price;

    #[Url]
    public $max_price;

    #[Url]
    public array $selected_categories = [];

    public function mount()
    {
        $this->initSortList();
        if (! isset($this->sortList[$this->sort])) {
            $this->sort = 'default';
        }
        if (! in_array($this->limit, $this->limitList)) {
            $this->limit = $this->limitList[0];
        }
    }

    public function updated($property)
    {
        $property = explode('.', $property);
        if (in_array($property[0], ['selected_filters', 'min_price', 'max_price', 'selected_categories'])) {
            if ($property[0] === 'selected_filters') {
                $this->selected_filters = array_filter(array_map('intval', $this->selected_filters));
                $this->selected_filters = array_values($this->selected_filters);
            }
            if ($property[0] === 'selected_categories') {
                $this->selected_categories = array_filter(array_map('intval', $this->selected_categories));
                $this->selected_categories = array_values($this->selected_categories);
            }

            $this->resetPage();
            $this->dispatch('filters-updated', ['filters' => $this->selected_filters]);
        }
    }

    public function updatedPage($page)
    {
        $page_title = $page > 1 ? " :: Сторінка - {$page}" : '';
        $this->dispatch('page-updated', title: shopName()." :: Новинки{$page_title}");
    }

    public function changeSort()
    {
        $this->sort = isset($this->sortList[$this->sort]) ? $this->sort : 'default';
    }

    public function changeLimit()
    {
        $this->limit = in_array($this->limit, $this->limitList) ? $this->limit : $this->limitList[0];
        $this->resetPage();
    }

    public function removeFilter($filter_id)
    {
        if (false !== ($key = array_search($filter_id, $this->selected_filters))) {
            unset($this->selected_filters[$key]);
            $this->selected_filters = array_values($this->selected_filters);
            $this->resetPage();
        }
    }

    public function removeCategory($category_id)
    {
        if (false !== ($key = array_search($category_id, $this->selected_categories))) {
            unset($this->selected_categories[$key]);
            $this->selected_categories = array_values($this->selected_categories);
            $this->resetPage();
        }
    }

    public function clearFilters()
    {
        $this->selected_filters = [];
        $this->selected_categories = [];
        $this->min_price = null;
        $this->max_price = null;
        $this->resetPage();
    }

    public function toggleFilter($filterId)
    {
        $filterId = (int) $filterId;

        $key = array_search($filterId, $this->selected_filters);

        if ($key !== false) {
            unset($this->selected_filters[$key]);
            $this->selected_filters = array_values($this->selected_filters);
        } else {
            $this->selected_filters[] = $filterId;
        }

        $this->resetPage();
        $this->dispatch('filters-updated', ['filters' => $this->selected_filters]);
    }

    public function toggleCategory($categoryId)
    {
        $categoryId = (int) $categoryId;

        $key = array_search($categoryId, $this->selected_categories);

        if ($key !== false) {
            unset($this->selected_categories[$key]);
            $this->selected_categories = array_values($this->selected_categories);
        } else {
            $this->selected_categories[] = $categoryId;
        }

        $this->resetPage();
    }

    public function render()
    {
        // Get price range for new products
        if (is_null($this->min_price) || is_null($this->max_price)) {
            $min_max_price = Product::selectRaw('min(price) as min_price, max(price) as max_price')
                ->where('is_new', 1)
                ->first();
            $this->min_price = $this->min_price ?? $min_max_price->min_price ?? 0;
            $this->max_price = $this->max_price ?? $min_max_price->max_price ?? 999999;
        }

        // Get all filter groups and their filters
        $filter_groups = [];
        $filterGroups = FilterGroup::with('filters')->where('is_active', 1)->get();
        foreach ($filterGroups as $filterGroup) {
            foreach ($filterGroup->filters as $filter) {
                $filter_groups[$filterGroup->id][] = (object) [
                    'filter_group_id' => $filterGroup->id,
                    'title' => $filterGroup->title,
                    'filter_id' => $filter->id,
                    'filter_title' => $filter->title,
                ];
            }
        }

        // Get categories that have new products
        $categories = Category::whereHas('products', function ($query) {
            $query->where('is_new', 1);
        })->get();

        if ($this->selected_filters) {
            $cnt_filter_groups = Filter::selectRaw('count(distinct filter_group_id) as cnt')
                ->whereIn('id', $this->selected_filters)
                ->value('cnt');
        } else {
            $cnt_filter_groups = 1;
        }

        // Build products query for new items only
        $products = Product::query()
            ->where('is_new', 1)
            ->when($this->selected_categories, function (Builder $query) {
                $query->whereIn('category_id', $this->selected_categories);
            })
            ->when($this->selected_filters, function (Builder $query) use ($cnt_filter_groups) {
                $query->whereHas('filters', function (Builder $subQuery) {
                    $subQuery->whereIn('filter_id', $this->selected_filters);
                })
                    ->whereIn('id', function ($subQuery) use ($cnt_filter_groups) {
                        $subQuery->select('product_id')
                            ->from('filter_products')
                            ->whereIn('filter_id', $this->selected_filters)
                            ->groupBy('product_id')
                            ->havingRaw('count(distinct filter_group_id) >= ?', [$cnt_filter_groups]);
                    });
            })
            ->with(['filters.filterGroup', 'category'])
            ->whereBetween('price', [$this->min_price, $this->max_price])
            ->orderBy($this->sortList[$this->sort]['order_field'], $this->sortList[$this->sort]['order_direction'])
            ->paginate($this->limit);

        $page = request()->query('page', 1);
        if ($page > $products->lastPage() && $products->lastPage() > 0) {
            abort(404);
        }

        $title = 'Новинки'.($page > 1 ? " :: Сторінка - {$page}" : '');

        return view('livewire.product.new-products-component', [
            'products' => $products,
            'filter_groups' => $filter_groups,
            'categories' => $categories,
            'title' => $title,
        ]);
    }
}
