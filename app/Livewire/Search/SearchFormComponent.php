<?php

namespace App\Livewire\Search;

use App\Models\Brand;
use App\Models\Category;
use App\Models\SearchQuery;
use App\Services\SearchService;
use Livewire\Component;

class SearchFormComponent extends Component
{
    public string $term = '';
    public bool $showPopular = false;

    public function search()
    {
        if ($this->term) {
            $this->redirect(locale_route('search', ['query' => $this->term]), navigate: true);
        }
    }

    public function clearSearch()
    {
        $this->term = '';
        $this->showPopular = false;
        $this->dispatch('$refresh');
    }

    public function focus()
    {
        $this->showPopular = true;
    }

    public function blur()
    {
        $this->showPopular = false;
    }

    public function selectPopular(string $query)
    {
        $this->term = $query;
        $this->showPopular = false;
    }

    public function trackClick(int $productId, string $query)
    {
        try {
            SearchQuery::where('normalized_query', SearchQuery::normalizeQuery($query))
                ->increment('click_count');
        } catch (\Throwable $e) {
            // Click tracking should never break the UI
        }
    }

    public function render()
    {
        $search_results = [];
        $search_categories = [];
        $search_brands = [];
        $popularSearches = [];

        if (mb_strlen($this->term, 'UTF-8') > 1) {
            $searchService = app(SearchService::class);
            $search_results = $searchService->quickSearch($this->term, 6);
            $this->showPopular = false;

            $escapedTerm = str_replace(['%', '_'], ['\\%', '\\_'], $this->term);

            $search_categories = Category::where('is_active', true)
                ->where('title', 'LIKE', '%' . $escapedTerm . '%')
                ->withCount('products')
                ->limit(3)
                ->get();

            $search_brands = Brand::where('name', 'LIKE', '%' . $escapedTerm . '%')
                ->withCount('products')
                ->limit(3)
                ->get();
        }

        if (empty($this->term) && $this->showPopular) {
            try {
                $popularSearches = SearchQuery::getPopularSearches(8);
            } catch (\Throwable $e) {
                $popularSearches = collect();
            }
        }

        return view('livewire.search.search-form-component', [
            'search_results' => $search_results,
            'search_categories' => $search_categories,
            'search_brands' => $search_brands,
            'popularSearches' => $popularSearches,
        ]);
    }
}
