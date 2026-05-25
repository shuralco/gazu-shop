<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Multi-vendor pricing per-warehouse:
 *   inventory.price             — override product price for this (product, warehouse).
 *                                 NULL = use products.price as fallback.
 *   inventory.compare_at_price  — strikethrough price for this row (optional).
 *   merchant_warehouses.delivery_eta — short label like "1 день" / "2-3 дні",
 *                                 shown in product warehouse selector.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            $table->decimal('price', 12, 2)->nullable()->after('reserved_quantity');
            $table->decimal('compare_at_price', 12, 2)->nullable()->after('price');
        });

        Schema::table('merchant_warehouses', function (Blueprint $table) {
            $table->string('delivery_eta', 64)->nullable()->after('working_hours');
        });
    }

    public function down(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            $table->dropColumn(['price', 'compare_at_price']);
        });

        Schema::table('merchant_warehouses', function (Blueprint $table) {
            $table->dropColumn('delivery_eta');
        });
    }
};
