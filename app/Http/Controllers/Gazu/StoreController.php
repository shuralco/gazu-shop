<?php

namespace App\Http\Controllers\Gazu;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

/**
 * GAZU storefront — окрема пісочниця, що живе на префіксі /gazu.
 * Не зачіпає чинного storefront / Livewire-сторінок.
 */
class StoreController extends Controller
{
    private array $imageKinds = ['filter', 'pad', 'shock', 'bulb', 'oil', 'spark', 'bearing', 'wiper'];

    /** Category-slug or keyword → part-image kind. First match wins. */
    private array $categoryImageKinds = [
        'oil-filter' => 'filter', 'air-filter' => 'filter', 'fuel-filter' => 'filter', 'cabin-filter' => 'filter',
        'spark-plug' => 'spark', 'glow-plug' => 'spark', 'ignition-coil' => 'spark', 'high-voltage' => 'spark',
        'water-pump' => 'belt', 'thermostat' => 'sensor', 'radiator' => 'filter', 'cooling-fan' => 'alternator',
        'timing-belt' => 'belt', 'timing-kit' => 'belt', 'timing-chain' => 'belt',
        'oxygen-sensor' => 'sensor', 'maf-sensor' => 'sensor', 'knock-sensor' => 'sensor', 'crank-sensor' => 'sensor',
        'abs-sensor' => 'sensor', 'tpms' => 'sensor', 'rain-sensor' => 'sensor',
        'brake-pads-front' => 'pad', 'brake-pads-rear' => 'pad', 'brake-pad' => 'pad',
        'brake-discs' => 'brake-disc', 'brake-disc' => 'brake-disc',
        'brake-caliper' => 'cv-joint', 'brake-cylinder' => 'cv-joint', 'brake-hose' => 'wiper',
        'brake-fluid' => 'oil',
        'shocks' => 'shock', 'shock' => 'shock',
        'spring' => 'spring',
        'ball-joint' => 'cv-joint', 'tie-rod' => 'cv-joint', 'stabilizer' => 'cv-joint', 'silentblock' => 'bearing',
        'hub-bearing' => 'bearing',
        'batter' => 'battery', 'starter' => 'alternator', 'alternator' => 'alternator', 'voltage' => 'sensor',
        'bulbs-h4' => 'bulb', 'bulbs-h7' => 'bulb', 'bulbs-led' => 'bulb', 'bulbs-fog' => 'bulb', 'led-strip' => 'bulb', 'xenon' => 'bulb',
        'fuse' => 'sensor', 'relay' => 'sensor', 'wiring' => 'belt', 'connector' => 'sensor',
        'horn' => 'horn', 'speaker' => 'horn', 'alarm' => 'sensor', 'parking-sensor' => 'sensor',
        'ignition-switch' => 'sensor', 'window-switch' => 'sensor', 'wiper-switch' => 'sensor',
        'clutch' => 'clutch', 'release-bearing' => 'bearing', 'clutch-cable' => 'belt',
        'cv-outer' => 'cv-joint', 'cv-inner' => 'cv-joint', 'cv-boot' => 'wiper', 'drive-shaft' => 'cv-joint',
        'transmission-mount' => 'bearing', 'gearbox' => 'cv-joint', 'shifter' => 'cv-joint',
        'cardan' => 'cv-joint', 'center-bearing' => 'bearing',
        'oils-' => 'oil', 'transmission-oil' => 'oil',
        'coolant' => 'coolant',
        'windshield-fluid' => 'oil',
        'headlight' => 'headlight', 'taillight' => 'taillight', 'fog-light' => 'headlight', 'side-mirror' => 'mirror', 'mirror-glass' => 'mirror',
        'fender' => 'taillight', 'bumper' => 'taillight', 'grille' => 'filter', 'hood' => 'taillight', 'door' => 'taillight',
        'windshield' => 'mirror', 'side-window' => 'mirror',
        'wiper' => 'wiper', 'wiper-motor' => 'wiper', 'washer-nozzle' => 'wiper',
        'molding' => 'belt', 'clip' => 'bearing', 'badge' => 'taillight',
        'mat' => 'mat', 'seat-cover' => 'mat', 'organizer' => 'mat', 'sun-shade' => 'mat', 'air-freshener' => 'oil',
        'dashcam' => 'sensor', 'phone-holder' => 'sensor', 'charger' => 'sensor', 'gps-tracker' => 'sensor', 'multimedia' => 'sensor',
        'tool' => 'tool', 'jack' => 'tool', 'compressor' => 'tool', 'jumper-cable' => 'belt',
        'fire-extinguisher' => 'tool', 'first-aid' => 'tool', 'warning-triangle' => 'taillight',
        'cleaner' => 'oil', 'polish' => 'oil', 'tire-care' => 'tire', 'tire' => 'tire',
    ];

    private function imageKindFor(?Product $p): string
    {
        if (! $p) return 'filter';
        // Try category slug first
        if ($p->relationLoaded('category') && ($cat = $p->getRelation('category'))) {
            $slug = (string) ($cat->slug ?? '');
            foreach ($this->categoryImageKinds as $needle => $kind) {
                if ($slug !== '' && str_contains($slug, $needle)) return $kind;
            }
        }
        // Fallback to legacy id-modulo distribution
        return $this->imageKinds[($p->id ?? 0) % count($this->imageKinds)];
    }

    private function decorate(Product $p): Product
    {
        // Накладаємо UI-поля поверх живої моделі, не торкаючись БД.
        $p->oem = $p->sku ?: ($p->barcode ?: '');
        // name: пріоритет translatable title, потім name-колонка
        $rawTitle = $p->getRawOriginal('title');
        $localizedTitle = is_string($rawTitle) && str_starts_with($rawTitle, '{')
            ? (json_decode($rawTitle, true)['uk'] ?? null)
            : $rawTitle;
        $p->name = $localizedTitle ?: ($p->name ?? '');
        // Products table has BOTH a legacy 'brand' string column AND a
        // brand_id FK with belongsTo Brand. $p->brand reads the attribute
        // (the string column, usually null on seeded data) BEFORE the
        // relation. Use getRelation('brand') to bypass that and read the
        // eager-loaded Brand model directly. Then fall through to
        // legacy brand string → manufacturer → 'GAZU'.
        $brandName = null;
        if ($p->relationLoaded('brand') && ($brandModel = $p->getRelation('brand'))) {
            $brandName = $brandModel->name;
            if (! $brandName) {
                $raw = $brandModel->getRawOriginal('name');
                if (is_string($raw) && str_starts_with($raw, '{')) {
                    $decoded = json_decode($raw, true);
                    $brandName = $decoded['uk'] ?? $decoded['en'] ?? null;
                } else {
                    $brandName = $raw;
                }
            }
        }
        // Use the original 'brand' string column as fallback (legacy data).
        $brandName = $brandName ?: $p->getRawOriginal('brand');
        $p->brand = (string) ($brandName ?: $p->manufacturer ?: 'GAZU');
        $p->image_kind = $this->imageKindFor($p);
        $p->qty = method_exists($p, 'totalAvailableQuantity') ? (int) $p->totalAvailableQuantity() : (int) ($p->quantity ?? 0);
        if (! $p->qty) {
            $p->qty = (int) ($p->quantity ?? 0);
        }
        $p->reviews = (int) ($p->reviews_count ?? 0);
        // Skip generic boilerplate excerpt from seeded data — show real
        // fitment info only (or hide the section).
        $excerpt = $p->excerpt ?? null;
        $isBoilerplate = is_string($excerpt) && str_contains($excerpt, 'Якісна автозапчастина від офіційного дилера');
        $p->fits = $isBoilerplate ? null : $excerpt;
        $p->condition = $p->is_new ? 'Новий' : 'Новий';
        $p->discount = ($p->old_price && $p->price && $p->old_price > $p->price)
            ? (int) round((($p->old_price - $p->price) / $p->old_price) * 100)
            : null;
        $p->url = route('gazu.product.show', ['slug' => $p->slug ?? $p->id]);

        return $p;
    }

