<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Append-only audit log of all inventory changes.
 *
 * Invariant: SUM(stock_movements.quantity WHERE warehouse_id=W AND product_id=P)
 *            == inventory.quantity for the same (W,P).
 * Sign convention: positive = stock-in (income, transfer_in, release),
 *                  negative = stock-out (ship, transfer_out, reserve uses
 *                  separate reserved_quantity, doesn't change physical qty).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('merchant_warehouses')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();

            $table->enum('type', [
                'income',         // приход від постачальника
                'reserve',        // блокування під замовлення (cart/checkout)
                'release',        // зняття блокування (cart abandoned, TTL expired)
                'ship',           // фактичне відвантаження (TTN created)
                'transfer_out',   // вихід на інший склад
                'transfer_in',    // прихід з іншого складу
                'adjustment',     // інвентаризація (correction)
            ]);

            $table->integer('quantity'); // signed
            $table->integer('reserved_delta')->default(0); // signed change to reserved_quantity

            $table->nullableMorphs('reference'); // Order, InventoryTransfer, ReceivingOrder
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('note')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index(['warehouse_id', 'product_id', 'created_at'], 'sm_wh_prod_time_idx');
            $table->index(['type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
