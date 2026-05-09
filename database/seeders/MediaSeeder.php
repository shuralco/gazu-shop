<?php

namespace Database\Seeders;

use App\Models\Media;
use Illuminate\Database\Seeder;

class MediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample media files - table only has path field
        $mediaPaths = [
            'uploads/banners/product-banner-1.jpg',
            'uploads/banners/product-banner-2.jpg',
            'uploads/categories/electronics.png',
            'uploads/categories/clothing.png',
            'uploads/products/default-product.jpg',
            'uploads/sliders/slider-1.jpg',
            'uploads/sliders/slider-2.jpg',
            'uploads/logos/shop-logo.png',
        ];

        foreach ($mediaPaths as $path) {
            Media::firstOrCreate(['path' => $path]);
        }

        $this->command->info('Media files seeded successfully!');
    }
}
