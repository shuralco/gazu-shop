<?php

namespace App\Services\Gazu;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Реальна фільтрація/пошук/сортування для GAZU catalog.
 * Підтримувані URL-параметри:
 *   ?cat=<id-or-slug>      — категорія (включаючи всіх нащадків)
 *   ?q=<text>              — пошук по title/sku/manufacturer
 *   ?brand[]=Bosch         — multi-select по manufacturer (фолбек коли немає brand_id)
 *   ?min=&max=             — діапазон ціни
 *   ?stock=in              — тільки в наявності
 *   ?sort=popular|price-asc|price-desc|new
 */
class CatalogQuery
{
    public const PER_PAGE = 24;

    private static ?bool $hasConditionColumn = null;
    private static ?array $categoryTree = null;

    public function __construct(private Request $request) {}

    private static function hasConditionColumn(): bool
    {
        return self::$hasConditionColumn ??= \Schema::hasColumn('products', 'condition');
    }

    public function category(): ?Category
    {
        $cat = $this->request->query('cat');
        if (! $cat) return null;

        return Category::query()
            ->when(is_numeric($cat), fn ($q) => $q->whereKey((int) $cat))
            ->when(! is_numeric($cat), fn ($q) => $q->where('slug->uk', $cat)->orWhere('slug->en', $cat))
            ->first();
    }

    /**
     * @return array{0: int, 1: int, 2: int} [min, max, current min, current max]
     */
    public function priceRange(?Category $cat): array
    {
        $key = $this->aggregateCacheKey('price-range', $cat);
        [$absMin, $absMax] = $this->cacheStore()->remember($key, 600, function () use ($cat) {
            $base = $this->scope(Product::query(), $cat);
            $row = $base->reorder()->selectRaw('MIN(price) as mn, MAX(price) as mx')->first();
            $mn = (int) floor((float) ($row?->mn ?? 0));
            $mx = (int) ceil((float) ($row?->mx ?? 1));
            if ($mx <= $mn) $mx = $mn + 1;
            return [$mn, $mx];
        });

        return [
            'min' => $absMin,
            'max' => $absMax,
            'currentMin' => (int) $this->request->query('min', $absMin),
            'currentMax' => (int) $this->request->query('max', $absMax),
        ];
    }

    /**
     * Список доступних брендів (manufacturer) у вибраній категорії з лічильниками.
     */
    public function availableBrands(?Category $cat): Collection
    {
        $key = $this->aggregateCacheKey('brands', $cat);
        return $this->cacheStore()->remember($key, 600, function () use ($cat) {
            // Prefer brand relation (brand_id FK) when available — AutoPartsSeeder
            // stores brand_id, not manufacturer. Fall back to manufacturer column
            // for legacy products that only have the string field.
            $useBrandFk = \Schema::hasColumn('products', 'brand_id') && \Schema::hasTable('brands');
            if ($useBrandFk) {
                $base = $this->scope(Product::query(), $cat);
                $rows = $base->reorder()
                    ->join('brands', 'products.brand_id', '=', 'brands.id')
                    ->selectRaw('brands.name as manufacturer, COUNT(*) as count')
                    ->whereNotNull('products.brand_id')
                    ->groupBy('brands.name')
                    ->orderByDesc('count')
                    ->limit(20)
                    ->get();
                if ($rows->isNotEmpty()) {
                    return $rows;
                }
            }
            $base = $this->scope(Product::query(), $cat);
            return $base->reorder()
                ->selectRaw('manufacturer, COUNT(*) as count')
                ->whereNotNull('manufacturer')
                ->where('manufacturer', '!=', '')
                ->groupBy('manufacturer')
                ->orderByDesc('count')
                ->limit(20)
                ->get();
        });
    }

    /**
     * Catalog-scoped cache store. Uses tags when supported (Redis/Memcached)
     * so ProductObserver/CategoryObserver can flush all entries with one call
     * on data change. Falls back to global cache on file/database drivers.
     */
    private function cacheStore(): \Illuminate\Contracts\Cache\Repository
    {
        $store = \Cache::store();
        if (method_exists($store->getStore(), 'tags')) {
            return $store->tags(['catalog']);
        }
        return $store;
    }

    private function aggregateCacheKey(string $kind, ?Category $cat): string
    {
        $catId = $cat?->id ?? 0;
        $search = trim((string) $this->request->query('q', ''));
        $stock = $this->request->query('stock') === 'in' ? 1 : 0;
        return "catalog:agg:$kind:cat=$catId:q=".md5($search).":stock=$stock";
    }

    public function selectedBrands(): array
    {
        $b = $this->request->query('brand', []);
        return is_array($b) ? array_filter(array_map('strval', $b)) : array_filter([(string) $b]);
    }

