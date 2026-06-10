<?php

namespace Database\Seeders;

use App\Http\Controllers\Gazu\InfoController;
use App\Models\InfoPage;
use Illuminate\Database\Seeder;

/**
 * Імпортує дефолтний контент інфо-сторінок (джерело — InfoController::defaults)
 * у таблицю info_pages, щоб вони стали редагованими в адмінці (Filament).
 * Ідемпотентний: оновлює за slug, не плодить дублі, не чіпає вже відредаговані
 * вручну поля (updateOrCreate лише створює відсутні).
 *
 * УВАГА: тексти — шаблонні. Перед продом впишіть реальні реквізити компанії
 * (ЄДРПОУ/ФОП, юр. назва, адреса, телефон, email) у відповідні сторінки.
 */
class InfoPageSeeder extends Seeder
{
    /** slug => [show_in_footer, show_in_topbar, sort_order] */
    private const PLACEMENT = [
        'about'        => [true, false, 10],
        'delivery'     => [true, true, 20],
        'warranty'     => [true, true, 30],
        'privacy'      => [true, false, 40],
        'terms'        => [true, false, 50],
        'offer'        => [true, false, 60],
        'faq'          => [true, true, 70],
        'wholesale'    => [true, false, 80],
        'loyalty'      => [true, false, 90],
        'careers'      => [false, false, 100],
        'certificates' => [true, false, 110],
    ];

    public function run(): void
    {
        foreach (InfoController::defaults() as $slug => $page) {
            [$footer, $topbar, $sort] = self::PLACEMENT[$slug] ?? [false, false, 999];

            InfoPage::firstOrCreate(
                ['slug' => $slug],
                [
                    'title' => $page['title'],
                    'intro' => $page['intro'] ?? null,
                    'content_html' => null,
                    'sections' => $page['sections'] ?? [],
                    'meta_title' => $page['title'].' — GAZU',
                    'meta_description' => $page['intro'] ?? null,
                    'is_active' => true,
                    'show_in_footer' => $footer,
                    'show_in_topbar' => $topbar,
                    'sort_order' => $sort,
                ]
            );
        }
    }
}
