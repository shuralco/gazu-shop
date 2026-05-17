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
        $cacheKey = 'sitemap_index_v3';
        $cacheDuration = config('seo.sitemap.cache_duration', 1440);

        try {
            $sitemap = Cache::remember($cacheKey, $cacheDuration, function () {
                return $this->generateSitemapIndex();
            });
        } catch (\Throwable $e) {
            \Log::error('[sitemap.index] '.$e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            // Plain inline fallback — no view, no model, no DB.
            $sitemap = '<?xml version="1.0" encoding="UTF-8"?>'.
                '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.
                '<sitemap><loc>'.URL::to('/sitemap-main.xml').'</loc><lastmod>'.now()->toISOString().'</lastmod></sitemap>'.
                '</sitemapindex>';
        }

        return response($sitemap)->header('Content-Type', 'application/xml; charset=utf-8');
    }

    public function main(): Response
    {
        return $this->cachedXml('sitemap_main_v3', fn () => $this->generateMainSitemap());
    }

    public function categories(): Response
    {
        return $this->cachedXml('sitemap_categories_v3', fn () => $this->generateCategoriesSitemap());
    }

    public function products(): Response
    {
        return $this->cachedXml('sitemap_products_v3', fn () => $this->generateProductsSitemap());
    }

    public function brands(): Response
    {
        return $this->cachedXml('sitemap_brands_v1', fn () => $this->generateBrandsSitemap());
    }

    private function cachedXml(string $key, \Closure $generator): Response
    {
        try {
            $sitemap = Cache::remember($key, config('seo.sitemap.cache_duration', 1440), $generator);
        } catch (\Throwable $e) {
            \Log::error('[sitemap '.$key.'] '.$e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            $sitemap = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>';
        }

        return response($sitemap)->header('Content-Type', 'application/xml; charset=utf-8');
    }

    private function generateSitemapIndex(): string
    {
        $now = now()->toISOString();

        // Category/Product::max('updated_at') returns a string, not a
        // Carbon instance, so `optional(...)->toISOString()` always
        // bubbled into the catch block above and we served an empty
        // sitemapindex. Parse explicitly.
        $catMax = Category::max('updated_at');
        $prodMax = Product::max('updated_at');

        $sitemaps = [
            [
                'loc' => URL::to('/sitemap-main.xml'),
                'lastmod' => $now,
            ],
            [
                'loc' => URL::to('/sitemap-categories.xml'),
                'lastmod' => $catMax ? \Carbon\Carbon::parse($catMax)->toISOString() : $now,
            ],
            [
                'loc' => URL::to('/sitemap-products.xml'),
                'lastmod' => $prodMax ? \Carbon\Carbon::parse($prodMax)->toISOString() : $now,
            ],
            [
                'loc' => URL::to('/sitemap-brands.xml'),
                'lastmod' => $now,
            ],
        ];

        return view('sitemap.index', compact('sitemaps'))->render();
    }

    private function generateMainSitemap(): string
    {
        $now = now()->toISOString();
        $urls = [
            ['loc' => URL::to('/'),          'lastmod' => $now, 'changefreq' => 'daily',   'priority' => 1.0],
            ['loc' => URL::to('/catalog'),   'lastmod' => $now, 'changefreq' => 'daily',   'priority' => 0.9],
            ['loc' => URL::to('/brand'),     'lastmod' => $now, 'changefreq' => 'weekly',  'priority' => 0.7],
            ['loc' => URL::to('/blog'),      'lastmod' => $now, 'changefreq' => 'weekly',  'priority' => 0.6],
            ['loc' => URL::to('/sto'),       'lastmod' => $now, 'changefreq' => 'monthly', 'priority' => 0.5],
            ['loc' => URL::to('/contacts'),  'lastmod' => $now, 'changefreq' => 'monthly', 'priority' => 0.5],
        ];
        return $this->buildUrlset($urls);
    }

    private function generateCategoriesSitemap(): string
    {
        $urls = [];
        Category::query()->where('is_active', true)->select(['id', 'slug', 'updated_at'])->chunk(500, function ($rows) use (&$urls) {
            foreach ($rows as $cat) {
                $slug = $cat->getRawOriginal('slug');
                if (is_string($slug) && str_starts_with($slug, '{')) {
                    $decoded = json_decode($slug, true);
                    $slug = $decoded['uk'] ?? $decoded['en'] ?? null;
                }
                if (! $slug) continue;
                $urls[] = [
                    // SEO URL: `/category-slug` (resolveSlug dispatches to catalog).
                    'loc' => URL::to('/'.$slug),
                    'lastmod' => optional($cat->updated_at)->toISOString() ?? now()->toISOString(),
                    'changefreq' => 'weekly',
                    'priority' => 0.7,
                ];
            }
        });
        return $this->buildUrlset($urls);
    }

    private function generateBrandsSitemap(): string
    {
        $urls = [
            ['loc' => URL::to('/brand'), 'lastmod' => now()->toISOString(), 'changefreq' => 'weekly', 'priority' => 0.7],
        ];
        if (\Schema::hasTable('brands')) {
            \App\Models\Brand::query()
                ->when(\Schema::hasColumn('brands', 'is_active'), fn ($q) => $q->where('is_active', true))
                ->select(['id', 'slug', 'updated_at'])
                ->chunk(500, function ($rows) use (&$urls) {
                    foreach ($rows as $brand) {
                        $slug = $brand->slug;
                        if (! $slug) continue;
                        $urls[] = [
                            'loc' => URL::to('/brand/'.$slug),
                            'lastmod' => optional($brand->updated_at)->toISOString() ?? now()->toISOString(),
                            'changefreq' => 'weekly',
                            'priority' => 0.6,
                        ];
                    }
                });
        }
        return $this->buildUrlset($urls);
    }

    private function generateProductsSitemap(): string
    {
        $urls = [];
        Product::query()->where('is_active', true)->select(['id', 'slug', 'updated_at'])->chunk(1000, function ($rows) use (&$urls) {
            foreach ($rows as $product) {
                $rawSlug = $product->getRawOriginal('slug');
                // Unwrap translatable JSON column → plain slug.
                if (is_string($rawSlug) && str_starts_with($rawSlug, '{')) {
                    $decoded = json_decode($rawSlug, true);
                    $rawSlug = $decoded['uk'] ?? $decoded['en'] ?? null;
                }
                $slug = $rawSlug ?: (string) $product->id;
                $urls[] = [
                    // Root-level Rozetka-style URL: `/<slug>` (no /product/ prefix).
                    'loc' => URL::to('/'.$slug),
                    'lastmod' => optional($product->updated_at)->toISOString() ?? now()->toISOString(),
                    'changefreq' => 'weekly',
                    'priority' => 0.8,
                ];
            }
        });
        return $this->buildUrlset($urls);
    }

    /**
     * Render <urlset> from a plain array — no view, no model methods,
     * no risk of HasTranslations/SeoMeta accessor throwing.
     */
    private function buildUrlset(array $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'.
               '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($urls as $u) {
            $xml .= '<url>'.
                '<loc>'.htmlspecialchars((string) $u['loc'], ENT_XML1).'</loc>'.
                '<lastmod>'.htmlspecialchars((string) ($u['lastmod'] ?? now()->toISOString()), ENT_XML1).'</lastmod>'.
                '<changefreq>'.($u['changefreq'] ?? 'weekly').'</changefreq>'.
                '<priority>'.number_format((float) ($u['priority'] ?? 0.5), 1).'</priority>'.
                '</url>';
        }
        $xml .= '</urlset>';
        return $xml;
    }

    public function clearCache(): Response
    {
        $cacheKeys = [
            'sitemap_index_v2',
            'sitemap_main_v2',
            'sitemap_categories_v2',
            'sitemap_products_v2',
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
        // Site-wide no-index toggle for staging/презентаційний домен.
        // Default TRUE — поки не виключили вручну в адмінці, домен закритий.
        $noindexAll = (bool) (\App\Models\DisplaySetting::get('seo_noindex_all', true));
        if ($noindexAll) {
            $robots = "User-agent: *\nDisallow: /\n";
            return response($robots)->header('Content-Type', 'text/plain; charset=UTF-8');
        }

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
