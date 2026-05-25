<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('up_shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();

            // TTN — entered manually by admin (no ecom auto-create yet)
            $table->string('ttn', 30)->nullable()->unique();
            $table->string('status', 20)->default('new')->index();
            $table->string('up_status_text', 200)->nullable();
            $table->string('up_status_code', 10)->nullable();

            // Recipient
            $table->string('recipient_name', 200);
            $table->string('recipient_phone', 30);
            $table->string('recipient_email', 100)->nullable();
            $table->unsignedInteger('recipient_city_id')->nullable()->index();
            $table->string('recipient_city_name', 200)->nullable();
            $table->unsignedInteger('recipient_branch_id')->nullable();
            $table->string('recipient_branch_address', 300)->nullable();
            $table->string('recipient_postcode', 10)->nullable();
            $table->string('recipient_street', 200)->nullable();
            $table->string('recipient_building', 30)->nullable();
            $table->string('recipient_apartment', 30)->nullable();

            // Shipment params
            $table->string('service_type', 30)->default('branch'); // branch | courier | express
            $table->decimal('weight', 8, 3)->default(0.5);
            $table->decimal('declared_value', 10, 2)->default(0);
            $table->decimal('cod_amount', 10, 2)->nullable();
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->string('description', 500)->nullable();

            // Status timestamps
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('last_tracked_at')->nullable();

            // Tracking history (JSON: [{date, status, location}])
            $table->json('tracking_history')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('up_shipments');
    }
};
