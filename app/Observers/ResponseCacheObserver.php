<?php

namespace App\Observers;

use Spatie\ResponseCache\Facades\ResponseCache;

/**
 * Auto-invalidates the Spatie ResponseCache whenever a storefront-visible model
 * changes. Wire any model whose data appears on a public page through this
 * observer (Product, Category, Brand, Page, InfoPage, DisplaySetting, MerchantWarehouse).
 *
 * For now we flush ALL response cache on any change — granular tags by model
 * type would be a follow-up.
 */
class ResponseCacheObserver
{
    public function saved($model): void   { $this->flush(); }
    public function deleted($model): void { $this->flush(); }
    public function restored($model): void { $this->flush(); }

    private function flush(): void
    {
        try {
            ResponseCache::clear();
        } catch (\Throwable $e) {
            // Cache invalidation must never break model save.
            report($e);
        }
    }
}
