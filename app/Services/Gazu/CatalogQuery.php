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
 *   ?filter[]=12&filter[]=15 — характеристики (filters.id): OR всередині групи, AND між групами
 *   ?sort=popular|price-asc|price-desc|new
 */
class CatalogQuery
{
    public const PER_PAGE = 24;

    private static ?bool $hasConditionColumn = null;
    private static ?bool $hasFilterTables = null;

    /**
     * Кеші на ЧАС ЗАПИТУ. Свідомо не static: під Octane воркер живе між
     * запитами, тож static-кеш віддавав би лічильники/курс/дерево іншого
     * запиту (напр. фасети попереднього авто) аж до перезапуску.
     */
    private ?string $basePriceExpr = null;
    private array $subtreeCountCache = [];
    private ?array $categoryTree = null;

    public function __construct(private Request $request) {}

    private static function hasConditionColumn(): bool
    {
        return self::$hasConditionColumn ??= \Schema::hasColumn('products', 'condition');
    }

    private static function hasFilterTables(): bool
    {
        return self::$hasFilterTables ??= \Schema::hasTable('filters') && \Schema::hasTable('filter_products');
    }

    /**
     * SQL-вираз ціни товару в базовій валюті (грн).
     *
     * `products.price` зберігається у валюті товару (`price_currency`), а вітрина
     * показує `display_price` = price / rate. Тому фільтр «від/до», діапазон
     * повзунка і сортування мусять рахувати ТОЙ САМИЙ перерахунок — інакше
     * товар за 458 ₴ потрапляє в діапазон «10–11».
     *
     * Акцесором тут не обійтись: WHERE/ORDER BY виконує БД.
     */
    private function basePriceExpr(): string
    {
        if ($this->basePriceExpr !== null) {
            return $this->basePriceExpr;
        }

        $raw = 'products.price';
        if (! \Schema::hasColumn('products', 'price_currency') || ! \Schema::hasTable('currencies')) {
            return $this->basePriceExpr = $raw;
        }

        $map = \App\Models\Currency::availableMap();
        $base = $map ? \App\Models\Currency::baseCode() : null;
        if (! $map || ! $base) {
            return $this->basePriceExpr = $raw;
        }
        $base = strtoupper($base);

        $cases = '';
        foreach ($map as $code => $meta) {
            $code = strtoupper((string) $code);
            $rate = (float) ($meta['rate'] ?? 1);
            // Коди валют беруться з БД — у SQL вставляємо лише ISO-подібні,
            // решту ігноруємо (жодного інтерполювання довільного рядка).
            if ($code === $base || $rate <= 0 || $rate == 1.0 || ! preg_match('/^[A-Z]{3}$/', $code)) {
                continue;
            }
            $cases .= " WHEN '{$code}' THEN {$rate}";
        }

        if ($cases === '') {
            return $this->basePriceExpr = $raw;
        }

        return $this->basePriceExpr = "($raw / (CASE UPPER(COALESCE(products.price_currency, '{$base}')){$cases} ELSE 1 END))";
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
            $expr = $this->basePriceExpr();
            $row = $base->reorder()->selectRaw("MIN($expr) as mn, MAX($expr) as mx")->first();
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

            // Категорії, у яких у поточному скоупі нічого немає, не показуємо —
            // клікати по них безглуздо (0 товарів).
            return $categories
                ->filter(fn ($c) => $c->products_count > 0)
                ->sortByDesc('products_count')
                ->values();
        });
    }

    /**
     * Рекурсивний підрахунок products у категорії + всіх її descendants.
     * Кешується в межах запиту (див. subtreeCountCache).
     */
    private function countProductsInSubtree(int $categoryId): int
    {
        if (isset($this->subtreeCountCache[$categoryId])) return $this->subtreeCountCache[$categoryId];

        $ids = $this->collectSubtreeIds($categoryId);
        // Лічильник мусить рахуватись у ПОТОЧНОМУ скоупі (підбір по авто, пошук,
        // наявність, характеристики) — інакше на сторінці конкретної моделі
        // категорії показують кількість по всьому каталогу.
        // $cat = null: категорію не звужуємо, її задає whereIn нижче.
        $count = $this->scope(Product::query(), null)
            ->whereIn('category_id', $ids)
            ->count();

        return $this->subtreeCountCache[$categoryId] = $count;
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
        // Характеристики входять у scope() → без них ключ схлопнув би всі
        // комбінації фільтрів в один запис кешу.
        $filters = $this->selectedFilters();
        sort($filters);
        $fh = $filters ? md5(implode(',', $filters)) : '0';

        // Підбір по авто ТЕЖ входить у scope() — без нього фасети сторінки
        // /zapchastyny/{make}/{model}/{engine} і загального каталогу писались би
        // в ОДИН запис кешу, і показувалось те, що потрапило туди першим
        // («іноді актуальні фільтри, іноді по всьому каталогу»).
        $vehicle = implode('|', [
            trim((string) $this->request->query('make', '')),
            trim((string) $this->request->query('model', '')),
            trim((string) $this->request->query('engine', '')),
        ]);
        $vh = trim($vehicle, '|') !== '' ? md5($vehicle) : '0';

        return "catalog:agg:$kind:cat=$catId:q=".md5($search).":stock=$stock:f=$fh:v=$vh";
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

        // Гуртові ціни — лише для залогінених клієнтів з групою (уникаємо N+1
        // при персональному ціноутворенні; гості йдуть із ResponseCache).
        if ($gid = auth()->user()?->customer_group_id) {
            $q->with(['groupPrices' => fn ($r) => $r->where('customer_group_id', $gid)]);
        }

        $q = $this->applyCategory($q, $cat);
        $q = $this->applySearch($q);
        $q = $this->applyBrands($q);
        $q = $this->applyConditions($q);
        $q = $this->applyPrice($q);
        $q = $this->applyStock($q);
        $q = $this->applyVehicle($q);
        $q = $this->applyFilters($q);
        $q = $this->applyFlags($q);
        $q = $this->applySort($q);

        return $q->paginate(self::PER_PAGE)->withQueryString();
    }

    /** Базові обмеження — для price-range. Без brand/price/condition/sort. */
    private function scope(Builder $q, ?Category $cat, ?int $exceptFilterGroup = null): Builder
    {
        $q->where('is_active', true);
        $q = $this->applyCategory($q, $cat);
        $q = $this->applySearch($q);
        $q = $this->applyStock($q);
        $q = $this->applyVehicle($q);
        $q = $this->applyFilters($q, $exceptFilterGroup);
        return $q;
    }

    /** Facet scope — додає price + condition для accurate counts brand/category facets.
     *  Excludes brand filter from itself (user має бачити інші brands щоб переключитись).
     *  $exceptFilterGroup — так само для характеристик: рахуючи лічильники групи,
     *  не застосовуємо вибір усередині неї самої. */
    private function facetScope(Builder $q, ?Category $cat, bool $excludeBrand = false, ?int $exceptFilterGroup = null): Builder
    {
        $q = $this->scope($q, $cat, $exceptFilterGroup);
        $q = $this->applyPrice($q);
        $q = $this->applyConditions($q);
        if (! $excludeBrand) {
            $q = $this->applyBrands($q);
        }
        return $q;
    }

    /** Обрані характеристики: ?filter[]=12&filter[]=15 → [12, 15] */
    public function selectedFilters(): array
    {
        $f = $this->request->query('filter', []);
        $f = is_array($f) ? $f : [$f];

        return array_values(array_unique(array_filter(array_map('intval', $f))));
    }

    /**
     * Фільтрація по характеристиках: OR всередині однієї групи, AND між групами.
     * Товар мусить мати збіг у КОЖНІЙ групі, з якої обрано хоч одне значення.
     * $exceptFilterGroup — пропустити цю групу (потрібно для її ж лічильників).
     */
    private function applyFilters(Builder $q, ?int $exceptFilterGroup = null): Builder
    {
        $ids = $this->selectedFilters();
        if (empty($ids) || ! self::hasFilterTables()) {
            return $q;
        }

        $byGroup = [];
        foreach (\DB::table('filters')->whereIn('id', $ids)->get(['id', 'filter_group_id']) as $row) {
            $gid = (int) $row->filter_group_id;
            if ($exceptFilterGroup !== null && $gid === $exceptFilterGroup) {
                continue;
            }
            $byGroup[$gid][] = (int) $row->id;
        }

        foreach ($byGroup as $groupFilterIds) {
            $q->whereIn('products.id', function ($sub) use ($groupFilterIds) {
                $sub->select('product_id')
                    ->from('filter_products')
                    ->whereIn('filter_id', $groupFilterIds);
            });
        }

        return $q;
    }

    /**
     * Групи характеристик із лічильниками для лівої панелі каталогу.
     * Які групи показувати: явно прив'язані до категорії (category_filters),
     * інакше — усі активні, що реально трапляються серед товарів у scope.
     */
    public function availableFilters(?Category $cat): Collection
    {
        if (! self::hasFilterTables()) {
            return collect();
        }

        $key = $this->aggregateCacheKey('filters', $cat);

        return $this->cacheStore()->remember($key, 600, function () use ($cat) {
            $selected = $this->selectedFilters();

            $pinned = [];
            if ($cat && \Schema::hasTable('category_filters')) {
                $pinned = \DB::table('category_filters')
                    ->whereIn('category_id', $this->collectDescendantIds($cat))
                    ->distinct()
                    ->pluck('filter_group_id')
                    ->all();
            }

            $groups = \App\Models\FilterGroup::query()
                ->where('is_active', true)
                ->when($pinned, fn ($q) => $q->whereIn('id', $pinned))
                ->orderBy('sort_order')
                ->orderBy('title')
                ->get(['id', 'title']);

            $out = collect();
            foreach ($groups as $g) {
                $scoped = $this->facetScope(Product::query(), $cat, exceptFilterGroup: (int) $g->id)
                    ->reorder()
                    ->select('products.id')
                    ->getQuery();

                $items = \DB::table('filter_products as fp')
                    ->join('filters as f', 'f.id', '=', 'fp.filter_id')
                    ->where('f.filter_group_id', $g->id)
                    ->where('f.is_active', true)
                    ->whereIn('fp.product_id', $scoped)
                    ->groupBy('f.id', 'f.title')
                    ->orderByDesc('count')
                    ->orderBy('f.title')
                    ->limit(30)
                    ->get([\DB::raw('f.id'), \DB::raw('f.title'), \DB::raw('COUNT(DISTINCT fp.product_id) as count')]);

                if ($items->isEmpty()) {
                    continue;
                }

                $out->push((object) [
                    'id' => (int) $g->id,
                    'title' => (string) $g->title,
                    'items' => $items,
                    'hasSelected' => $items->contains(fn ($i) => in_array((int) $i->id, $selected, true)),
                ]);
            }

            return $out;
        });
    }

    private function applyCategory(Builder $q, ?Category $cat): Builder
    {
        if (! $cat) return $q;
        $ids = $this->collectDescendantIds($cat);
        return $q->whereIn('category_id', $ids);
    }

    private function collectDescendantIds(Category $cat): array
    {
        $tree = $this->categoryTree ??= Category::query()
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

        // Пошук по КОДУ має ігнорувати пробіли/дефіси: "1ED 819 644",
        // "1ED819644" і "1ED-819-644" повинні знаходити один і той самий
        // товар. Нормалізуємо і колонку (REPLACE у SQL), і сам запит.
        $codeTerm = preg_replace('/[\s\-]+/u', '', $term);
        $codeColumns = ['sku', 'barcode', 'cross_code', 'extra_codes'];

        return $q->where(function ($w) use ($variants, $synonyms, $codeTerm, $codeColumns) {
            foreach ($variants as $v) {
                $like = '%'.$v.'%';
                $w->orWhere('sku', 'like', $like)
                  ->orWhere('barcode', 'like', $like)
                  ->orWhere('cross_code', 'like', $like)
                  ->orWhere('extra_codes', 'like', $like)
                  ->orWhere('manufacturer', 'like', $like)
                  ->orWhere('title', 'like', $like)
                  ->orWhere('search_tags', 'like', $like);
            }
            foreach ($synonyms as $s) {
                $like = '%'.$s.'%';
                $w->orWhere('title', 'like', $like)
                  ->orWhere('search_tags', 'like', $like);
            }
            // Space/dash-insensitive code match.
            if ($codeTerm !== '') {
                $codeLike = '%'.mb_strtoupper($codeTerm).'%';
                foreach ($codeColumns as $col) {
                    $w->orWhereRaw(
                        "UPPER(REPLACE(REPLACE(COALESCE($col,''),' ',''),'-','')) LIKE ?",
                        [$codeLike]
                    );
                }
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
        $expr = $this->basePriceExpr();
        // Число вставляємо в SQL, а не біндимо: PDO віддає float як рядок, і
        // SQLite (тести) порівнює число з текстом завжди на користь тексту —
        // умова стає хибною. sprintf('%F') робить вставку безпечною.
        if ($min !== null && $min !== '') $q->whereRaw("$expr >= ".$this->sqlNumber($min));
        if ($max !== null && $max !== '') $q->whereRaw("$expr <= ".$this->sqlNumber($max));
        return $q;
    }

    /** Літерал числа для SQL — жодного інтерполювання довільного рядка. */
    private function sqlNumber(mixed $value): string
    {
        return sprintf('%F', (float) $value);
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
        // engine у URL — slug (сирий code містить пробіли/слеш → 404 у pretty-URL).
        // Резолвимо назад у реальний code; сирий code теж приймаємо (backward-compat).
        if ($engine !== '') {
            $engine = $this->resolveEngineCode($make, $model, $engine);
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
     * Резолв engine-сегмента URL у реальний `car_engines.code`.
     *
     * У pretty-URL іде slug (CarEngine::urlSlug), бо сирий code містить пробіли.
     * Точний code теж приймаємо — старі посилання / боти. Якщо не знайдено —
     * повертаємо як є (фільтр просто нічого не збіжить, але без 404).
     */
    private function resolveEngineCode(string $make, string $model, string $engine): string
    {
        if (! \Schema::hasTable('car_engines') || ! \Schema::hasTable('car_models')) {
            return $engine;
        }

        $codes = \DB::table('car_engines as e')
            ->join('car_models as m', 'm.id', '=', 'e.model_id')
            ->when($make !== '', fn ($q) => $q
                ->join('car_makes as mk', 'mk.id', '=', 'm.make_id')
                ->where('mk.slug', $make))
            ->when($model !== '', fn ($q) => $q->where('m.slug', $model))
            ->pluck('e.code');

        foreach ($codes as $code) {
            if ((string) $code === $engine) {
                return $engine;
            }
        }

        $want = \App\Models\CarEngine::urlSlug($engine);
        foreach ($codes as $code) {
            if (\App\Models\CarEngine::urlSlug((string) $code) === $want) {
                return (string) $code;
            }
        }

        return $engine;
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
        $sort = $this->request->query('sort');

        // При пошуку (q) і дефолтному сортуванні — спершу збіги по КОДУ,
        // потім решта (артикул важливіший за назву). Простий CASE-boost.
        $term = trim((string) $this->request->query('q', ''));
        if ($term !== '' && in_array($sort, [null, '', 'relevance'], true)) {
            $codeTerm = preg_replace('/[\s\-]+/u', '', $term);
            if ($codeTerm !== '') {
                $codeLike = '%'.mb_strtoupper($codeTerm).'%';
                $cols = ['sku', 'barcode', 'cross_code', 'extra_codes'];
                $cases = implode(' OR ', array_map(
                    fn ($c) => "UPPER(REPLACE(REPLACE(COALESCE($c,''),' ',''),'-','')) LIKE ?",
                    $cols
                ));
                $q->orderByRaw("CASE WHEN $cases THEN 0 ELSE 1 END", array_fill(0, count($cols), $codeLike));
            }
        }

        $priceExpr = $this->basePriceExpr();

        return match ($sort) {
            'price-asc'  => $q->orderByRaw("$priceExpr asc"),
            'price-desc' => $q->orderByRaw("$priceExpr desc"),
            'new'        => $q->orderByDesc('id'),
            // «Популярні» (дефолт): рейтинг → відгуки → новизна. Тай-брейк за id
            // піднімає щойно додані товари (rating=0, reviews=0) на початок серед
            // рівних, інакше вони губляться на 2-й сторінці категорії.
            default      => $q->orderByDesc('rating')->orderByDesc('reviews_count')->orderByDesc('id'),
        };
    }
}
