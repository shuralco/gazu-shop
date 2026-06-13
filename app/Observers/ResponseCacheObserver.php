<?php

namespace App\Observers;

use Illuminate\Support\Facades\Cache;
use Spatie\ResponseCache\Facades\ResponseCache;

/**
 * Auto-invalidates caches whenever a storefront-visible model changes.
 * Wire any model whose data appears on a public page through this observer
 * (Product, Category, Brand, Page, InfoPage, DisplaySetting, MerchantWarehouse,
 * Inventory, CarMake, CarModel, CarEngine — see AppServiceProvider).
 *
 * TWO layers are flushed on every change:
 *   1. Spatie ResponseCache (cached HTML, Redis tag 'gazu-response').
 *   2. Named Cache::remember() keys holding DB-derived data the storefront
 *      renders (mega-menu car makes, shop stats, home featured rows, etc.).
 *
 * Без шару 2 HTML-кеш чистився, але сторінка re-render'илась зі СТАРИХ
 * Cache::remember ключів аж до закінчення TTL (10хв–1год) — admin-зміни
 * не відображались. Див. memory: gazu_octane_cache_hotfix.
 */
class ResponseCacheObserver
{
    /**
     * DB-derived caches that any storefront-visible model change can stale.
     * Flushed alongside ResponseCache. Keep in sync with keys read by
     * GazuMenuComposer + StoreController::home().
     */
    private const DERIVED_KEYS = [
        'gazu_mega_carmakes',
        'gazu_shop_stats',
        'home:hero:makes',
        'home:new:8',
        'home:promo:8',
        'home:popular404',
        'cars:makes',
        'category_hierarchy',
        'mega_menu_structure',
        'display_settings_all',
    ];

    public function saved($model): void
    {
        // Inventory змінюється ДУЖЕ часто (кожне замовлення / резерв / np:sync).
        // 1) Чисті зміни кількості (резерв 5→4) — кеш не чіпаємо взагалі.
        // 2) Перемикання СТАТУСУ наявності (в наявності ↔ немає) — це видимо на
        //    сторінці, АЛЕ робимо SCOPED-інвалідацію (лише сторінка товару + його
        //    категорії), а НЕ повний ResponseCache::clear() усього storefront.
        //    Інакше кожне замовлення, що вибиває товар зі stock, стирало весь
        //    кеш → постійні cold-вікна (~0.9с перший хіт). Див. memory.
        if ($model instanceof \App\Models\Inventory) {
            if ($this->inventoryStatusFlipped($model)) {
                $this->flushProduct($model->product);
            }

            return;
        }

        // Зміна самого товару — scoped (товар рідко редагують, але нащо валити
        // весь storefront заради одного товару).
        if ($model instanceof \App\Models\Product) {
            $this->flushProduct($model);

            return;
        }

        // Категорії/бренди/налаштування/нав-моделі впливають на багато сторінок
        // одразу (меню, лічильники) і змінюються рідко (admin) → повний flush.
        $this->flush();
    }

    public function deleted($model): void { $this->flushFor($model); }
    public function restored($model): void { $this->flushFor($model); }

    private function flushFor($model): void
    {
        if ($model instanceof \App\Models\Inventory) {
            $this->flushProduct($model->product);

            return;
        }
        if ($model instanceof \App\Models\Product) {
            $this->flushProduct($model);

            return;
        }
        $this->flush();
    }

    /**
     * Scoped-інвалідація для одного товару: forget сторінки товару + сторінок
     * його категорій (де він з'являється в лістингу), + DB-derived кеші + меню.
     * НЕ робить повний ResponseCache::clear() → решта storefront лишається теплою.
     */
    private function flushProduct(?\App\Models\Product $product): void
    {
        if (! $product) {
            // Не змогли резолвити товар (напр. видалений) → безпечний фолбек.
            $this->flush();

            return;
        }

        $urls = [];
        try {
            // B: URL через контракт моделі ($model->url()) — observer лишається
            // theme-agnostic; нова тема визначає свій URL у моделі, не тут.
            if (method_exists($product, 'url')) {
                $urls[] = $product->url();
            } else {
                // Fallback (GAZU-роут) для моделей без url().
                $slug = $product->slug ?: $product->id;
                if (is_array($slug)) {
                    $slug = $slug['uk'] ?? reset($slug) ?: $product->id;
                }
                $urls[] = route('gazu.product.show', ['slug' => $slug]);
                $urls[] = route('gazu.product.show', ['slug' => $product->id]);
            }

            // Категорія товару + предки (де він у лістингу зі stock-бейджем).
            $cat = $product->category;
            $guard = 0;
            while ($cat && $guard++ < 6) {
                if (method_exists($cat, 'url')) {
                    $urls[] = $cat->url();
                } else {
                    $cs = $cat->slug ?? null;
                    if (is_array($cs)) {
                        $cs = $cs['uk'] ?? reset($cs) ?: null;
                    }
                    if ($cs) {
                        $urls[] = url('/'.ltrim((string) $cs, '/'));
                    }
                }
                $cat = $cat->parent;
            }
        } catch (\Throwable $e) {
            report($e);
        }

        foreach (array_unique(array_filter($urls)) as $url) {
            try {
                ResponseCache::forget($url);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $this->forgetDerived();
    }

    /**
     * Чи перемкнувся статус наявності цього inventory-рядка (0 ↔ >0)?
     * Нова інвентаризація або зміна, що перетинає нуль — так; інакше ні.
     */
    private function inventoryStatusFlipped($inv): bool
    {
        if ($inv->wasRecentlyCreated) {
            return true;
        }
        if (! $inv->wasChanged('quantity')) {
            return false;
        }
        $orig = (int) $inv->getOriginal('quantity');
        $new = (int) ($inv->quantity ?? 0);

        return ($orig > 0) !== ($new > 0);
    }

    private function flush(): void
    {
        // Cache invalidation must never break a model save — swallow + report.
        try {
            ResponseCache::clear();
        } catch (\Throwable $e) {
            report($e);
        }

        $this->forgetDerived();
    }

    /**
     * DB-derived Cache::remember ключі + fragment-кеш меню + catalog-агрегати.
     * Спільне для повного flush і scoped-інвалідації (це дешеві ключі, тож
     * чистимо їх завжди — інакше storefront re-render'иться зі старих даних).
     */
    private function forgetDerived(): void
    {
        // Список явних ключів — з config('storefront.derived_cache_keys')
        // (нова тема задає свої БЕЗ правки цього класу); fallback = GAZU-const.
        $keys = config('storefront.derived_cache_keys');
        if (! is_array($keys) || $keys === []) {
            $keys = self::DERIVED_KEYS;
        }
        foreach ($keys as $key) {
            try {
                Cache::forget($key);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        // Tag-flush: будь-який Cache::tags([derived_tag])->remember(...) інвалідовується
        // АВТОМАТИЧНО — це шлях, яким нова тема інтегрує кешування без списку ключів.
        // + fragment-кеш меню. Теги з config (дефолти GAZU).
        $tags = array_filter([
            config('storefront.derived_cache_tag', 'storefront'),
            config('storefront.menu_cache_tag', 'gazu-menu'),
        ]);
        foreach (array_unique($tags) as $tag) {
            try {
                Cache::tags([$tag])->flush();
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
}
