<?php

namespace Database\Seeders;

use App\Models\DisplaySetting;
use Illuminate\Database\Seeder;

class DisplaySettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Налаштування товарів
            [
                'key' => 'show_product_filters',
                'title' => 'Показувати характеристики товарів',
                'description' => 'Відображати фільтри/характеристики на картках товарів (колір, розмір, матеріал)',
                'type' => 'boolean',
                'group' => 'products',
                'value' => true,
                'sort_order' => 1,
            ],
            [
                'key' => 'max_product_filters_display',
                'title' => 'Максимум характеристик на картці',
                'description' => 'Скільки характеристик показувати на картці товару (решта буде +N)',
                'type' => 'number',
                'group' => 'products',
                'value' => 3,
                'sort_order' => 2,
            ],
            [
                'key' => 'show_product_badges',
                'title' => 'Показувати бейджі товарів',
                'description' => 'Відображати бейджі "ХІТ", "НОВИНКА", знижки на картках',
                'type' => 'boolean',
                'group' => 'products',
                'value' => true,
                'sort_order' => 3,
            ],

            // Налаштування головної сторінки
            [
                'key' => 'show_hero_section',
                'title' => 'Показувати Hero секцію',
                'description' => 'Відображати велику банерну секцію на головній сторінці',
                'type' => 'boolean',
                'group' => 'homepage',
                'value' => true,
                'sort_order' => 1,
            ],
            [
                'key' => 'show_categories_wall',
                'title' => 'Показувати стіну категорій',
                'description' => 'Відображати сітку категорій на головній сторінці',
                'type' => 'boolean',
                'group' => 'homepage',
                'value' => true,
                'sort_order' => 2,
            ],
            [
                'key' => 'show_hit_products',
                'title' => 'Показувати хіти продажу',
                'description' => 'Відображати секцію з рекомендованими товарами',
                'type' => 'boolean',
                'group' => 'homepage',
                'value' => true,
                'sort_order' => 3,
            ],
            [
                'key' => 'show_new_products',
                'title' => 'Показувати новинки',
                'description' => 'Відображати секцію з новими товарами',
                'type' => 'boolean',
                'group' => 'homepage',
                'value' => true,
                'sort_order' => 4,
            ],
            [
                'key' => 'hit_products_count',
                'title' => 'Кількість хітів на головній',
                'description' => 'Скільки хітів показувати в секції рекомендованих товарів',
                'type' => 'number',
                'group' => 'homepage',
                'value' => 4,
                'sort_order' => 5,
            ],
            [
                'key' => 'new_products_count',
                'title' => 'Кількість новинок на головній',
                'description' => 'Скільки новинок показувати в секції новинок',
                'type' => 'number',
                'group' => 'homepage',
                'value' => 8,
                'sort_order' => 6,
            ],

            // Налаштування каталогу
            [
                'key' => 'default_products_per_page',
                'title' => 'Товарів на сторінці за замовчуванням',
                'description' => 'Скільки товарів показувати на сторінці каталогу за замовчуванням',
                'type' => 'number',
                'group' => 'catalog',
                'value' => 25,
                'sort_order' => 1,
            ],
            [
                'key' => 'show_product_count_in_categories',
                'title' => 'Показувати кількість товарів у категоріях',
                'description' => 'Відображати кількість товарів поруч з назвою категорії',
                'type' => 'boolean',
                'group' => 'catalog',
                'value' => true,
                'sort_order' => 2,
            ],
            [
                'key' => 'show_filter_modal',
                'title' => 'Показувати модальні фільтри',
                'description' => 'Відображати фільтри в модальному вікні замість сайдбару',
                'type' => 'boolean',
                'group' => 'catalog',
                'value' => true,
                'sort_order' => 3,
            ],

            // Налаштування навігації
            [
                'key' => 'enable_mega_menu',
                'title' => 'Увімкнути мега-меню',
                'description' => 'Показувати розширене меню каталогу при наведенні',
                'type' => 'boolean',
                'group' => 'navigation',
                'value' => true,
                'sort_order' => 1,
            ],
            [
                'key' => 'mega_menu_columns',
                'title' => 'Кількість колонок в мега-меню',
                'description' => 'На скільки колонок розбивати категорії в мега-меню',
                'type' => 'number',
                'group' => 'navigation',
                'value' => 4,
                'sort_order' => 2,
            ],
            [
                'key' => 'show_quick_categories',
                'title' => 'Показувати швидкі категорії',
                'description' => 'Відображати горизонтальне меню з категоріями під шапкою',
                'type' => 'boolean',
                'group' => 'navigation',
                'value' => true,
                'sort_order' => 3,
            ],
            [
                'key' => 'quick_categories_count',
                'title' => 'Кількість швидких категорій',
                'description' => 'Скільки категорій показувати в горизонтальному меню',
                'type' => 'number',
                'group' => 'navigation',
                'value' => 6,
                'sort_order' => 4,
            ],
            [
                'key' => 'mega_menu_subcategories_limit',
                'title' => 'Ліміт підкategorій в мега-меню',
                'description' => 'Максимум підкategorій для показу в одній колонці мега-меню',
                'type' => 'number',
                'group' => 'navigation',
                'value' => 6,
                'sort_order' => 5,
            ],
            [
                'key' => 'show_mega_menu_promo',
                'title' => 'Показувати промо в мега-меню',
                'description' => 'Відображати промо-блок в мега-меню поруч з категоріями',
                'type' => 'boolean',
                'group' => 'navigation',
                'value' => true,
                'sort_order' => 6,
            ],

            // Налаштування мобільного відображення
            [
                'key' => 'mobile_products_per_row',
                'title' => 'Товарів в ряд на мобільних',
                'description' => 'Кількість товарів в ряду на мобільних пристроях (1 або 2)',
                'type' => 'number',
                'group' => 'mobile',
                'value' => 2,
                'sort_order' => 1,
            ],
            [
                'key' => 'mobile_grid_gap',
                'title' => 'Відступ між товарами на мобільних',
                'description' => 'Розмір відступу між товарами в мобільній сітці (2, 4, 6)',
                'type' => 'number',
                'group' => 'mobile',
                'value' => 4,
                'sort_order' => 2,
            ],
        ];

        foreach ($settings as $setting) {
            DisplaySetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
