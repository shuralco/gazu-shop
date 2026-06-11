<?php

namespace App\Services\FeedGenerator;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class YmlFeedGenerator
{
    public function generate(string $type = 'google', array $options = []): string
    {
        $cacheKey = "product_feed_{$type}_".md5(json_encode($options));
        return Cache::remember($cacheKey, 3600, function () use ($type, $options) {
            return $this->buildFeed($type, $options);
        });
    }

    public function clearCache(?string $type = null): void
    {
        // Clear all known feed cache entries
        $types = $type ? [$type] : ['google', 'rozetka', 'prom', 'olx'];
        foreach ($types as $t) {
            Cache::forget("product_feed_{$t}");
            // Also wipe any options-keyed variants — best effort
            Cache::forget("product_feed_{$t}_d41d8cd98f00b204e9800998ecf8427e");
        }
    }

    public function lastGeneratedAt(string $type): ?\Carbon\Carbon
    {
        $key = "product_feed_{$type}";
        if (! Cache::has($key) && ! Cache::has($key.'_d41d8cd98f00b204e9800998ecf8427e')) {
            return null;
        }
        // Cache has no built-in created_at — use store metadata if file driver
        return Cache::get($key.'_at') ? \Carbon\Carbon::parse(Cache::get($key.'_at')) : null;
    }

    private function buildFeed(string $type, array $options = []): string
    {
        Cache::put("product_feed_{$type}_at", now()->toDateTimeString(), 3600);

        if ($type === 'olx') {
            return $this->buildOlxFeed($options);
        }
        return $this->buildYmlFeed($type, $options);
    }

    private function buildYmlFeed(string $type, array $options): string
    {
        $shopName = shopSetting('shop_name', 'GAZU');
        $shopUrl = config('app.url');
        $currency = shopSetting('shop_currency', 'UAH');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<yml_catalog date="' . now()->format('Y-m-d H:i') . '">' . "\n";
        $xml .= '<shop>' . "\n";
        $xml .= "<name>{$shopName}</name>\n";
        $xml .= "<company>{$shopName}</company>\n";
        $xml .= "<url>{$shopUrl}</url>\n";

        // Currencies
        $xml .= "<currencies>\n";
        $xml .= '<currency id="UAH" rate="1"/>' . "\n";
        $xml .= "</currencies>\n";

        // Categories
        $xml .= "<categories>\n";
        $categories = Category::where('is_active', true)->get();
        foreach ($categories as $category) {
            $parentAttr = $category->parent_id ? ' parentId="' . $category->parent_id . '"' : '';
            $xml .= '<category id="' . $category->id . '"' . $parentAttr . '>' . htmlspecialchars($category->title) . '</category>' . "\n";
        }
        $xml .= "</categories>\n";

        // Offers (products)
        $xml .= "<offers>\n";
        $this->applyFilters(Product::where('is_active', true)->with(['category', 'brandModel']), $options)
            ->chunk(200, function ($products) use (&$xml, $shopUrl, $currency, $type) {
                foreach ($products as $product) {
                    $xml .= $this->buildOffer($product, $shopUrl, $currency, $type);
                }
            });
        $xml .= "</offers>\n";

        $xml .= "</shop>\n";
        $xml .= '</yml_catalog>';

        return $xml;
    }

    private function buildOffer(Product $product, string $shopUrl, string $currency, string $type): string
    {
        $available = $product->stock_status === 'in_stock' ? 'true' : 'false';
        $xml = '<offer id="' . $product->id . '" available="' . $available . '">' . "\n";
        $xml .= '<url>' . $shopUrl . '/' . $product->getLocalizedSlug('uk') . '</url>' . "\n";
        $xml .= '<price>' . number_format($product->price, 2, '.', '') . '</price>' . "\n";

        if ($product->old_price > 0 && $product->old_price > $product->price) {
            $xml .= '<oldprice>' . number_format($product->old_price, 2, '.', '') . '</oldprice>' . "\n";
        }

        $xml .= '<currencyId>' . $currency . '</currencyId>' . "\n";
        $xml .= '<categoryId>' . $product->category_id . '</categoryId>' . "\n";

        if ($product->image) {
            $xml .= '<picture>' . $shopUrl . '/storage/' . $product->image . '</picture>' . "\n";
        }

        $xml .= '<name>' . htmlspecialchars($product->title) . '</name>' . "\n";

        if ($product->excerpt) {
            $xml .= '<description>' . htmlspecialchars($product->excerpt) . '</description>' . "\n";
        }

        if ($product->brandModel) {
            $xml .= '<vendor>' . htmlspecialchars($product->brandModel->name) . '</vendor>' . "\n";
        }

        if ($product->sku) {
            $xml .= '<vendorCode>' . htmlspecialchars($product->sku) . '</vendorCode>' . "\n";
        }

        if ($product->barcode) {
            $xml .= '<barcode>' . $product->barcode . '</barcode>' . "\n";
        }

        // Google Shopping specific
        if ($type === 'google') {
            $xml .= '<g:condition>new</g:condition>' . "\n";
            $xml .= '<g:availability>' . ($product->stock_status === 'in_stock' ? 'in stock' : 'out of stock') . '</g:availability>' . "\n";
        }

        $xml .= '</offer>' . "\n";

        return $xml;
    }