    private function fetchProducts(int $limit = 8): \Illuminate\Support\Collection
    {
        $store = \Cache::store();
        $cache = method_exists($store->getStore(), 'tags') ? $store->tags(['catalog']) : $store;

        $items = $cache->remember("home:featured:v2:limit=$limit", 300, function () use ($limit) {
            $q = Product::query()
                ->with(['category', 'inventory'])
                ->where('is_active', true);
            if (\Schema::hasColumn('products', 'brand_id')) {
                $q->with('brand');
            }
            return $q->orderByDesc('rating')->limit($limit)->get();
        });

        if ($items->isEmpty()) {
            return $this->mockProducts($limit);
        }

        return $items->map(fn ($p) => $this->decorate($p));
    }

    private function mockProducts(int $limit = 8): \Illuminate\Support\Collection
    {
        $base = [
            ['name' => 'Фільтр масляний BOSCH F 026 407 023', 'oem' => '06A 115 561 B', 'brand' => 'Bosch', 'image_kind' => 'filter', 'price' => 184, 'old_price' => 240, 'discount' => 23, 'condition' => 'Новий', 'qty' => 12, 'rating' => 4.7, 'reviews' => 42, 'fits' => 'VW Passat B8 2014–2024 · 2.0 TDI'],
            ['name' => 'Колодки гальмівні передні TRW GDB1763', 'oem' => '8K0 698 151 H', 'brand' => 'TRW', 'image_kind' => 'pad', 'price' => 1240, 'condition' => 'Новий', 'qty' => 7, 'rating' => 4.9, 'reviews' => 128, 'fits' => 'Audi A4 B8 / VW Passat B8 / Skoda Superb III'],
            ['name' => 'Амортизатор задній KYB 343396 Excel-G', 'oem' => '5Q0 513 045', 'brand' => 'KYB', 'image_kind' => 'shock', 'price' => 1890, 'condition' => 'Новий', 'qty' => 4, 'rating' => 4.6, 'reviews' => 73, 'fits' => 'VW Golf VII / Skoda Octavia A7'],
            ['name' => 'Лампа головного світла OSRAM Night Breaker H7 +200%', 'oem' => '64210NB200', 'brand' => 'Osram', 'image_kind' => 'bulb', 'price' => 620, 'old_price' => 780, 'discount' => 20, 'condition' => 'Новий', 'qty' => 32, 'rating' => 4.8, 'reviews' => 215, 'fits' => 'Універсальна, цоколь H7'],
            ['name' => 'Олива моторна Mobil 1 ESP 5W-30 4 л', 'oem' => '154297', 'brand' => 'Mobil', 'image_kind' => 'oil', 'price' => 2150, 'condition' => 'Новий', 'qty' => 18, 'rating' => 4.9, 'reviews' => 312, 'fits' => 'Дизельні двигуни з DPF, ACEA C3'],
            ['name' => 'Свічка запалювання NGK BKR6E-11 (2756)', 'oem' => '101 000 045 AA', 'brand' => 'NGK', 'image_kind' => 'spark', 'price' => 142, 'condition' => 'Новий', 'qty' => 86, 'rating' => 4.7, 'reviews' => 504, 'fits' => 'VAG бензинові 1.4–2.0 TSI'],
            ['name' => 'Підшипник маточини передньої FAG 713 6107 70', 'oem' => '8V0 498 625 A', 'brand' => 'FAG', 'image_kind' => 'bearing', 'price' => 1620, 'condition' => 'Новий', 'qty' => 0, 'rating' => 4.5, 'reviews' => 38, 'fits' => 'Audi A3 8V / VW Golf VII'],
            ['name' => 'Щітки склоочисника Bosch Aerotwin AR653S 650+450', 'oem' => '3 397 118 933', 'brand' => 'Bosch', 'image_kind' => 'wiper', 'price' => 780, 'condition' => 'Новий', 'qty' => 24, 'rating' => 4.8, 'reviews' => 189, 'fits' => 'Більшість моделей VAG 2010+'],
        ];

        return collect(array_slice($base, 0, $limit))->map(function ($p, $i) {
            $p['id'] = $i + 1;
            $p['url'] = route('gazu.product.show', ['slug' => 'sample-'.($i + 1)]);
            return (object) $p;
        });
    }

    private function fetchCategories(): \Illuminate\Support\Collection
    {
        if (! class_exists(Category::class)) {
            return collect();
        }
        $store = \Cache::store();
        $cache = method_exists($store->getStore(), 'tags') ? $store->tags(['catalog']) : $store;

        return $cache->remember('home:root-categories:limit=8', 900, function () {
            try {
                return Category::query()
                    ->where('is_active', true)
                    ->whereNull('parent_id')
                    ->orderBy('sort_order')
                    ->limit(8)
                    ->get();
            } catch (\Throwable $e) {
                return collect();
            }
        });
    }

    public function home(string $variant = 'v1')
    {
        $variant = in_array($variant, ['v1', 'v2', 'v3'], true) ? $variant : 'v1';
        $products = $this->fetchProducts(8);
        $categories = $this->fetchCategories();

        // Pre-fetch car makes для SSR brand tiles у hero — без pop-in
        // після Alpine fetch. Cached 1h, дешева операція.
        $heroMakes = \Cache::remember('home:hero:makes', 3600, function () {
            if (! class_exists(\App\Models\CarMake::class)) return [];
            return \App\Models\CarMake::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(['id', 'slug', 'name'])
                ->toArray();
        });

        // Новинки + Акції — 8 кожна для home featured rows
        $relations = ['category', 'inventory.warehouse'];
        if (\Schema::hasColumn('products', 'brand_id')) $relations[] = 'brand';
        $newProducts = \Cache::remember('home:new:8', 600, function () use ($relations) {
            return \App\Models\Product::query()
                ->with($relations)
                ->where('is_active', true)
                ->where('is_new', true)
                ->orderByDesc('updated_at')
                ->limit(8)
                ->get();
        });
        $promoProducts = \Cache::remember('home:promo:8', 600, function () use ($relations) {
            return \App\Models\Product::query()
                ->with($relations)
                ->where('is_active', true)
                ->whereNotNull('old_price')
                ->whereColumn('old_price', '>', 'price')
                ->orderByDesc('updated_at')
                ->limit(8)
                ->get();
        });
        $newProducts = $newProducts->map(fn ($p) => $this->decorate($p));
        $promoProducts = $promoProducts->map(fn ($p) => $this->decorate($p));

        return view("gazu.home.$variant", [
            'featured' => $products->take(4),
            'popular' => $products->skip(4)->take(4)->values(),
            'newProducts' => $newProducts,
            'promoProducts' => $promoProducts,
            'categories' => $categories,
            'heroMakes' => $heroMakes,
            'activeNav' => $variant === 'v2' ? 'compat' : 'catalog',
        ]);
    }

