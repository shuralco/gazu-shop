<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Мультивалюта для цін складу (inventory) та гуртових цін (product_group_prices).
 * Валюта береться з довідника /admin/currencies; на сайті ціна показується в
 * базовій (грн) за курсом (Currency::toBase).
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['inventory', 'product_group_prices'] as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'price_currency')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->string('price_currency', 3)->default('UAH')->after('price');
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['inventory', 'product_group_prices'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'price_currency')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('price_currency');
                });
            }
        }
    }
};