    public function paginate(?Category $cat = null): LengthAwarePaginator
    {
        $q = Product::query()
            ->where('is_active', true)
            ->with(['category:id,title,slug', 'inventory:id,product_id,quantity,reserved_quantity']);

        if (\Schema::hasColumn('products', 'brand_id')) {
            $q->with('brand:id,name,slug');
        }

        $q = $this->applyCategory($q, $cat);
        $q = $this->applySearch($q);
        $q = $this->applyBrands($q);
        $q = $this->applyConditions($q);
        $q = $this->applyPrice($q);
        $q = $this->applyStock($q);
        $q = $this->applySort($q);

        return $q->paginate(self::PER_PAGE)->withQueryString();
    }

    /** Базові обмеження — для price-range / available brands. Без застосування brand/price/sort. */
    private function scope(Builder $q, ?Category $cat): Builder
    {
        $q->where('is_active', true);
        $q = $this->applyCategory($q, $cat);
        $q = $this->applySearch($q);
        $q = $this->applyStock($q);
        return $q;
    }

    private function applyCategory(Builder $q, ?Category $cat): Builder
    {
        if (! $cat) return $q;
        $ids = $this->collectDescendantIds($cat);
        return $q->whereIn('category_id', $ids);
    }

    private function collectDescendantIds(Category $cat): array
    {
        $tree = self::$categoryTree ??= Category::query()
            ->select('id', 'parent_id')
            ->get()
            ->groupBy('parent_id')
            ->map(fn ($rows) => $rows->pluck('id')->all())
            ->all();

        $ids = [$cat->id];
        $stack = [$cat->id];
        while ($stack) {
            $parent = array_pop($stack);
            foreach ($tree[$parent] ?? [] as $childId) {
                $ids[] = $childId;
                $stack[] = $childId;
            }
        }
        return $ids;
    }

    private function applySearch(Builder $q): Builder
    {
        $term = trim((string) $this->request->query('q', ''));
        if ($term === '') return $q;

        return $q->where(function ($w) use ($term) {
            $like = '%'.$term.'%';
            $w->where('sku', 'like', $like)
              ->orWhere('barcode', 'like', $like)
              ->orWhere('manufacturer', 'like', $like)
              ->orWhere('title', 'like', $like)
              ->orWhere('search_tags', 'like', $like);
        });
    }

    private function applyBrands(Builder $q): Builder
    {
        $brands = $this->selectedBrands();
        if (empty($brands)) return $q;

        // Prefer brand_id FK when available — matches the same source as
        // availableBrands() which prefers the brand relation.
        if (\Schema::hasColumn('products', 'brand_id') && \Schema::hasTable('brands')) {
            return $q->where(function ($w) use ($brands) {
                $w->whereHas('brand', fn ($b) => $b->whereIn('name', $brands))
                  ->orWhereIn('manufacturer', $brands);
            });
        }
        return $q->whereIn('manufacturer', $brands);
    }

    private function applyPrice(Builder $q): Builder
    {
        $min = $this->request->query('min');
        $max = $this->request->query('max');
        if ($min !== null && $min !== '') $q->where('price', '>=', (float) $min);
        if ($max !== null && $max !== '') $q->where('price', '<=', (float) $max);
        return $q;
    }

    private function applyStock(Builder $q): Builder
    {
        if ($this->request->query('stock') === 'in') {
            $q->where('quantity', '>', 0);
        }
        return $q;
    }

    public function selectedConditions(): array
    {
        $c = $this->request->query('condition', []);
        return is_array($c) ? array_filter(array_map('strval', $c)) : array_filter([(string) $c]);
    }

    public function availableConditions(?Category $cat): \Illuminate\Support\Collection
    {
        if (! self::hasConditionColumn()) {
            return collect();
        }
        $key = $this->aggregateCacheKey('conditions', $cat);
        return $this->cacheStore()->remember($key, 600, function () use ($cat) {
            $base = $this->scope(Product::query(), $cat);
            return $base->reorder()
                ->selectRaw('`condition`, COUNT(*) as count')
                ->whereNotNull('condition')
                ->where('condition', '!=', '')
                ->groupBy('condition')
                ->orderByDesc('count')
                ->get();
        });
    }

    private function applyConditions(Builder $q): Builder
    {
        $conds = $this->selectedConditions();
        if (! empty($conds) && self::hasConditionColumn()) {
            $q->whereIn('condition', $conds);
        }
        return $q;
    }

    private function applySort(Builder $q): Builder
    {
        return match ($this->request->query('sort')) {
            'price-asc'  => $q->orderBy('price'),
            'price-desc' => $q->orderByDesc('price'),
            'new'        => $q->orderByDesc('id'),
            default      => $q->orderByDesc('rating')->orderByDesc('reviews_count'),
        };
    }
}