    /**
     * Root-level URL dispatcher. Slugs ending in -\d+ (e.g. "filtr-xxx-13")
     * resolve to a product page; everything else is treated as a category
     * (SEO-friendly URL like `/audio-alarm`).
     */
    public function resolveSlug(Request $request, string $slug)
    {
        // Category first — slugs like `brake-fluids-2` are categories even
        // though they end in `-\d+`. Falling back to product if no category
        // matches keeps Rozetka-style `…-123` product URLs working.
        $category = \App\Models\Category::query()
            ->where('slug', $slug)
            ->orWhere('slug->uk', $slug)
            ->orWhere('slug->en', $slug)
            ->first();

        if ($category) {
            $request->merge(['cat' => $slug]);
            return $this->catalog($request);
        }

        if (preg_match('/-\d+$/', $slug)) {
            return $this->product($slug);
        }

        return $this->notFound();
    }

    /**
     * Pretty-URL catalog: /zapchastyny/{make}/{model?}/{engine?}.
     * Merges path params into query, then defers to catalog() — handles search/sort/etc.
     */
    public function catalogByCar(Request $request, string $make, ?string $model = null, ?string $engine = null)
    {
        $request->query->set('make', $make);
        if ($model !== null)  $request->query->set('model', $model);
        if ($engine !== null) $request->query->set('engine', $engine);
        return $this->catalog($request);
    }

    public function catalog(Request $request, string $variant = 'v1')
    {
        // Canonical-redirect: if both make and at least the path '/catalog' are used,
        // 301 to the pretty URL so search engines see one URL per vehicle filter.
        $routeName = optional($request->route())->getName();
        if (
            $request->isMethod('get')
            && $request->filled('make')
            && $variant === 'v1'
            && $routeName === 'gazu.catalog'
            && ! $request->hasAny(['cat', 'q', 'brand', 'min', 'max', 'stock', 'sort', 'page', 'condition', 'promo', 'hits', 'new'])
        ) {
            $segments = ['zapchastyny', $request->query('make')];
            if ($request->filled('model'))  { $segments[] = $request->query('model'); }
            if ($request->filled('engine')) { $segments[] = $request->query('engine'); }
            return redirect('/'.implode('/', $segments), 301);
        }

        // SEO redirect: `/catalog?cat=foo` → `/foo` (no other filters).
        // Only triggered for the legacy `gazu.catalog` route; the new
        // `gazu.category` resolver calls this method internally with the
        // same `cat` query, so we'd loop without this guard.
        if (
            $request->isMethod('get')
            && $request->filled('cat')
            && ! $request->hasAny(['q', 'brand', 'min', 'max', 'stock', 'sort', 'page', 'condition', 'promo', 'hits', 'new'])
            && $variant === 'v1'
            && $routeName === 'gazu.catalog'
        ) {
            return redirect('/'.$request->query('cat'), 301);
        }

        $variant = in_array($variant, ['v1', 'v2', 'v3'], true) ? $variant : 'v1';

        $query = new \App\Services\Gazu\CatalogQuery($request);
        $category = $query->category();
        $paginator = $query->paginate($category);

        // Декорація під product-card props.
        $products = collect($paginator->items())->map(fn ($p) => $this->decorate($p));
        if ($products->isEmpty() && ! $request->hasAny(['cat', 'q', 'brand', 'min', 'max', 'stock', 'make', 'model', 'engine'])) {
            // Жодних товарів і жодних фільтрів — показуємо моки, щоб шаблон не виглядав порожнім.
            $products = $this->mockProducts(12);
        }

        // Підкатегорії та ancestor-ланцюг для drilldown UI + крошок.
        $subcategories = $this->loadSubcategories($category);
        $ancestors = $this->loadAncestors($category);

        return view("gazu.catalog.$variant", [
            'products'            => $products,
            'paginator'           => $paginator,
            'category'            => $category,
            'subcategories'       => $subcategories,
            'ancestors'           => $ancestors,
            'priceRange'          => $query->priceRange($category),
            'availableBrands'     => $query->availableBrands($category),
            'selectedBrands'      => $query->selectedBrands(),
            'availableConditions' => $query->availableConditions($category),
            'selectedConditions'  => $query->selectedConditions(),
            'searchQuery'         => (string) $request->query('q', ''),
            'currentSort'         => (string) $request->query('sort', 'popular'),
            'inStockOnly'         => $request->query('stock') === 'in',
            'totalCount'          => $paginator->total(),
            // Sub-nav active highlight: /novynky → new, /khity → hits, /akcii → promo
            'activeNav'           => $request->query('new') == 1 ? 'new'
                : ($request->query('hits') == 1 ? 'hits'
                : ($request->query('promo') == 1 ? 'promo'
                : 'catalog')),
            // Car-selector (4B) — pre-populate dropdowns when URL carries the filter.
            'selectedMake'        => (string) $request->query('make', ''),
            'selectedModel'       => (string) $request->query('model', ''),
            'selectedEngine'      => (string) $request->query('engine', ''),
        ]);
    }

    /** Returns child categories with aggregate product count (for drilldown). */
    private function loadSubcategories(?\App\Models\Category $cat): \Illuminate\Support\Collection
    {
        if (! $cat) return collect();
        $store = \Cache::store();
        $cache = method_exists($store->getStore(), 'tags') ? $store->tags(['catalog']) : $store;
        return $cache->remember('cat-children:'.$cat->id, 600, function () use ($cat) {
            $children = \App\Models\Category::query()
                ->where('parent_id', $cat->id)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(['id', 'title', 'slug']);

            // Aggregate descendant product counts (so L1→L2 plitka shows total).
            $parents = \App\Models\Category::query()->pluck('parent_id', 'id')->all();
            $direct = \App\Models\Product::query()
                ->where('is_active', true)
                ->whereNotNull('category_id')
                ->selectRaw('category_id, COUNT(*) as c')
                ->groupBy('category_id')
                ->pluck('c', 'category_id')
                ->map(fn ($v) => (int) $v)
                ->all();
            $tree = $direct;
            foreach ($direct as $cid => $c) {
                $p = $parents[$cid] ?? null;
                while ($p) {
                    $tree[$p] = ($tree[$p] ?? 0) + $c;
                    $p = $parents[$p] ?? null;
                }
            }

            return $children->map(function ($child) use ($tree) {
                $child->products_count = (int) ($tree[$child->id] ?? 0);
                return $child;
            });
        });
    }

