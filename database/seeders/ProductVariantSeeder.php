<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductVariantSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::orderBy('id')->take(5)->get();

        if ($products->count() < 3) {
            $this->command?->warn('Need at least 3 products to seed variants. Skipping.');
            return;
        }

        // Product 1: Color (3) + Size (4) = 12 variants
        $this->seedProductWithColorAndSize($products[0]);

        // Product 2: Memory (3) + Color (2) = 6 variants
        $this->seedProductWithMemoryAndColor($products[1]);

        // Product 3: Size only (3) = 3 variants
        $this->seedProductWithSizeOnly($products[2]);

        if ($products->count() >= 4) {
            $this->seedProductWithColorOnly($products[3]);
        }

        if ($products->count() >= 5) {
            $this->seedProductWithMemoryOnly($products[4]);
        }
    }

    private function seedProductWithColorAndSize(Product $product): void
    {
        $colorOption = ProductOption::create([
            'product_id' => $product->id,
            'name' => 'Колір',
            'type' => 'color',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $colors = [
            ['value' => 'Червоний', 'color_hex' => '#EF4444', 'price_modifier' => 0],
            ['value' => 'Синій', 'color_hex' => '#3B82F6', 'price_modifier' => 0],
            ['value' => 'Чорний', 'color_hex' => '#111827', 'price_modifier' => 100],
        ];

        $colorValues = [];
        foreach ($colors as $i => $color) {
            $colorValues[] = ProductOptionValue::create([
                'product_option_id' => $colorOption->id,
                'value' => $color['value'],
                'color_hex' => $color['color_hex'],
                'price_modifier' => $color['price_modifier'],
                'sort_order' => $i,
                'is_active' => true,
            ]);
        }

        $sizeOption = ProductOption::create([
            'product_id' => $product->id,
            'name' => 'Розмір',
            'type' => 'button',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $sizes = [
            ['value' => 'S', 'price_modifier' => 0],
            ['value' => 'M', 'price_modifier' => 0],
            ['value' => 'L', 'price_modifier' => 50],
            ['value' => 'XL', 'price_modifier' => 100],
        ];

        $sizeValues = [];
        foreach ($sizes as $i => $size) {
            $sizeValues[] = ProductOptionValue::create([
                'product_option_id' => $sizeOption->id,
                'value' => $size['value'],
                'price_modifier' => $size['price_modifier'],
                'sort_order' => $i,
                'is_active' => true,
            ]);
        }

        // Generate 12 variants (3 colors x 4 sizes)
        foreach ($colorValues as $color) {
            foreach ($sizeValues as $size) {
                $this->createVariant($product, [$colorOption, $sizeOption], [$color, $size]);
            }
        }
    }

    private function seedProductWithMemoryAndColor(Product $product): void
    {
        $memoryOption = ProductOption::create([
            'product_id' => $product->id,
            'name' => "Пам'ять",
            'type' => 'button',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $memories = [
            ['value' => '64GB', 'price_modifier' => 0],
            ['value' => '128GB', 'price_modifier' => 500],
            ['value' => '256GB', 'price_modifier' => 1200],
        ];

        $memoryValues = [];
        foreach ($memories as $i => $mem) {
            $memoryValues[] = ProductOptionValue::create([
                'product_option_id' => $memoryOption->id,
                'value' => $mem['value'],
                'price_modifier' => $mem['price_modifier'],
                'sort_order' => $i,
                'is_active' => true,
            ]);
        }

        $colorOption = ProductOption::create([
            'product_id' => $product->id,
            'name' => 'Колір',
            'type' => 'color',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $colors = [
            ['value' => 'Чорний', 'color_hex' => '#111827', 'price_modifier' => 0],
            ['value' => 'Білий', 'color_hex' => '#F9FAFB', 'price_modifier' => 0],
        ];

        $colorValues = [];
        foreach ($colors as $i => $color) {
            $colorValues[] = ProductOptionValue::create([
                'product_option_id' => $colorOption->id,
                'value' => $color['value'],
                'color_hex' => $color['color_hex'],
                'price_modifier' => $color['price_modifier'],
                'sort_order' => $i,
                'is_active' => true,
            ]);
        }

        // Generate 6 variants (3 memories x 2 colors)
        foreach ($memoryValues as $memory) {
            foreach ($colorValues as $color) {
                $this->createVariant($product, [$memoryOption, $colorOption], [$memory, $color]);
            }
        }
    }

    private function seedProductWithSizeOnly(Product $product): void
    {
        $sizeOption = ProductOption::create([
            'product_id' => $product->id,
            'name' => 'Розмір',
            'type' => 'button',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $sizes = [
            ['value' => 'S', 'price_modifier' => -50],
            ['value' => 'M', 'price_modifier' => 0],
            ['value' => 'L', 'price_modifier' => 50],
        ];

        foreach ($sizes as $i => $size) {
            $sizeValue = ProductOptionValue::create([
                'product_option_id' => $sizeOption->id,
                'value' => $size['value'],
                'price_modifier' => $size['price_modifier'],
                'sort_order' => $i,
                'is_active' => true,
            ]);
            $this->createVariant($product, [$sizeOption], [$sizeValue]);
        }
    }

    private function seedProductWithColorOnly(Product $product): void
    {
        $colorOption = ProductOption::create([
            'product_id' => $product->id,
            'name' => 'Колір',
            'type' => 'color',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $colors = [
            ['value' => 'Червоний', 'color_hex' => '#EF4444', 'price_modifier' => 0],
            ['value' => 'Зелений', 'color_hex' => '#22C55E', 'price_modifier' => 0],
            ['value' => 'Синій', 'color_hex' => '#3B82F6', 'price_modifier' => 0],
            ['value' => 'Жовтий', 'color_hex' => '#EAB308', 'price_modifier' => 50],
        ];

        foreach ($colors as $i => $color) {
            $colorValue = ProductOptionValue::create([
                'product_option_id' => $colorOption->id,
                'value' => $color['value'],
                'color_hex' => $color['color_hex'],
                'price_modifier' => $color['price_modifier'],
                'sort_order' => $i,
                'is_active' => true,
            ]);
            $this->createVariant($product, [$colorOption], [$colorValue]);
        }
    }

    private function seedProductWithMemoryOnly(Product $product): void
    {
        $memoryOption = ProductOption::create([
            'product_id' => $product->id,
            'name' => "Пам'ять",
            'type' => 'button',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $memories = [
            ['value' => '128GB', 'price_modifier' => 0],
            ['value' => '256GB', 'price_modifier' => 800],
            ['value' => '512GB', 'price_modifier' => 1800],
            ['value' => '1TB', 'price_modifier' => 3500],
        ];

        foreach ($memories as $i => $mem) {
            $memValue = ProductOptionValue::create([
                'product_option_id' => $memoryOption->id,
                'value' => $mem['value'],
                'price_modifier' => $mem['price_modifier'],
                'sort_order' => $i,
                'is_active' => true,
            ]);
            $this->createVariant($product, [$memoryOption], [$memValue]);
        }
    }

    private function createVariant(Product $product, array $options, array $values): void
    {
        $optionValuesJson = [];
        $valueIds = [];
        $skuParts = [$product->sku ?? 'PRD-' . $product->id];

        foreach ($values as $index => $value) {
            $option = $options[$index];
            $optionValuesJson[$option->name] = $value->value;
            $valueIds[] = $value->id;
            $skuParts[] = Str::upper(Str::limit(Str::slug($value->value, ''), 6, ''));
        }

        $sku = implode('-', $skuParts);
        $counter = 0;
        $originalSku = $sku;
        while (ProductVariant::where('sku', $sku)->exists()) {
            $counter++;
            $sku = $originalSku . '-' . $counter;
        }

        $modifier = collect($values)->sum('price_modifier');

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => $sku,
            'price' => null,
            'old_price' => null,
            'quantity' => rand(0, 50),
            'stock_status' => rand(0, 10) > 2 ? 'in_stock' : 'out_of_stock',
            'option_values' => $optionValuesJson,
            'is_active' => true,
        ]);

        $variant->optionValues()->attach($valueIds);
    }
}
