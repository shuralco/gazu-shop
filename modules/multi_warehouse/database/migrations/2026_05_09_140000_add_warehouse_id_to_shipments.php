<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 5 — split TTN per warehouse.
 *
 * np_shipments.warehouse_id and up_shipments.warehouse_id pin a shipment
 * to the merchant warehouse it ships from. NovaPoshtaTtnCreator and the
 * UkrPoshta service prefer this over orders.warehouse_id so a single
 * order can produce N shipments, each with its own sender refs.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('np_shipments') && ! Schema::hasColumn('np_shipments', 'warehouse_id')) {
            Schema::table('np_shipments', function (Blueprint $table) {
                $table->foreignId('warehouse_id')
                    ->nullable()
                    ->after('order_id')
                    ->constrained('merchant_warehouses')
                    ->nullOnDelete();
                $table->index('warehouse_id');
            });
        }

        if (Schema::hasTable('up_shipments') && ! Schema::hasColumn('up_shipments', 'warehouse_id')) {
            Schema::table('up_shipments', function (Blueprint $table) {
                $table->foreignId('warehouse_id')
                    ->nullable()
                    ->after('order_id')
                    ->constrained('merchant_warehouses')
                    ->nullOnDelete();
                $table->index('warehouse_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('np_shipments') && Schema::hasColumn('np_shipments', 'warehouse_id')) {
            Schema::table('np_shipments', function (Blueprint $table) {
                $table->dropForeign(['warehouse_id']);
                $table->dropIndex(['warehouse_id']);
                $table->dropColumn('warehouse_id');
            });
        }
        if (Schema::hasTable('up_shipments') && Schema::hasColumn('up_shipments', 'warehouse_id')) {
            Schema::table('up_shipments', function (Blueprint $table) {
                $table->dropForeign(['warehouse_id']);
                $table->dropIndex(['warehouse_id']);
                $table->dropColumn('warehouse_id');
            });
        }
    }
};
