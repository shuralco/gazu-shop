<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_homepage_loads(): void
    {
        $this->get('/')->assertStatus(200);
    }

    public function test_category_page_loads(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $locale = app()->getLocale();
        $slug = $category->getLocalizedSlug($locale);
        $this->get("/{$locale}/{$slug}")->assertStatus(200);
    }

    public function test_product_page_loads(): void
    {
        $product = Product::factory()->create(['is_active' => true]);
        $locale = app()->getLocale();
        $slug = $product->getLocalizedSlug($locale);
        $this->get("/{$locale}/{$slug}")->assertStatus(200);
    }

    public function test_search_page_loads(): void
    {
        $this->get('/search')->assertStatus(200);
    }

    public function test_specials_page_loads(): void
    {
        $this->get('/specials')->assertStatus(200);
    }

    public function test_hits_page_loads(): void
    {
        $this->get('/hits')->assertStatus(200);
    }

    public function test_brands_page_loads(): void
    {
        $this->get('/brands')->assertStatus(200);
    }

    public function test_comparison_page_loads(): void
    {
        $this->get('/comparison')->assertStatus(200);
    }
}
