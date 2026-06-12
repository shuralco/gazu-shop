<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\DisplaySetting;
use App\Models\Product;
use App\Models\SeoMeta;
use Illuminate\Support\Str;

class SeoMetaGenerator
{
    private array $ukrainianTemplates = [
        'category' => [
            'title' => 'Категорія {title} - купити товари онлайн в {shop_name}',
            'description' => 'Великий вибір товарів у категорії {title}. Швидка доставка по Україні, гарантія якості. Замовляйте в {shop_name} - {product_count} товарів за вигідними цінами.',
            'keywords' => 'категорія {title}, купити {title}, {title} ціна, {title} доставка',
        ],
        'product' => [
            'title' => '{title} - купити за {price} грн з доставкою | {shop_name}',
            'description' => 'Купити {title} за найкращою ціною {price} грн. Детальний опис, фото, відгуки. Швидка доставка по Україні. Замовляйте в {shop_name}.',
            'keywords' => '{title}, купити {title}, {title} ціна, {title} доставка, {category}',
        ],
        'homepage' => [
            'title' => '{shop_name} - інтернет-магазин якісних товарів з доставкою по Україні',
            'description' => 'Інтернет-магазин {shop_name} - великий вибір товарів за вигідними цінами. Швидка доставка, гарантія якості, зручна оплата. Замовляйте онлайн!',
            'keywords' => 'інтернет-магазин, купити товари онлайн, доставка по Україні, {shop_name}',
        ],
        'specials' => [
            'title' => 'Акції та знижки - спеціальні пропозиції | {shop_name}',
            'description' => 'Актуальні акції та спеціальні пропозиції в {shop_name}. Знижки до 50%, розпродажі, бонуси. Встигніть придбати товари за вигідними цінами!',
            'keywords' => 'акції, знижки, спеціальні пропозиції, розпродаж, {shop_name}',
        ],
        'hits' => [
            'title' => 'Хіти продажів - найпопулярніші товари | {shop_name}',
            'description' => 'Хіти продажів у {shop_name} - найпопулярніші товари серед покупців. Перевірена якість, відмінні відгуки. Замовляйте лідерів продажів!',
            'keywords' => 'хіти продажів, популярні товари, бестселери, {shop_name}',
        ],
        'new' => [
            'title' => 'Новинки - останні надходження товарів | {shop_name}',
            'description' => 'Новинки у {shop_name} - останні надходження товарів. Будьте першими, хто спробує нові продукти. Актуальні тренди та інновації.',
            'keywords' => 'новинки, нові товари, останні надходження, {shop_name}',
        ],
        'search' => [
            'title' => 'Пошук товарів по запиту "{query}" | {shop_name}',
            'description' => 'Результати пошуку по запиту "{query}" у {shop_name}. Знайдено {results_count} товарів. Швидка доставка, гарантія якості.',
            'keywords' => 'пошук товарів, {query}, знайти товар, {shop_name}',
        ],
        'brand' => [
            'title' => 'Бренд {brand_name} - купити товари онлайн в {shop_name}',
            'description' => 'Великий асортимент товарів бренду {brand_name}. Оригінальна продукція, гарантія якості, швидка доставка по Україні.',
            'keywords' => '{brand_name}, товари {brand_name}, купити {brand_name}, {brand_name} україна, бренд {brand_name}',
        ],
        'brands_index' => [
            'title' => 'Всі бренди - каталог брендів в {shop_name}',
            'description' => 'Повний каталог брендів в нашому інтернет-магазині. Оригінальні товари від світових виробників з гарантією якості.',
            'keywords' => 'бренди, каталог брендів, товари брендів, оригінальні бренди',
        ],
    ];

