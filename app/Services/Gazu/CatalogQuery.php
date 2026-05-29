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
     * Список доступних кореневих категорій з лічильниками у поточному filter-scope.
     * Поточна категорія (якщо є) пріоритетна — показуємо її children.
     * Без категорії — root categories.
     *
     * Лічильник: SUM products по всьому subtree (root + children + grandchildren),
     * бо в більшості e-commerce товари висять на L2/L3 категоріях, не на L1 root.
     */
    public function availableCategories(?Category $cat): Collection
    {
        $key = $this->aggregateCacheKey('categories', $cat);
        return $this->cacheStore()->remember($key, 600, function () use ($cat) {
            $query = Category::query()
                ->where('is_active', true)
                ->limit(20);

            if ($cat) {
                $query->where('parent_id', $cat->id);
            } else {
                $query->whereNull('parent_id');
            }

            $categories = $query->get(['id', 'parent_id', 'slug', 'title']);

            // Build subtree counts: для кожної категорії — count активних products
            // у ній самій + ВСІХ її descendants (recursive children).
            foreach ($categories as $c) {
                $c->products_count = $this->countProductsInSubtree($c->id);
            }

            return $categories->sortByDesc('products_count')->values();
        });
    }

    /**
     * Рекурсивний підрахунок products у категорії + всіх її descendants.
     * Cached per request через static array.
     */
    private function countProductsInSubtree(int $categoryId): int
    {
        static $cache = [];
        if (isset($cache[$categoryId])) return $cache[$categoryId];

        $ids = $this->collectSubtreeIds($categoryId);
        $count = Product::query()
            ->where('is_active', true)
            ->whereIn('category_id', $ids)
            ->count();

        return $cache[$categoryId] = $count;
    }

    /**
     * Collect category ID + all descendant IDs (1-2 levels typical).
     * Bulk query: один SELECT для всіх дочірніх, потім recursion рівнями.
     */
    private function collectSubtreeIds(int $categoryId, int $depth = 0): array
    {
        if ($depth > 5) return [$categoryId];
        $ids = [$categoryId];
        $children = Category::query()
            ->where('parent_id', $categoryId)
            ->where('is_active', true)
            ->pluck('id')
            ->all();
        foreach ($children as $childId) {
            $ids = array_merge($ids, $this->collectSubtreeIds($childId, $depth + 1));
        }
        return $ids;
    }

    /**
     * Список доступних брендів (manufacturer) у вибраній категорії з лічильниками.
     */
    public function availableBrands(?Category $cat): Collection
    {
        $key = $this->aggregateCacheKey('brands', $cat);
        return $this->cacheStore()->remember($key, 600, function () use ($cat) {
            // Try brand_id FK first via subquery (avoids JOIN ambiguity on
            // is_active columns shared by both tables). AutoPartsSeeder
            // populates brand_id only; legacy data populates manufacturer.
            // NOTE: Brand.name is HasTranslations (JSON column), so we use
            // .slug (non-translatable) as the filter value and resolve to
            // display name via the Translatable accessor.
            $useBrandFk = \Schema::hasColumn('products', 'brand_id') && \Schema::hasTable('brands');
            if ($useBrandFk) {
                // Facet з excludeBrand → brand counts враховують price+condition+inStock,
                // АЛЕ не active brand filter (інакше при обраному brand = Mahle всі інші brands мали б count 0).
                $base = $this->facetScope(Product::query(), $cat, excludeBrand: true);
                $brandIdCounts = $base->reorder()
                    ->whereNotNull('brand_id')
                    ->selectRaw('brand_id, COUNT(*) as count')
                    ->groupBy('brand_id')
                    ->orderByDesc('count')
                    ->limit(20)
                    ->pluck('count', 'brand_id');
                if ($brandIdCounts->isNotEmpty()) {
                    $brands = \App\Models\Brand::query()
                        ->whereIn('id', $brandIdCounts->keys())
                        ->get(['id', 'name', 'slug']);
                    return $brands->map(fn ($b) => (object) [
                        // Send slug as filter value (URL ?brand[]=<slug>),
                        // show translated name as label.
                        'manufacturer' => (string) ($b->slug ?: $b->name),
                        'label' => (string) $b->name,
                        'count' => $brandIdCounts[$b->id] ?? 0,
                    ])->sortByDesc('count')->values();
                }
            }
            $base = $this->facetScope(Product::query(), $cat, excludeBrand: true);
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
        // Load relations fully — column restriction breaks HasTranslations
        // on Brand.name (the translatable accessor needs all attribute
        // bookkeeping the trait sets up via casts/observers).
        $q = Product::query()
            ->where('is_active', true)
            ->with(['category', 'inventory.warehouse']);

        if (\Schema::hasColumn('products', 'brand_id')) {
            $q->with('brand');
        }

        $q = $this->applyCategory($q, $cat);
        $q = $this->applySearch($q);
        $q = $this->applyBrands($q);
        $q = $this->applyConditions($q);
        $q = $this->applyPrice($q);
        $q = $this->applyStock($q);
        $q = $this->applyVehicle($q);
        $q = $this->applyFlags($q);
        $q = $this->applySort($q);

        return $q->paginate(self::PER_PAGE)->withQueryString();
    }

    /** Базові обмеження — для price-range. Без brand/price/condition/sort. */
    private function scope(Builder $q, ?Category $cat): Builder
    {
        $q->where('is_active', true);
        $q = $this->applyCategory($q, $cat);
        $q = $this->applySearch($q);
        $q = $this->applyStock($q);
        $q = $this->applyVehicle($q);
        return $q;
    }

    /** Facet scope — додає price + condition для accurate counts brand/category facets.
     *  Excludes brand filter from itself (user має бачити інші brands щоб переключитись). */
    private function facetScope(Builder $q, ?Category $cat, bool $excludeBrand = false): Builder
    {
        $q = $this->scope($q, $cat);
        $q = $this->applyPrice($q);
        $q = $this->applyConditions($q);
        if (! $excludeBrand) {
            $q = $this->applyBrands($q);
        }
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

        // Case-insensitive across Cyrillic + Latin on any driver:
        // MySQL's utf8mb4 LIKE is CI, but SQLite's is only CI for ASCII —
        // so we LIKE against several case variants of the term. Cheap
        // (3-4 OR branches), driver-agnostic, no LOWER() on JSON columns.
        $variants = array_values(array_unique(array_filter([
            $term,
            mb_strtolower($term),
            mb_strtoupper($term),
            mb_convert_case($term, MB_CASE_TITLE),
        ])));

        // Колоквіальні/росіянізм синоніми (масло→олив, тормоз→гальм, ...) —
        // інакше LIKE '%масло%' = 0, бо каталог зве товар «оливний».
        $synonyms = \App\Support\SearchSynonyms::expand($term);

        return $q->where(function ($w) use ($variants, $synonyms) {
            foreach ($variants as $v) {
                $like = '%'.$v.'%';
                $w->orWhere('sku', 'like', $like)
                  ->orWhere('barcode', 'like', $like)
                  ->orWhere('manufacturer', 'like', $like)
                  ->orWhere('title', 'like', $like)
                  ->orWhere('search_tags', 'like', $like);
            }
            foreach ($synonyms as $s) {
                $like = '%'.$s.'%';
                $w->orWhere('title', 'like', $like)
                  ->orWhere('search_tags', 'like', $like);
            }
        });
    }

    private function applyBrands(Builder $q): Builder
    {
        $brands = $this->selectedBrands();
        if (empty($brands)) return $q;

        if (\Schema::hasColumn('products', 'brand_id') && \Schema::hasTable('brands')) {
            // Brand.name is HasTranslations (stored as JSON) so we can't do
            // a direct WHERE name IN (...). Filter values are slugs (sent
            // by availableBrands as 'manufacturer'). Resolve slug→id.
            $brandIds = \App\Models\Brand::query()
                ->whereIn('slug', $brands)
                ->pluck('id')
                ->all();

            return $q->where(function ($w) use ($brands, $brandIds) {
                if (! empty($brandIds)) {
                    $w->whereIn('brand_id', $brandIds);
                }
                $w->orWhereIn('manufacturer', $brands);
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

    /**
     * ?make=byd&model=han&engine=dm-i — restrict to parts whose
     * compatibleEngines pivot matches. If only `make` (no model/engine),
     * match any engine under that make. Same logic for model-only.
     * Silently no-op when the vehicle tables are missing.
     */
    private function applyVehicle(Builder $q): Builder
    {
        if (! \Schema::hasTable('product_compatibility')) {
            return $q;
        }
        $make   = trim((string) $this->request->query('make', ''));
        $model  = trim((string) $this->request->query('model', ''));
        $engine = trim((string) $this->request->query('engine', ''));
        if ($make === '' && $model === '' && $engine === '') {
            return $q;
        }
        $q->whereHas('compatibleEngines', function (Builder $eq) use ($make, $model, $engine) {
            if ($engine !== '') {
                $eq->where('car_engines.code', $engine);
            }
            if ($model !== '') {
                $eq->whereHas('model', function (Builder $mq) use ($model, $make) {
                    $mq->where('car_models.slug', $model);
                    if ($make !== '') {
                        $mq->whereHas('make', function (Builder $kq) use ($make) {
                            $kq->where('car_makes.slug', $make);
                        });
                    }
                });
            } elseif ($make !== '') {
                $eq->whereHas('model.make', function (Builder $kq) use ($make) {
                    $kq->where('car_makes.slug', $make);
                });
            }
        });
        return $q;
    }

    /**
     * Header nav shortcuts: ?promo=1 (discounted), ?hits=1 (is_hit),
     * ?new=1 (is_new). Each filter applied independently.
     */
    private function applyFlags(Builder $q): Builder
    {
        if ($this->request->boolean('promo')) {
            $q->whereNotNull('old_price')->whereColumn('old_price', '>', 'price');
        }
        if ($this->request->boolean('hits') && \Schema::hasColumn('products', 'is_hit')) {
            $q->where('is_hit', true);
        }
        if ($this->request->boolean('new') && \Schema::hasColumn('products', 'is_new')) {
            $q->where('is_new', true);
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
