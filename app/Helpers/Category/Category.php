<?php

namespace App\Helpers\Category;

use App\Helpers\Container;
use App\Models\DisplaySetting;

class Category
{
    public static string $tpl;

    public static function getMenu(string $tpl, string $cacheKey = '')
    {
        self::$tpl = $tpl;
        if ($cacheKey) {
            $menu_html = cache($cacheKey, '');
            if ($menu_html) {
                return $menu_html;
            }
        }
        $categories_data = self::getCategories();
        $categories_tree = self::getTree($categories_data);
        $menu_html = self::getHtml($categories_tree);
        if ($cacheKey) {
            cache([$cacheKey => $menu_html], now()->addDay());
        }

        return $menu_html;
    }

    public static function getTree($data): array
    {
        $categories_tree = [];
        foreach ($data as $id => &$node) {
            if (! $node['parent_id']) {
                $categories_tree[$id] = &$node;
            } else {
                $data[$node['parent_id']]['children'][$id] = &$node;
            }
        }

        return $categories_tree;
    }

    public static function getHtml(array $tree, $tab = ''): string
    {
        $str = '';
        foreach ($tree as $id => $item) {
            $str .= self::item2Tpl($item, $tab, $id);
        }

        return $str;
    }

    public static function item2Tpl($item, $tab, $id): string
    {
        ob_start();
        echo view(self::$tpl, ['item' => $item, 'tab' => $tab, 'id' => $id]);

        return ob_get_clean();
    }

    public static function getCategories()
    {
        $categories_data = Container::get('categories_data');
        if (! $categories_data) {
            $categories_data = \App\Models\Category::all()->keyBy('id')->toArray();
            Container::set('categories_data', $categories_data);
        }

        return $categories_data;
    }

    public static function getIds(int $category_id): string
    {
        $categories = self::getCategories();
        $ids = '';
        $processed = [];

        foreach ($categories as $category) {
            if ($category['parent_id'] == $category_id && ! in_array($category['id'], $processed)) {
                $processed[] = $category['id'];
                $ids .= $category['id'].',';
                $child_ids = self::getChildIds($category['id'], $processed);
                if ($child_ids) {
                    $ids .= $child_ids;
                }
            }
        }

        return $ids;
    }

    private static function getChildIds(int $category_id, array &$processed): string
    {
        $categories = self::getCategories();
        $ids = '';

        foreach ($categories as $category) {
            if ($category['parent_id'] == $category_id && ! in_array($category['id'], $processed)) {
                $processed[] = $category['id'];
                $ids .= $category['id'].',';
                $child_ids = self::getChildIds($category['id'], $processed);
                if ($child_ids) {
                    $ids .= $child_ids;
                }
            }
        }

        return $ids;
    }

    public static function getBreadcrumbs(int $category_id)
    {
        $categories = self::getCategories();
        $breadcrumbs = [];
        $current_id = $category_id;

        while ($current_id && isset($categories[$current_id])) {
            $category = $categories[$current_id];
            $breadcrumbs[$category['slug']] = $category['title'];
            $current_id = $category['parent_id'];
        }

        return array_reverse($breadcrumbs, true);
    }

    public static function getMegaMenu(): string
    {
        if (! DisplaySetting::get('enable_mega_menu', true)) {
            return '';
        }

        $categories_data = self::getCategories();
        $categories_tree = self::getTree($categories_data);

        $maxColumns = DisplaySetting::get('mega_menu_columns', 4);
        $subcategoriesLimit = DisplaySetting::get('mega_menu_subcategories_limit', 6);

        $html = '';
        $count = 0;
        foreach ($categories_tree as $category) {
            if ($count >= $maxColumns) {
                break;
            }

            $html .= '<div>';
            $html .= '<h4 class="font-black text-black mb-6 text-xl border-b-4 border-black pb-3">'.strtoupper($category['title']).'</h4>';
            $html .= '<ul class="space-y-3">';

            if (! empty($category['children'])) {
                foreach (array_slice($category['children'], 0, $subcategoriesLimit) as $child) {
                    $html .= '<li><a wire:navigate href="'.locale_url($child['slug']).'" class="text-black text-base font-medium hover:font-black transition-all">'.$child['title'].'</a></li>';
                }

                if (count($category['children']) > $subcategoriesLimit) {
                    $html .= '<li><a wire:navigate href="'.locale_url($category['slug']).'" class="text-black text-sm font-black hover:underline">+ ВСЕ В КАТЕГОРІЇ</a></li>';
                }
            }

            $html .= '</ul>';
            $html .= '</div>';
            $count++;
        }

        return $html;
    }

    public static function getMobileMenu(): string
    {
        if (! DisplaySetting::get('show_mobile_menu', true)) {
            return '';
        }

        $categories_data = self::getCategories();
        $categories_tree = self::getTree($categories_data);

        $html = '';
        foreach ($categories_tree as $category) {
            $categoryId = 'mobile'.$category['id'];
            $html .= '<div class="mb-8">';
            $html .= '<button class="w-full text-left text-2xl font-black text-black mb-4 pb-2 border-b-2 border-black flex justify-between items-center" onclick="toggleSubmenu(\''.$categoryId.'\')">';
            $html .= strtoupper($category['title']);
            $html .= '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
            $html .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path>';
            $html .= '</svg>';
            $html .= '</button>';

            if (! empty($category['children'])) {
                $html .= '<div id="'.$categoryId.'" class="submenu pl-4">';
                foreach ($category['children'] as $child) {
                    $html .= '<a wire:navigate href="'.locale_url($child['slug']).'" class="block py-2 text-lg font-medium text-black hover:font-black">'.$child['title'].'</a>';
                }
                $html .= '</div>';
            }

            $html .= '</div>';
        }

        return $html;
    }

    public static function getQuickCategories(): string
    {
        if (! DisplaySetting::get('show_quick_categories', true)) {
            return '';
        }

        $categories_data = self::getCategories();
        $categories_tree = self::getTree($categories_data);
        $limit = DisplaySetting::get('quick_categories_count', 6);

        $html = '<div class="hidden md:block bg-gray-100 border-b-2 border-black">';
        $html .= '<div class="max-w-screen-2xl mx-auto px-4 md:px-8">';
        $html .= '<div class="flex items-center gap-6 py-3 overflow-x-auto">';

        $count = 0;
        foreach ($categories_tree as $category) {
            if ($count >= $limit) {
                break;
            }

            $html .= '<a wire:navigate href="'.locale_url($category['slug']).'" ';
            $html .= 'class="text-black font-bold text-sm whitespace-nowrap hover:underline hover:font-black transition-all">';
            $html .= strtoupper($category['title']);
            $html .= '</a>';
            $count++;
        }

        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
}
