<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Inbound goods from a supplier into a warehouse.
 *
 * Lifecycle:
 *   draft       — being prepared
 *   received    — items physically counted in (income movements logged,
 *                 inventory incremented)
 *   cancelled   — discarded (no inventory effect)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receiving_orders', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g. RCV-2026-000001

            $table->foreignId('warehouse_id')->constrained('merchant_warehouses')->restrictOnDelete();
            $table->string('supplier_name')->nullable();
            $table->string('invoice_number')->nullable();
            $table->date('invoice_date')->nullable();

            $table->enum('status', ['draft', 'received', 'cancelled'])->default('draft');

            $table->timestamp('received_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('received_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->text('note')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index(['warehouse_id', 'status']);
        });

        Schema::create('receiving_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receiving_order_id')->constrained('receiving_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('cost_price', 10, 2)->nullable(); // for analytics
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['receiving_order_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receiving_items');
        Schema::dropIfExists('receiving_orders');
    }
};
