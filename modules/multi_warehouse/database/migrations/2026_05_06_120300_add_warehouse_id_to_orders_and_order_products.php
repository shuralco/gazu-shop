<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('warehouse_id')
                ->nullable()
                ->after('shipping_post_office_ref')
                ->constrained('merchant_warehouses')
                ->nullOnDelete();

            $table->enum('fulfillment_status', [
                'pending', 'reserved', 'picking', 'packed', 'shipped', 'cancelled',
            ])->default('pending')->after('status');

            $table->index('warehouse_id');
            $table->index('fulfillment_status');
        });

        Schema::table('order_products', function (Blueprint $table) {
            $table->foreignId('warehouse_id')
                ->nullable()
                ->after('product_id')
                ->constrained('merchant_warehouses')
                ->nullOnDelete();

            $table->index('warehouse_id');
        });
    }

    public function down(): void
    {
        Schema::table('order_products', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropIndex(['warehouse_id']);
            $table->dropColumn('warehouse_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropIndex(['warehouse_id']);
            $table->dropIndex(['fulfillment_status']);
            $table->dropColumn(['warehouse_id', 'fulfillment_status']);
        });
    }
};
