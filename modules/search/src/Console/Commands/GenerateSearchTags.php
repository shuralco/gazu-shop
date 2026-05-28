<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class GenerateSearchTags extends Command
{
    protected $signature = 'search:generate-tags {--force : Overwrite existing tags}';
    protected $description = 'Auto-generate search tags for products based on price, category, and flags';

    private array $categoryKeywords = [
        // Electronics & gaming
        'електроніка' => ['електроніка', 'техніка', 'гаджет'],
        'комп\'ютер' => ['комп\'ютер', 'пк', 'pc', 'комплектуючі'],
        'ноутбук' => ['ноутбук', 'лептоп', 'портативний'],
        'телефон' => ['телефон', 'смартфон', 'мобільний'],
        'планшет' => ['планшет', 'таблет'],
        'телевізор' => ['телевізор', 'тв', 'екран'],
        'gaming' => ['ігри', 'геймінг', 'gaming', 'гра', 'ігровий', 'для ігор'],
        'game' => ['ігри', 'геймінг', 'gaming', 'гра', 'ігровий', 'для ігор'],
        'ігровий' => ['ігри', 'геймінг', 'gaming', 'гра', 'ігровий', 'для ігор'],
        'геймер' => ['ігри', 'геймінг', 'gaming', 'гра', 'ігровий', 'для ігор'],
        // Sports
        'спорт' => ['спорт', 'фітнес', 'тренування', 'активний'],
        'фітнес' => ['спорт', 'фітнес', 'тренування', 'здоров\'я'],
        'велосипед' => ['спорт', 'велосипед', 'активний відпочинок'],
        // Home
        'дім' => ['для дому', 'домашній', 'побутовий', 'home'],
        'кухня' => ['для кухні', 'кухонний', 'побутова техніка'],
        'меблі' => ['меблі', 'інтер\'єр', 'для дому'],
        // Fashion
        'одяг' => ['одяг', 'мода', 'стиль', 'fashion'],
        'взуття' => ['взуття', 'кросівки', 'черевики'],
        // Kids
        'дитяч' => ['дитячий', 'для дітей', 'дитина', 'kids'],
        'іграшк' => ['іграшка', 'для дітей', 'дитячий', 'розвиваючий'],
        // Beauty
        'косметик' => ['краса', 'косметика', 'догляд', 'beauty'],
        'парфум' => ['парфуми', 'аромат', 'подарунок'],
        // Office
        'офіс' => ['для роботи', 'офісний', 'бізнес', 'professional'],
        'канцеляр' => ['канцелярія', 'офіс', 'для навчання', 'школа'],
    ];

    private float $priceP25 = 0;
    private float $priceP50 = 0;
    private float $priceP75 = 0;

    public function handle(): int
    {
        $force = $this->option('force');

        // Precompute price percentiles for relative pricing
        $allPrices = Product::where('is_active', true)->orderBy('price')->pluck('price')->map(fn ($p) => (float) $p);

        if ($allPrices->isNotEmpty()) {
            $count = $allPrices->count();
            $this->priceP25 = $allPrices->get((int) floor($count * 0.25)) ?? 0;
            $this->priceP50 = $allPrices->get((int) floor($count * 0.50)) ?? 0;
            $this->priceP75 = $allPrices->get((int) floor($count * 0.75)) ?? 0;

            $this->info("Price quartiles: P25={$this->priceP25}, P50={$this->priceP50}, P75={$this->priceP75}");
        }

        $query = Product::query()->with(['category', 'brandModel']);

        if (!$force) {
            $query->where(function ($q) {
                $q->whereNull('search_tags')->orWhere('search_tags', '');
            });
        }

        $total = $query->count();

        if ($total === 0) {
            $this->info('No products to process. Use --force to overwrite existing tags.');
            return 0;
        }

        $this->info("Generating search tags for {$total} products...");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $updated = 0;

        $query->chunkById(100, function ($products) use (&$updated, $bar) {
            foreach ($products as $product) {
                $tags = $this->generateTags($product);

                if (!empty($tags)) {
                    $product->search_tags = implode(', ', array_unique($tags));
                    $product->saveQuietly();
                    $updated++;
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info("Updated {$updated} products with search tags.");

        return 0;
    }

    private function generateTags(Product $product): array
    {
        $tags = [];
        $price = (float) $product->price;
        $titleUk = mb_strtolower($product->getTranslation('title', 'uk', false) ?? '');
        $titleEn = mb_strtolower($product->getTranslation('title', 'en', false) ?? '');
        $categoryTitle = mb_strtolower($product->category?->getTranslation('title', 'uk', false) ?? '');
        $brandName = mb_strtolower($product->brandModel?->name ?? '');
        $combined = $titleUk . ' ' . $titleEn . ' ' . $categoryTitle . ' ' . $brandName;

        // Price-based tags (absolute thresholds)
        if ($price > 0 && $price < 1000) {
            $tags = array_merge($tags, ['бюджетний', 'дешевий', 'економ', 'недорого', 'доступний']);
        } elseif ($price >= 1000 && $price <= 5000) {
            $tags = array_merge($tags, ['середній', 'оптимальний', 'помірний']);
        } elseif ($price > 5000 && $price <= 15000) {
            $tags = array_merge($tags, ['преміум', 'якісний', 'топ']);
        } elseif ($price > 15000) {
            $tags = array_merge($tags, ['преміум', 'дорогий', 'топ', 'елітний', 'люкс']);
        }

        // Relative price tags (within store's price range)
        if ($this->priceP25 > 0 && $price > 0) {
            if ($price <= $this->priceP25) {
                $tags = array_merge($tags, ['бюджетний', 'дешевий', 'економ', 'недорого', 'доступний', 'вигідна ціна']);
            } elseif ($price <= $this->priceP50) {
                $tags = array_merge($tags, ['середня ціна', 'оптимальний', 'помірний']);
            } elseif ($price > $this->priceP75) {
                $tags = array_merge($tags, ['дорогий', 'преміум', 'топовий']);
            }
        }

        // Category & title keyword matching
        foreach ($this->categoryKeywords as $keyword => $relatedTags) {
            if (mb_strpos($combined, $keyword) !== false) {
                $tags = array_merge($tags, $relatedTags);
            }
        }

        // Flag-based tags
        if ($product->is_hit) {
            $tags = array_merge($tags, ['популярний', 'хіт', 'бестселер', 'рекомендований']);
        }

        if ($product->is_new) {
            $tags = array_merge($tags, ['новий', 'новинка', 'свіжий', 'останній']);
        }

        // Discount tags
        if ($product->old_price > 0 && $product->old_price > $product->price) {
            $discountPercent = round((1 - $product->price / $product->old_price) * 100);
            $tags = array_merge($tags, ['акція', 'знижка', 'розпродаж', 'вигідно', 'sale']);

            if ($discountPercent >= 30) {
                $tags[] = 'великий розпродаж';
            }
        }

        // Gift-worthy products (hits, or certain categories)
        if ($product->is_hit || mb_strpos($combined, 'подарунок') !== false || mb_strpos($combined, 'набір') !== false) {
            $tags = array_merge($tags, ['подарунок', 'gift', 'ідея подарунка']);
        }

        // Product type specific tags from title
        $titlePatterns = [
            'навушники' => ['аудіо', 'музика', 'звук'],
            'клавіатура' => ['введення', 'друк', 'офіс'],
            'мишка' => ['введення', 'офіс'],
            'монітор' => ['дисплей', 'екран', 'зображення'],
            'камера' => ['фото', 'відео', 'зйомка'],
            'принтер' => ['друк', 'офіс', 'документи'],
            'зарядк' => ['зарядка', 'акумулятор', 'енергія'],
            'чохол' => ['захист', 'аксесуар'],
            'кабел' => ['кабель', 'провід', 'підключення', 'аксесуар'],
        ];

        foreach ($titlePatterns as $pattern => $patternTags) {
            if (mb_strpos($titleUk, $pattern) !== false) {
                $tags = array_merge($tags, $patternTags);
            }
        }

        return array_unique(array_filter($tags));
    }
}
