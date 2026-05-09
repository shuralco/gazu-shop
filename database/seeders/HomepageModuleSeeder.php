<?php

namespace Database\Seeders;

use App\Models\HomepageModule;
use Illuminate\Database\Seeder;

class HomepageModuleSeeder extends Seeder
{
    public function run(): void
    {
        HomepageModule::truncate();

        HomepageModule::create([
            'type' => 'hero',
            'title' => null,
            'sort_order' => 1,
            'is_active' => true,
            'settings' => [
                'subtitle' => 'E-COMMERCE 2025',
                'title_line1' => 'СУЧАСНИЙ',
                'title_line2' => 'МАГАЗИН',
                'description' => "ЯКІСНІ ТОВАРИ.\nШВИДКА ДОСТАВКА.\nПРОСТИЙ СЕРВІС.",
                'button_text' => 'ПОЧАТИ ПОКУПКИ',
                'button_url' => '/specials',
                'bg_color' => '#ffffff',
            ],
        ]);

        HomepageModule::create([
            'type' => 'products_grid',
            'title' => 'ХІТИ ПРОДАЖІВ',
            'sort_order' => 2,
            'is_active' => true,
            'settings' => [
                'filter' => 'hits',
                'limit' => 8,
                'columns' => 4,
            ],
        ]);

        HomepageModule::create([
            'type' => 'categories',
            'title' => 'КАТЕГОРІЇ',
            'sort_order' => 3,
            'is_active' => true,
            'settings' => [
                'limit' => 6,
                'style' => 'grid',
            ],
        ]);

        HomepageModule::create([
            'type' => 'products_grid',
            'title' => 'НОВИНКИ',
            'sort_order' => 4,
            'is_active' => true,
            'settings' => [
                'filter' => 'new',
                'limit' => 8,
                'columns' => 4,
            ],
        ]);

        HomepageModule::create([
            'type' => 'banner',
            'title' => null,
            'sort_order' => 5,
            'is_active' => true,
            'settings' => [
                'text' => 'БЕЗКОШТОВНА ДОСТАВКА ВІД 1500 ГРН',
                'subtext' => 'На всі замовлення по Україні',
                'button_text' => 'ЗАМОВИТИ',
                'button_url' => '/specials',
                'bg_color' => '#000000',
                'text_color' => '#ffffff',
            ],
        ]);

        HomepageModule::create([
            'type' => 'advantages',
            'title' => null,
            'sort_order' => 6,
            'is_active' => true,
            'settings' => [
                'items' => [
                    ['icon' => '🚚', 'title' => 'БЕЗКОШТОВНА ДОСТАВКА', 'text' => 'При замовленні від 1500 грн'],
                    ['icon' => '💳', 'title' => 'БЕЗПЕЧНА ОПЛАТА', 'text' => 'LiqPay, WayForPay, Monobank'],
                    ['icon' => '🔄', 'title' => 'ПОВЕРНЕННЯ 14 ДНІВ', 'text' => 'Гарантія повернення'],
                    ['icon' => '📞', 'title' => 'ПІДТРИМКА 24/7', 'text' => "Завжди на зв'язку"],
                ],
            ],
        ]);

        HomepageModule::create([
            'type' => 'brands',
            'title' => 'БРЕНДИ',
            'sort_order' => 7,
            'is_active' => true,
            'settings' => [
                'limit' => 12,
            ],
        ]);

        HomepageModule::create([
            'type' => 'products_grid',
            'title' => 'АКЦІЙНІ ТОВАРИ',
            'sort_order' => 8,
            'is_active' => true,
            'settings' => [
                'filter' => 'specials',
                'limit' => 4,
                'columns' => 4,
            ],
        ]);

        HomepageModule::create([
            'type' => 'newsletter',
            'title' => null,
            'sort_order' => 9,
            'is_active' => true,
            'settings' => [
                'title' => 'ПІДПИШІТЬСЯ НА РОЗСИЛКУ',
                'description' => 'ОТРИМУЙТЕ ЕКСКЛЮЗИВНІ ПРОПОЗИЦІЇ ТА ЗНИЖКИ',
                'button_text' => 'ПІДПИСАТИСЯ',
            ],
        ]);
    }
}