    private array $englishTemplates = [
        'category' => [
            'title' => '{title} Category - Buy Online at {shop_name}',
            'description' => 'Wide selection of products in {title} category. Fast delivery across Ukraine, quality guarantee. Order at {shop_name} - {product_count} products at great prices.',
            'keywords' => '{title} category, buy {title}, {title} price, {title} delivery',
        ],
        'product' => [
            'title' => '{title} - Buy for {price} UAH with Delivery | {shop_name}',
            'description' => 'Buy {title} at the best price {price} UAH. Detailed description, photos, reviews. Fast delivery across Ukraine. Order at {shop_name}.',
            'keywords' => '{title}, buy {title}, {title} price, {title} delivery, {category}',
        ],
        'homepage' => [
            'title' => '{shop_name} - Online Store for Quality Products with Ukraine Delivery',
            'description' => 'Online store {shop_name} - wide selection of products at great prices. Fast delivery, quality guarantee, convenient payment. Order online!',
            'keywords' => 'online store, buy products online, Ukraine delivery, {shop_name}',
        ],
        'specials' => [
            'title' => 'Sales & Discounts - Special Offers | {shop_name}',
            'description' => 'Current sales and special offers at {shop_name}. Discounts up to 50%, clearance, bonuses. Don\'t miss great deals!',
            'keywords' => 'sales, discounts, special offers, clearance, {shop_name}',
        ],
        'hits' => [
            'title' => 'Best Sellers - Most Popular Products | {shop_name}',
            'description' => 'Best sellers at {shop_name} - most popular products among customers. Proven quality, excellent reviews. Order bestsellers!',
            'keywords' => 'best sellers, popular products, bestsellers, {shop_name}',
        ],
        'new' => [
            'title' => 'New Arrivals - Latest Products | {shop_name}',
            'description' => 'New arrivals at {shop_name} - latest product additions. Be first to try new products. Current trends and innovations.',
            'keywords' => 'new arrivals, new products, latest additions, {shop_name}',
        ],
        'search' => [
            'title' => 'Search Results for "{query}" | {shop_name}',
            'description' => 'Search results for "{query}" at {shop_name}. Found {results_count} products. Fast delivery, quality guarantee.',
            'keywords' => 'product search, {query}, find product, {shop_name}',
        ],
        'brand' => [
            'title' => 'Brand {brand_name} - Buy Products Online at {shop_name}',
            'description' => 'Wide range of {brand_name} products. Original products, quality guarantee, fast delivery across Ukraine.',
            'keywords' => '{brand_name}, {brand_name} products, buy {brand_name}, {brand_name} ukraine, brand {brand_name}',
        ],
        'brands_index' => [
            'title' => 'All Brands - Brand Catalog at {shop_name}',
            'description' => 'Complete brand catalog at our online store. Original products from global manufacturers with quality guarantee.',
            'keywords' => 'brands, brand catalog, brand products, original brands',
        ],
    ];

    public function generateForCategory(Category $category, string $language = 'uk'): array
    {
        $vars = [
            'name' => (string) $category->title,
            'count' => \plural_uk_count($category->products()->where('is_active', true)->count(), 'товар', 'товари', 'товарів'),
        ];
        $title = \App\Support\SeoTemplates::title('category', $vars);
        $description = \App\Support\SeoTemplates::description('category', $vars);
        $keywords = $category->title.', купити '.strtolower($category->title).', '.strtolower($category->title).' ціна';

        return [
            'meta_title' => $this->limitTitle($title),
            'meta_description' => $this->limitDescription($description),
            'meta_keywords' => $keywords,
            'page_type' => 'category',
            'url_slug' => $category->getLocalizedSlug($language),
        ];
    }

