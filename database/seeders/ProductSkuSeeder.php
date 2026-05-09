<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSkuSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Заповнення кодів товарів...');

        DB::transaction(function () {
            $products = Product::all();

            $this->command->info("Оновлення кодів для {$products->count()} товарів");

            foreach ($products as $product) {
                $sku = 'SKU-'.str_pad($product->id, 6, '0', STR_PAD_LEFT);

                $product->update(['sku' => $sku]);

                $this->command->line("Товар #{$product->id} '{$product->title}' -> {$sku}");
            }

            $totalProducts = Product::count();
            $withSku = Product::whereNotNull('sku')->where('sku', '!=', '')->count();

            $this->command->info("Завершено! {$withSku}/{$totalProducts} товарів мають коди");
        });
    }
}
