<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FilterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('filter_groups')->insert([
            ['id' => 1, 'title' => 'Колір', 'is_active' => true, 'sort_order' => 1],
            ['id' => 2, 'title' => 'Розмір одягу', 'is_active' => true, 'sort_order' => 2],
            ['id' => 3, 'title' => 'Розмір взуття', 'is_active' => true, 'sort_order' => 3],
            ['id' => 4, 'title' => 'Матеріал', 'is_active' => true, 'sort_order' => 4],
            ['id' => 5, 'title' => 'Бренд', 'is_active' => true, 'sort_order' => 5],
            ['id' => 6, 'title' => 'Стать', 'is_active' => true, 'sort_order' => 6],
        ]);

        // Get actual category IDs and assign filter groups
        $categoryIds = DB::table('categories')->pluck('id')->toArray();
        $categoryFilters = [];

        foreach ($categoryIds as $categoryId) {
            // Assign different filter groups based on category
            if ($categoryId % 2 == 1) {
                // Odd categories get Color and Shoe size
                $categoryFilters[] = ['category_id' => $categoryId, 'filter_group_id' => 1]; // Color
                $categoryFilters[] = ['category_id' => $categoryId, 'filter_group_id' => 3]; // Shoe size
                $categoryFilters[] = ['category_id' => $categoryId, 'filter_group_id' => 5]; // Brand
            } else {
                // Even categories get Color and Clothing size
                $categoryFilters[] = ['category_id' => $categoryId, 'filter_group_id' => 1]; // Color
                $categoryFilters[] = ['category_id' => $categoryId, 'filter_group_id' => 2]; // Clothing size
                $categoryFilters[] = ['category_id' => $categoryId, 'filter_group_id' => 4]; // Material
            }
            $categoryFilters[] = ['category_id' => $categoryId, 'filter_group_id' => 6]; // Gender
        }

        DB::table('category_filters')->insert($categoryFilters);

        DB::table('filters')->insert([
            // Кольори
            ['id' => 1, 'title' => 'Чорний', 'filter_group_id' => 1, 'is_active' => true, 'sort_order' => 1],
            ['id' => 2, 'title' => 'Білий', 'filter_group_id' => 1, 'is_active' => true, 'sort_order' => 2],
            ['id' => 3, 'title' => 'Червоний', 'filter_group_id' => 1, 'is_active' => true, 'sort_order' => 3],
            ['id' => 4, 'title' => 'Жовтий', 'filter_group_id' => 1, 'is_active' => true, 'sort_order' => 4],
            ['id' => 17, 'title' => 'Синій', 'filter_group_id' => 1, 'is_active' => true, 'sort_order' => 5],
            ['id' => 18, 'title' => 'Зелений', 'filter_group_id' => 1, 'is_active' => true, 'sort_order' => 6],
            ['id' => 19, 'title' => 'Сірий', 'filter_group_id' => 1, 'is_active' => true, 'sort_order' => 7],

            // Розміри одягу
            ['id' => 5, 'title' => 'XS', 'filter_group_id' => 2, 'is_active' => true, 'sort_order' => 1],
            ['id' => 6, 'title' => 'S', 'filter_group_id' => 2, 'is_active' => true, 'sort_order' => 2],
            ['id' => 7, 'title' => 'M', 'filter_group_id' => 2, 'is_active' => true, 'sort_order' => 3],
            ['id' => 8, 'title' => 'L', 'filter_group_id' => 2, 'is_active' => true, 'sort_order' => 4],
            ['id' => 9, 'title' => 'XL', 'filter_group_id' => 2, 'is_active' => true, 'sort_order' => 5],
            ['id' => 20, 'title' => 'XXL', 'filter_group_id' => 2, 'is_active' => true, 'sort_order' => 6],

            // Розміри взуття
            ['id' => 10, 'title' => '37', 'filter_group_id' => 3, 'is_active' => true, 'sort_order' => 1],
            ['id' => 11, 'title' => '38', 'filter_group_id' => 3, 'is_active' => true, 'sort_order' => 2],
            ['id' => 12, 'title' => '39', 'filter_group_id' => 3, 'is_active' => true, 'sort_order' => 3],
            ['id' => 13, 'title' => '40', 'filter_group_id' => 3, 'is_active' => true, 'sort_order' => 4],
            ['id' => 14, 'title' => '41', 'filter_group_id' => 3, 'is_active' => true, 'sort_order' => 5],
            ['id' => 15, 'title' => '42', 'filter_group_id' => 3, 'is_active' => true, 'sort_order' => 6],
            ['id' => 16, 'title' => '43', 'filter_group_id' => 3, 'is_active' => true, 'sort_order' => 7],
            ['id' => 21, 'title' => '44', 'filter_group_id' => 3, 'is_active' => true, 'sort_order' => 8],

            // Матеріали
            ['id' => 22, 'title' => 'Бавовна', 'filter_group_id' => 4, 'is_active' => true, 'sort_order' => 1],
            ['id' => 23, 'title' => 'Поліестер', 'filter_group_id' => 4, 'is_active' => true, 'sort_order' => 2],
            ['id' => 24, 'title' => 'Шкіра', 'filter_group_id' => 4, 'is_active' => true, 'sort_order' => 3],
            ['id' => 25, 'title' => 'Джинс', 'filter_group_id' => 4, 'is_active' => true, 'sort_order' => 4],
            ['id' => 26, 'title' => 'Вовна', 'filter_group_id' => 4, 'is_active' => true, 'sort_order' => 5],

            // Бренди
            ['id' => 27, 'title' => 'Nike', 'filter_group_id' => 5, 'is_active' => true, 'sort_order' => 1],
            ['id' => 28, 'title' => 'Adidas', 'filter_group_id' => 5, 'is_active' => true, 'sort_order' => 2],
            ['id' => 29, 'title' => 'Puma', 'filter_group_id' => 5, 'is_active' => true, 'sort_order' => 3],
            ['id' => 30, 'title' => 'New Balance', 'filter_group_id' => 5, 'is_active' => true, 'sort_order' => 4],

            // Стать
            ['id' => 31, 'title' => 'Чоловіча', 'filter_group_id' => 6, 'is_active' => true, 'sort_order' => 1],
            ['id' => 32, 'title' => 'Жіноча', 'filter_group_id' => 6, 'is_active' => true, 'sort_order' => 2],
            ['id' => 33, 'title' => 'Унісекс', 'filter_group_id' => 6, 'is_active' => true, 'sort_order' => 3],
        ]);

        // Assign random filters to products
        $products = DB::table('products')->get();
        $filterProducts = [];

        foreach ($products as $product) {
            // Assign random color
            $colorFilters = [1, 2, 3, 4, 17, 18, 19];
            $randomColor = $colorFilters[array_rand($colorFilters)];
            $filterProducts[] = ['filter_id' => $randomColor, 'product_id' => $product->id, 'filter_group_id' => 1];

            // Assign random size based on category
            if ($product->category_id % 2 == 0) {
                // Clothing size
                $sizeFilters = [5, 6, 7, 8, 9, 20];
                $randomSize = $sizeFilters[array_rand($sizeFilters)];
                $filterProducts[] = ['filter_id' => $randomSize, 'product_id' => $product->id, 'filter_group_id' => 2];

                // Material
                $materialFilters = [22, 23, 24, 25, 26];
                $randomMaterial = $materialFilters[array_rand($materialFilters)];
                $filterProducts[] = ['filter_id' => $randomMaterial, 'product_id' => $product->id, 'filter_group_id' => 4];
            } else {
                // Shoe size
                $shoeFilters = [10, 11, 12, 13, 14, 15, 16, 21];
                $randomShoe = $shoeFilters[array_rand($shoeFilters)];
                $filterProducts[] = ['filter_id' => $randomShoe, 'product_id' => $product->id, 'filter_group_id' => 3];

                // Brand
                $brandFilters = [27, 28, 29, 30];
                $randomBrand = $brandFilters[array_rand($brandFilters)];
                $filterProducts[] = ['filter_id' => $randomBrand, 'product_id' => $product->id, 'filter_group_id' => 5];
            }

            // Gender
            $genderFilters = [31, 32, 33];
            $randomGender = $genderFilters[array_rand($genderFilters)];
            $filterProducts[] = ['filter_id' => $randomGender, 'product_id' => $product->id, 'filter_group_id' => 6];
        }

        DB::table('filter_products')->insert($filterProducts);
    }
}
