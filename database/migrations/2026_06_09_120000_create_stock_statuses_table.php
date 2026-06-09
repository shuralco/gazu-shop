<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Довідник статусів наявності товару — адмін може додавати/редагувати власні
 * (в наявності / під замовлення / передзамовлення / немає тощо).
 * products.stock_status (varchar) зберігає `key` цього довідника.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('stock_statuses')) {
            Schema::create('stock_statuses', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();              // зберігається в products.stock_status
                $table->string('label');                       // людська назва (uk)
                $table->string('color', 32)->default('gray');  // Filament/badge color
                $table->string('icon')->nullable();            // heroicon
                // schema.org availability — для Product rich-snippet на вітрині
                $table->string('availability', 32)->default('InStock'); // InStock|OutOfStock|PreOrder|BackOrder
                $table->boolean('is_orderable')->default(true); // чи можна додавати в кошик / купувати
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_default')->default(false);  // для нових товарів
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Seed стандартних статусів — узгоджено зі старими enum-значеннями
        // ('in_stock','out_of_stock','preorder'), плюс «під замовлення».
        $defaults = [
            ['key' => 'in_stock',     'label' => 'В наявності',       'color' => 'success', 'icon' => 'heroicon-o-check-circle',        'availability' => 'InStock',   'is_orderable' => true,  'sort_order' => 1, 'is_default' => true],
            ['key' => 'under_order',  'label' => 'Під замовлення',     'color' => 'warning', 'icon' => 'heroicon-o-clock',               'availability' => 'BackOrder', 'is_orderable' => true,  'sort_order' => 2, 'is_default' => false],
            ['key' => 'preorder',     'label' => 'Передзамовлення',    'color' => 'info',    'icon' => 'heroicon-o-calendar-days',       'availability' => 'PreOrder',  'is_orderable' => true,  'sort_order' => 3, 'is_default' => false],
            ['key' => 'out_of_stock', 'label' => 'Немає в наявності',  'color' => 'danger',  'icon' => 'heroicon-o-x-circle',            'availability' => 'OutOfStock','is_orderable' => false, 'sort_order' => 4, 'is_default' => false],
        ];
        foreach ($defaults as $row) {
            DB::table('stock_statuses')->updateOrInsert(
                ['key' => $row['key']],
                $row + ['is_active' => true, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_statuses');
    }
};