    /** Returns ancestors chain (closest first) for breadcrumb building. */
    private function loadAncestors(?\App\Models\Category $cat): \Illuminate\Support\Collection
    {
        if (! $cat || ! $cat->parent_id) return collect();
        $chain = collect();
        $parent = \App\Models\Category::find($cat->parent_id);
        while ($parent) {
            $chain->prepend($parent);
            $parent = $parent->parent_id ? \App\Models\Category::find($parent->parent_id) : null;
        }
        return $chain;
    }

    public function product(string $slug, string $variant = 'v1')
    {
        $variant = in_array($variant, ['v1', 'v2', 'v3'], true) ? $variant : 'v1';

        // 1) Спробуємо чисельний id (якщо переданий sample-1 → 1)
        $product = null;
        if (is_numeric($slug)) {
            $product = Product::query()->with(['brand', 'category', 'inventory'])->find((int) $slug);
        }

        // 2) JSON-slug — Spatie translatable зберігає як {"uk": "...", "en": "..."}
        if (! $product) {
            $product = Product::query()
                ->with(['brand', 'category', 'inventory'])
                ->where('slug->uk', $slug)
                ->orWhere('slug->en', $slug)
                ->first();
        }

        // 3) Якщо slug закінчується на "-{id}" (наша конвенція з seed) — витягнемо id
        if (! $product && preg_match('/-(\d+)$/', $slug, $m)) {
            $product = Product::query()->with(['brand', 'category', 'inventory'])->find((int) $m[1]);
        }

        // 4) Plain-string slug (для legacy)
        if (! $product) {
            $product = Product::query()
                ->with(['brand', 'category', 'inventory'])
                ->where('slug', $slug)
                ->first();
        }

        if ($product) {
            $product = $this->decorate($product);
        } else {
            // Якщо БД має товари — slug просто невалідний → 404.
            // Mock-фолбек тільки коли каталог взагалі порожній.
            if (Product::query()->where('is_active', true)->exists()) {
                return $this->notFound();
            }
            $product = $this->mockProducts(1)->first();
        }

        $related = $this->fetchProducts(4);

        // Real "analogs": same-category products excluding self.
        // Prefer explicit related_products pivot if any rows of type='analog' exist;
        // fallback to category-mate suggestion.
        $analogs = collect();
        if (isset($product->id) && $product->id) {
            $pivotAnalogs = \App\Models\Product::query()
                ->whereHas('relatedProducts', fn ($q) => $q
                    ->where('related_products.product_id', $product->id)
                    ->where('related_products.type', 'analog'))
                ->limit(8)
                ->get();
            if ($pivotAnalogs->isNotEmpty()) {
                $analogs = $pivotAnalogs->map(fn ($x) => $this->decorate($x));
            } elseif (($product instanceof Product) && $product->category_id) {
                $analogs = \App\Models\Product::query()
                    ->where('category_id', $product->category_id)
                    ->where('id', '!=', $product->id)
                    ->where('is_active', true)
                    ->inRandomOrder()
                    ->limit(8)
                    ->get()
                    ->map(fn ($x) => $this->decorate($x));
            }
        }

        // Per-warehouse inventory rows for the warehouse selector.
        $warehouseStocks = collect();
        $closestWarehouseId = null;
        if (isset($product->id) && $product->id) {
            $closest = app(\App\Services\Warehouse\WarehouseLocator::class)->closestForRequest();
            $closestWarehouseId = $closest?->id;

            $warehouseStocks = \App\Models\Inventory::with('warehouse')
                ->where('product_id', $product->id)
                ->whereHas('warehouse', fn ($q) => $q->where('is_active', true))
                ->get()
                // Sort: closest first (when in stock), then in-stock, then by sort_order.
                ->sortBy([
                    fn ($a, $b) => ($b->warehouse_id === $closestWarehouseId && $b->quantity > 0 ? 1 : 0)
                        <=> ($a->warehouse_id === $closestWarehouseId && $a->quantity > 0 ? 1 : 0),
                    fn ($a, $b) => ($b->quantity > 0 ? 1 : 0) <=> ($a->quantity > 0 ? 1 : 0),
                    fn ($a, $b) => ($a->warehouse->sort_order ?? 0) <=> ($b->warehouse->sort_order ?? 0),
                ])
                ->values();
        }

        return view("gazu.product.$variant", [
            'p' => $product,
            'related' => $related,
            'analogs' => $analogs,
            'warehouseStocks' => $warehouseStocks,
            'closestWarehouseId' => $closestWarehouseId,
            'activeNav' => 'catalog',
        ]);
    }

    public function cart()
    {
        $cart = \App\Helpers\Cart\Cart::getCart();
        if (empty($cart)) {
            return view('gazu.cart.empty', ['activeNav' => null]);
        }

        $cartProductIds = collect($cart)->pluck('product_id')->filter()->map(fn ($v) => (int) $v)->all();
        $recommended = $this->fetchRecommended($cartProductIds, 4);

        return view('gazu.cart.index', [
            'cart' => $cart,
            'cartTotal' => \App\Helpers\Cart\Cart::getCartTotal(),
            'recommended' => $recommended,
            'activeNav' => null,
        ]);
    }

    private function fetchRecommended(array $excludeIds, int $limit = 4): \Illuminate\Support\Collection
    {
        $store = \Cache::store();
        $cache = method_exists($store->getStore(), 'tags') ? $store->tags(['catalog']) : $store;
        $key = 'cart:recommended:v2:exclude='.md5(implode(',', $excludeIds)).":limit=$limit";

        $items = $cache->remember($key, 300, function () use ($excludeIds, $limit) {
            $q = Product::query()
                ->with(['category', 'inventory'])
                ->where('is_active', true);
            if (\Schema::hasColumn('products', 'brand_id')) {
                $q->with('brand');
            }
            if (! empty($excludeIds)) {
                $q->whereNotIn('id', $excludeIds);
            }
            return $q->orderByDesc('rating')->orderByDesc('reviews_count')->limit($limit)->get();
        });

        return $items->map(fn ($p) => $this->decorate($p));
    }

    public function emptyCart()
    {
        return view('gazu.cart.empty', ['activeNav' => null]);
    }

    public function checkout()
    {
        return view('gazu.checkout', [
            'activeNav' => null,
        ]);
    }

    public function account(Request $request)
    {
        $user = $request->user();
        $orders = $user
            ? $user->orders()->with('orderProducts')->orderByDesc('id')->paginate(10)
            : collect();

        return view('gazu.account.orders', [
            'user' => $user,
            'orders' => $orders,
            'activeNav' => null,
        ]);
    }