    public function generateForProduct(Product $product, string $language = 'uk'): array
    {
        $formattedPrice = number_format($product->price, 0, ',', ' ');
        $vars = [
            'name' => (string) $product->title,
            'price' => $formattedPrice,
            'sku' => (string) ($product->sku ?? ''),
            'brand' => (string) ($product->manufacturer ?? $product->brandModel?->name ?? ''),
            'category' => (string) ($product->category?->title ?? ''),
            'excerpt' => \Illuminate\Support\Str::limit(strip_tags((string) ($product->excerpt ?? '')), 100, ''),
        ];
        $title = \App\Support\SeoTemplates::title('product', $vars);
        $description = \App\Support\SeoTemplates::description('product', $vars);
        $keywords = $product->title.', купити '.strtolower($product->title).', '.strtolower($product->title).' ціна, '.($product->category?->title ?? 'товари');

        return [
            'meta_title' => $this->limitTitle($title),
            'meta_description' => $this->limitDescription($description),
            'meta_keywords' => $keywords,
            'page_type' => 'product',
            'url_slug' => $product->getLocalizedSlug($language),
        ];
    }

    public function generateForPage(string $pageType, array $data = [], string $language = 'uk'): array
    {
        $templates = $language === 'uk' ? $this->ukrainianTemplates : $this->englishTemplates;

        if (! isset($templates[$pageType])) {
            return $this->getDefaultTemplate($pageType, $language);
        }

        $template = $templates[$pageType];
        $variables = array_merge([
            'shop_name' => config('app.name', 'SimpleShop'),
        ], $data);

        return $this->processTemplate($template, $variables);
    }

    public function generateBulkForCategories(string $language = 'uk'): int
    {
        $generated = 0;

        Category::chunk(50, function ($categories) use ($language, &$generated) {
            foreach ($categories as $category) {
                $seoData = $this->generateForCategory($category, $language);

                SeoMeta::updateOrCreate(
                    [
                        'seoable_type' => Category::class,
                        'seoable_id' => $category->id,
                        'language' => $language,
                    ],
                    array_merge($seoData, [
                        'robots_index' => true,
                        'robots_follow' => true,
                        'is_active' => true,
                        'priority' => 0.7,
                        'changefreq' => 'weekly',
                        'auto_generated' => true,
                    ])
                );

                $generated++;
            }
        });

        return $generated;
    }

    public function generateBulkForProducts(string $language = 'uk'): int
    {
        $generated = 0;

        Product::with('category')->chunk(50, function ($products) use ($language, &$generated) {
            foreach ($products as $product) {
                $seoData = $this->generateForProduct($product, $language);

                SeoMeta::updateOrCreate(
                    [
                        'seoable_type' => Product::class,
                        'seoable_id' => $product->id,
                        'language' => $language,
                    ],
                    array_merge($seoData, [
                        'robots_index' => true,
                        'robots_follow' => true,
                        'is_active' => true,
                        'priority' => 0.8,
                        'changefreq' => 'daily',
                        'auto_generated' => true,
                    ])
                );

                $generated++;
            }
        });

        return $generated;
    }

    public function generateForBrand(Brand $brand, string $language = 'uk'): array
    {
        $templates = $language === 'uk' ? $this->ukrainianTemplates : $this->englishTemplates;
        $template = $templates['brand'];

        $variables = [
            'brand_name' => $brand->name,
            'shop_name' => config('app.name', 'SimpleShop'),
        ];

        return $this->processTemplate($template, $variables);
    }

    public function generateBulkForBrands(string $language = 'uk'): int
    {
        $generated = 0;

        Brand::where('is_active', true)->chunk(50, function ($brands) use ($language, &$generated) {
            foreach ($brands as $brand) {
                $seoData = $this->generateForBrand($brand, $language);

                SeoMeta::updateOrCreate(
                    [
                        'seoable_type' => Brand::class,
                        'seoable_id' => $brand->id,
                        'language' => $language,
                    ],
                    array_merge($seoData, [
                        'robots_index' => true,
                        'robots_follow' => true,
                        'is_active' => true,
                        'priority' => 0.6,
                        'changefreq' => 'monthly',
                        'auto_generated' => true,
                    ])
                );

                $generated++;
            }
        });

        return $generated;
    }

