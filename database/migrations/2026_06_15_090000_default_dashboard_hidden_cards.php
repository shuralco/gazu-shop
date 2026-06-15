<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Розумний дефолт дашборду: ховаємо надлишкові/малокорисні для щоденної
 * роботи картки (решта — дієві метрики). Ставимо ЛИШЕ якщо адмін ще не
 * налаштував дашборд сам (dashboard_cards порожній) — не перетираємо вибір.
 * Клієнт будь-коли поверне у «Налаштування дашборду».
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('display_settings')) {
            return;
        }

        $existing = \App\Models\DisplaySetting::get('dashboard_cards');
        if (is_array($existing) && ! empty($existing)) {
            return; // адмін уже налаштував — не чіпаємо
        }

        $hidden = [
            'orders_done',      // «Виконано %» — похідне
            'orders_payments',  // розбивка оплат — вже в підзаголовках
            'revenue_30d',      // лишаємо сьогодні/7д/усього
            'categories',       // статичний лічильник
            'brands',           // статичний лічильник
            'config_status',    // setup-health «Готово» — разове
        ];

        $cfg = [];
        foreach ($hidden as $id) {
            $cfg[$id] = ['visible' => false];
        }

        \App\Models\DisplaySetting::set('dashboard_cards', $cfg, 'Налаштування карток дашборду');
        \App\Models\DisplaySetting::flushSettingsCache();
    }

    public function down(): void
    {
        // Не чіпаємо — це лише дефолт-видимість.
    }
};
