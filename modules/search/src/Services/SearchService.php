<?php

namespace App\Services;

use App\Models\Product;
use App\Models\SearchQuery;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class SearchService
{
    private function escapeLike(string $value): string
    {
        return str_replace(['%', '_', '\\'], ['\\%', '\\_', '\\\\'], $value);
    }

    /**
     * Full paginated search (for search results page).
     */
    public function search(string $query, int $perPage = 12, array $filters = []): LengthAwarePaginator
    {
        $query = mb_substr(trim($query), 0, 200);
        if (empty($query)) {
            return new LengthAwarePaginator([], 0, $perPage);
        }

        if ($this->isScoutAvailable()) {
            $results = $this->scoutSearch($query, $perPage, $filters);
        } else {
            $results = $this->sqlSearch($query, $perPage, $filters);
        }

        if (mb_strlen(trim($query)) >= 2) {
            try {
                SearchQuery::logSearch($query, $results->total());
            } catch (\Throwable $e) {
                // Search logging should never break the search itself
            }
        }

        return $results;
    }

    /**
     * Quick search for autocomplete (header search form).
     */
    public function quickSearch(string $query, int $limit = 10): Collection
    {
        $query = mb_substr(trim($query), 0, 200);
        if (empty($query)) {
            return new Collection();
        }

        if ($this->isScoutAvailable()) {
            $results = $this->scoutQuickSearch($query, $limit);
        } else {
            $results = $this->sqlQuickSearch($query, $limit);
        }

        if (mb_strlen(trim($query)) >= 2) {
            try {
                SearchQuery::logSearch($query, $results->count());
            } catch (\Throwable $e) {
                // Search logging should never break the search itself
            }
        }

        return $results;
    }

    /**
     * Check if Scout with a real driver (not collection) is available and configured.
     */
    private function isScoutAvailable(): bool
    {
        $driver = config('scout.driver');

        if (!$driver || $driver === 'collection') {
            return false;
        }

        if (!trait_exists(\Laravel\Scout\Searchable::class)) {
            return false;
        }

        return in_array(\Laravel\Scout\Searchable::class, class_uses_recursive(Product::class));
    }

    // ─── Scout / Meilisearch ────────────────────────────────────────────

    private function scoutSearch(string $query, int $perPage, array $filters): LengthAwarePaginator
    {
        $search = Product::search($query);

        if (!empty($filters['category_ids'])) {
            // Meilisearch filter syntax for array of IDs
            $ids = implode(', ', array_map('intval', $filters['category_ids']));
            $search->whereIn('category_id', array_map('intval', $filters['category_ids']));
        }

        if (!empty($filters['brands'])) {
            $search->whereIn('brand', $filters['brands']);
        }

        if (isset($filters['price_min']) && $filters['price_min'] !== null) {
            $search->where('price', '>=', (float) $filters['price_min']);
        }

        if (isset($filters['price_max']) && $filters['price_max'] !== null) {
            $search->where('price', '<=', (float) $filters['price_max']);
        }

        return $search
            ->query(fn ($q) => $q->where('is_active', true)->with(['category:id,title,slug', 'brandModel:id,name']))
            ->paginate($perPage);
    }

    private function scoutQuickSearch(string $query, int $limit): Collection
    {
        return Product::search($query)
            ->query(fn ($q) => $q->where('is_active', true))
            ->take($limit)
            ->get();
    }

    // ─── SQL LIKE fallback ──────────────────────────────────────────────

    private function sqlSearch(string $query, int $perPage, array $filters): LengthAwarePaginator
    {
        $builder = Product::query()->with(['category:id,title,slug', 'brandModel:id,name']);
        $builder->where('is_active', true);

        if (!empty($query)) {
            $searchTerm = '%' . $this->escapeLike($query) . '%';

            $builder->where(function ($q) use ($searchTerm) {
                $q->where('title', 'LIKE', $searchTerm)
                    ->orWhere('excerpt', 'LIKE', $searchTerm)
                    ->orWhere('sku', 'LIKE', $searchTerm)
                    ->orWhere('content', 'LIKE', $searchTerm);
            });
        }

        // Filters
        if (!empty($filters['category_ids'])) {
            $builder->whereIn('category_id', $filters['category_ids']);
        }

        if (!empty($filters['brands'])) {
            $builder->whereIn('brand', $filters['brands']);
        }

        if (isset($filters['price_min']) && $filters['price_min'] !== null) {
            $builder->where('price', '>=', $filters['price_min']);
        }

        if (isset($filters['price_max']) && $filters['price_max'] !== null) {
            $builder->where('price', '<=', $filters['price_max']);
        }

        // Sorting (passed via filters for simplicity)
        $sort = $filters['sort'] ?? 'relevance';

        switch ($sort) {
            case 'price_asc':
                $builder->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $builder->orderBy('price', 'desc');
                break;
            case 'title_asc':
                $builder->orderBy('title', 'asc');
                break;
            case 'hits':
                $builder->orderBy('is_hit', 'desc')->orderBy('rating', 'desc');
                break;
            case 'newest':
                $builder->orderBy('is_new', 'desc')->orderBy('created_at', 'desc');
                break;
            default:
                // Relevance: titles matching from start rank higher
                if (!empty($query)) {
                    $escaped = $this->escapeLike($query);
                    $builder->orderByRaw("CASE
                        WHEN title LIKE ? THEN 1
                        WHEN title LIKE ? THEN 2
                        WHEN excerpt LIKE ? THEN 3
                        ELSE 4
                    END", [$escaped . '%', '%' . $escaped . '%', '%' . $escaped . '%'])
                    ->orderBy('rating', 'desc');
                }
        }

        return $builder->paginate($perPage);
    }

    private function sqlQuickSearch(string $query, int $limit): Collection
    {
        return Product::query()
            ->where('is_active', true)
            ->where(function ($q) use ($query) {
                $escaped = $this->escapeLike($query);
                $q->where('title', 'LIKE', '%' . $escaped . '%')
                    ->orWhere('sku', 'LIKE', '%' . $escaped . '%');
            })
            ->orderByRaw("CASE WHEN title LIKE ? THEN 1 ELSE 2 END", [$this->escapeLike($query) . '%'])
            ->limit($limit)
            ->get();
    }
}