    /**
     * Застосовує фільтри (тільки в наявності, з фото, певні категорії, бренди).
     */
    private function applyFilters(\Illuminate\Database\Eloquent\Builder $q, array $options): \Illuminate\Database\Eloquent\Builder
    {
        if (! empty($options['only_in_stock'])) {
            $q->where('stock_status', 'in_stock');
        }
        if (! empty($options['only_with_image'])) {
            $q->whereNotNull('image')->where('image', '!=', '');
        }
        if (! empty($options['only_with_price'])) {
            $q->where('price', '>', 0);
        }
        if (! empty($options['category_ids']) && is_array($options['category_ids'])) {
            $q->whereIn('category_id', $options['category_ids']);
        }
        if (! empty($options['brand_ids']) && is_array($options['brand_ids'])) {
            $q->whereIn('brand_id', $options['brand_ids']);
        }
        if (! empty($options['min_price'])) {
            $q->where('price', '>=', (float) $options['min_price']);
        }
        if (! empty($options['max_price'])) {
            $q->where('price', '<=', (float) $options['max_price']);
        }

        return $q;
    }

    /**
     * OLX adverts.xml — простіший формат: список <ad> з обов'язковими полями.
     * Документація OLX UA: https://help.olx.ua/hc/uk/articles/360020268673
     */
    private function buildOlxFeed(array $options): string
    {
        $shopName = shopSetting('shop_name', 'GAZU');
        $shopUrl = config('app.url');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<adverts generated="'.now()->toIso8601String().'" shop="'.htmlspecialchars($shopName).'">'."\n";

        $this->applyFilters(Product::where('is_active', true)->with(['category', 'brandModel']), $options)
            ->chunk(200, function ($products) use (&$xml, $shopUrl) {
                foreach ($products as $product) {
                    $xml .= $this->buildOlxAd($product, $shopUrl);
                }
            });

        $xml .= '</adverts>';
        return $xml;
    }

    private function buildOlxAd(Product $product, string $shopUrl): string
    {
        $title = is_array($product->title) ? ($product->title['uk'] ?? '') : ($product->title ?? '');
        $description = is_array($product->excerpt ?? null) ? ($product->excerpt['uk'] ?? '') : ($product->excerpt ?? '');
        if (! $description) {
            $description = is_array($product->content ?? null) ? ($product->content['uk'] ?? '') : ($product->content ?? '');
        }
        $description = strip_tags((string) $description);

        $url = $shopUrl.'/'.$product->getLocalizedSlug('uk');

        $xml = '<ad id="'.$product->id.'">'."\n";
        $xml .= '  <title>'.htmlspecialchars(mb_substr($title, 0, 100)).'</title>'."\n";
        $xml .= '  <description>'.htmlspecialchars(mb_substr($description ?: $title, 0, 9000)).'</description>'."\n";
        $xml .= '  <category>'.htmlspecialchars($product->category?->title ?? 'Авто').'</category>'."\n";
        $xml .= '  <subcategory>'.htmlspecialchars($product->category?->title ?? 'Запчастини').'</subcategory>'."\n";
        $xml .= '  <price currency="UAH">'.number_format((float) $product->price, 2, '.', '').'</price>'."\n";
        $xml .= '  <state>new</state>'."\n";
        $xml .= '  <url>'.$url.'</url>'."\n";

        if ($product->image) {
            $imgUrl = str_starts_with($product->image, 'http')
                ? $product->image
                : $shopUrl.'/storage/'.$product->image;
            $xml .= '  <images><image>'.htmlspecialchars($imgUrl).'</image></images>'."\n";
        }

        if ($product->sku) {
            $xml .= '  <vendor_code>'.htmlspecialchars($product->sku).'</vendor_code>'."\n";
        }
        if ($product->brandModel) {
            $xml .= '  <vendor>'.htmlspecialchars($product->brandModel->name).'</vendor>'."\n";
        }

        $xml .= '  <quantity>'.(int) ($product->quantity ?? 0).'</quantity>'."\n";
        $xml .= '</ad>'."\n";

        return $xml;
    }
}
