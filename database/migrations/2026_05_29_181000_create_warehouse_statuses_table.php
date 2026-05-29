<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Кастомні статуси складів — адмін визначає власні (активний, на обслуговуванні,
 * закритий, тимчасовий тощо). merchant_warehouses.status зберігає key.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('warehouse_statuses')) {
            Schema::create('warehouse_statuses', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->string('label');
                $table->string('color', 32)->default('gray');
                $table->string('icon')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_default')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('merchant_warehouses') && ! Schema::hasColumn('merchant_warehouses', 'status')) {
            Schema::table('merchant_warehouses', function (Blueprint $table) {
                $table->string('status')->default('active')->after('is_active');
            });
        }

        $defaults = [
            ['key' => 'active',      'label' => 'Активний',         'color' => 'success', 'icon' => 'heroicon-o-check-circle',       'sort_order' => 1, 'is_default' => true],
            ['key' => 'maintenance', 'label' => 'На обслуговуванні', 'color' => 'warning', 'icon' => 'heroicon-o-wrench-screwdriver', 'sort_order' => 2, 'is_default' => false],
            ['key' => 'limited',     'label' => 'Обмежений',         'color' => 'info',    'icon' => 'heroicon-o-exclamation-triangle','sort_order' => 3, 'is_default' => false],
            ['key' => 'closed',      'label' => 'Закритий',          'color' => 'danger',  'icon' => 'heroicon-o-x-circle',           'sort_order' => 4, 'is_default' => false],
        ];
        foreach ($defaults as $row) {
            DB::table('warehouse_statuses')->updateOrInsert(
                ['key' => $row['key']],
                $row + ['is_active' => true, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('merchant_warehouses') && Schema::hasColumn('merchant_warehouses', 'status')) {
            Schema::table('merchant_warehouses', fn (Blueprint $t) => $t->dropColumn('status'));
        }
        Schema::dropIfExists('warehouse_statuses');
    }
};
