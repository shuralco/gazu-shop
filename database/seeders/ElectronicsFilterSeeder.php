<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ElectronicsFilterSeeder extends Seeder
{
    /**
     * Run the database seeds for electronics and tech products.
     */
    public function run(): void
    {
        // Додаткові групи фільтрів для електроніки
        $newGroups = [
            ['id' => 7, 'title' => 'Діагональ екрану', 'is_active' => true, 'sort_order' => 7],
            ['id' => 8, 'title' => 'Оперативна пам\'ять', 'is_active' => true, 'sort_order' => 8],
            ['id' => 9, 'title' => 'Внутрішня пам\'ять', 'is_active' => true, 'sort_order' => 9],
            ['id' => 10, 'title' => 'Процесор', 'is_active' => true, 'sort_order' => 10],
            ['id' => 11, 'title' => 'Операційна система', 'is_active' => true, 'sort_order' => 11],
            ['id' => 12, 'title' => 'Тип екрану', 'is_active' => true, 'sort_order' => 12],
            ['id' => 13, 'title' => 'Роздільна здатність', 'is_active' => true, 'sort_order' => 13],
            ['id' => 14, 'title' => 'Батарея', 'is_active' => true, 'sort_order' => 14],
        ];

        foreach ($newGroups as $group) {
            DB::table('filter_groups')->insertOrIgnore($group);
        }

        // Фільтри для нових груп
        $newFilters = [
            // Діагональ екрану
            ['title' => '5.5"', 'filter_group_id' => 7, 'is_active' => true, 'sort_order' => 1],
            ['title' => '6.1"', 'filter_group_id' => 7, 'is_active' => true, 'sort_order' => 2],
            ['title' => '6.7"', 'filter_group_id' => 7, 'is_active' => true, 'sort_order' => 3],
            ['title' => '10.9"', 'filter_group_id' => 7, 'is_active' => true, 'sort_order' => 4],
            ['title' => '13"', 'filter_group_id' => 7, 'is_active' => true, 'sort_order' => 5],
            ['title' => '15.6"', 'filter_group_id' => 7, 'is_active' => true, 'sort_order' => 6],
            ['title' => '17"', 'filter_group_id' => 7, 'is_active' => true, 'sort_order' => 7],

            // Оперативна пам'ять
            ['title' => '2 GB', 'filter_group_id' => 8, 'is_active' => true, 'sort_order' => 1],
            ['title' => '4 GB', 'filter_group_id' => 8, 'is_active' => true, 'sort_order' => 2],
            ['title' => '6 GB', 'filter_group_id' => 8, 'is_active' => true, 'sort_order' => 3],
            ['title' => '8 GB', 'filter_group_id' => 8, 'is_active' => true, 'sort_order' => 4],
            ['title' => '12 GB', 'filter_group_id' => 8, 'is_active' => true, 'sort_order' => 5],
            ['title' => '16 GB', 'filter_group_id' => 8, 'is_active' => true, 'sort_order' => 6],
            ['title' => '32 GB', 'filter_group_id' => 8, 'is_active' => true, 'sort_order' => 7],

            // Внутрішня пам'ять
            ['title' => '64 GB', 'filter_group_id' => 9, 'is_active' => true, 'sort_order' => 1],
            ['title' => '128 GB', 'filter_group_id' => 9, 'is_active' => true, 'sort_order' => 2],
            ['title' => '256 GB', 'filter_group_id' => 9, 'is_active' => true, 'sort_order' => 3],
            ['title' => '512 GB', 'filter_group_id' => 9, 'is_active' => true, 'sort_order' => 4],
            ['title' => '1 TB', 'filter_group_id' => 9, 'is_active' => true, 'sort_order' => 5],
            ['title' => '2 TB', 'filter_group_id' => 9, 'is_active' => true, 'sort_order' => 6],

            // Процесор
            ['title' => 'Apple A15', 'filter_group_id' => 10, 'is_active' => true, 'sort_order' => 1],
            ['title' => 'Apple M1', 'filter_group_id' => 10, 'is_active' => true, 'sort_order' => 2],
            ['title' => 'Apple M2', 'filter_group_id' => 10, 'is_active' => true, 'sort_order' => 3],
            ['title' => 'Snapdragon 888', 'filter_group_id' => 10, 'is_active' => true, 'sort_order' => 4],
            ['title' => 'Intel Core i5', 'filter_group_id' => 10, 'is_active' => true, 'sort_order' => 5],
            ['title' => 'Intel Core i7', 'filter_group_id' => 10, 'is_active' => true, 'sort_order' => 6],
            ['title' => 'AMD Ryzen 5', 'filter_group_id' => 10, 'is_active' => true, 'sort_order' => 7],
            ['title' => 'AMD Ryzen 7', 'filter_group_id' => 10, 'is_active' => true, 'sort_order' => 8],

            // Операційна система
            ['title' => 'iOS', 'filter_group_id' => 11, 'is_active' => true, 'sort_order' => 1],
            ['title' => 'Android', 'filter_group_id' => 11, 'is_active' => true, 'sort_order' => 2],
            ['title' => 'Windows 11', 'filter_group_id' => 11, 'is_active' => true, 'sort_order' => 3],
            ['title' => 'macOS', 'filter_group_id' => 11, 'is_active' => true, 'sort_order' => 4],
            ['title' => 'Linux', 'filter_group_id' => 11, 'is_active' => true, 'sort_order' => 5],

            // Тип екрану
            ['title' => 'IPS', 'filter_group_id' => 12, 'is_active' => true, 'sort_order' => 1],
            ['title' => 'OLED', 'filter_group_id' => 12, 'is_active' => true, 'sort_order' => 2],
            ['title' => 'AMOLED', 'filter_group_id' => 12, 'is_active' => true, 'sort_order' => 3],
            ['title' => 'Retina', 'filter_group_id' => 12, 'is_active' => true, 'sort_order' => 4],
            ['title' => 'LCD', 'filter_group_id' => 12, 'is_active' => true, 'sort_order' => 5],

            // Роздільна здатність
            ['title' => 'HD (1280x720)', 'filter_group_id' => 13, 'is_active' => true, 'sort_order' => 1],
            ['title' => 'Full HD (1920x1080)', 'filter_group_id' => 13, 'is_active' => true, 'sort_order' => 2],
            ['title' => '2K (2560x1440)', 'filter_group_id' => 13, 'is_active' => true, 'sort_order' => 3],
            ['title' => '4K (3840x2160)', 'filter_group_id' => 13, 'is_active' => true, 'sort_order' => 4],
            ['title' => '5K (5120x2880)', 'filter_group_id' => 13, 'is_active' => true, 'sort_order' => 5],

            // Батарея
            ['title' => '3000 mAh', 'filter_group_id' => 14, 'is_active' => true, 'sort_order' => 1],
            ['title' => '4000 mAh', 'filter_group_id' => 14, 'is_active' => true, 'sort_order' => 2],
            ['title' => '5000 mAh', 'filter_group_id' => 14, 'is_active' => true, 'sort_order' => 3],
            ['title' => '6000 mAh', 'filter_group_id' => 14, 'is_active' => true, 'sort_order' => 4],
            ['title' => '10 годин', 'filter_group_id' => 14, 'is_active' => true, 'sort_order' => 5],
            ['title' => '15 годин', 'filter_group_id' => 14, 'is_active' => true, 'sort_order' => 6],
            ['title' => '20 годин', 'filter_group_id' => 14, 'is_active' => true, 'sort_order' => 7],
        ];

        foreach ($newFilters as $filter) {
            DB::table('filters')->insertOrIgnore($filter);
        }

        $this->command->info('Electronics filter groups and filters seeded successfully!');
    }
}
