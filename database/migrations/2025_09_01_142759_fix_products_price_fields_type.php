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
        Schema::table('products', function (Blueprint $table) {
            // Change price and old_price from INTEGER to DECIMAL(10,2)
            // This preserves decimal precision and prevents data loss
            $table->decimal('price', 10, 2)->change();
            $table->decimal('old_price', 10, 2)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Revert back to INTEGER (this will cause data loss!)
            $table->integer('price')->change();
            $table->integer('old_price')->default(0)->change();
        });
    }
};
