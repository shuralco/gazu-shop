<?php

namespace Modules\LayoutBuilder;

use App\Models\LayoutBlock;
use App\Support\Hooks;
use App\Support\ModuleManager;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

/**
 * Bootstraps the layout_builder module.
 *
 *   boot(): підписує Hooks::on() для КОЖНОЇ іменованої зони storefront.
 *   Listener рендерить активні LayoutBlock цієї зони (відсортовані за
 *   sort_order). Якщо модуль вимкнено в БД або таблиці ще немає —
 *   listener тихо повертає null (нічого не рендерить).
 *
 *   Core/тема просто має `@hookAction('layout.home.top')` тощо — модуль
 *   вирішує що рендерити. Це OpenCart-стиль layout positions.
 */
class LayoutBuilderServiceProvider extends ServiceProvider
{
    /** Hook-point → zone-key (значення в стовпці zone). */
    private const ZONES = [
        'layout.home.top' => 'home.top',
        'layout.home.bottom' => 'home.bottom',
        'layout.product.sidebar' => 'product.sidebar',
        'layout.page.top' => 'page.top',
        'layout.page.bottom' => 'page.bottom',
    ];

    public function boot(): void
    {
        foreach (self::ZONES as $hook => $zoneKey) {
            Hooks::on($hook, function (...$args) use ($zoneKey) {
                return $this->renderZone($zoneKey, $args);
            }, priority: 10, source: 'layout_builder');
        }
    }

    /**
     * Рендер усіх активних блоків зони. Lazy DB/schema-aware гейтинг:
     *   - module disabled у БД → null
     *   - таблиці ще немає (міграція не виконана) → null
     */
    private function renderZone(string $zoneKey, array $args): ?string
    {
        if (! ModuleManager::for('layout_builder')->enabled()) {
            return null;
        }

        try {
            if (! Schema::hasTable('layout_blocks')) {
                return null;
            }

            $blocks = LayoutBlock::renderable($zoneKey);

            // Зони CMS-сторінок: блок можна обмежити конкретними сторінками
            // через config.pages = "slug1, slug2" (OpenCart-стиль layout
            // assignment). Порожній/відсутній список = всі сторінки.
            if (str_starts_with($zoneKey, 'page.')) {
                $slug = (string) ($args[0] ?? '');
                $blocks = $blocks->filter(function (LayoutBlock $b) use ($slug) {
                    $pages = trim((string) ($b->config['pages'] ?? ''));
                    if ($pages === '') {
                        return true;
                    }
                    $list = array_filter(array_map('trim', explode(',', $pages)));

                    return $slug !== '' && in_array($slug, $list, true);
                })->values();
            }

            if ($blocks->isEmpty()) {
                return null;
            }

            return view('layout_builder::zone', [
                'blocks' => $blocks,
                'zone' => $zoneKey,
                'args' => $args,
            ])->render();
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }
}
