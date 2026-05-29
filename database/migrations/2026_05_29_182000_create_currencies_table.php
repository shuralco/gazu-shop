<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Кастомні валюти — адмін визначає власні (код, символ, курс до базової).
 * CurrencyService читає з цієї таблиці (fallback на config/currencies.php).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('currencies')) {
            Schema::create('currencies', function (Blueprint $table) {
                $table->id();
                $table->string('code', 8)->unique();       // UAH/USD/EUR
                $table->string('name');                      // Гривня
                $table->string('symbol', 8);                 // ₴
                $table->decimal('rate', 16, 6)->default(1);  // курс до базової
                $table->string('position', 8)->default('after'); // before|after
                $table->unsignedTinyInteger('decimals')->default(2);
                $table->boolean('is_base')->default(false);  // базова (rate=1)
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        $defaults = [
            ['code' => 'UAH', 'name' => 'Гривня',    'symbol' => '₴', 'rate' => 1.0,   'position' => 'after',  'decimals' => 0, 'is_base' => true,  'sort_order' => 1],
            ['code' => 'USD', 'name' => 'US Dollar',  'symbol' => '$', 'rate' => 0.024, 'position' => 'before', 'decimals' => 2, 'is_base' => false, 'sort_order' => 2],
            ['code' => 'EUR', 'name' => 'Euro',       'symbol' => '€', 'rate' => 0.022, 'position' => 'before', 'decimals' => 2, 'is_base' => false, 'sort_order' => 3],
        ];
        foreach ($defaults as $row) {
            DB::table('currencies')->updateOrInsert(
                ['code' => $row['code']],
                $row + ['is_active' => true, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
