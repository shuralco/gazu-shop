<?php

namespace App\Observers;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class ProductObserver
{
    public function saved(Product $product): void
    {
        $this->flushCatalogCache();
    }

    public function deleted(Product $product): void
    {
        $this->flushCatalogCache();
    }

    private function flushCatalogCache(): void
    {
        if (method_exists(Cache::getStore(), 'tags')) {
            Cache::tags(['catalog'])->flush();
        }
    }
}
