<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;

class SitemapController extends Controller
{
    public function index(): Response
    {
        try {
            $cacheKey = 'sitemap_index';
            $cacheDuration = config('seo.sitemap.cache_duration', 1440); // minutes

            $sitemap = Cache::remember($cacheKey, $cacheDuration, function () {
                return $this->generateSitemapIndex();
            });

            return response($sitemap)
                ->header('Content-Type', 'application/xml; charset=utf-8');
        } catch (\Exception $e) {
            // Fallback sitemap у випадку помилки
            $sitemap = '<?xml version="1.0" encoding="UTF-8"?><sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></sitemapindex>';

            return response($sitemap)
                ->header('Content-Type', 'application/xml; charset=utf-8');
        }
    }

    public function main(): Response
    {
        $cacheKey = 'sitemap_main';
        $cacheDuration = config('seo.sitemap.cache_duration', 1440);

        $sitemap = Cache::remember($cacheKey, $cacheDuration, function () {
            return $this->generateMainSitemap();
        });

        return response($sitemap, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }

    public function categories(): Response
    {
        $cacheKey = 'sitemap_categories';
        $cacheDuration = config('seo.sitemap.cache_duration', 1440);

        $sitemap = Cache::remember($cacheKey, $cacheDuration, function () {
            return $this->generateCategoriesSitemap();
        });

        return response($sitemap, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }

    public function products(): Response
    {
        $cacheKey = 'sitemap_products';
        $cacheDuration = config('seo.sitemap.cache_duration', 1440);

        $sitemap = Cache::remember($cacheKey, $cacheDuration, function () {
            return $this->generateProductsSitemap();
        });

        return response($sitemap, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }

    private function generateSitemapIndex(): string
    {
        $sitemaps = [
            [
                'loc' => URL::to('/sitemap-main.xml'),
                'lastmod' => now()->toISOString(),
            ],
            [
                'loc' => URL::to('/sitemap-categories.xml'),
                'lastmod' => optional(Category::max('updated_at'))->toISOString() ?? now()->toISOString(),
            ],
            [
                'loc' => URL::to('/sitemap-products.xml'),
                'lastmod' => optional(Product::max('updated_at'))->toISOString() ?? now()->toISOString(),
            ],
        ];

        return view('sitemap.index', compact('sitemaps'))->render();
    }

    private function generateMainSitemap(): string
    {
        $urls = [];

        $urls[] = [
            'loc' => URL::to('/'),
            'lastmod' => now()->toISOString(),
            'changefreq' => config('seo.sitemap.changefreq.home', 'daily'),
            'priority' => config('seo.sitemap.priorities.home', 1.0),
        ];

        $staticPages = [
            '/about' => ['changefreq' => 'monthly', 'priority' => 0.7],
            '/contacts' => ['changefreq' => 'monthly', 'priority' => 0.8],
            '/delivery' => ['changefreq' => 'monthly', 'priority' => 0.6],
            '/privacy' => ['changefreq' => 'yearly', 'priority' => 0.3],
            '/terms' => ['changefreq' => 'yearly', 'priority' => 0.3],
        ];

        foreach ($staticPages as $url => $options) {
            $urls[] = [
                'loc' => URL::to($url),
                'lastmod' => now()->toISOString(),
                'changefreq' => $options['changefreq'],
                'priority' => $options['priority'],
            ];
        }

        return view('sitemap.urlset', compact('urls'))->render();
    }

    private function generateCategoriesSitemap(): string
    {
        $urls = [];

        $categories = Category::query()
            ->whereHas('seoMeta', function ($query) {
                $query->where('is_active', true)
                    ->where('robots_index', true)
                    ->where('priority', '>', 0);
            })
            ->orWhereDoesntHave('seoMeta')
            ->where('is_active', true)
            ->get();

        foreach ($categories as $category) {
            if (! $category->shouldIncludeInSitemap()) {
                continue;
            }

            $urls[] = [
                'loc' => $category->getCanonicalUrl(),
                'lastmod' => $category->updated_at->toISOString(),
                'changefreq' => $category->getSitemapChangefreq(),
                'priority' => $category->getSitemapPriority(),
            ];
        }

        return view('sitemap.urlset', compact('urls'))->render();
    }

    private function generateProductsSitemap(): string
    {
        $urls = [];

        Product::query()
            ->with(['seoMeta'])
            ->where('is_active', true)
            ->whereHas('seoMeta', function ($query) {
                $query->where('is_active', true)
                    ->where('robots_index', true)
                    ->where('priority', '>', 0);
            })
            ->orWhereDoesntHave('seoMeta')
            ->chunk(1000, function ($products) use (&$urls) {
                foreach ($products as $product) {
                    if (! $product->shouldIncludeInSitemap()) {
                        continue;
                    }

                    $urls[] = [
                        'loc' => $product->getCanonicalUrl(),
                        'lastmod' => $product->updated_at->toISOString(),
                        'changefreq' => $product->getSitemapChangefreq(),
                        'priority' => $product->getSitemapPriority(),
                    ];
                }
            });

        return view('sitemap.urlset', compact('urls'))->render();
    }

    public function clearCache(): Response
    {
        $cacheKeys = [
            'sitemap_index',
            'sitemap_main',
            'sitemap_categories',
            'sitemap_products',
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sitemap cache cleared successfully',
        ]);
    }

    public function robotsTxt(): Response
    {
        $robots = "User-agent: *\n";
        $robots .= "Allow: /\n";
        $robots .= "Disallow: /admin/\n";
        $robots .= "Disallow: /cart\n";
        $robots .= "Disallow: /checkout\n";
        $robots .= "Disallow: /user/\n";
        $robots .= "Disallow: /login\n";
        $robots .= "Disallow: /register\n";
        $robots .= "\n";
        $robots .= 'Sitemap: '.URL::to('/sitemap.xml')."\n";

        return response($robots)
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
