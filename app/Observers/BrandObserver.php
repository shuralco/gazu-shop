<?php

namespace App\Observers;

use App\Models\Brand;
use App\Models\Product;

class BrandObserver
{
    public function updated(Brand $brand): void
    {
        if ($brand->isDirty('name')) {
            Product::where('brand_id', $brand->id)->searchable();
        }
    }
}
