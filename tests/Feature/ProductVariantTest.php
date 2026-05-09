<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ProductVariantTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_variant_creation(): void
    {
        $product = Product::factory()->create(['price' => 100]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'TEST-VAR-002',
            'price' => 120,
            'quantity' => 5,
            'stock_status' => 'in_stock',
            'option_values' => ['Розмір' => 'XL'],
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('product_variants', [
            'id' => $variant->id,
            'sku' => 'TEST-VAR-002',
            'product_id' => $product->id,
        ]);

        $this->assertTrue($product->hasVariants());
    }

    public function test_variant_effective_price_uses_own_price(): void
    {
        $product = Product::factory()->create(['price' => 100]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'TEST-VAR-003',
            'price' => 150,
            'quantity' => 5,
            'stock_status' => 'in_stock',
            'option_values' => ['Колір' => 'Синій'],
            'is_active' => true,
        ]);

        $this->assertEquals(150, $variant->getEffectivePrice());
    }

    public function test_variant_display_name(): void
    {
        $product = Product::factory()->create();

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'TEST-VAR-004',
            'quantity' => 1,
            'stock_status' => 'in_stock',
            'option_values' => ['Колір' => 'Червоний', 'Розмір' => 'M'],
            'is_active' => true,
        ]);

        $this->assertEquals('Червоний / M', $variant->getDisplayName());
    }

    public function test_inactive_variant_not_counted(): void
    {
        $product = Product::factory()->create();

        ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'TEST-VAR-005',
            'quantity' => 5,
            'stock_status' => 'in_stock',
            'option_values' => ['Розмір' => 'S'],
            'is_active' => false,
        ]);

        $this->assertFalse($product->hasVariants());
    }

    public function test_variant_is_in_stock(): void
    {
        $product = Product::factory()->create();

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'TEST-VAR-006',
            'price' => 100,
            'quantity' => 10,
            'stock_status' => 'in_stock',
            'option_values' => ['Розмір' => 'L'],
            'is_active' => true,
        ]);

        $this->assertTrue($variant->isInStock());

        $outOfStock = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'TEST-VAR-007',
            'price' => 100,
            'quantity' => 0,
            'stock_status' => 'in_stock',
            'option_values' => ['Розмір' => 'XXL'],
            'is_active' => true,
        ]);

        $this->assertFalse($outOfStock->isInStock());
    }

    public function test_product_has_many_variants(): void
    {
        $product = Product::factory()->create();

        ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'TEST-VAR-A',
            'quantity' => 5,
            'stock_status' => 'in_stock',
            'option_values' => ['Колір' => 'Червоний'],
            'is_active' => true,
        ]);

        ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'TEST-VAR-B',
            'quantity' => 3,
            'stock_status' => 'in_stock',
            'option_values' => ['Колір' => 'Синій'],
            'is_active' => true,
        ]);

        $this->assertEquals(2, $product->variants()->count());
    }
}
