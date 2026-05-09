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
            // SKU and inventory
            $table->string('sku', 100)->nullable()->unique()->after('slug');
            $table->string('barcode', 100)->nullable()->after('sku');

            // SEO fields
            $table->string('meta_title')->nullable()->after('content');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->text('meta_keywords')->nullable()->after('meta_description');

            // Additional info
            $table->string('brand')->nullable()->after('category_id');
            $table->string('manufacturer')->nullable()->after('brand');
            $table->decimal('weight', 10, 3)->nullable()->after('price');
            $table->string('dimensions')->nullable()->after('weight');

            // Ratings
            $table->decimal('rating', 3, 2)->default(0)->after('is_new');
            $table->integer('reviews_count')->default(0)->after('rating');

            // Stock management
            $table->enum('stock_status', ['in_stock', 'out_of_stock', 'preorder'])->default('in_stock')->after('quantity');
            $table->integer('min_quantity')->default(1)->after('stock_status');

            // Indexes
            $table->index('sku');
            $table->index('brand');
            $table->index('stock_status');
            $table->index('rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['sku']);
            $table->dropIndex(['brand']);
            $table->dropIndex(['stock_status']);
            $table->dropIndex(['rating']);

            $table->dropColumn([
                'sku',
                'barcode',
                'meta_title',
                'meta_description',
                'meta_keywords',
                'brand',
                'manufacturer',
                'weight',
                'dimensions',
                'rating',
                'reviews_count',
                'stock_status',
                'min_quantity',
            ]);
        });
    }
};
