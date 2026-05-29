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
        // GAZU fork serves categories at the root-level pretty URL `/{slug}`
        // (no `/{locale}` prefix). StoreController::resolveSlug() dispatches
        // a slug with no numeric suffix to the catalog view.
        $category = Category::factory()->create(['is_active' => true]);
        $locale = app()->getLocale();
        $slug = $category->getLocalizedSlug($locale);
        $this->get("/{$slug}")->assertStatus(200);
    }

    public function test_product_page_loads(): void
    {
        // Products also live at root-level `/{slug}`; resolveSlug() routes a
        // Rozetka-style `…-{id}` slug to the product page.
        $product = Product::factory()->create(['is_active' => true]);
        $locale = app()->getLocale();
        $slug = $product->getLocalizedSlug($locale);
        $this->get("/{$slug}")->assertStatus(200);
    }

    public function test_search_page_loads(): void
    {
        $this->get('/search')->assertStatus(200);
    }

    public function test_specials_page_loads(): void
    {
        // GAZU uses the UA pretty URL `/akcii` (catalog with ?promo=1).
        $this->get('/akcii')->assertStatus(200);
    }

    public function test_hits_page_loads(): void
    {
        // GAZU uses the UA pretty URL `/khity` (catalog with ?hits=1).
        $this->get('/khity')->assertStatus(200);
    }

    public function test_brands_page_loads(): void
    {
        // GAZU brands index lives at `/brand` (singular); `/brendy` and
        // `/brands` are not served.
        $this->get('/brand')->assertStatus(200);
    }

    public function test_wishlist_page_loads(): void
    {
        // The brutal-codebase comparison feature was dropped in the GAZU fork
        // in favour of a wishlist. There is no `/comparison` route anymore.
        $this->get('/wishlist')->assertStatus(200);
    }
}