    public function generateBulkForPages(array $pageTypes, string $language = 'uk'): int
    {
        $generated = 0;

        foreach ($pageTypes as $pageType => $data) {
            $seoData = $this->generateForPage($pageType, $data, $language);

            SeoMeta::updateOrCreate(
                [
                    'page_type' => $pageType,
                    'language' => $language,
                ],
                array_merge($seoData, [
                    'url_slug' => $pageType,
                    'robots_index' => true,
                    'robots_follow' => true,
                    'is_active' => true,
                    'priority' => $this->getPagePriority($pageType),
                    'changefreq' => $this->getPageChangefreq($pageType),
                    'auto_generated' => true,
                ])
            );

            $generated++;
        }

        return $generated;
    }

    private function processTemplate(array $template, array $variables): array
    {
        $result = [];

        foreach ($template as $key => $value) {
            $result['meta_'.$key] = $this->replaceVariables($value, $variables);
        }

        // Generate Open Graph data
        $result['og_title'] = $result['og_title'] ?? $result['meta_title'];
        $result['og_description'] = $result['og_description'] ?? $result['meta_description'];

        // Generate Twitter data
        $result['twitter_title'] = $result['twitter_title'] ?? $result['meta_title'];
        $result['twitter_description'] = $result['twitter_description'] ?? $result['meta_description'];

        return $result;
    }

    private function replaceVariables(string $template, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $template = str_replace("{{$key}}", $value, $template);
        }

