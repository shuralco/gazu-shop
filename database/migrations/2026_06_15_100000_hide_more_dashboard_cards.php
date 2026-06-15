<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Ховаємо ще картки за запитом: Пошук (search_total/zero), ТТН/відправлень,
 * Залишок на суму, Немає в наявності. Мерджимо у наявний dashboard_cards
 * (зберігаємо порядок/решту), бо попередній дефолт уже міг бути заданий.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('display_settings')) {
            return;
        }

        $cfg = \App\Models\DisplaySetting::get('dashboard_cards');
        $cfg = is_array($cfg) ? $cfg : [];

        // + avg_order (середній чек) — «нижній ряд» Продажів; виручка всього лишається.
        foreach (['search_total', 'search_zero', 'shipments', 'stock_value', 'out_of_stock', 'avg_order'] as $id) {
            $cfg[$id] = array_merge($cfg[$id] ?? [], ['visible' => false]);
        }

        \App\Models\DisplaySetting::set('dashboard_cards', $cfg, 'Налаштування карток дашборду');
        \App\Models\DisplaySetting::flushSettingsCache();
    }

    public function down(): void
    {
        // Не чіпаємо.
    }
};
