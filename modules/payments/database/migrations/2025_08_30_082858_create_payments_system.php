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
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('order_id');
            $table->string('gateway', 50); // liqpay, wayforpay, monobank
            $table->string('external_id')->nullable();
            $table->enum('status', ['pending', 'processing', 'success', 'failed', 'reversed'])->default('pending');
            $table->decimal('amount', 10, 2);
            $table->char('currency', 3)->default('UAH');
            $table->decimal('fee_amount', 8, 2)->default(0);
            $table->json('metadata')->nullable();
            $table->timestamp('webhook_received_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->index('order_id');
            $table->index('external_id');
            $table->index('status');
            $table->index('gateway');
        });

        Schema::create('payment_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('payment_id');
            $table->string('action', 100); // webhook, verify, refund
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('payment_id')->references('id')->on('payments')->onDelete('cascade');
            $table->index('payment_id');
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_logs');
        Schema::dropIfExists('payments');
    }
};
