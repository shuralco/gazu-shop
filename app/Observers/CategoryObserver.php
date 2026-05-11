<?php

namespace App\Observers;

use App\Models\Category;
use Illuminate\Support\Facades\Cache;

class CategoryObserver
{
    public function updated(Category $category): void
    {
        if ($category->isDirty('title')) {
            $category->products()->searchable();
        }
        $this->flushCatalogCache();
    }

    public function created(Category $category): void
    {
        $this->flushCatalogCache();
    }

    public function deleted(Category $category): void
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
