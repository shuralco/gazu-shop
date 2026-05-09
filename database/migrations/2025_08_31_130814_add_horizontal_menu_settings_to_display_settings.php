<?php

use App\Models\DisplaySetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $horizontalMenuSettings = [
            'enable_horizontal_menu' => [
                'value' => true,
                'type' => 'boolean',
                'description' => 'Увімкнути горизонтальне меню',
            ],
            'horizontal_menu_display_mode' => [
                'value' => 'horizontal_only',
                'type' => 'string',
                'description' => 'Режим відображення: catalog_only, horizontal_only, both',
            ],
            'horizontal_menu_items_limit' => [
                'value' => 6,
                'type' => 'integer',
                'description' => 'Кількість пунктів в горизонтальному меню',
            ],
            'horizontal_show_delivery_info' => [
                'value' => true,
                'type' => 'boolean',
                'description' => 'Показувати інформацію про доставку',
            ],
            'horizontal_delivery_text' => [
                'value' => 'БЕЗКОШТОВНА ДОСТАВКА ВІД 1500₴',
                'type' => 'string',
                'description' => 'Текст інформації про доставку',
            ],
            'horizontal_enable_mega_menu' => [
                'value' => true,
                'type' => 'boolean',
                'description' => 'Увімкнути мега-меню для горизонтального каталогу',
            ],
            'horizontal_mega_menu_columns' => [
                'value' => 4,
                'type' => 'integer',
                'description' => 'Кількість колонок в горизонтальному мега-меню',
            ],
            'horizontal_mega_menu_subcategories_limit' => [
                'value' => 6,
                'type' => 'integer',
                'description' => 'Ліміт підкатегорій в горизонтальному мега-меню',
            ],
            'horizontal_mega_menu_promo_title' => [
                'value' => 'ШВИДКІ ПОКУПКИ',
                'type' => 'string',
                'description' => 'Заголовок промо-блоку горизонтального мега-меню',
            ],
            'horizontal_mega_menu_promo_subtitle' => [
                'value' => 'Популярні товари в один клік',
                'type' => 'string',
                'description' => 'Підзаголовок промо-блоку горизонтального мега-меню',
            ],
            'horizontal_mega_menu_promo_button' => [
                'value' => 'ПЕРЕГЛЯНУТИ ХІТИ',
                'type' => 'string',
                'description' => 'Текст кнопки промо-блоку горизонтального мега-меню',
            ],
            'horizontal_show_mega_menu_promo' => [
                'value' => true,
                'type' => 'boolean',
                'description' => 'Показувати промо-блок в горизонтальному мега-меню',
            ],
        ];

        foreach ($horizontalMenuSettings as $key => $setting) {
            DisplaySetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $setting['value'],
                    'type' => $setting['type'],
                    'title' => $setting['description'],
                    'description' => $setting['description'],
                    'group' => 'horizontal_menu',
                    'is_active' => true,
                    'sort_order' => 0,
                ]
            );
        }
    }

    public function down(): void
    {
        $keys = [
            'enable_horizontal_menu',
            'horizontal_menu_display_mode',
            'horizontal_menu_items_limit',
            'horizontal_show_delivery_info',
            'horizontal_delivery_text',
            'horizontal_enable_mega_menu',
            'horizontal_mega_menu_columns',
            'horizontal_mega_menu_subcategories_limit',
            'horizontal_mega_menu_promo_title',
            'horizontal_mega_menu_promo_subtitle',
            'horizontal_mega_menu_promo_button',
            'horizontal_show_mega_menu_promo',
        ];

        DisplaySetting::whereIn('key', $keys)->delete();
    }
};
