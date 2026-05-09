<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Поля для модуля Quick Fill (китайський постачальник):
 *  - cost_price       — закупівельна ціна
 *  - cost_currency    — валюта закупки (CNY, USD, UAH)
 *  - markup_percent   — % націнки → retail = cost * fx * (1 + m/100)
 *  - supplier_url     — посилання 1688/Aliexpress/Taobao
 *  - original_name_cn — оригінальна китайська назва
 *  - condition        — стан (new, used, refurbished)
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'cost_price')) {
                $table->decimal('cost_price', 12, 2)->nullable()->after('price');
            }
            if (! Schema::hasColumn('products', 'cost_currency')) {
                $table->string('cost_currency', 3)->default('CNY')->after('cost_price');
            }
            if (! Schema::hasColumn('products', 'markup_percent')) {
                $table->decimal('markup_percent', 6, 2)->nullable()->after('cost_currency');
            }
            if (! Schema::hasColumn('products', 'supplier_url')) {
                $table->string('supplier_url', 1000)->nullable()->after('markup_percent');
            }
            if (! Schema::hasColumn('products', 'original_name_cn')) {
                $table->string('original_name_cn', 500)->nullable()->after('supplier_url');
            }
            if (! Schema::hasColumn('products', 'condition')) {
                $table->string('condition', 20)->default('new')->after('original_name_cn');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            foreach (['cost_price', 'cost_currency', 'markup_percent', 'supplier_url', 'original_name_cn', 'condition'] as $col) {
                if (Schema::hasColumn('products', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
