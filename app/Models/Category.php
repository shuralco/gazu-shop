<?php

namespace App\Models;

use App\Traits\HasSeoMeta;
use App\Traits\TranslatableToArray;
use Laravel\Scout\Searchable;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory, HasSeoMeta, HasTranslations, TranslatableToArray, Searchable;

    public array $translatable = ['title', 'meta_title', 'meta_description', 'slug'];

    protected $fillable = ['name', 'title', 'description', 'parent_id', 'is_active', 'image', 'meta_title', 'meta_description', 'meta_keywords', 'slug', 'sort_order', 'depth'];

    protected static function boot(): void
    {
        parent::boot();

        // Auto-generate transliterated slugs per locale
        static::saving(function ($category) {
            if (!config('slugs.auto_transliterate', true)) {
                return;
            }

            $service = app(\App\Services\TransliterationService::class);
            $locales = config('slugs.locales', ['uk', 'en']);

            foreach ($locales as $locale) {
                $title = $category->getTranslation('title', $locale, false);
                if (!$title) {
                    continue;
                }

                $existingSlug = $category->getTranslation('slug', $locale, false);
                if ($existingSlug) {
                    continue;
                }

                $slug = $service->generateSlug($title, $locale);
                $slug = Str::limit($slug, config('slugs.max_length', 100), '');
                $category->setTranslation('slug', $locale, $slug);
            }
        });
    }

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

        foreach (config('slugs.locales', ['uk', 'en']) as $loc) {
            $slug = $this->getTranslation('slug', $loc, false);
            if ($slug) {
                return $slug;
            }
        }

        return $this->getAttributes()['slug'] ?? '';
    }

    /**
     * Канонічний публічний URL категорії. Контракт для theme-agnostic
     * cache-інвалідації (ResponseCacheObserver). Нова тема перевизначає тут.
     */
    public function url(): string
    {
        $slug = $this->getLocalizedSlug();

        return $slug ? url('/'.ltrim($slug, '/')) : route('gazu.catalog');
    }

    /**
     * Find a category by its locale-specific slug.
     */
    public static function findBySlug(string $slug, ?string $locale = null): ?self
    {
        $locale = $locale ?? app()->getLocale();

        $category = static::where("slug->{$locale}", $slug)->first();

        if ($category) {
            return $category;
        }

        foreach (config('slugs.locales', ['uk', 'en']) as $loc) {
            if ($loc === $locale) {
                continue;
            }
            $category = static::where("slug->{$loc}", $slug)->first();
            if ($category) {
                return $category;
            }
        }

        // Legacy fallback
        return static::where('slug', $slug)->first();
    }

    /**
     * Каскад при видаленні: parent_id/products.category_id/category_filters —
     * FK RESTRICT → пряме видалення категорії з дітьми/товарами падало (500).
     * Підкатегорії піднімаємо до батька, товари переносимо до батька, звʼязки
     * category_filters чистимо.
     */
    protected static function booted(): void
    {
        static::deleting(function (self $c) {
            static::where('parent_id', $c->id)->update(['parent_id' => $c->parent_id]);
            if ($c->parent_id) {
                \Illuminate\Support\Facades\DB::table('products')
                    ->where('category_id', $c->id)->update(['category_id' => $c->parent_id]);
            }
            \Illuminate\Support\Facades\DB::table('category_filters')->where('category_id', $c->id)->delete();
        });
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function filterGroups(): BelongsToMany
    {
        return $this->belongsToMany(FilterGroup::class, 'category_filters');
    }

    protected $casts = [
        'is_active' => 'boolean',
        'meta_keywords' => 'array',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeForMegaMenu(Builder $query): Builder
    {
        return $query->with('children')
            ->whereNull('parent_id')
            ->orderBy('sort_order');
    }

    public function getAllLevels(): array
    {
        $result = [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'children' => [],
        ];

        if ($this->children->isNotEmpty()) {
            foreach ($this->children as $child) {
                $result['children'][] = [
                    'id' => $child->id,
                    'title' => $child->title,
                    'slug' => $child->slug,
                ];
            }
        }

        return $result;
    }

    public function getHierarchy(): string
    {
        $level = $this->parent_id ? 1 : 0;
        $prefix = str_repeat('— ', $level);

        return $prefix.$this->title;
    }

    public static function getHierarchicalOptions(): array
    {
        $categories = self::with('children')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        $options = [];
        foreach ($categories as $category) {
            self::buildOptions($category, $options);
        }

        return $options;
    }

    private static function buildOptions(Category $category, array &$options): void
    {
        $options[$category->id] = $category->getHierarchy();

        if ($category->children->isNotEmpty()) {
            foreach ($category->children as $child) {
                self::buildOptions($child, $options);
            }
        }
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->getTranslation('title', 'uk', false) ?? '',
            'title_en' => $this->getTranslation('title', 'en', false) ?? '',
            'slug' => $this->getLocalizedSlug('uk'),
            'products_count' => $this->products()->count(),
            'is_active' => (bool) $this->is_active,
            'parent_id' => $this->parent_id,
        ];
    }

    public function searchableAs(): string
    {
        return 'categories';
    }

    public function shouldBeSearchable(): bool
    {
        return (bool) $this->is_active;
    }

    /**
     * Breadcrumb-style path: "Двигун → Фільтри → Оливні".
     * Used in admin table to show where the category lives in the tree.
     */
    public function getFullPathAttribute(): string
    {
        $titles = [];
        $node = $this;
        $maxDepth = 6; // guard against cyclic self-FK
        while ($node && $maxDepth-- > 0) {
            $title = is_array($node->title)
                ? ($node->title['uk'] ?? $node->title['en'] ?? '')
                : (string) $node->title;
            $titles[] = $title;
            $node = $node->parent_id ? $node->parent : null;
        }

        return implode(' → ', array_reverse(array_filter($titles)));
    }

    /**
     * Depth in the tree: 0 = root, 1 = child of root, 2 = grandchild.
     */
    public function getDepthAttribute(): int
    {
        $depth = 0;
        $node = $this;
        while ($node && $node->parent_id && $depth < 10) {
            $node = $node->parent;
            $depth++;
        }

        return $depth;
    }

    /**
     * Recursive product count over the WHOLE subtree (this category + all
     * descendants). Products usually hang off leaf sub-categories, so the
     * direct `products_count` is 0 for parent nodes — this accessor walks
     * the children tree and sums the real total.
     *
     * Perf: builds two request-static maps with exactly TWO queries total
     * (one grouped product count, one parent_id -> children map), then every
     * subsequent call is pure in-memory recursion. So rendering the admin
     * table (up to 100 rows) costs 2 queries, not 2 per row — no N+1.
     */
    public function getDescendantProductsCountAttribute(): int
    {
        static $directCounts = null;
        static $childrenMap = null;

        if ($directCounts === null) {
            // Direct product count per category — single grouped query.
            $directCounts = Product::query()
                ->selectRaw('category_id, COUNT(*) as aggregate')
                ->whereNotNull('category_id')
                ->groupBy('category_id')
                ->pluck('aggregate', 'category_id')
                ->toArray();

            // parent_id -> [child ids] adjacency map — single lightweight query.
            $childrenMap = [];
            foreach (self::query()->get(['id', 'parent_id']) as $row) {
                $childrenMap[(int) $row->parent_id][] = (int) $row->id;
            }
        }

        $total = 0;
        $stack = [(int) $this->id];
        $guard = 0;
        while ($stack && $guard++ < 5000) {
            $id = array_pop($stack);
            $total += (int) ($directCounts[$id] ?? 0);
            foreach ($childrenMap[$id] ?? [] as $childId) {
                $stack[] = $childId;
            }
        }

        return $total;
    }
}
