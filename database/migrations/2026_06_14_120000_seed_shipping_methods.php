<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Засіваємо методи доставки (shipping_methods) для кожного провайдера —
 * ідемпотентно. Без них «Спосіб доставки» у формі замовлення порожній, тож
 * поля міста/відділення НП не зʼявляються і менеджер НЕ може обрати відділення
 * (провайдерів засіяно раніше, методи лишались порожні на частині середовищ).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('shipping_methods') || ! Schema::hasTable('shipping_providers')) {
            return;
        }

        $now = now();
        $methods = [
            // provider code => list of methods
            'novaposhta' => [
                ['method_code' => 'warehouse', 'name' => 'Нова Пошта — відділення', 'base_cost' => 60, 'estimated_days' => 2, 'sort_order' => 1],
                ['method_code' => 'postomat', 'name' => 'Нова Пошта — поштомат', 'base_cost' => 50, 'estimated_days' => 2, 'sort_order' => 2],
                ['method_code' => 'courier', 'name' => 'Нова Пошта — курʼєр', 'base_cost' => 90, 'estimated_days' => 2, 'sort_order' => 3],
            ],
            'ukrposhta' => [
                ['method_code' => 'branch', 'name' => 'УкрПошта — відділення', 'base_cost' => 40, 'estimated_days' => 4, 'sort_order' => 4],
            ],
            'pickup' => [
                ['method_code' => 'pickup', 'name' => 'Самовивіз (Київ)', 'base_cost' => 0, 'estimated_days' => 0, 'sort_order' => 5],
            ],
        ];

        $hasSort = Schema::hasColumn('shipping_methods', 'sort_order');

        foreach ($methods as $providerCode => $rows) {
            $providerId = DB::table('shipping_providers')->where('code', $providerCode)->value('id');
            if (! $providerId) {
                continue;
            }

            foreach ($rows as $m) {
                $exists = DB::table('shipping_methods')
                    ->where('provider_id', $providerId)
                    ->where('method_code', $m['method_code'])
                    ->exists();
                if ($exists) {
                    continue;
                }

                $row = [
                    'provider_id' => $providerId,
                    'name' => $m['name'],
                    'method_code' => $m['method_code'],
                    'base_cost' => $m['base_cost'],
                    'per_kg_cost' => 0,
                    'estimated_days' => $m['estimated_days'],
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                if ($hasSort) {
                    $row['sort_order'] = $m['sort_order'];
                }

                DB::table('shipping_methods')->insert($row);
            }
        }
    }

    public function down(): void
    {
        // Не видаляємо — можуть бути повʼязані замовлення.
    }
};
