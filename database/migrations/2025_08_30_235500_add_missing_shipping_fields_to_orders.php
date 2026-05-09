<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Check if columns don't exist before adding
            if (! Schema::hasColumn('orders', 'shipping_city')) {
                $table->string('shipping_city')->nullable()->after('shipping_data');
            }
            if (! Schema::hasColumn('orders', 'shipping_city_ref')) {
                $table->string('shipping_city_ref')->nullable()->after('shipping_city');
            }
            if (! Schema::hasColumn('orders', 'shipping_warehouse')) {
                $table->text('shipping_warehouse')->nullable()->after('shipping_city_ref');
            }
            if (! Schema::hasColumn('orders', 'shipping_warehouse_ref')) {
                $table->string('shipping_warehouse_ref')->nullable()->after('shipping_warehouse');
            }
            if (! Schema::hasColumn('orders', 'shipping_address')) {
                $table->text('shipping_address')->nullable()->after('shipping_warehouse_ref');
            }
            if (! Schema::hasColumn('orders', 'shipping_post_office')) {
                $table->text('shipping_post_office')->nullable()->after('shipping_address');
            }
            if (! Schema::hasColumn('orders', 'shipping_post_office_ref')) {
                $table->string('shipping_post_office_ref')->nullable()->after('shipping_post_office');
            }
            if (! Schema::hasColumn('orders', 'payment_transaction_id')) {
                $table->string('payment_transaction_id')->nullable()->after('payment_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'shipping_city',
                'shipping_city_ref',
                'shipping_warehouse',
                'shipping_warehouse_ref',
                'shipping_address',
                'shipping_post_office',
                'shipping_post_office_ref',
                'payment_transaction_id',
            ]);
        });
    }
};