    public function orderDetails(Request $request, int $order)
    {
        $user = $request->user();
        $orderModel = \App\Models\Order::with('orderProducts')->find($order);
        if (! $orderModel || $orderModel->user_id !== $user->id) {
            abort(404);
        }

        return view('gazu.account.order-details', [
            'user' => $user,
            'order' => $orderModel,
            'activeNav' => null,
        ]);
    }

    public function garage()
    {
        return view('gazu.account.garage', [
            'user' => auth()->user(),
            'activeNav' => null,
        ]);
    }

    public function brand(?string $slug = null)
    {
        // /gazu/brand (без slug) → список усіх брендів
        if (! $slug) {
            $allBrands = Brand::query()
                ->when(\Schema::hasColumn('brands', 'is_active'), fn ($q) => $q->where('is_active', true))
                ->orderBy('name')
                ->withCount('products')
                ->get();

            return view('gazu.brand-list', [
                'brands' => $allBrands,
                'activeNav' => 'brands',
            ]);
        }

        // /gazu/brand/{slug} → конкретний бренд
        $brand = Brand::query()->where('slug', $slug)->orWhere('slug', strtolower($slug))->first();
        if (! $brand) {
            // fallback: спробуємо знайти по name (case-insensitive)
            $brand = Brand::query()->whereRaw('LOWER(name) = ?', [strtolower($slug)])->first();
        }
        if (! $brand) abort(404);

        $products = Product::query()
            ->where('is_active', true)
            ->where(function ($q) use ($brand) {
                $q->where('brand_id', $brand->id)
                  ->orWhere('manufacturer', $brand->name);
            })
            ->orderByDesc('rating')
            ->limit(12)
            ->get()
            ->map(fn ($p) => $this->decorate($p));

        $productsCount = Product::query()
            ->where('is_active', true)
            ->where(fn ($q) => $q->where('brand_id', $brand->id)->orWhere('manufacturer', $brand->name))
            ->count();

        // Категорії, в яких є товари цього бренду — для блоку «За категоріями»
        $categories = Category::query()
            ->whereIn('id', Product::query()
                ->where('is_active', true)
                ->where(fn ($q) => $q->where('brand_id', $brand->id)->orWhere('manufacturer', $brand->name))
                ->pluck('category_id')
                ->filter()
                ->unique()
            )
            ->limit(8)
            ->get();

        return view('gazu.brand', [
            'brand' => $brand,
            'products' => $products,
            'productsCount' => $productsCount,
            'brandCategories' => $categories,
            'activeNav' => 'brands',
        ]);
    }

    public function sto()
    {
        return view('gazu.sto', ['activeNav' => 'sto']);
    }

    public function blog(?string $slug = null)
    {
        if ($slug) {
            $page = \App\Models\Page::query()
                ->where('is_active', true)
                ->where(function ($q) use ($slug) {
                    $q->where('slug->uk', $slug)->orWhere('slug->en', $slug)->orWhere('slug', $slug);
                })
                ->first();
            if (! $page) abort(404);

            return view('gazu.blog-show', [
                'page' => $page,
                'activeNav' => 'blog',
            ]);
        }

        $posts = \App\Models\Page::query()
            ->where('is_active', true)
            ->when(\Schema::hasColumn('pages', 'template'), fn ($q) => $q->where('template', 'blog_post'))
            ->orderByDesc('id')
            ->paginate(12);

        return view('gazu.blog', [
            'posts' => $posts,
            'activeNav' => 'blog',
        ]);
    }

    public function contacts()
    {
        return view('gazu.contacts', ['activeNav' => null]);
    }

    public function vin()
    {
        return view('gazu.vin', ['activeNav' => 'vin']);
    }

    public function search(Request $request)
    {
        $q = (string) $request->query('q', '');
        $query = new \App\Services\Gazu\CatalogQuery($request);
        $paginator = $query->paginate(null);
        $products = collect($paginator->items())->map(fn ($p) => $this->decorate($p));

        return view('gazu.catalog.v1', [
            'products' => $products,
            'paginator' => $paginator,
            'category' => null,
            'priceRange' => $query->priceRange(null),
            'availableBrands' => $query->availableBrands(null),
            'selectedBrands' => $query->selectedBrands(),
            'searchQuery' => $q,
            'currentSort' => (string) $request->query('sort', 'popular'),
            'inStockOnly' => $request->query('stock') === 'in',
            'totalCount' => $paginator->total(),
            'activeNav' => 'catalog',
        ]);
    }

    /**
     * AJAX: Nova Poshta cities autocomplete.
     */
    public function npCities(Request $request)
    {
        $q = mb_strtolower(trim((string) $request->query('q', '')));
        $items = $this->queryCities($q);

        // Lazy fetch from NP API if local DB has no match
        if ($q !== '' && mb_strlen($q) >= 2 && $items->isEmpty()) {
            try {
                $svc = app(\App\Services\NovaPoshtaApiService::class);
                $r = $svc->searchCities($request->query('q'), 10);
                foreach ($r['data'] ?? [] as $c) {
                    \App\Models\NpCity::updateOrCreate(['ref' => $c['Ref']], [
                        'description' => $c['Description'],
                        'description_ru' => $c['DescriptionRu'] ?? null,
                        'area_ref' => $c['Area'] ?? null,
                        'area_description' => $c['AreaDescription'] ?? null,
                        'settlement_type' => $c['SettlementTypeDescription'] ?? null,
                        'is_branch' => (bool) ($c['IsBranch'] ?? false),
                    ]);
                }
                $items = $this->queryCities($q);
            } catch (\Throwable $e) {
                \Log::warning('NP cities lazy fetch failed', ['e' => $e->getMessage()]);
            }
        }

        return response()->json([
            'items' => $items->map(fn ($c) => [
                'ref' => $c->ref,
                'name' => $c->description,
                'area' => $c->area_description,
                'type' => $c->settlement_type,
            ])->all(),
        ]);
    }

    private function queryCities(string $q)
    {
        $query = \App\Models\NpCity::query();
        if ($q !== '') {
            $query->where(function ($x) use ($q) {
                $x->whereRaw('LOWER(description) LIKE ?', ["$q%"])
                  ->orWhereRaw('LOWER(description) LIKE ?', ["%$q%"])
                  ->orWhereRaw('LOWER(description_ru) LIKE ?', ["%$q%"]);
            });
            $query->orderByRaw(
                "CASE WHEN LOWER(description) = ? THEN 0 WHEN LOWER(description) LIKE ? THEN 1 WHEN settlement_type = 'місто' THEN 2 ELSE 3 END",
                [$q, "$q%"]
            );
        }

        return $query->orderBy('description')
            ->limit(15)
            ->get(['ref', 'description', 'area_description', 'settlement_type']);
    }

