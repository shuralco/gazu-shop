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
        // Раніше КОЖНА така зміна робила повний ResponseCache::clear() → весь
        // storefront стирався багато разів на день → постійні cold-вікна.
        // Тепер флашимо лише коли реально перемкнувся СТАТУС наявності
        // (в наявності ↔ немає) — саме він видимий на сторінці. Чисті зміни
        // кількості (резерв 5→4) кеш не чіпають.
        if ($model instanceof \App\Models\Inventory && ! $this->inventoryStatusFlipped($model)) {
            return;
        }

        $this->flush();
    }

    public function deleted($model): void { $this->flush(); }
    public function restored($model): void { $this->flush(); }

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

        foreach (self::DERIVED_KEYS as $key) {
            try {
                Cache::forget($key);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        // Fragment-кеш мега-меню (відрендерений HTML нав-дерева) — інвалідувати
        // при зміні категорій/брендів/товарів (рахунки в меню). Tag-store (redis).
        try {
            Cache::tags(['gazu-menu'])->flush();
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
