<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class SearchQuery extends Model
{
    protected $fillable = [
        'query',
        'normalized_query',
        'results_count',
        'search_count',
        'click_count',
        'last_searched_at',
    ];

    protected function casts(): array
    {
        return [
            'results_count' => 'integer',
            'search_count' => 'integer',
            'click_count' => 'integer',
            'last_searched_at' => 'datetime',
        ];
    }

    /**
     * Log a search query - upsert with incremented search_count.
     */
    public static function logSearch(string $query, int $resultsCount): void
    {
        $normalized = static::normalizeQuery($query);
        if (empty($normalized)) return;

        try {
            static::upsert([
                'query' => $query,
                'normalized_query' => $normalized,
                'results_count' => $resultsCount,
                'search_count' => 1,
                'last_searched_at' => now(),
            ], ['normalized_query'], ['search_count' => \DB::raw('search_count + 1'), 'results_count' => $resultsCount, 'last_searched_at' => now()]);
        } catch (\Throwable $e) {
            // Never break search for analytics
        }
    }

    /**
     * Get the most popular search queries.
     */
    public static function getPopularSearches(int $limit = 10): Collection
    {
        return self::where('results_count', '>', 0)
            ->where('search_count', '>=', 2)
            ->orderByDesc('search_count')
            ->limit($limit)
            ->get();
    }

    /**
     * Get searches that returned zero results (to improve catalog/synonyms).
     */
    public static function getZeroResultSearches(int $limit = 20): Collection
    {
        return self::where('results_count', 0)
            ->orderByDesc('search_count')
            ->limit($limit)
            ->get();
    }

    /**
     * Normalize a query string for deduplication.
     */
    public static function normalizeQuery(string $query): string
    {
        $normalized = mb_strtolower(trim($query));
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        return Str::limit($normalized, 250, '');
    }
}
