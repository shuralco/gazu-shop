<?php

namespace App\Livewire\Search;

use App\Helpers\Traits\CartTrait;
use App\Models\Category;
use App\Models\Product;
use App\Services\SearchService;
use App\Traits\BrandDisplayTrait;
use Livewire\Component;
use Livewire\WithPagination;

class SearchComponent extends Component
{
    use BrandDisplayTrait, CartTrait, WithPagination;

    public $query = '';

    public string $correctedQuery = '';

    public $suggestions = [];

    public $selectedCategories = [];

    public $selectedBrands = [];

    public $priceFrom = null;

    public $priceTo = null;

    public $sortBy = 'relevance';

    public $viewMode = 'grid';

    public function mount()
    {
        $query = request()->query('query') ?? request()->query('q') ?? '';
        $this->query = htmlspecialchars(strip_tags($query));
    }

    public function search()
    {
        $this->resetPage();
    }

    public function clearSearch()
    {
        $this->query = '';
        $this->selectedCategories = [];
        $this->selectedBrands = [];
        $this->priceFrom = null;
        $this->priceTo = null;
        $this->resetPage();
    }

    public function selectSuggestion($suggestion)
    {
        $this->query = $suggestion;
        $this->suggestions = [];
        $this->search();
    }

    public function searchByTag($tag)
    {
        $this->query = $tag;
        $this->search();
    }

    public function searchByCategory($slug)
    {
        return redirect(locale_url($slug));
    }

    public function setViewMode($mode)
    {
        $this->viewMode = $mode;
    }

    public function updated($property)
    {
        if (in_array($property, ['selectedCategories', 'selectedBrands', 'priceFrom', 'priceTo', 'sortBy'])) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $products = collect();
        $categories = collect();
        $brands = [];
        $recommendedProducts = collect();
        $expandedResults = collect();
        $expandedQuery = '';
        $popularCategories = Category::whereNull('parent_id')->withCount('products')->limit(8)->get();

        $this->correctedQuery = '';

        if ($this->query && strlen($this->query) >= 2) {
            $searchService = app(SearchService::class);

            $priceFrom = max(0, (float) $this->priceFrom);
            $priceTo = $this->priceTo ? max($priceFrom, (float) $this->priceTo) : null;

            $products = $searchService->search($this->query, 12, [
                'category_ids' => $this->selectedCategories,
                'brands' => $this->selectedBrands,
                'price_min' => $priceFrom ?: null,
                'price_max' => $priceTo,
                'sort' => $this->sortBy,
            ]);

            // Detect typo correction by Meilisearch
            $first = $products->first();
            if ($products->total() > 0 && $first && mb_strlen($this->query) >= 3) {
                $firstTitle = mb_strtolower($first->title);
                $queryLower = mb_strtolower($this->query);
                $queryWords = explode(' ', $queryLower);

                foreach ($queryWords as $word) {
                    if (mb_strlen($word) >= 3 && mb_strpos($firstTitle, $word) === false) {
                        $titleWords = explode(' ', $firstTitle);
                        foreach ($titleWords as $tw) {
                            $maxLen = max(mb_strlen($word), mb_strlen($tw));
                            if ($maxLen > 0) {
                                similar_text($word, $tw, $percent);
                                if ($percent > 50) {
                                    $this->correctedQuery = str_ireplace($word, $tw, $this->query);
                                    break 2;
                                }
                            }
                        }
                    }
                }
            }

            // Zero-result fallback
            if ($products->total() === 0) {
                // Try removing last word from query
                $words = explode(' ', trim($this->query));
                if (count($words) > 1) {
                    array_pop($words);
                    $expandedQuery = implode(' ', $words);
                    $expandedResults = $searchService->search($expandedQuery, 8, [])->getCollection();
                }

                // Load hit products as recommendations
                $recommendedProducts = Product::where('is_active', true)
                    ->where('is_hit', true)
                    ->inRandomOrder()
                    ->limit(8)
                    ->get();

                // Load popular categories with product counts
                $popularCategories = Category::whereNull('parent_id')
                    ->withCount('products')
                    ->orderByDesc('products_count')
                    ->limit(4)
                    ->get();
            }

            $categories = Category::whereHas('products', function ($q) {
                $q->where('title', 'LIKE', '%'.$this->query.'%');
            })->withCount('products')->get();

            $brands = Product::where('title', 'LIKE', '%'.$this->query.'%')
                ->whereNotNull('brand')
                ->where('brand', '!=', '')
                ->selectRaw('brand, COUNT(*) as count')
                ->groupBy('brand')
                ->orderBy('count', 'desc')
                ->pluck('count', 'brand')
                ->toArray();
        }

        return view('livewire.search.search-component', [
            'products' => $products,
            'categories' => $categories,
            'brands' => $brands,
            'popularCategories' => $popularCategories,
            'recommendedProducts' => $recommendedProducts,
            'expandedResults' => $expandedResults,
            'expandedQuery' => $expandedQuery,
            'suggestions' => $this->suggestions,
        ]);
    }
}
