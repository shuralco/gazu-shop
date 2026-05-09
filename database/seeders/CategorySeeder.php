<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Відключити foreign key constraints
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=OFF;');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        // Очистити існуючі категорії
        DB::table('categories')->truncate();

        // Включити foreign key constraints
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=ON;');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        $this->createTwoLevelStructure();
    }

    private function createTwoLevelStructure(): void
    {
        // Основні категорії (рівень 1)
        $mainCategories = [
            ['title' => 'Електроніка', 'slug' => 'electronics', 'parent_id' => null, 'sort_order' => 1],
            ['title' => 'Одяг', 'slug' => 'clothing', 'parent_id' => null, 'sort_order' => 2],
            ['title' => 'Дім і сад', 'slug' => 'home-garden', 'parent_id' => null, 'sort_order' => 3],
            ['title' => 'Спорт', 'slug' => 'sport', 'parent_id' => null, 'sort_order' => 4],
            ['title' => 'Краса', 'slug' => 'beauty', 'parent_id' => null, 'sort_order' => 5],
            ['title' => 'Авто', 'slug' => 'auto', 'parent_id' => null, 'sort_order' => 6],
        ];

        $this->insertCategories($mainCategories);

        // Підкategories (рівень 2)
        $this->createElectronicsSubcategories();
        $this->createClothingSubcategories();
        $this->createHomeGardenSubcategories();
        $this->createSportSubcategories();
        $this->createBeautySubcategories();
        $this->createAutoSubcategories();
    }

    private function createElectronicsSubcategories(): void
    {
        $electronicsId = DB::table('categories')->where('slug', 'electronics')->first()->id;
        $subcategories = [
            ['title' => 'Смартфони', 'slug' => 'smartphones', 'parent_id' => $electronicsId, 'sort_order' => 1],
            ['title' => 'Ноутбуки', 'slug' => 'laptops', 'parent_id' => $electronicsId, 'sort_order' => 2],
            ['title' => 'Планшети', 'slug' => 'tablets', 'parent_id' => $electronicsId, 'sort_order' => 3],
            ['title' => 'Телевізори', 'slug' => 'tv', 'parent_id' => $electronicsId, 'sort_order' => 4],
            ['title' => 'Навушники', 'slug' => 'headphones', 'parent_id' => $electronicsId, 'sort_order' => 5],
            ['title' => 'Фотокамери', 'slug' => 'cameras', 'parent_id' => $electronicsId, 'sort_order' => 6],
            ['title' => 'Ігрові консолі', 'slug' => 'gaming-consoles', 'parent_id' => $electronicsId, 'sort_order' => 7],
            ['title' => 'Аксесуари', 'slug' => 'electronics-accessories', 'parent_id' => $electronicsId, 'sort_order' => 8],
        ];
        $this->insertCategories($subcategories);
    }

    private function createClothingSubcategories(): void
    {
        $clothingId = DB::table('categories')->where('slug', 'clothing')->first()->id;
        $subcategories = [
            ['title' => 'Чоловічий одяг', 'slug' => 'men-clothing', 'parent_id' => $clothingId, 'sort_order' => 1],
            ['title' => 'Жіночий одяг', 'slug' => 'women-clothing', 'parent_id' => $clothingId, 'sort_order' => 2],
            ['title' => 'Дитячий одяг', 'slug' => 'kids-clothing', 'parent_id' => $clothingId, 'sort_order' => 3],
            ['title' => 'Взуття', 'slug' => 'shoes', 'parent_id' => $clothingId, 'sort_order' => 4],
            ['title' => 'Аксесуари', 'slug' => 'clothing-accessories', 'parent_id' => $clothingId, 'sort_order' => 5],
            ['title' => 'Спортивний одяг', 'slug' => 'sport-clothing', 'parent_id' => $clothingId, 'sort_order' => 6],
        ];
        $this->insertCategories($subcategories);
    }

    private function createHomeGardenSubcategories(): void
    {
        $homeGardenId = DB::table('categories')->where('slug', 'home-garden')->first()->id;
        $subcategories = [
            ['title' => 'Меблі', 'slug' => 'furniture', 'parent_id' => $homeGardenId, 'sort_order' => 1],
            ['title' => 'Декор', 'slug' => 'decor', 'parent_id' => $homeGardenId, 'sort_order' => 2],
            ['title' => 'Кухонне приладдя', 'slug' => 'kitchen', 'parent_id' => $homeGardenId, 'sort_order' => 3],
            ['title' => 'Садівництво', 'slug' => 'gardening', 'parent_id' => $homeGardenId, 'sort_order' => 4],
            ['title' => 'Освітлення', 'slug' => 'lighting', 'parent_id' => $homeGardenId, 'sort_order' => 5],
            ['title' => 'Текстиль', 'slug' => 'textiles', 'parent_id' => $homeGardenId, 'sort_order' => 6],
        ];
        $this->insertCategories($subcategories);
    }

    private function createSportSubcategories(): void
    {
        $sportId = DB::table('categories')->where('slug', 'sport')->first()->id;
        $subcategories = [
            ['title' => 'Фітнес', 'slug' => 'fitness', 'parent_id' => $sportId, 'sort_order' => 1],
            ['title' => 'Футбол', 'slug' => 'football', 'parent_id' => $sportId, 'sort_order' => 2],
            ['title' => 'Баскетбол', 'slug' => 'basketball', 'parent_id' => $sportId, 'sort_order' => 3],
            ['title' => 'Туризм', 'slug' => 'tourism', 'parent_id' => $sportId, 'sort_order' => 4],
            ['title' => 'Водний спорт', 'slug' => 'water-sports', 'parent_id' => $sportId, 'sort_order' => 5],
            ['title' => 'Зимовий спорт', 'slug' => 'winter-sports', 'parent_id' => $sportId, 'sort_order' => 6],
        ];
        $this->insertCategories($subcategories);
    }

    private function createBeautySubcategories(): void
    {
        $beautyId = DB::table('categories')->where('slug', 'beauty')->first()->id;
        $subcategories = [
            ['title' => 'Косметика', 'slug' => 'cosmetics', 'parent_id' => $beautyId, 'sort_order' => 1],
            ['title' => 'Парфумерія', 'slug' => 'perfume', 'parent_id' => $beautyId, 'sort_order' => 2],
            ['title' => 'Догляд за шкірою', 'slug' => 'skincare', 'parent_id' => $beautyId, 'sort_order' => 3],
            ['title' => 'Догляд за волоссям', 'slug' => 'haircare', 'parent_id' => $beautyId, 'sort_order' => 4],
            ['title' => 'Манікюр', 'slug' => 'manicure', 'parent_id' => $beautyId, 'sort_order' => 5],
            ['title' => 'Аксесуари', 'slug' => 'beauty-accessories', 'parent_id' => $beautyId, 'sort_order' => 6],
        ];
        $this->insertCategories($subcategories);
    }

    private function createAutoSubcategories(): void
    {
        $autoId = DB::table('categories')->where('slug', 'auto')->first()->id;
        $subcategories = [
            ['title' => 'Запчастини', 'slug' => 'auto-parts', 'parent_id' => $autoId, 'sort_order' => 1],
            ['title' => 'Автохімія', 'slug' => 'auto-chemistry', 'parent_id' => $autoId, 'sort_order' => 2],
            ['title' => 'Інструменти', 'slug' => 'auto-tools', 'parent_id' => $autoId, 'sort_order' => 3],
            ['title' => 'Аксесуари', 'slug' => 'auto-accessories', 'parent_id' => $autoId, 'sort_order' => 4],
            ['title' => 'Шини', 'slug' => 'tires', 'parent_id' => $autoId, 'sort_order' => 5],
            ['title' => 'Масла', 'slug' => 'auto-oils', 'parent_id' => $autoId, 'sort_order' => 6],
        ];
        $this->insertCategories($subcategories);
    }

    private function insertCategories(array $categories): void
    {
        foreach ($categories as $category) {
            $category['is_active'] = true;
            $category['created_at'] = now();
            $category['updated_at'] = now();
            foreach (['title', 'slug', 'meta_title', 'meta_description'] as $f) {
                if (isset($category[$f]) && is_string($category[$f]) && $category[$f] !== '' && $category[$f][0] !== '{') {
                    $category[$f] = json_encode(['uk' => $category[$f]], JSON_UNESCAPED_UNICODE);
                }
            }
            DB::table('categories')->insert($category);
        }
    }
}
