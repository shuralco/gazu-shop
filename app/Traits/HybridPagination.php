<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

trait HybridPagination
{
    use WithPagination;

    #[Url]
    public int $additionalPages = 0;

    public bool $showLoadMore = false;

    public int $totalItems = 0;

    public function loadMorePage(): void
    {
        $this->additionalPages++;
        $this->dispatch('items-loaded', [
            'currentPage' => $this->getPage(),
            'additionalPages' => $this->additionalPages,
            'totalItems' => $this->totalItems,
        ]);
    }

    public function resetPagination(): void
    {
        $this->resetPage();
        $this->additionalPages = 0;
        $this->showLoadMore = false;
    }

    protected function applyHybridPagination(Builder $query, int $perPage = 25): LengthAwarePaginator
    {
        $this->totalItems = $query->count();

        // Get regular pagination
        $paginated = $query->paginate($perPage);

        // If we have additional pages to load, get those items too
        if ($this->additionalPages > 0) {
            $currentPage = $paginated->currentPage();
            $additionalItems = collect();

            // Load additional pages (next pages after current)
            for ($i = 1; $i <= $this->additionalPages; $i++) {
                $nextPageItems = $query->forPage($currentPage + $i, $perPage)->get();
                $additionalItems = $additionalItems->merge($nextPageItems);
            }

            // Merge current page items with additional items
            $allItems = $paginated->getCollection()->merge($additionalItems);

            // Update the paginated collection
            $paginated->setCollection($allItems);
        }

        // Calculate if we can load more pages
        $maxLoadablePage = $paginated->currentPage() + $this->additionalPages;
        $this->showLoadMore = $maxLoadablePage < $paginated->lastPage();

        return $paginated;
    }

    public function getPaginationInfo(): array
    {
        return [
            'additionalPages' => $this->additionalPages,
            'totalItems' => $this->totalItems,
            'showLoadMore' => $this->showLoadMore,
            'currentItemsCount' => $this->getCurrentItemsCount(),
        ];
    }

    protected function getCurrentItemsCount(): int
    {
        $perPage = property_exists($this, 'limit') ? $this->limit : 25;

        return $perPage * (1 + $this->additionalPages);
    }
}
