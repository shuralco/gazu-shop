<?php

namespace App\Services;

use App\Models\Category;
use App\Models\DisplaySetting;
use Illuminate\Support\Collection;

class HeaderService
{
    public function __construct(
        private DisplaySetting $displaySetting,
        private Category $category
    ) {}

    public function getTopBarConfig(): array
    {
        return cache()->remember('header_top_bar_config', 3600, function () {
            $settings = DisplaySetting::getTopBarSettings();

            return [
                'show' => $settings['show_top_bar'] ?? true,
                'phone' => $settings['header_phone'] ?? shopPhone(),
                'email' => $settings['header_email'] ?? shopEmail(),
                'working_hours' => $settings['header_working_hours'] ?? 'ПН-НД: 9:00-21:00',
                'promo_text' => $settings['header_promo_text'] ?? 'БЕЗКОШТОВНА ДОСТАВКА ВІД 1500₴',
                'promo_amount' => $settings['header_promo_amount'] ?? 1500,
                'show_social' => $settings['show_social_links'] ?? true,
                'facebook_url' => $settings['facebook_url'] ?? '#',
                'instagram_url' => $settings['instagram_url'] ?? '#',
            ];
        });
    }

    public function getMainHeaderConfig(): array
    {
        return cache()->remember('header_main_config', 3600, function () {
            $settings = DisplaySetting::getHeaderSettings();

            return [
                'logo_type' => $settings['logo_type'] ?? 'text',
                'logo_text' => $settings['logo_text'] ?? 'SIMPLESHOP',
                'menu_catalog_text' => $settings['menu_catalog_text'] ?? 'КАТАЛОГ',
                'menu_brands_text' => $settings['menu_brands_text'] ?? 'БРЕНДИ',
                'menu_specials_text' => $settings['menu_specials_text'] ?? 'АКЦІЇ',
                'menu_help_text' => $settings['menu_help_text'] ?? 'ДОПОМОГА',
            ];
        });
    }

    public function getMegaMenuConfig(): array
    {
        return cache()->remember('mega_menu_config', 1800, function () {
            $settings = DisplaySetting::getMegaMenuSettings();
            $navigationSettings = DisplaySetting::getHeaderSettings();

            $columns = $navigationSettings['mega_menu_columns'] ?? 4;
            $subcategoriesLimit = $navigationSettings['mega_menu_subcategories_limit'] ?? 6;

            return [
                'enabled' => $navigationSettings['enable_mega_menu'] ?? true,
                'columns' => $columns,
                'subcategories_limit' => $subcategoriesLimit,
                'show_promo' => $navigationSettings['show_mega_menu_promo'] ?? true,
                'promo_title' => $settings['mega_menu_promo_title'] ?? 'АКЦІЇ ТИЖНЯ',
                'promo_subtitle' => $settings['mega_menu_promo_subtitle'] ?? 'Знижки до 50% на вибрані категорії',
                'promo_button' => $settings['mega_menu_promo_button'] ?? 'ПЕРЕГЛЯНУТИ ВСІ',
                'phone_label' => $settings['mega_menu_phone_label'] ?? 'ГАРЯЧА ЛІНІЯ',
                'categories' => $this->getMegaMenuCategories($columns, $subcategoriesLimit),
            ];
        });
    }

    public function getHorizontalMenuConfig(): array
    {
        // ТИМЧАСОВО БЕЗ КЕШУ - ВИПРАВЛЯЄМО ПРОБЛЕМУ
        $categoriesLimit = DisplaySetting::get('horizontal_menu_items_limit', 6) ?? 6;
        $displayMode = DisplaySetting::get('horizontal_menu_display_mode', 'horizontal_only');
        $enabled = DisplaySetting::get('enable_horizontal_menu', true);

        return [
            'enabled' => $enabled,
            'display_mode' => $displayMode,
            'catalog_text' => DisplaySetting::get('menu_catalog_text', 'КАТАЛОГ'),
            'menu_items' => $this->getHorizontalMenuItems($categoriesLimit),
            'show_delivery_info' => DisplaySetting::get('horizontal_show_delivery_info', true),
            'delivery_text' => DisplaySetting::get('horizontal_delivery_text', 'БЕЗКОШТОВНА ДОСТАВКА ВІД 1500₴'),
            'show_mega_menu' => DisplaySetting::get('horizontal_enable_mega_menu', true),
            'mega_menu_columns' => DisplaySetting::get('horizontal_mega_menu_columns', 4),
            'mega_menu_categories' => $this->getHorizontalMenuCategories($categoriesLimit),
            'mega_menu_structure' => $this->getHorizontalMegaMenuStructure(),
            'mega_menu_subcategories_limit' => DisplaySetting::get('horizontal_mega_menu_subcategories_limit', 6),
            'promo_title' => DisplaySetting::get('horizontal_mega_menu_promo_title', 'ШВИДКІ ПОКУПКИ'),
            'promo_subtitle' => DisplaySetting::get('horizontal_mega_menu_promo_subtitle', 'Популярні товари в один клік'),
            'promo_button' => DisplaySetting::get('horizontal_mega_menu_promo_button', 'ПЕРЕГЛЯНУТИ ХІТИ'),
            'show_promo' => DisplaySetting::get('horizontal_show_mega_menu_promo', true),
        ];
    }