    /**
     * AJAX: Nova Poshta warehouses for given city ref/name.
     */
    public function npWarehouses(Request $request)
    {
        $cityRef = (string) $request->query('city_ref', '');
        $cityName = (string) $request->query('city', '');
        $q = mb_strtolower(trim((string) $request->query('q', '')));
        // type: branch (звичайні відділення) | postomat | all
        $type = (string) $request->query('type', 'branch');

        // Якщо city_ref не передано — шукаємо за назвою
        if ($cityRef === '' && $cityName !== '') {
            $matched = \App\Models\NpCity::where('description', $cityName)
                ->orWhereRaw('LOWER(description) LIKE ?', [mb_strtolower($cityName).'%'])
                ->orderByRaw("CASE WHEN LOWER(description) = ? THEN 0 WHEN settlement_type = 'місто' THEN 1 ELSE 2 END", [mb_strtolower($cityName)])
                ->first();
            if ($matched) {
                $cityRef = $matched->ref;
            }
        }

        // Без міста — нема результатів (інакше повертатиме випадкові #1 з різних сіл)
        if ($cityRef === '') {
            return response()->json(['items' => []]);
        }

        $build = function () use ($cityRef, $type, $q) {
            return \App\Models\NpWarehouse::query()
                ->when(\Schema::hasColumn('np_warehouses', 'is_active'), fn ($x) => $x->where('is_active', true))
                ->where('city_ref', $cityRef)
                ->when($type === 'postomat', fn ($x) => $x->where('type_description', 'Postomat'))
                ->when($type === 'branch', fn ($x) => $x->where(function ($w) {
                    $w->whereIn('type_description', ['Branch', 'Store', 'DropOff'])
                      ->orWhereNull('type_description')
                      ->orWhere('type_description', '');
                }))
                ->when($q !== '', function ($x) use ($q) {
                    $x->where(function ($w) use ($q) {
                        $w->whereRaw('LOWER(description) LIKE ?', ["%$q%"])
                          ->orWhere('number', 'like', "%$q%");
                    });
                })
                ->orderByRaw('CAST(COALESCE(NULLIF(number,""), site_key) AS UNSIGNED)')
                ->limit(500)
                ->get(['ref', 'number', 'site_key', 'description', 'short_address', 'type_description', 'latitude', 'longitude']);
        };

        $items = $build();

        // Lazy fetch — якщо для цього міста ще не sync-нуто warehouses, тягнемо з NP API
        $hasAnyForCity = \App\Models\NpWarehouse::where('city_ref', $cityRef)->exists();
        if (! $hasAnyForCity) {
            try {
                $svc = app(\App\Services\NovaPoshtaApiService::class);
                $page = 1;
                while (true) {
                    $r = $svc->getWarehouses($cityRef, '', 500, $page);
                    if (! ($r['success'] ?? false) || empty($r['data'])) {
                        break;
                    }
                    foreach ($r['data'] as $w) {
                        \App\Models\NpWarehouse::updateOrCreate(['ref' => $w['Ref']], [
                            'site_key' => $w['Number'] ?? null,
                            'number' => $w['Number'] ?? null,
                            'description' => $w['Description'],
                            'short_address' => $w['ShortAddress'] ?? null,
                            'city_ref' => $w['CityRef'] ?? $cityRef,
                            'city_description' => $w['CityDescription'] ?? null,
                            'type_ref' => $w['TypeOfWarehouse'] ?? null,
                            'type_description' => $w['CategoryOfWarehouse'] ?? null,
                            'longitude' => $w['Longitude'] ?? null,
                            'latitude' => $w['Latitude'] ?? null,
                            'total_max_weight' => (int) ($w['TotalMaxWeightAllowed'] ?? 30),
                            'is_active' => ($w['WarehouseStatus'] ?? 'Working') === 'Working',
                        ]);
                    }
                    if (count($r['data']) < 500) {
                        break;
                    }
                    $page++;
                }
                $items = $build();
            } catch (\Throwable $e) {
                \Log::warning('NP warehouses lazy fetch failed', ['e' => $e->getMessage()]);
            }
        }

        return response()->json([
            'items' => $items->map(fn ($w) => [
                'ref' => $w->ref,
                'number' => $w->number ?: $w->site_key,
                'name' => $w->description,
                'short_address' => $w->short_address,
                'type' => $w->type_description,
                'is_postomat' => $w->type_description === 'Postomat',
                'lat' => is_numeric($w->latitude) ? (float) $w->latitude : null,
                'lng' => is_numeric($w->longitude) ? (float) $w->longitude : null,
            ])->all(),
        ]);
    }

