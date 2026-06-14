<?php

namespace App\Models;

use App\Services\LemmatizationService;
use App\Traits\HasSeoMeta;
use App\Traits\TranslatableToArray;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;

class Product extends Model
{
    use HasFactory, HasSeoMeta, HasTranslations, TranslatableToArray, Searchable;

    public array $translatable = ['title', 'excerpt', 'content', 'meta_title', 'meta_description', 'slug'];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->sku)) {
                $product->sku = self::generateUniqueSku();
            }
        });

        // Auto-generate transliterated slugs per locale
        static::saving(function ($product) {
            if (!config('slugs.auto_transliterate', true)) {
                return;
            }

            $service = app(\App\Services\TransliterationService::class);
            $locales = config('slugs.locales', ['uk', 'en']);

            foreach ($locales as $locale) {
                $title = $product->getTranslation('title', $locale, false);
                if (!$title) {
                    continue;
                }

                // Only generate if slug is empty for this locale
                $existingSlug = $product->getTranslation('slug', $locale, false);
                if ($existingSlug) {
                    continue;
                }

                $slug = $service->generateSlug($title, $locale);

                if (config('slugs.append_id', true) && $product->id) {
                    $slug .= config('slugs.separator', '-') . $product->id;
                }

                $slug = Str::limit($slug, config('slugs.max_length', 100), '');
                $product->setTranslation('slug', $locale, $slug);
            }
        });

        // After creating, if ID was not available during saving, regenerate slugs with ID
        static::created(function ($product) {
            if (!config('slugs.append_id', true) || !config('slugs.auto_transliterate', true)) {
                return;
            }

            $service = app(\App\Services\TransliterationService::class);
            $locales = config('slugs.locales', ['uk', 'en']);
            $needsUpdate = false;

            foreach ($locales as $locale) {
                $title = $product->getTranslation('title', $locale, false);
                $currentSlug = $product->getTranslation('slug', $locale, false);

                if (!$title) {
                    continue;
                }

                // Check if slug was generated without ID (during create)
                $idSuffix = config('slugs.separator', '-') . $product->id;
                if ($currentSlug && !str_ends_with($currentSlug, $idSuffix)) {
                    $slug = $service->generateSlug($title, $locale) . $idSuffix;
                    $slug = Str::limit($slug, config('slugs.max_length', 100), '');
                    $product->setTranslation('slug', $locale, $slug);
                    $needsUpdate = true;
                }
            }

            if ($needsUpdate) {
                $product->saveQuietly();
            }
        });

        // Smart cache invalidation (без tags для SQLite)
        static::saved(function ($product) {
            Cache::forget("category_filters_{$product->category_id}");
            Cache::forget("category_brands_{$product->category_id}");
            Cache::forget('home_page_data');

            $pattern = "filtered_products_{$product->category_id}_*";
            self::clearCacheByPattern($pattern);

            if ($product->is_hit) {
                Cache::forget('hit_products_'.\App\Models\DisplaySetting::get('hit_products_count', 4));
            }
            if ($product->is_new) {
                Cache::forget('new_products_'.\App\Models\DisplaySetting::get('new_products_count', 8));
            }

            // Invalidate CacheService product caches
            app(\App\Services\CacheService::class)->invalidateProductCaches();
        });

        static::deleted(function ($product) {
            Cache::forget("category_filters_{$product->category_id}");
            Cache::forget("category_brands_{$product->category_id}");
            Cache::forget('home_page_data');
            self::clearCacheByPattern("filtered_products_{$product->category_id}_*");

            // Invalidate CacheService product caches
            app(\App\Services\CacheService::class)->invalidateProductCaches();
        });
    }

    protected $fillable = [
        'name', 'title', 'slug', 'sku', 'barcode',
        'category_id', 'brand_id', 'manufacturer',
        'price', 'old_price', 'weight', 'dimensions',
        'length', 'width', 'height',
        'excerpt', 'content', 'description',
        'meta_title', 'meta_description', 'meta_keywords', 'search_tags',
        'quantity', 'stock_status', 'min_quantity',
        'image', 'gallery',
        'is_hit', 'is_new', 'is_active',
        'rating', 'reviews_count',
        'wholesale_min_quantity',
        // Quick Fill module — поля для роботи з китайським постачальником
        'cost_price', 'cost_currency', 'markup_percent', 'supplier_url', 'original_name_cn', 'condition',
        // GAZU product page — JSON блоки
        'specifications', 'compatibility', 'analogs',
    ];

    protected $casts = [
        'is_hit' => 'boolean',
        'is_new' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'old_price' => 'decimal:2',
        'weight' => 'decimal:3',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'rating' => 'decimal:2',
        'gallery' => 'array',
        'meta_keywords' => 'array',
        'min_quantity' => 'integer',
        'reviews_count' => 'integer',
        'wholesale_min_quantity' => 'integer',
        'cost_price' => 'decimal:2',
        'markup_percent' => 'decimal:2',
        'specifications' => 'array',
        'compatibility' => 'array',
        'analogs' => 'array',
    ];

    /**
     * Get the locale-aware slug for the current or given locale.
     */
    public function getLocalizedSlug(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $slug = $this->getTranslation('slug', $locale, false);

        if ($slug) {
            return $slug;
        }

        // Fallback: try other locales
        foreach (config('slugs.locales', ['uk', 'en']) as $loc) {
            $slug = $this->getTranslation('slug', $loc, false);
            if ($slug) {
                return $slug;
            }
        }

        // Last resort: return raw attribute (for legacy non-JSON slugs)
        return $this->getAttributes()['slug'] ?? '';
    }

    /**
     * Канонічний публічний URL товару. Контракт для theme-agnostic
     * cache-інвалідації (ResponseCacheObserver використовує $model->url()
     * замість хардкод-роуту). Нова тема перевизначає тут.
     */
    public function url(): string
    {
        return route('gazu.product.show', ['slug' => $this->getLocalizedSlug() ?: (string) $this->id]);
    }

    /**
     * Find a product by its locale-specific slug.
     */
    public static function findBySlug(string $slug, ?string $locale = null): ?self
    {
        $locale = $locale ?? app()->getLocale();

        // Try current locale first
        $product = static::where("slug->{$locale}", $slug)->first();

        if ($product) {
            return $product;
        }

        // Try all locales as fallback
        foreach (config('slugs.locales', ['uk', 'en']) as $loc) {
            if ($loc === $locale) {
                continue;
            }
            $product = static::where("slug->{$loc}", $slug)->first();
            if ($product) {
                return $product;
            }
        }

        // Legacy fallback: plain slug (non-JSON)
        return static::where('slug', $slug)->first();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brandModel(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    /**
     * Per-warehouse stock rows. Replaces the legacy single
     * products.quantity column once Phase 2 ships.
     */
    public function inventory(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    /**
     * Engines this part is compatible with (марка → модель → двигун).
     * Powers the "Підходить чи ні?" check on the product page and the
     * car-selector filter in catalog/hero.
     */
    public function compatibleEngines(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            CarEngine::class,
            'product_compatibility',
            'product_id',
            'engine_id'
        )->withPivot('note')->withTimestamps();
    }

    /**
     * Stock count at a specific warehouse (or default if null).
     */
    public function inventoryFor(MerchantWarehouse|int|null $warehouse = null): ?Inventory
    {
        $warehouseId = $warehouse instanceof MerchantWarehouse
            ? $warehouse->id
            : ($warehouse ?? optional(MerchantWarehouse::default())->id);

        if (! $warehouseId) {
            return null;
        }

        return $this->inventory()->where('warehouse_id', $warehouseId)->first();
    }

    /**
     * Total available units across all active warehouses (qty - reserved).
     * Falls back to legacy products.quantity if inventory rows missing.
     */
    public function totalAvailableQuantity(): int
    {
        $rows = $this->relationLoaded('inventory')
            ? $this->inventory
            : $this->inventory()->get(['quantity', 'reserved_quantity']);

        if ($rows->isEmpty()) {
            return (int) ($this->attributes['quantity'] ?? 0);
        }

        return (int) $rows->sum(fn ($row) => max(0, $row->quantity - $row->reserved_quantity));
    }

    // Helper для очищення кешу по паттерну
    private static function clearCacheByPattern(string $pattern): void
    {
        // Для file cache можемо видалити файли по паттерну
        $cacheDir = storage_path('framework/cache/data');
        if (is_dir($cacheDir)) {
            $files = glob($cacheDir.'/*'.str_replace(['*', '_'], ['*', '\\_'], $pattern).'*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }
    }

    // Scope для кешованих фільтрованих товарів
    public function scopeCachedFiltered(Builder $query, int $categoryId, array $filters = [], array $brands = [], ?float $minPrice = null, ?float $maxPrice = null): Builder
    {
        $filtersHash = md5(serialize([$filters, $brands, $minPrice, $maxPrice]));
        $cacheKey = "filtered_products_{$categoryId}_{$filtersHash}";

        return Cache::remember($cacheKey, 900, function () use ($query, $categoryId, $filters, $brands, $minPrice, $maxPrice) {
            return $query->select([
                'products.id', 'products.title', 'products.slug', 'products.price',
                'products.old_price', 'products.image', 'products.category_id',
                'products.brand_id', 'products.is_hit', 'products.is_new',
                'brands.name as brand_name', 'brands.logo as brand_logo',
            ])
                ->leftJoin((new Brand)->getTable(), (new self)->getTable().'.brand_id', '=', (new Brand)->getTable().'.id')
                ->where((new self)->getTable().'.category_id', $categoryId)
                ->when($filters, function ($q) use ($filters) {
                    $cnt_filter_groups = \App\Models\Filter::selectRaw('count(distinct filter_group_id) as cnt')
                        ->whereIn('id', $filters)
                        ->value('cnt') ?: 1;

                    $q->whereHas('filters', function ($subQuery) use ($filters) {
                        $subQuery->whereIn('filter_id', $filters);
                    })
                        ->whereIn('products.id', function ($subQuery) use ($filters, $cnt_filter_groups) {
                            $subQuery->select('product_id')
                                ->from('filter_products')
                                ->whereIn('filter_id', $filters)
                                ->groupBy('product_id')
                                ->havingRaw('count(distinct filter_group_id) >= ?', [$cnt_filter_groups]);
                        });
                })
                ->when($brands, function ($q) use ($brands) {
                    $q->whereIn('products.brand_id', $brands);
                })
                ->when($minPrice !== null && $maxPrice !== null, function ($q) use ($minPrice, $maxPrice) {
                    $q->whereBetween('products.price', [$minPrice, $maxPrice]);
                })
                ->get();
        });
    }

    public function groupPrices(): HasMany
    {
        return $this->hasMany(ProductGroupPrice::class);
    }

    public function wishlistedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'wishlists');
    }

    public function getPriceForUser(?User $user = null): float
    {
        if (!$user || !$user->customer_group_id) {
            return (float) $this->price;
        }

        $groupPrice = $this->groupPrices()
            ->where('customer_group_id', $user->customer_group_id)
            ->first();

        if ($groupPrice) {
            return (float) $groupPrice->price;
        }

        $group = $user->customerGroup;

        if ($group && $group->discount_percentage > 0) {
            return round($this->price * (1 - $group->discount_percentage / 100), 2);
        }

        return (float) $this->price;
    }

    public function relatedProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'related_products', 'product_id', 'related_product_id')
            ->withPivot('type', 'sort_order')
            ->withTimestamps();
    }

    /**
     * Товари-аналоги (замінники) — реальні товари каталогу через
     * related_products(type=analog). withPivotValue і фільтрує по типу,
     * і проставляє type=analog при attach/sync з адмінки.
     */
    public function analogProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'related_products', 'product_id', 'related_product_id')
            ->withPivotValue('type', 'analog')
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    public function filters(): BelongsToMany
    {
        return $this->belongsToMany(Filter::class, 'filter_products')
            ->using(\App\Models\Pivots\FilterProduct::class)
            ->withPivot('filter_group_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(ProductOption::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function hasVariants(): bool
    {
        return $this->variants()->where('is_active', true)->exists();
    }

    protected function gallery(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                $gallery = $value ? json_decode($value, true) : [];

                // Ensure all gallery images have proper paths
                return array_map(function ($img) {
                    // Skip empty values
                    if (empty($img)) {
                        return null;
                    }

                    // If it's just a number like "4.jpg", prepend the full path
                    if (preg_match('/^\d+\.jpg$/i', $img)) {
                        return '/assets/img/products/'.$img;
                    }

                    // Ensure leading slash
                    return str_starts_with($img, '/') ? $img : '/'.$img;
                }, array_filter($gallery));
            },
            set: fn ($value) => $value ? json_encode($value) : null
        );
    }

    public function orderProducts(): HasMany
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('status', Review::STATUS_APPROVED);
    }

    public function updateRatingFromReviews(): void
    {
        $reviews = $this->approvedReviews;

        if ($reviews->count() > 0) {
            $this->rating = $reviews->avg('rating');
            $this->reviews_count = $reviews->count();
        } else {
            $this->rating = 0;
            $this->reviews_count = 0;
        }

        $this->saveQuietly();
    }

    /**
     * Volume weight (kg) by Nova Poshta formula: L*W*H / 4000 (cm³ → kg).
     * Returns 0 if any dimension is missing.
     */
    public function getVolumeWeight(): float
    {
        if (! $this->length || ! $this->width || ! $this->height) {
            return 0.0;
        }
        return round(((float) $this->length * (float) $this->width * (float) $this->height) / 4000, 3);
    }

    /**
     * Effective shipping weight = MAX(actual weight, volume weight).
     * Default to 0.5kg for products without weight set.
     */
    public function getShippingWeight(): float
    {
        $actual = (float) ($this->weight ?? 0);
        $volume = $this->getVolumeWeight();
        return max($actual, $volume, 0.5);
    }

    public function getImage()
    {
        if ($this->image) {
            // If image path doesn't start with /, add it
            $imagePath = str_starts_with($this->image, '/') ? $this->image : '/'.$this->image;

            // Remove any double slashes that might occur
            return preg_replace('/\/+/', '/', $imagePath);
        }

        return '/assets/img/default-product.jpg';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeHits(Builder $query): Builder
    {
        return $query->where('is_hit', true);
    }

    public function scopeNew(Builder $query): Builder
    {
        return $query->where('is_new', true);
    }

    public function media()
    {
        // Media table is not properly configured for polymorphic relationships
        // Return empty collection to prevent SQL errors
        return collect([]);
    }

    public static function generateUniqueSku(): string
    {
        do {
            // Generate simple SKU format: XXXXXX (e.g., 123456)
            $sku = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
        } while (self::where('sku', $sku)->exists());

        return $sku;
    }

    public function toSearchableArray(): array
    {
        $titleUk = $this->getTranslation('title', 'uk', false) ?? '';
        $titleEn = $this->getTranslation('title', 'en', false) ?? '';
        $excerptUk = $this->getTranslation('excerpt', 'uk', false) ?? '';
        $excerptEn = $this->getTranslation('excerpt', 'en', false) ?? '';

        return [
            'id' => $this->id,
            'title' => $titleUk,
            'title_en' => $titleEn,
            'slug' => $this->getLocalizedSlug('uk'),
            'excerpt' => $excerptUk,
            'excerpt_en' => $excerptEn,
            'content' => mb_substr(strip_tags($this->getTranslation('content', 'uk', false) ?? ''), 0, 500),
            'sku' => $this->sku,
            'brand' => $this->brandModel?->name ?? $this->brand ?? '',
            'manufacturer' => $this->manufacturer ?? '',
            'category_id' => $this->category_id,
            'category_title' => $this->category?->getTranslation('title', 'uk', false) ?? '',
            'category_title_en' => $this->category?->getTranslation('title', 'en', false) ?? '',
            'price' => (float) $this->price,
            'old_price' => (float) ($this->old_price ?? 0),
            'discount_percent' => $this->old_price > 0 ? round((1 - $this->price / $this->old_price) * 100) : 0,
            'is_hit' => (bool) $this->is_hit,
            'is_new' => (bool) $this->is_new,
            'is_active' => (bool) $this->is_active,
            'is_special' => $this->old_price > $this->price,
            'rating' => (float) ($this->rating ?? 0),
            'reviews_count' => (int) ($this->reviews_count ?? 0),
            'created_at' => $this->created_at?->timestamp ?? 0,
            'options_text' => $this->getOptionsSearchText(),
            'search_tags' => $this->search_tags ?? '',
            'title_lemmas' => app(LemmatizationService::class)->lemmatize(
                $titleUk . ' ' . ($this->brandModel?->name ?? '')
            ),
        ];
    }

    private function getOptionsSearchText(): string
    {
        if (!$this->relationLoaded('variants')) {
            $this->load('variants.optionValues.option');
        }

        $texts = [];

        foreach ($this->variants ?? [] as $variant) {
            foreach ($variant->optionValues ?? [] as $ov) {
                $texts[] = ($ov->option->name ?? '') . ' ' . $ov->value;
            }
        }

        return implode(' ', array_unique($texts));
    }

    public function searchableAs(): string
    {
        return 'products';
    }

    public function shouldBeSearchable(): bool
    {
        return (bool) $this->is_active;
    }
}
