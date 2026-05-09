<?php

use App\Models\DisplaySetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $headerSettings = [
            // Top Bar Settings
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
                'key' => 'header_working_hours',
                'title' => 'Графік роботи',
                'description' => 'Режим роботи магазину',
                'value' => 'ПН-НД: 9:00-21:00',
                'type' => 'string',
                'group' => 'header_top_bar',
                'sort_order' => 4,
            ],
            [
                'key' => 'header_promo_text',
                'title' => 'Промо текст',
                'description' => 'Текст безкоштовної доставки або акції',
                'value' => 'БЕЗКОШТОВНА ДОСТАВКА ВІД 1500₴',
                'type' => 'string',
                'group' => 'header_top_bar',
                'sort_order' => 5,
            ],
            [
                'key' => 'header_promo_amount',
                'title' => 'Сума безкоштовної доставки',
                'description' => 'Мінімальна сума для безкоштовної доставки',
                'value' => 1500,
                'type' => 'number',
                'group' => 'header_top_bar',
                'sort_order' => 6,
            ],

            // Social Media Settings
            [
                'key' => 'show_social_links',
                'title' => 'Показувати соціальні мережі',
                'description' => 'Відображати посилання на соцмережі у верхній панелі',
                'value' => true,
                'type' => 'boolean',
                'group' => 'header_social',
                'sort_order' => 1,
            ],
            [
                'key' => 'facebook_url',
                'title' => 'Facebook URL',
                'description' => 'Посилання на Facebook сторінку',
                'value' => 'https://facebook.com/simpleshop',
                'type' => 'string',
                'group' => 'header_social',
                'sort_order' => 2,
            ],
            [
                'key' => 'instagram_url',
                'title' => 'Instagram URL',
                'description' => 'Посилання на Instagram профіль',
                'value' => 'https://instagram.com/simpleshop',
                'type' => 'string',
                'group' => 'header_social',
                'sort_order' => 3,
            ],

            // Main Header Settings
            [
                'key' => 'logo_type',
                'title' => 'Тип логотипу',
                'description' => 'Як відображати логотип: text, image, або both',
                'value' => 'text',
                'type' => 'string',
                'group' => 'header_main',
                'sort_order' => 1,
            ],
            [
                'key' => 'logo_text',
                'title' => 'Текст логотипу',
                'description' => 'Назва магазину для логотипу',
                'value' => 'SIMPLESHOP',
                'type' => 'string',
                'group' => 'header_main',
                'sort_order' => 2,
            ],
            [
                'key' => 'menu_catalog_text',
                'title' => 'Текст кнопки каталогу',
                'description' => 'Назва кнопки каталогу в меню',
                'value' => 'КАТАЛОГ',
                'type' => 'string',
                'group' => 'header_main',
                'sort_order' => 3,
            ],
            [
                'key' => 'menu_brands_text',
                'title' => 'Текст кнопки брендів',
                'description' => 'Назва кнопки брендів в меню',
                'value' => 'БРЕНДИ',
                'type' => 'string',
                'group' => 'header_main',
                'sort_order' => 4,
            ],
            [
                'key' => 'menu_specials_text',
                'title' => 'Текст кнопки акцій',
                'description' => 'Назва кнопки акцій в меню',
                'value' => 'АКЦІЇ',
                'type' => 'string',
                'group' => 'header_main',
                'sort_order' => 5,
            ],
            [
                'key' => 'menu_help_text',
                'title' => 'Текст кнопки допомоги',
                'description' => 'Назва кнопки допомоги в меню',
                'value' => 'ДОПОМОГА',
                'type' => 'string',
                'group' => 'header_main',
                'sort_order' => 6,
            ],

            // Mega Menu Content Settings
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
                'key' => 'mega_menu_promo_subtitle',
                'title' => 'Опис промо блоку',
                'description' => 'Текст опису промо секції',
                'value' => 'Знижки до 50% на вибрані категорії',
                'type' => 'string',
                'group' => 'mega_menu_content',
                'sort_order' => 2,
            ],
            [
                'key' => 'mega_menu_promo_button',
                'title' => 'Текст кнопки промо',
                'description' => 'Текст кнопки в промо блоці',
                'value' => 'ПЕРЕГЛЯНУТИ ВСІ',
                'type' => 'string',
                'group' => 'mega_menu_content',
                'sort_order' => 3,
            ],
            [
                'key' => 'mega_menu_phone_label',
                'title' => 'Підпис телефону',
                'description' => 'Підпис для номеру телефону в промо блоці',
                'value' => 'ГАРЯЧА ЛІНІЯ',
                'type' => 'string',
                'group' => 'mega_menu_content',
                'sort_order' => 4,
            ],

            // Horizontal Menu Settings
            [
                'key' => 'horizontal_menu_mode',
                'title' => 'Режим горизонтального меню',
                'description' => 'catalog_only, horizontal_only, або both',
                'value' => 'both',
                'type' => 'string',
                'group' => 'horizontal_menu',
                'sort_order' => 1,
            ],
            [
                'key' => 'horizontal_menu_style',
                'title' => 'Стиль горизонтального меню',
                'description' => 'Визначає стиль відображення: tabs, buttons, links',
                'value' => 'buttons',
                'type' => 'string',
                'group' => 'horizontal_menu',
                'sort_order' => 2,
            ],
            [
                'key' => 'horizontal_menu_background',
                'title' => 'Колір фону горизонтального меню',
                'description' => 'Hex код кольору або назва CSS класу',
                'value' => '#000000',
                'type' => 'string',
                'group' => 'horizontal_menu',
                'sort_order' => 3,
            ],
            [
                'key' => 'horizontal_categories_limit',
                'title' => 'Ліміт категорій',
                'description' => 'Максимум категорій у горизонтальному меню',
                'value' => 6,
                'type' => 'number',
                'group' => 'horizontal_menu',
                'sort_order' => 4,
            ],
        ];

        foreach ($headerSettings as $setting) {
            DisplaySetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }

    public function down(): void
    {
        DisplaySetting::whereIn('group', [
            'header_top_bar',
            'header_social',
            'header_main',
            'mega_menu_content',
            'horizontal_menu',
        ])->delete();
    }
};
