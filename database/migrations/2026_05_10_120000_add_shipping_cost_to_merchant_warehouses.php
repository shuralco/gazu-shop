<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 7 — per-warehouse shipping cost.
 *
 * Each warehouse can declare its own base shipping cost and
 * free-shipping threshold. Cart calculator groups order_products by
 * warehouse_id and sums per-warehouse line totals; if a warehouse's
 * subtotal ≥ free_shipping_threshold, shipping is waived for that
 * warehouse. Otherwise base_shipping_cost is added.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('merchant_warehouses', function (Blueprint $table) {
            $table->decimal('shipping_cost', 10, 2)->default(0)->after('delivery_eta');
            $table->decimal('free_shipping_threshold', 10, 2)->nullable()->after('shipping_cost');
        });
    }

    public function down(): void
    {
        Schema::table('merchant_warehouses', function (Blueprint $table) {
            $table->dropColumn(['shipping_cost', 'free_shipping_threshold']);
        });
    }
};
