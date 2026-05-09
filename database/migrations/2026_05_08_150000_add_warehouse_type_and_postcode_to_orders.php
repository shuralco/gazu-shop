<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'shipping_warehouse_type')) {
                $table->string('shipping_warehouse_type', 20)->nullable()->after('shipping_warehouse_ref');
            }
            if (! Schema::hasColumn('orders', 'shipping_postcode')) {
                $table->string('shipping_postcode', 10)->nullable()->after('shipping_address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'shipping_warehouse_type')) {
                $table->dropColumn('shipping_warehouse_type');
            }
            if (Schema::hasColumn('orders', 'shipping_postcode')) {
                $table->dropColumn('shipping_postcode');
            }
        });
    }
};
