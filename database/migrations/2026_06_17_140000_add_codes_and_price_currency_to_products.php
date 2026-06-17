<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Крос-коди + валюта ціни товару.
 *   - cross_code     — основний крос/OEM-код (необовʼязковий)
 *   - extra_codes    — JSON-масив додаткових кодів (артикули аналогів), необовʼязковий
 *   - price_currency — валюта, у якій введена ціна (UAH/USD/EUR/CNY); на сайті
 *                      завжди показуємо в грн за курсом (ChinesePriceCalculator::fxRate)
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'cross_code')) {
                $table->string('cross_code', 120)->nullable()->after('barcode');
            }
            if (! Schema::hasColumn('products', 'extra_codes')) {
                $table->json('extra_codes')->nullable()->after('cross_code');
            }
            if (! Schema::hasColumn('products', 'price_currency')) {
                $table->string('price_currency', 3)->default('UAH')->after('price');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }
        Schema::table('products', function (Blueprint $table) {
            foreach (['cross_code', 'extra_codes', 'price_currency'] as $col) {
                if (Schema::hasColumn('products', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
