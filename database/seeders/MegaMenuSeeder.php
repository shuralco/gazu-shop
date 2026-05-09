<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\DisplaySetting;
use Illuminate\Database\Seeder;

class MegaMenuSeeder extends Seeder
{
    public function run(): void
    {
        $this->createMainMegaMenuStructure();
        $this->createHorizontalMegaMenuStructure();
        $this->createPromoSettings();
    }

    private function createMainMegaMenuStructure(): void
    {
        $rootCategories = Category::whereNull('parent_id')
            ->orWhere('parent_id', 0)
            ->where('is_active', true)
            ->with(['children' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('sort_order')
            ->get();

        $columns = [];
        $columnItems = [];
        $perColumn = max(1, (int) ceil($rootCategories->count() / 4));

        foreach ($rootCategories as $i => $category) {
            $columnItems[] = [
                'type' => 'category',
                'title' => $category->title,
                'slug' => $category->slug,
                'category_id' => $category->id,
                'show_all_link' => $category->children->count() > 6,
                'children' => $category->children->take(8)->map(fn ($c) => [
                    'title' => $c->title,
                    'slug' => $c->slug,
                ])->values()->toArray(),
            ];

            if (count($columnItems) >= $perColumn || $i === $rootCategories->count() - 1) {
                $columns[] = $columnItems;
                $columnItems = [];
            }
        }

        // Додати колонку зі спеціальними пропозиціями
        $columns[] = [
            [
                'type' => 'custom_link',
                'title' => 'ХІТИ ПРОДАЖІВ',
                'url' => '/hits',
            ],
            [
                'type' => 'custom_link',
                'title' => 'АКЦІЇ',
                'url' => '/specials',
            ],
            [
                'type' => 'custom_link',
                'title' => 'НОВИНКИ',
                'url' => '/new',
            ],
        ];

        DisplaySetting::updateOrCreate(
            ['key' => 'main_mega_menu_structure'],
            [
                'value' => json_encode(['columns' => $columns]),
                'type' => 'json',
                'title' => 'Структура основного мега-меню',
                'description' => 'JSON структура для основного мега-меню',
                'group' => 'mega_menu',
                'is_active' => true,
                'sort_order' => 0,
            ]
        );
    }

    private function createHorizontalMegaMenuStructure(): void
    {
        $rootCategories = Category::whereNull('parent_id')
            ->orWhere('parent_id', 0)
            ->where('is_active', true)
            ->with(['children' => fn ($q) => $q->where('is_active', true)->take(4)])
            ->orderBy('sort_order')
            ->take(3)
            ->get();

        $columns = [];

        foreach ($rootCategories as $category) {
            $columns[] = [
                [
                    'type' => 'category',
                    'title' => $category->title,
                    'slug' => $category->slug,
                    'category_id' => $category->id,
                    'show_all_link' => true,
                    'children' => $category->children->map(fn ($c) => [
                        'title' => $c->title,
                        'slug' => $c->slug,
                    ])->values()->toArray(),
                ],
            ];
        }

        $columns[] = [
            ['type' => 'custom_link', 'title' => 'ХІТИ', 'url' => '/hits'],
            ['type' => 'custom_link', 'title' => 'НОВИНКИ', 'url' => '/new'],
        ];

        DisplaySetting::updateOrCreate(
            ['key' => 'horizontal_mega_menu_structure'],
            [
                'value' => json_encode(['columns' => $columns]),
                'type' => 'json',
                'title' => 'Структура горизонтального мега-меню',
                'description' => 'JSON структура для горизонтального мега-меню',
                'group' => 'mega_menu',
                'is_active' => true,
                'sort_order' => 0,
            ]
        );
    }

    private function createPromoSettings(): void
    {
        $promoSettings = [
            'main_show_promo' => true,
            'main_mega_menu_promo_title' => 'АКЦІЇ ТИЖНЯ',
            'main_mega_menu_promo_subtitle' => 'Знижки до 50% на вибрані категорії товарів',
            'main_mega_menu_promo_button' => 'ПЕРЕГЛЯНУТИ ВСІ',
            'main_mega_menu_promo_url' => '/specials',
        ];

        foreach ($promoSettings as $key => $value) {
            DisplaySetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'type' => is_bool($value) ? 'boolean' : 'string',
                    'title' => ucfirst(str_replace('_', ' ', $key)),
                    'group' => 'mega_menu',
                    'is_active' => true,
                    'sort_order' => 0,
                ]
            );
        }
    }
}
