<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Корекція карток дашборду за запитом:
 *  - Каталог +2: повертаємо Категорії + Бренди
 *  - Клієнти та сервіс +1: повертаємо Доставка та оплата (config_status)
 *  - Продажі: стрічка в один ряд → ховаємо «Замовлень за 7 днів» (зайва)
 * Мерджимо у наявний dashboard_cards.
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

        // Повернути (показати)
        foreach (['categories', 'brands', 'config_status'] as $id) {
            $cfg[$id] = array_merge($cfg[$id] ?? [], ['visible' => true]);
        }
        // Прибрати з Продажів зайву
        $cfg['orders_7d'] = array_merge($cfg['orders_7d'] ?? [], ['visible' => false]);

        \App\Models\DisplaySetting::set('dashboard_cards', $cfg, 'Налаштування карток дашборду');
        \App\Models\DisplaySetting::flushSettingsCache();
    }

    public function down(): void
    {
        // Не чіпаємо.
    }
};
