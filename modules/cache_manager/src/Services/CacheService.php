<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class CacheService
{
    /**
     * Cache TTL constants in seconds.
     */
    private const TTL_LONG = 3600;    // 1 hour

    private const TTL_MEDIUM = 1800;  // 30 minutes

    /**
     * Get all active categories (cached for 1 hour).
     */
    public function getCategories(): Collection
    {
        return Cache::remember('cache_service_categories', self::TTL_LONG, function () {
            return Category::query()
                ->select(['id', 'title', 'slug', 'parent_id', 'image', 'sort_order'])
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();
        });
    }

    /**
     * Get all active brands (cached for 1 hour).
     */
    public function getBrands(): Collection
    {
        return Cache::remember('cache_service_brands', self::TTL_LONG, function () {
            return Brand::query()
                ->select(['id', 'name', 'slug', 'logo'])
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Get hit (bestseller) products (cached for 30 min).
     */
    public function getHitProducts(int $limit = 8): Collection
    {
        return Cache::remember("cache_service_hit_products_{$limit}", self::TTL_MEDIUM, function () use ($limit) {
            return Product::query()
                ->select(['id', 'title', 'slug', 'price', 'old_price', 'image', 'category_id', 'brand_id', 'is_hit', 'is_new'])
                ->with(['brandModel:id,name,logo,slug', 'filters.filterGroup:id,title'])
                ->where('is_active', true)
                ->where('is_hit', true)
                ->orderByDesc('id')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Get new products (cached for 30 min).
     */
    public function getNewProducts(int $limit = 8): Collection
    {
        return Cache::remember("cache_service_new_products_{$limit}", self::TTL_MEDIUM, function () use ($limit) {
            return Product::query()
                ->select(['id', 'title', 'slug', 'price', 'old_price', 'image', 'category_id', 'brand_id', 'is_hit', 'is_new'])
                ->with(['brandModel:id,name,logo,slug', 'filters.filterGroup:id,title'])
                ->where('is_active', true)
                ->where('is_new', true)
                ->orderByDesc('id')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Invalidate all product-related caches.
     */
    public function invalidateProductCaches(): void
    {
        // Hit product caches (common limits)
        foreach ([4, 8, 12, 16, 20] as $limit) {
            Cache::forget("cache_service_hit_products_{$limit}");
            Cache::forget("cache_service_new_products_{$limit}");
        }

        // Homepage and category caches
        Cache::forget('home_page_data');
        Cache::forget('homepage_modules');
        Cache::forget('cache_service_categories');
        Cache::forget('cache_service_brands');
    }

    /**
     * Invalidate category-specific caches.
     */
    public function invalidateCategoryCaches(?int $categoryId = null): void
    {
        Cache::forget('cache_service_categories');

        if ($categoryId) {
            Cache::forget("category_filters_{$categoryId}");
            Cache::forget("category_brands_{$categoryId}");
        }
    }

    /**
     * Invalidate brand caches.
     */
    public function invalidateBrandCaches(): void
    {
        Cache::forget('cache_service_brands');
    }
}
