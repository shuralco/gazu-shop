<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DisplaySettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding display settings...');

        \App\Models\DisplaySetting::truncate();

        $settings = [
            [
                'key' => 'show_top_bar',
                'title' => 'Показувати верхню панель',
                'description' => 'Відображати верхню лінію з контактами та промо',
                'value' => true,
                'type' => 'boolean',
                'group' => 'header_top_bar',
                'sort_order' => 1,
            ],
            [
                'key' => 'header_phone',
                'title' => 'Телефон у верхній панелі',
                'description' => 'Номер телефону для відображення',
                'value' => '+380 XX XXX XX XX',
                'type' => 'string',
                'group' => 'header_top_bar',
                'sort_order' => 2,
            ],
            [
                'key' => 'header_email',
                'title' => 'Email у верхній панелі',
                'description' => 'Електронна пошта для відображення',
                'value' => 'info@simpleshop.com',
                'type' => 'string',
                'group' => 'header_top_bar',
                'sort_order' => 3,
            ],
            [
                'key' => 'mega_menu_enabled',
                'title' => 'Увімкнути мега-меню',
                'description' => 'Показувати розширене меню з категоріями',
                'value' => true,
                'type' => 'boolean',
                'group' => 'mega_menu',
                'sort_order' => 1,
            ],
            [
                'key' => 'mega_menu_promo_title',
                'title' => 'Заголовок промо блоку',
                'description' => 'Заголовок промо секції в мега-меню',
                'value' => 'АКЦІЇ ТИЖНЯ',
                'type' => 'string',
                'group' => 'mega_menu_content',
                'sort_order' => 1,
            ],
            [
                'key' => 'seo_auto_generate',
                'title' => 'Автоматична генерація SEO',
                'description' => 'Автоматично генерувати SEO дані для нових товарів',
                'value' => true,
                'type' => 'boolean',
                'group' => 'seo',
                'sort_order' => 1,
            ],
            [
                'key' => 'seo_default_language',
                'title' => 'Мова SEO за замовчуванням',
                'description' => 'Основна мова для генерації SEO',
                'value' => 'uk',
                'type' => 'string',
                'group' => 'seo',
                'sort_order' => 2,
            ],
            [
                'key' => 'sitemap_cache_duration',
                'title' => 'Тривалість кешування sitemap',
                'description' => 'Час кешування sitemap в хвилинах',
                'value' => 1440,
                'type' => 'number',
                'group' => 'seo',
                'sort_order' => 3,
            ],
        ];

        foreach ($settings as $setting) {
            \App\Models\DisplaySetting::create($setting);
        }

        $this->command->info('Display settings seeded successfully!');
    }
}
