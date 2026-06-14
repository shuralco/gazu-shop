<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Засіваємо провайдерів доставки для checkout (ідемпотентно), щоб клієнт міг
 * керувати ними в адмінці (Оплата і доставка → Способи доставки), а checkout
 * читав їх із БД. На частині середовищ shipping_providers порожня — там
 * доставка трималась на legacy module-gating у blade.
 *
 * Активність визначаємо за тим самим правилом, що й попередній хардкод:
 * Нова Пошта/УкрПошта — лише якщо відповідний модуль увімкнено; самовивіз —
 * завжди (це core). Так перехід на БД не змінює видимих способів доставки.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('shipping_providers')) {
            return;
        }

        $hasSort = Schema::hasColumn('shipping_providers', 'sort_order');
        $hasDesc = Schema::hasColumn('shipping_providers', 'description');
        $now = now();

        $providers = [
            ['code' => 'novaposhta', 'name' => 'Нова Пошта', 'desc' => 'Відділення / Поштомат / Курʼєр НП — 1-3 дні', 'sort' => 10, 'module' => 'novaposhta'],
            ['code' => 'ukrposhta', 'name' => 'УкрПошта', 'desc' => 'Відділення / адреса · 3-5 днів, дешевше', 'sort' => 20, 'module' => 'ukrposhta'],
            ['code' => 'pickup', 'name' => 'Самовивіз з магазину', 'desc' => 'Самовивіз — безкоштовно', 'sort' => 30, 'module' => null],
        ];

        foreach ($providers as $p) {
            if (DB::table('shipping_providers')->where('code', $p['code'])->exists()) {
                continue;
            }

            $active = true;
            if ($p['module'] !== null) {
                try {
                    $active = (bool) module($p['module'])->enabled();
                } catch (\Throwable $e) {
                    $active = true; // якщо module() недоступний — лишаємо доступним, клієнт вимкне за потреби
                }
            }

            $row = [
                'name' => $p['name'],
                'code' => $p['code'],
                'is_active' => $active,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            if ($hasSort) {
                $row['sort_order'] = $p['sort'];
            }
            if ($hasDesc) {
                $row['description'] = $p['desc'];
            }

            DB::table('shipping_providers')->insert($row);
        }
    }

    public function down(): void
    {
        // Не видаляємо — провайдери можуть мати повʼязані замовлення/методи.
    }
};