    /**
     * AJAX: УкрПошта — міста за назвою.
     */
    public function upCities(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json(['items' => []]);
        }
        try {
            $svc = app(\App\Services\UkrPoshtaApiService::class);
            $r = $svc->getCities($q);
            $items = collect($r ?? [])
                ->map(fn ($c) => is_object($c) ? (array) $c : (array) $c)
                ->take(15)
                ->map(fn ($c) => [
                    'id' => (int) ($c['CITY_ID'] ?? 0),
                    'name' => trim(($c['SHORTCITYTYPE_UA'] ?? '').' '.($c['CITY_UA'] ?? $c['CITY_EN'] ?? '')),
                    'region' => $c['REGION_UA'] ?? $c['DISTRICT_UA'] ?? '',
                ])
                ->filter(fn ($c) => $c['name'] !== '' && $c['id'] > 0)
                ->values()->all();

            return response()->json(['items' => $items]);
        } catch (\Throwable $e) {
            \Log::warning('UP cities fetch failed', ['e' => $e->getMessage()]);

            return response()->json(['items' => []]);
        }
    }

    /**
     * AJAX: УкрПошта — поштові відділення для міста.
     */
    public function upPostOffices(Request $request)
    {
        $cityId = (int) $request->query('city_id', 0);
        if ($cityId === 0) {
            return response()->json(['items' => []]);
        }
        try {
            $svc = app(\App\Services\UkrPoshtaApiService::class);
            $r = $svc->getPostOffices($cityId);
            $items = collect($r ?? [])
                ->map(fn ($p) => is_object($p) ? (array) $p : (array) $p)
                ->take(80)
                ->map(fn ($p) => [
                    'id' => (int) ($p['ID'] ?? 0),
                    'name' => $p['PO_SHORT'] ?? $p['PO_LONG'] ?? '',
                    'address' => $p['ADDRESS'] ?? '',
                    'postcode' => (string) ($p['POSTCODE'] ?? $p['POSTINDEX'] ?? ''),
                    'phone' => $p['PHONE'] ?? '',
                ])
                ->filter(fn ($p) => $p['postcode'] !== '' && $p['id'] > 0)
                ->sortBy('postcode')
                ->values()->all();

            return response()->json(['items' => $items]);
        } catch (\Throwable $e) {
            \Log::warning('UP post offices fetch failed', ['e' => $e->getMessage()]);

            return response()->json(['items' => []]);
        }
    }

    /**
     * AJAX: NP розрахунок вартості + дати доставки.
     */
    public function npCalculate(Request $request)
    {
        $cityRef = (string) $request->query('city_ref', '');
        $type = (string) $request->query('type', 'branch'); // branch | postomat | np_courier
        if ($cityRef === '') {
            return response()->json(['cost' => null, 'days' => null]);
        }

        $serviceType = match ($type) {
            'np_courier' => 'WarehouseDoors',
            default => 'WarehouseWarehouse',
        };

        $cart = \App\Helpers\Cart\Cart::getCart();
        $cartTotal = (float) \App\Helpers\Cart\Cart::getCartTotal();
        $weight = max(0.5, collect($cart)->sum(fn ($i) => (float) ($i['weight'] ?? 0.5) * (int) ($i['quantity'] ?? 1)));

        $sender = config('novaposhta.sender_city_ref', '8d5a980d-391c-11dd-90d9-001a92567626');

        try {
            $svc = app(\App\Services\NovaPoshtaApiService::class);
            $priceR = $svc->calculateShippingCost([
                'CitySender' => $sender,
                'CityRecipient' => $cityRef,
                'ServiceType' => $serviceType,
                'Weight' => (string) $weight,
                'Cost' => (string) (int) $cartTotal,
                'CargoType' => 'Parcel',
                'SeatsAmount' => '1',
            ]);
            $cost = $priceR['data'][0]['Cost'] ?? null;

            $dateR = $svc->getEstimatedDeliveryDate($sender, $cityRef, $serviceType);
            $deliveryDate = $dateR['data'][0]['DeliveryDate']['date'] ?? null;
            $days = null;
            if ($deliveryDate) {
                $days = max(1, (int) round((strtotime($deliveryDate) - time()) / 86400));
            }

            return response()->json([
                'cost' => $cost ? (int) $cost : null,
                'days' => $days,
                'date' => $deliveryDate ? \Illuminate\Support\Carbon::parse($deliveryDate)->format('d.m') : null,
            ]);
        } catch (\Throwable $e) {
            \Log::warning('NP calculate failed', ['e' => $e->getMessage()]);

            return response()->json(['cost' => null, 'days' => null]);
        }
    }

    /**
     * AJAX: NP streets for given city (для NP Курʼєра).
     */
    public function npStreets(Request $request)
    {
        $cityRef = (string) $request->query('city_ref', '');
        $q = trim((string) $request->query('q', ''));
        if ($cityRef === '' || mb_strlen($q) < 2) {
            return response()->json(['items' => []]);
        }
        try {
            $svc = app(\App\Services\NovaPoshtaApiService::class);
            $r = $svc->getStreets($cityRef, $q);
            $items = collect($r['data'] ?? [])->map(fn ($s) => [
                'ref' => $s['Ref'] ?? '',
                'name' => trim(($s['StreetsTypeRef_Description'] ?? $s['StreetsType'] ?? 'вул.').' '.($s['Description'] ?? '')),
            ])->all();

            return response()->json(['items' => $items]);
        } catch (\Throwable $e) {
            \Log::warning('NP streets fetch failed', ['e' => $e->getMessage()]);

            return response()->json(['items' => []]);
        }
    }

    /**
     * AJAX endpoint для live-search autocomplete у header.
     */
    public function searchSuggest(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json(['items' => [], 'total' => 0]);
        }

        // Case-insensitive on any driver (SQLite LIKE is ASCII-only CI) —
        // LIKE against several case variants of the query.
        $variants = array_values(array_unique(array_filter([
            $q,
            mb_strtolower($q),
            mb_strtoupper($q),
            mb_convert_case($q, MB_CASE_TITLE),
        ])));
        $searchClosure = function ($w) use ($variants) {
            foreach ($variants as $v) {
                $like = '%'.$v.'%';
                $w->orWhere('sku', 'like', $like)
                  ->orWhere('barcode', 'like', $like)
                  ->orWhere('manufacturer', 'like', $like)
                  ->orWhere('title', 'like', $like);
            }
        };

        $items = Product::query()
            ->where('is_active', true)
            ->where($searchClosure)
            ->orderByDesc('rating')
            ->limit(8)
            ->get(['id', 'title', 'slug', 'sku', 'manufacturer', 'price', 'image']);

        $imgKinds = $this->imageKinds;
        $payload = $items->map(function ($p) use ($imgKinds) {
            $title = is_array($p->title) ? ($p->title['uk'] ?? '') : ($p->title ?? '');
            $slug = is_array($p->slug) ? ($p->slug['uk'] ?? '') : ($p->slug ?? '');
            return [
                'id' => $p->id,
                'title' => $title,
                'sku' => $p->sku,
                'manufacturer' => $p->manufacturer ?: '',
                'price' => (float) $p->price,
                'price_formatted' => number_format((float) $p->price, 0, '.', ' '),
                'image_kind' => $imgKinds[$p->id % count($imgKinds)],
                'url' => route('gazu.product.show', ['slug' => $slug ?: $p->id]),
            ];
        });

        return response()->json([
            'items' => $payload,
            'total' => Product::query()
                ->where('is_active', true)
                ->where($searchClosure)
                ->count(),
            'q' => $q,
        ]);
    }

    public function notFound()
    {
        return response()->view('gazu.404', ['activeNav' => null], 404);
    }

    // -- Car-selector cascade --------------------------------------------------
    //
    // марка → модель → двигун. Endpoints are public + heavily cached (1h)
    // because the data set is small and rarely changes. The hero / catalog
    // selector + the product-page «Підходить чи ні» check all call these.

    public function apiCarMakes()
    {
        $items = \Cache::remember('cars:makes', 3600, function () {
            return \App\Models\CarMake::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(['id', 'slug', 'name'])
                ->all();
        });
        return response()->json(['items' => $items]);
    }

    public function apiCarModels(Request $request)
    {
        $makeKey = (string) $request->query('make', '');
        if ($makeKey === '') return response()->json(['items' => []]);
        $cacheKey = 'cars:models:'.md5($makeKey);
        $items = \Cache::remember($cacheKey, 3600, function () use ($makeKey) {
            $make = \App\Models\CarMake::query()
                ->where(function ($q) use ($makeKey) {
                    $q->where('slug', $makeKey);
                    if (ctype_digit($makeKey)) {
                        $q->orWhere('id', (int) $makeKey);
                    }
                })
                ->first();
            if (! $make) return [];
            return $make->models()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(['id', 'make_id', 'slug', 'name', 'body_type', 'years_range'])
                ->all();
        });
        return response()->json(['items' => $items]);
    }

    /**
     * 4D: «Чи підходить ця запчастина моєму авто?»
     * Returns { fits: bool, engine: {...}, productCompatCount: int }.
     * Engine lookup is by composite (make.slug, model.slug, engine.code).
     */
    public function apiCompatCheck(Request $request)
    {
        $productId = (int) $request->query('product_id', 0);
        $makeSlug  = trim((string) $request->query('make', ''));
        $modelSlug = trim((string) $request->query('model', ''));
        $engineCode = trim((string) $request->query('engine', ''));
        if (! $productId || $makeSlug === '' || $modelSlug === '' || $engineCode === '') {
            return response()->json(['ok' => false, 'message' => 'Заповніть всі поля'], 422);
        }

        $engine = \App\Models\CarEngine::query()
            ->where('car_engines.code', $engineCode)
            ->whereHas('model', function ($q) use ($modelSlug, $makeSlug) {
                $q->where('car_models.slug', $modelSlug)
                  ->whereHas('make', fn ($mq) => $mq->where('car_makes.slug', $makeSlug));
            })
            ->with(['model.make'])
            ->first();
        if (! $engine) {
            return response()->json(['ok' => true, 'fits' => false, 'message' => 'Цей двигун не знайдено в базі.']);
        }

        $fits = \DB::table('product_compatibility')
            ->where('product_id', $productId)
            ->where('engine_id', $engine->id)
            ->exists();

        return response()->json([
            'ok' => true,
            'fits' => $fits,
            'engine' => [
                'id'    => $engine->id,
                'label' => trim(($engine->model->make->name ?? '').' '.($engine->model->name ?? '').' '.($engine->label ?? $engine->code)),
            ],
        ]);
    }

    /**
     * Recently-viewed: повертає список товарів за ID (?ids=1,2,3).
     * Зберігає порядок з input.
     */
    public function apiProductsByIds(Request $request)
    {
        $raw = (string) $request->query('ids', '');
        $ids = array_values(array_filter(array_map('intval', explode(',', $raw)), fn ($x) => $x > 0));
        if (empty($ids)) return response()->json(['items' => []]);
        $ids = array_slice($ids, 0, 12);
        $products = \App\Models\Product::query()
            ->whereIn('id', $ids)
            ->where('is_active', true)
            ->limit(12)
            ->get();
        $orderMap = array_flip($ids);
        $products = $products->sortBy(fn ($p) => $orderMap[$p->id] ?? 999)->values();
        // Repeat the part-image.blade.php logic so recently-viewed gets the SAME
        // photo as catalog cards (resolves from public/img/parts/{kind}/*.webp pool).
        static $partPoolCache = [];
        $resolvePartImage = function (string $kind, ?int $seed) use (&$partPoolCache) {
            if (! array_key_exists($kind, $partPoolCache)) {
                $dir = public_path("img/parts/{$kind}");
                $files = is_dir($dir) ? glob($dir.'/*.webp') : [];
                sort($files);
                $partPoolCache[$kind] = array_map('basename', $files);
            }
            $pool = $partPoolCache[$kind];
            if (! empty($pool)) {
                $idx = $seed !== null ? abs($seed) % count($pool) : 0;
                return asset("img/parts/{$kind}/".$pool[$idx]);
            }
            if (is_file(public_path("img/parts/{$kind}.webp"))) {
                return asset("img/parts/{$kind}.webp");
            }
            return null;
        };

        $items = $products->map(function ($p) use ($resolvePartImage) {
            $title = is_array($p->title) ? ($p->title['uk'] ?? '') : ($p->title ?? '');
            $name = $title ?: ($p->name ?? '');
            $brand = is_object($p->brand ?? null) ? ($p->brand->name ?? '') : (is_string($p->brand ?? null) ? $p->brand : '');
            if (! $brand) $brand = is_array(($p->brand ?? null)) ? ($p->brand['uk'] ?? '') : '';
            $image = null;
            if (! empty($p->image)) {
                $image = \Str::startsWith($p->image, ['http://','https://','/']) ? $p->image : asset('storage/'.$p->image);
            }
            // Fallback на part-image pool (same algorithm as <x-gazu.part-image>).
            // image_kind НЕ в DB — controller hot-injects через ID hash. Repeat те саме.
            if (! $image) {
                $kind = $p->image_kind ?? $this->imageKindFor($p);
                $image = $resolvePartImage((string) $kind, (int) $p->id);
            }
            return [
                'id'    => $p->id,
                'name'  => is_string($name) ? $name : '',
                'brand' => is_string($brand) ? $brand : '',
                'price' => number_format((float) $p->price, 0, '.', ' '),
                'url'   => route('gazu.product.show', ['slug' => $p->slug ?? $p->id]),
                'image' => $image,
            ];
        })->all();
        return response()->json(['items' => $items]);
    }

    public function apiCarEngines(Request $request)
    {
        $makeKey = (string) $request->query('make', '');
        $modelKey = (string) $request->query('model', '');
        if ($modelKey === '') return response()->json(['items' => []]);
        $cacheKey = 'cars:engines:'.md5($makeKey.':'.$modelKey);
        $items = \Cache::remember($cacheKey, 3600, function () use ($makeKey, $modelKey) {
            $q = \App\Models\CarModel::query()->where('is_active', true);
            if ($makeKey !== '') {
                $q->whereHas('make', function ($mq) use ($makeKey) {
                    $mq->where('slug', $makeKey);
                    if (ctype_digit($makeKey)) {
                        $mq->orWhere('id', (int) $makeKey);
                    }
                });
            }
            $q->where(function ($w) use ($modelKey) {
                $w->where('slug', $modelKey);
                if (ctype_digit($modelKey)) {
                    $w->orWhere('id', (int) $modelKey);
                }
            });
            $model = $q->first();
            if (! $model) return [];
            return $model->engines()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(['id', 'model_id', 'code', 'label', 'fuel_type', 'hp', 'years_range'])
                ->all();
        });
        return response()->json(['items' => $items]);
    }

    // Mobile previews
    public function mobile(Request $request, string $page = 'home')
    {
        $page = in_array($page, ['home', 'catalog', 'product', 'cart'], true) ? $page : 'home';

        $data = ['activeNav' => null];

        if ($page === 'catalog') {
            $query = new \App\Services\Gazu\CatalogQuery($request);
            $category = $query->category();
            $paginator = $query->paginate($category);
            $items = collect($paginator->items())->map(fn ($p) => $this->decorate($p));
            if ($items->isEmpty() && ! $request->hasAny(['cat', 'q', 'brand'])) {
                $items = $this->mockProducts(6);
            }
            $data['products'] = $items;
            $data['paginator'] = $paginator;
            $data['category'] = $category;
            $data['totalCount'] = $paginator->total();
            $data['availableBrands'] = $query->availableBrands($category);
        } elseif ($page === 'cart') {
            $cart = \App\Helpers\Cart\Cart::getCart();
            $data['cart'] = $cart;
            $data['cartTotal'] = \App\Helpers\Cart\Cart::getCartTotal();
            $data['products'] = $this->fetchProducts(0);
        } elseif ($page === 'product') {
            $first = Product::query()->where('is_active', true)->orderByDesc('rating')->first();
            $data['product'] = $first ? $this->decorate($first) : $this->mockProducts(1)->first();
            $data['products'] = collect();
        } else {
            $data['products'] = $this->fetchProducts(6);
        }

        return view("gazu.mobile.$page", $data);
    }
}