        return $template;
    }

    private function getDefaultTemplate(string $pageType, string $language): array
    {
        $shopName = config('app.name', 'SimpleShop');
        $pageTitle = Str::title(str_replace(['_', '-'], ' ', $pageType));

        if ($language === 'uk') {
            return [
                'meta_title' => "{$pageTitle} | {$shopName}",
                'meta_description' => "Сторінка {$pageTitle} в інтернет-магазині {$shopName}. Якісні товари, швидка доставка по Україні.",
                'meta_keywords' => "{$pageTitle}, {$shopName}",
            ];
        }

        return [
            'meta_title' => "{$pageTitle} | {$shopName}",
            'meta_description' => "{$pageTitle} page at {$shopName} online store. Quality products, fast delivery across Ukraine.",
            'meta_keywords' => "{$pageTitle}, {$shopName}",
        ];
    }

    private function getPagePriority(string $pageType): float
    {
        return match ($pageType) {
            'homepage' => 1.0,
            'specials', 'hits', 'new' => 0.9,
            'brands_index' => 0.7,
            'brand' => 0.6,
            'about', 'contacts' => 0.6,
            'delivery', 'payment' => 0.5,
            default => 0.4,
        };
    }

    private function getPageChangefreq(string $pageType): string
    {
        return match ($pageType) {
            'homepage' => 'daily',
            'specials', 'hits', 'new' => 'daily',
            'brands_index' => 'weekly',
            'brand' => 'monthly',
            'search' => 'always',
            'about', 'contacts', 'delivery', 'payment' => 'monthly',
            default => 'weekly',
        };
    }

    public function generateKeywords(string $baseKeyword, array $additionalKeywords = [], string $language = 'uk'): string
    {
        $keywords = [$baseKeyword];

        if ($language === 'uk') {
            $keywords = array_merge($keywords, [
                "купити {$baseKeyword}",
                "{$baseKeyword} ціна",
                "{$baseKeyword} доставка",
                "{$baseKeyword} в Україні",
            ]);
        } else {
            $keywords = array_merge($keywords, [
                "buy {$baseKeyword}",
                "{$baseKeyword} price",
                "{$baseKeyword} delivery",
                "{$baseKeyword} Ukraine",
            ]);
        }

        $keywords = array_merge($keywords, $additionalKeywords);

        return implode(', ', array_unique($keywords));
    }

    public function generateCanonicalUrl(string $slug, string $type = 'product', ?string $locale = null): string
    {
        $baseUrl = config('app.url');
        $locale = $locale ?? app()->getLocale();

        return match ($type) {
            'category' => "{$baseUrl}/{$locale}/{$slug}",
            'product' => "{$baseUrl}/{$locale}/{$slug}",
            'brand' => "{$baseUrl}/{$locale}/brands/{$slug}",
            'brands_index' => "{$baseUrl}/{$locale}/brands",
            'page' => "{$baseUrl}/{$locale}/{$slug}",
            default => "{$baseUrl}/{$locale}/{$slug}",
        };
    }

    public function truncateDescription(string $description, int $maxLength = 155): string
    {
        if (mb_strlen($description) <= $maxLength) {
            return $description;
        }

        $truncated = mb_substr($description, 0, $maxLength - 3);
        $lastSpace = mb_strrpos($truncated, ' ');

        if ($lastSpace !== false) {
            $truncated = mb_substr($truncated, 0, $lastSpace);
        }

        return $truncated.'...';
    }

    public function validateSeoData(array $data): array
    {
        $errors = [];
        $limits = $this->getSeoLimits();

        if (empty($data['title'])) {
            $errors[] = 'SEO title обов\'язковий';
        } elseif (mb_strlen($data['title']) > $limits['title_max_length']) {
            $errors[] = "SEO title повинен бути коротше {$limits['title_max_length']} символів";
        }

        if (empty($data['description'])) {
            $errors[] = 'SEO description обов\'язковий';
        } elseif (mb_strlen($data['description']) > $limits['description_max_length']) {
            $errors[] = "SEO description повинен бути коротше {$limits['description_max_length']} символів";
        }

        if (! empty($data['keywords']) && mb_strlen($data['keywords']) > 255) {
            $errors[] = 'SEO keywords повинні бути коротше 255 символів';
        }

        return $errors;
    }

    private function limitTitle(string $title): string
    {
        $maxLength = DisplaySetting::get('seo_title_max_length', 60);

        return Str::limit($title, $maxLength, '');
    }

    private function limitDescription(string $description): string
    {
        $maxLength = DisplaySetting::get('seo_description_max_length', 160);

        return $this->truncateDescription($description, $maxLength);
    }

    public function getSeoLimits(): array
    {
        return [
            'title_min_length' => DisplaySetting::get('seo_title_min_length', 10),
            'title_max_length' => DisplaySetting::get('seo_title_max_length', 60),
            'description_min_length' => DisplaySetting::get('seo_description_min_length', 50),
            'description_max_length' => DisplaySetting::get('seo_description_max_length', 160),
            'keywords_max_count' => DisplaySetting::get('seo_keywords_max_count', 10),
        ];
    }

    public function applyLimitsToExistingRecords(): int
    {
        $limits = $this->getSeoLimits();
        $count = 0;

        // Update Products table
        $products = Product::whereNotNull('meta_title')->get();
        foreach ($products as $product) {
            $updated = false;

            if ($product->meta_title && mb_strlen($product->meta_title) > $limits['title_max_length']) {
                $product->meta_title = $this->limitTitle($product->meta_title);
                $updated = true;
            }

            if ($product->meta_description && mb_strlen($product->meta_description) > $limits['description_max_length']) {
                $product->meta_description = $this->limitDescription($product->meta_description);
                $updated = true;
            }

            if ($updated) {
                $product->save();
                $count++;
            }
        }

        // Update SeoMeta table
        $seoMetas = SeoMeta::all();
        foreach ($seoMetas as $seoMeta) {
            $updated = false;

            if ($seoMeta->meta_title && mb_strlen($seoMeta->meta_title) > $limits['title_max_length']) {
                $seoMeta->meta_title = $this->limitTitle($seoMeta->meta_title);
                $updated = true;
            }

            if ($seoMeta->meta_description && mb_strlen($seoMeta->meta_description) > $limits['description_max_length']) {
                $seoMeta->meta_description = $this->limitDescription($seoMeta->meta_description);
                $updated = true;
            }

            if ($updated) {
                $seoMeta->save();
                $count++;
            }
        }

        return $count;
    }
}
