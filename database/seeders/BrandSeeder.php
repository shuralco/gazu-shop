<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            ['name' => 'Apple', 'sort_order' => 1],
            ['name' => 'Samsung', 'sort_order' => 2],
            ['name' => 'Sony', 'sort_order' => 3],
            ['name' => 'LG', 'sort_order' => 4],
            ['name' => 'Nike', 'sort_order' => 5],
            ['name' => 'Adidas', 'sort_order' => 6],
            ['name' => 'Xiaomi', 'sort_order' => 7],
            ['name' => 'Canon', 'sort_order' => 8],
            ['name' => 'Dell', 'sort_order' => 9],
            ['name' => 'HP', 'sort_order' => 10],
            ['name' => 'Asus', 'sort_order' => 11],
            ['name' => 'Lenovo', 'sort_order' => 12],
            ['name' => 'Philips', 'sort_order' => 13],
            ['name' => 'Bosch', 'sort_order' => 14],
            ['name' => 'Zara', 'sort_order' => 15],
            ['name' => 'H&M', 'sort_order' => 16],
            ['name' => 'IKEA', 'sort_order' => 17],
            ['name' => 'Levis', 'sort_order' => 18],
            ['name' => 'Puma', 'sort_order' => 19],
            ['name' => 'Microsoft', 'sort_order' => 20],
        ];

        foreach ($brands as $brandData) {
            $name = $brandData['name'];
            Brand::create([
                'name' => $name,
                'slug' => \Str::slug($name),
                'description' => "Офіційні товари бренду {$name}. Висока якість, оригінальна продукція.",
                'meta_title' => "{$name} - купити товари бренду онлайн в SimpleShop",
                'meta_description' => "Широкий асортимент товарів бренду {$name}. Оригінальна продукція, швидка доставка по Україні. Гарантія якості.",
                'meta_keywords' => \Str::lower($name).", товари {$name}, купити {$name}, {$name} україна",
                'is_active' => true,
                'sort_order' => $brandData['sort_order'],
            ]);
        }
    }
}
