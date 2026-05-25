<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Movement of stock between two own warehouses.
 *
 * Lifecycle:
 *   draft       — being prepared, items can be added/removed
 *   sent        — physically left source warehouse (transfer_out logged,
 *                  source inventory decremented)
 *   received    — arrived at destination (transfer_in logged, dest. inv +)
 *   cancelled   — terminated (no inventory effect if it was still draft;
 *                  if sent, items returned via inverse adjustment)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g. TRF-2026-000001

            $table->foreignId('from_warehouse_id')->constrained('merchant_warehouses')->restrictOnDelete();
            $table->foreignId('to_warehouse_id')->constrained('merchant_warehouses')->restrictOnDelete();

            $table->enum('status', ['draft', 'sent', 'received', 'cancelled'])->default('draft');

            $table->string('tracking_number')->nullable(); // optional NP/UP TTN
            $table->string('carrier')->nullable();

            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('shipped_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('received_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->text('note')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index(['from_warehouse_id', 'status']);
            $table->index(['to_warehouse_id', 'status']);
        });

        Schema::create('inventory_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_id')->constrained('inventory_transfers')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->string('note')->nullable();
            $table->timestamps();

            $table->unique(['transfer_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transfer_items');
        Schema::dropIfExists('inventory_transfers');
    }
};
