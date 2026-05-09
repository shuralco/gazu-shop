<?php

namespace App\Observers;

use App\Models\Category;

class CategoryObserver
{
    public function updated(Category $category): void
    {
        // If title changed, re-index all products in this category
        if ($category->isDirty('title')) {
            $category->products()->searchable();
        }
    }
}
