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
        // Fix categories table - make parent_id nullable foreign key
        Schema::table('categories', function (Blueprint $table) {
            // Drop existing parent_id column and recreate as proper foreign key
            $table->dropColumn('parent_id');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->constrained('categories')->after('slug');
        });

        // Fix order_products table - add proper foreign key for product_id
        Schema::table('order_products', function (Blueprint $table) {
            // Change price to match products table type
            $table->unsignedBigInteger('price')->change();

            // Add foreign key constraint for product_id
            $table->foreign('product_id')->references('id')->on('products');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->default(0)->after('slug');
        });

        Schema::table('order_products', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->unsignedInteger('price')->change();
        });
    }
};
