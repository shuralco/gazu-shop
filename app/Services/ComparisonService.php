<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class ComparisonService
{
    private const SESSION_KEY = 'comparison_products';
    private const MAX_PRODUCTS = 4;

    public function add(int $productId): bool
    {
        $items = $this->getIds();
        if (count($items) >= self::MAX_PRODUCTS) return false;
        if (in_array($productId, $items)) return false;

        $items[] = $productId;
        session()->put(self::SESSION_KEY, $items);
        return true;
    }

    public function remove(int $productId): void
    {
        $items = $this->getIds();
        $items = array_values(array_filter($items, fn ($id) => $id !== $productId));
        session()->put(self::SESSION_KEY, $items);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public function getIds(): array
    {
        return session()->get(self::SESSION_KEY, []);
    }

    public function getCount(): int
    {
        return count($this->getIds());
    }

    public function isInComparison(int $productId): bool
    {
        return in_array($productId, $this->getIds());
    }

    public function getProducts(): Collection
    {
        $ids = $this->getIds();
        if (empty($ids)) return collect();

        return Product::whereIn('id', $ids)
            ->with(['category:id,title', 'brandModel:id,name', 'filters.filterGroup'])
            ->get();
    }

    public function getComparisonData(): array
    {
        $products = $this->getProducts();
        if ($products->isEmpty()) return ['products' => [], 'attributes' => []];

        // Collect all filter groups and their values per product
        $allGroups = [];
        foreach ($products as $product) {
            foreach ($product->filters as $filter) {
                $groupName = $filter->filterGroup->title ?? 'Інше';
                $allGroups[$groupName][$product->id] = $filter->title;
            }
        }

        // Build attribute rows
        $attributes = [];

        // Base attributes
        $attributes[] = ['name' => 'Ціна', 'values' => $products->mapWithKeys(fn ($p) => [$p->id => number_format($p->price, 2) . ' ₴'])->toArray()];
        $attributes[] = ['name' => 'Бренд', 'values' => $products->mapWithKeys(fn ($p) => [$p->id => $p->brandModel?->name ?? '—'])->toArray()];
        $attributes[] = ['name' => 'Артикул', 'values' => $products->mapWithKeys(fn ($p) => [$p->id => $p->sku ?? '—'])->toArray()];
        $attributes[] = ['name' => 'Вага', 'values' => $products->mapWithKeys(fn ($p) => [$p->id => $p->weight ? $p->weight . ' кг' : '—'])->toArray()];
        $attributes[] = ['name' => 'Наявність', 'values' => $products->mapWithKeys(fn ($p) => [$p->id => $p->stock_status === 'in_stock' ? 'В наявності' : 'Немає'])->toArray()];
        $attributes[] = ['name' => 'Рейтинг', 'values' => $products->mapWithKeys(fn ($p) => [$p->id => $p->rating . ' ★ (' . $p->reviews_count . ')'])->toArray()];

        // Filter-based attributes
        foreach ($allGroups as $groupName => $productValues) {
            $row = ['name' => $groupName, 'values' => []];
            foreach ($products as $product) {
                $row['values'][$product->id] = $productValues[$product->id] ?? '—';
            }
            $attributes[] = $row;
        }

        return [
            'products' => $products,
            'attributes' => $attributes,
        ];
    }
}
