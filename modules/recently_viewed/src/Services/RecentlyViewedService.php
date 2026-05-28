<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class RecentlyViewedService
{
    private const SESSION_KEY = 'recently_viewed';
    private const MAX_ITEMS = 20;

    public function add(int $productId): void
    {
        $items = $this->getIds();
        $items = array_filter($items, fn($id) => $id !== $productId);
        array_unshift($items, $productId);
        $items = array_slice($items, 0, self::MAX_ITEMS);
        session()->put(self::SESSION_KEY, $items);
    }

    public function getIds(): array
    {
        return session()->get(self::SESSION_KEY, []);
    }

    public function getProducts(int $limit = 8, ?int $excludeId = null): Collection
    {
        $ids = $this->getIds();
        if ($excludeId) $ids = array_filter($ids, fn($id) => $id !== $excludeId);
        $ids = array_slice($ids, 0, $limit);
        if (empty($ids)) return collect();
        $products = Product::whereIn('id', $ids)
            ->where('is_active', true)
            ->get();
        // Preserve original viewing order
        $idOrder = array_flip($ids);
        return $products->sortBy(fn($p) => $idOrder[$p->id] ?? PHP_INT_MAX)->values();
    }

    public function getCount(): int
    {
        return count($this->getIds());
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }
}
