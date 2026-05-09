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
            $table->string('phone')->nullable()->after('email');
            $table->decimal('shipping_cost', 8, 2)->default(0)->after('total');
            $table->string('shipping_provider')->nullable()->after('shipping_cost');
            $table->string('shipping_method')->nullable()->after('shipping_provider');
            $table->json('shipping_data')->nullable()->after('shipping_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['phone', 'shipping_cost', 'shipping_provider', 'shipping_method', 'shipping_data']);
        });
    }
};