    private function getMegaMenuCategories(int $columns = 4, int $subcategoriesLimit = 6): Collection
    {
        return $this->category
            ->whereNull('parent_id')
            ->with(['children' => function ($query) use ($subcategoriesLimit) {
                $query->with(['children' => function ($subQuery) {
                    $subQuery->orderBy('sort_order')->limit(4);
                }])
                    ->limit($subcategoriesLimit)
                    ->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->limit($columns)
            ->get();
    }

    private function getHorizontalMenuCategories(int $limit = 6): Collection
    {
        return $this->category
            ->whereNull('parent_id')
            ->with(['children' => function ($query) {
                $query->with(['children' => function ($subQuery) {
                    $subQuery->orderBy('sort_order')->limit(3);
                }])
                    ->orderBy('sort_order')
                    ->limit(5);
            }])
            ->orderBy('sort_order')
            ->limit($limit)
            ->get();
    }

    private function getHorizontalMenuItems(?int $limit = 6): array
    {
        // Prefer stored horizontal menu items from MegaMenuEditor page
        $storedItems = DisplaySetting::get('horizontal_menu_items', null);

        if ($storedItems) {
            $items = $storedItems;
            if (is_string($items)) {
                $items = json_decode($items, true) ?? [];
            }

            if (is_array($items) && ! empty($items)) {
                // Add active state based on current URL
                return array_map(function ($item) {
                    $url = ltrim($item['url'] ?? '', '/');

                    return [
                        'text' => strtoupper($item['text'] ?? ''),
                        'url' => $item['url'] ?? '#',
                        'active' => ! empty($url) && request()->is("{$url}*"),
                    ];
                }, $items);
            }
        }

        // Fallback: generate from categories
        $limit = $limit ?? 6;
        $categories = $this->getHorizontalMenuCategories($limit);
        $items = [];

        foreach ($categories as $category) {
            $items[] = [
                'text' => strtoupper($category->title),
                'url' => "/{$category->slug}",
                'active' => request()->is("{$category->slug}*"),
            ];
        }

        $staticMenuItems = [
            ['text' => __('general.brands'), 'url' => '/brands', 'active' => request()->is('*/brands*')],
            ['text' => __('general.specials'), 'url' => '/specials', 'active' => request()->is('*/specials*')],
            ['text' => __('general.hits'), 'url' => '/hits', 'active' => request()->is('*/hits*')],
            ['text' => __('general.new_products'), 'url' => '/new', 'active' => request()->is('*/new*')],
        ];

        return array_merge($items, $staticMenuItems);
    }

    public function isTopBarEnabled(): bool
    {
        return DisplaySetting::get('show_top_bar', true);
    }

    public function isMegaMenuEnabled(): bool
    {
        return DisplaySetting::get('enable_mega_menu', true);
    }

    public function isHorizontalMenuEnabled(): bool
    {
        $mode = DisplaySetting::get('horizontal_menu_mode', 'both');

        return in_array($mode, ['horizontal_only', 'both']);
    }

    public function isCatalogButtonEnabled(): bool
    {
        $mode = DisplaySetting::get('horizontal_menu_mode', 'both');

        return in_array($mode, ['catalog_only', 'both']);
    }

    public function getHorizontalMegaMenuStructure(): array
    {
        $structure = DisplaySetting::get('horizontal_mega_menu_structure', []);

        if (empty($structure) || ! isset($structure['columns'])) {
            return ['columns' => [], 'custom_links' => []];
        }

        return $structure;
    }

    public function flushAllCaches(): void
    {
        cache()->forget('header_top_bar_config');
        cache()->forget('header_main_config');
        cache()->forget('mega_menu_config');
        cache()->forget('horizontal_menu_config');
        DisplaySetting::flushHeaderCache();
    }
}
