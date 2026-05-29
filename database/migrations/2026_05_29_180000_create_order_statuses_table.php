<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Кастомні статуси замовлень — адмін може додавати/редагувати власні.
 * orders.status (varchar) зберігає `key` цього довідника.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('order_statuses')) {
            Schema::create('order_statuses', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();         // зберігається в orders.status
                $table->string('label');                  // людська назва (uk)
                $table->string('color', 32)->default('gray'); // Filament badge color
                $table->string('icon')->nullable();        // heroicon
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_default')->default(false); // для нових замовлень
                $table->boolean('is_final')->default(false);   // термінальний (виконано/скасовано)
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Seed стандартних статусів (узгоджено з checkout, що пише 'pending').
        $defaults = [
            ['key' => 'pending',    'label' => 'Очікує',       'color' => 'warning', 'icon' => 'heroicon-o-clock',          'sort_order' => 1, 'is_default' => true,  'is_final' => false],
            ['key' => 'processing', 'label' => 'Обробляється',  'color' => 'info',    'icon' => 'heroicon-o-arrow-path',     'sort_order' => 2, 'is_default' => false, 'is_final' => false],
            ['key' => 'shipped',    'label' => 'Відправлено',   'color' => 'primary', 'icon' => 'heroicon-o-paper-airplane', 'sort_order' => 3, 'is_default' => false, 'is_final' => false],
            ['key' => 'completed',  'label' => 'Виконано',      'color' => 'success', 'icon' => 'heroicon-o-check-circle',   'sort_order' => 4, 'is_default' => false, 'is_final' => true],
            ['key' => 'cancelled',  'label' => 'Скасовано',     'color' => 'danger',  'icon' => 'heroicon-o-x-circle',       'sort_order' => 5, 'is_default' => false, 'is_final' => true],
        ];
        foreach ($defaults as $row) {
            DB::table('order_statuses')->updateOrInsert(
                ['key' => $row['key']],
                $row + ['is_active' => true, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_statuses');
    }
};
