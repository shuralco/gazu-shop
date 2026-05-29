<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Filter;
use App\Models\FilterGroup;
use App\Models\FilterLanding;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for the `related_products` module:
 *   - FilterLanding::productsQuery() — фільтр по category / brand / filter_ids
 *   - GET /lp/{slug} рендериться 200
 *   - GET /api/products/{id}/snapshot повертає JSON (price/qty/image/specs)
 *   - GET /api/products/{id}/variant-by-options — variant за набором опцій
 *
 * Ізольовано на sqlite :memory: (tests/bootstrap.php форсує). RefreshDatabase.
 */
class RelatedProductsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Прикріпити Filter до Product через filter_products pivot
     * (filter_id + product_id + filter_group_id).
     */
    private function attachFilter(Product $product, Filter $filter): void
    {
        $product->filters()->attach($filter->id, [
            'filter_group_id' => $filter->filter_group_id,
        ]);
    }

    // ---------------------------------------------------------------------
    // FilterLanding::productsQuery
    // ---------------------------------------------------------------------

    public function test_products_query_filters_by_category(): void
    {
        $catA = Category::factory()->create(['is_active' => true]);
        $catB = Category::factory()->create(['is_active' => true]);

        $inCat = Product::factory()->create(['category_id' => $catA->id, 'is_active' => true]);
        Product::factory()->create(['category_id' => $catB->id, 'is_active' => true]);

        $landing = FilterLanding::create([
            'slug' => 'cat-landing',
            'title' => 'Cat landing',
            'category_id' => $catA->id,
            'is_active' => true,
        ]);

        $ids = $landing->productsQuery()->pluck('id')->all();

        $this->assertSame([$inCat->id], $ids);
    }

    public function test_products_query_filters_by_brand(): void
    {
        $brandA = Brand::factory()->create(['is_active' => true]);
        $brandB = Brand::factory()->create(['is_active' => true]);

        $inBrand = Product::factory()->create(['brand_id' => $brandA->id, 'is_active' => true]);
        Product::factory()->create(['brand_id' => $brandB->id, 'is_active' => true]);

        $landing = FilterLanding::create([
            'slug' => 'brand-landing',
            'title' => 'Brand landing',
            'brand_id' => $brandA->id,
            'is_active' => true,
        ]);

        $ids = $landing->productsQuery()->pluck('id')->all();

        $this->assertSame([$inBrand->id], $ids);
    }

    public function test_products_query_filters_by_filter_ids(): void
    {
        $group = FilterGroup::factory()->create();
        $filter = Filter::factory()->forGroup($group)->create();

        $matching = Product::factory()->create(['is_active' => true]);
        $this->attachFilter($matching, $filter);

        // Product without the filter must be excluded.
        Product::factory()->create(['is_active' => true]);

        $landing = FilterLanding::create([
            'slug' => 'filter-landing',
            'title' => 'Filter landing',
            'filter_ids' => [$filter->id],
            'is_active' => true,
        ]);

        $ids = $landing->productsQuery()->pluck('id')->all();

        $this->assertSame([$matching->id], $ids);
    }

    public function test_products_query_combines_category_brand_and_filter(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $brand = Brand::factory()->create(['is_active' => true]);
        $group = FilterGroup::factory()->create();
        $filter = Filter::factory()->forGroup($group)->create();

        // Matches all three constraints.
        $match = Product::factory()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'is_active' => true,
        ]);
        $this->attachFilter($match, $filter);

        // Right category + brand, but missing the filter → excluded.
        Product::factory()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'is_active' => true,
        ]);

        // Has the filter, but wrong brand → excluded.
        $otherBrand = Brand::factory()->create(['is_active' => true]);
        $wrongBrand = Product::factory()->create([
            'category_id' => $category->id,
            'brand_id' => $otherBrand->id,
            'is_active' => true,
        ]);
        $this->attachFilter($wrongBrand, $filter);

        $landing = FilterLanding::create([
            'slug' => 'combo-landing',
            'title' => 'Combo landing',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'filter_ids' => [$filter->id],
            'is_active' => true,
        ]);

        $ids = $landing->productsQuery()->pluck('id')->all();

        $this->assertSame([$match->id], $ids);
    }

    public function test_products_query_excludes_inactive_products(): void
    {
        $category = Category::factory()->create(['is_active' => true]);

        $active = Product::factory()->create(['category_id' => $category->id, 'is_active' => true]);
        Product::factory()->create(['category_id' => $category->id, 'is_active' => false]);

        $landing = FilterLanding::create([
            'slug' => 'active-only',
            'title' => 'Active only',
            'category_id' => $category->id,
            'is_active' => true,
        ]);

        $ids = $landing->productsQuery()->pluck('id')->all();

        $this->assertSame([$active->id], $ids);
    }

    // ---------------------------------------------------------------------
    // GET /lp/{slug}
    // ---------------------------------------------------------------------

    public function test_landing_page_renders_200(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        Product::factory()->count(3)->create(['category_id' => $category->id, 'is_active' => true]);

        $landing = FilterLanding::create([
            'slug' => 'oil-filters-bosch',
            'title' => 'Масляні фільтри Bosch',
            'h1' => 'Масляні фільтри Bosch',
            'category_id' => $category->id,
            'is_active' => true,
        ]);

        $this->get('/lp/'.$landing->slug)->assertStatus(200);
    }

    public function test_landing_page_increments_views_count(): void
    {
        $landing = FilterLanding::create([
            'slug' => 'views-counter',
            'title' => 'Views counter',
            'is_active' => true,
            'views_count' => 0,
        ]);

        $this->get('/lp/'.$landing->slug)->assertStatus(200);

        $this->assertSame(1, (int) $landing->fresh()->views_count);
    }

    public function test_inactive_landing_returns_404(): void
    {
        $landing = FilterLanding::create([
            'slug' => 'hidden-landing',
            'title' => 'Hidden landing',
            'is_active' => false,
        ]);

        $this->get('/lp/'.$landing->slug)->assertStatus(404);
    }

    public function test_unknown_landing_slug_returns_404(): void
    {
        $this->get('/lp/does-not-exist')->assertStatus(404);
    }

    // ---------------------------------------------------------------------
    // GET /api/products/{id}/snapshot
    // ---------------------------------------------------------------------

    public function test_snapshot_returns_expected_json_shape(): void
    {
        $product = Product::factory()->create([
            'is_active' => true,
            'price' => 249.50,
            'quantity' => 7,
            'image' => '/assets/img/products/snap.jpg',
            'specifications' => ['Виробник' => 'Bosch', 'Тип' => 'Масляний'],
        ]);

        $response = $this->getJson('/api/products/'.$product->id.'/snapshot');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id', 'slug', 'title', 'price', 'old_price',
                'image', 'qty', 'in_stock', 'sku', 'specs', 'url',
            ])
            ->assertJson([
                'id' => $product->id,
                'price' => 249.50,
                'qty' => 7,
                'in_stock' => true,
                'image' => '/assets/img/products/snap.jpg',
                'specs' => ['Виробник' => 'Bosch', 'Тип' => 'Масляний'],
            ]);
    }

    public function test_snapshot_reports_out_of_stock_when_quantity_zero(): void
    {
        $product = Product::factory()->create([
            'is_active' => true,
            'quantity' => 0,
        ]);

        $this->getJson('/api/products/'.$product->id.'/snapshot')
            ->assertStatus(200)
            ->assertJson(['qty' => 0, 'in_stock' => false]);
    }

    public function test_snapshot_404_for_inactive_product(): void
    {
        $product = Product::factory()->create(['is_active' => false]);

        $this->getJson('/api/products/'.$product->id.'/snapshot')
            ->assertStatus(404);
    }

    // ---------------------------------------------------------------------
    // GET /api/products/{id}/variant-by-options
    // ---------------------------------------------------------------------

    public function test_variant_by_options_returns_matching_variant(): void
    {
        $product = Product::factory()->create(['is_active' => true, 'price' => 100]);

        $colorOption = ProductOption::create([
            'product_id' => $product->id,
            'name' => 'Колір',
            'type' => 'select',
            'is_active' => true,
        ]);
        $red = ProductOptionValue::create([
            'product_option_id' => $colorOption->id,
            'value' => 'Червоний',
            'price_modifier' => 0,
            'is_active' => true,
        ]);
        $blue = ProductOptionValue::create([
            'product_option_id' => $colorOption->id,
            'value' => 'Синій',
            'price_modifier' => 0,
            'is_active' => true,
        ]);

        $redVariant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'VAR-RED',
            'price' => 120,
            'quantity' => 5,
            'stock_status' => 'in_stock',
            'option_values' => ['Колір' => 'Червоний'],
            'is_active' => true,
        ]);
        $redVariant->optionValues()->attach($red->id);

        $blueVariant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'VAR-BLUE',
            'price' => 130,
            'quantity' => 3,
            'stock_status' => 'in_stock',
            'option_values' => ['Колір' => 'Синій'],
            'is_active' => true,
        ]);
        $blueVariant->optionValues()->attach($blue->id);

        $response = $this->getJson(
            '/api/products/'.$product->id.'/variant-by-options?option_value_ids[]='.$red->id
        );

        $response->assertStatus(200)
            ->assertJson([
                'id' => $product->id,
                'variant_id' => $redVariant->id,
                'price' => 120,
                'qty' => 5,
                'in_stock' => true,
                'sku' => 'VAR-RED',
                'has_variant' => true,
            ]);
    }

    public function test_variant_by_options_falls_back_when_no_variant_matches(): void
    {
        $product = Product::factory()->create(['is_active' => true, 'price' => 200]);

        $sizeOption = ProductOption::create([
            'product_id' => $product->id,
            'name' => 'Розмір',
            'type' => 'select',
            'is_active' => true,
        ]);
        // Option value with a +50 modifier, but NO variant ties to it.
        $xl = ProductOptionValue::create([
            'product_option_id' => $sizeOption->id,
            'value' => 'XL',
            'price_modifier' => 50,
            'is_active' => true,
        ]);

        $response = $this->getJson(
            '/api/products/'.$product->id.'/variant-by-options?option_value_ids[]='.$xl->id
        );

        // No exact variant → base price + modifier, has_variant=false, variant_id=null.
        $response->assertStatus(200)
            ->assertJson([
                'id' => $product->id,
                'variant_id' => null,
                'price' => 250.0,
                'has_variant' => false,
            ]);
    }

    public function test_variant_by_options_requires_picked_ids(): void
    {
        $product = Product::factory()->create(['is_active' => true]);

        $this->getJson('/api/products/'.$product->id.'/variant-by-options')
            ->assertStatus(422)
            ->assertJson(['error' => 'no option_value_ids picked']);
    }
}
