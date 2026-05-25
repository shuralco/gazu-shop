<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-warehouse inventory pivot. quantity = physically present units;
 * reserved_quantity = units locked by checkout reservations (Phase 2).
 * Available = quantity - reserved_quantity (computed via accessor).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('merchant_warehouses')->cascadeOnDelete();

            $table->integer('quantity')->default(0);
            $table->integer('reserved_quantity')->default(0);

            $table->unsignedInteger('reorder_point')->nullable();
            $table->unsignedInteger('reorder_quantity')->nullable();

            $table->timestamp('last_counted_at')->nullable();

            $table->timestamps();

            $table->unique(['product_id', 'warehouse_id'], 'inventory_product_warehouse_unique');
            $table->index('warehouse_id');
            $table->index(['warehouse_id', 'quantity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory');
    }
};
