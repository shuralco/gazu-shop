<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Виконати міграції для створення таблиць системи доставки.
     */
    public function up(): void
    {
        // Таблиця провайдерів доставки
        Schema::create('shipping_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 50)->unique();
            $table->string('api_endpoint')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('configuration')->nullable();
            $table->timestamps();

            $table->index(['is_active']);
            $table->index(['code']);
        });

        // Таблиця методів доставки
        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('provider_id')->constrained('shipping_providers')->onDelete('cascade');
            $table->string('method_code', 100);
            $table->text('description')->nullable();
            $table->decimal('base_cost', 10, 2)->default(0);
            $table->decimal('per_kg_cost', 8, 2)->default(0);
            $table->integer('estimated_days')->nullable();
            $table->decimal('max_weight', 8, 2)->nullable();
            $table->json('additional_config')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active']);
            $table->index(['provider_id', 'method_code']);
        });

        // Таблиця зон доставки
        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('country_code', 2)->default('UA');
            $table->json('regions')->nullable();
            $table->json('postal_codes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active']);
            $table->index(['country_code']);
        });

        // Таблиця тарифів доставки
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('method_id')->constrained('shipping_methods')->onDelete('cascade');
            $table->foreignId('zone_id')->constrained('shipping_zones')->onDelete('cascade');
            $table->decimal('weight_min', 8, 2)->default(0);
            $table->decimal('weight_max', 8, 2)->nullable();
            $table->decimal('base_cost', 10, 2);
            $table->decimal('per_kg_cost', 8, 2)->default(0);
            $table->integer('delivery_days')->nullable();
            $table->timestamps();

            $table->index(['method_id', 'zone_id']);
            $table->index(['weight_min', 'weight_max']);
        });

        // Таблиця відправлень
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('method_id')->constrained('shipping_methods');
            $table->string('tracking_number')->unique();
            $table->string('provider_reference')->nullable();
            $table->enum('status', [
                'pending',
                'created',
                'picked_up',
                'in_transit',
                'out_for_delivery',
                'delivered',
                'failed',
                'returned',
            ])->default('pending');
            $table->json('sender_address');
            $table->json('recipient_address');
            $table->decimal('weight', 8, 2);
            $table->json('dimensions')->nullable();
            $table->decimal('declared_value', 10, 2)->nullable();
            $table->decimal('shipping_cost', 10, 2);
            $table->json('additional_data')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index(['status']);
            $table->index(['tracking_number']);
            $table->index(['order_id']);
        });

        // Таблиця оновлень відстеження
        Schema::create('tracking_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->onDelete('cascade');
            $table->string('status');
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->timestamp('event_time');
            $table->json('raw_data')->nullable();
            $table->timestamps();

            $table->index(['shipment_id', 'event_time']);
            $table->index(['status']);
        });

        // Таблиця адрес для доставки (кеш для швидкого доступу)
        Schema::create('shipping_addresses', function (Blueprint $table) {
            $table->id();
            $table->string('provider_code', 50);
            $table->string('type'); // city, warehouse, street
            $table->string('provider_ref')->nullable();
            $table->string('name');
            $table->string('name_ua')->nullable();
            $table->string('parent_ref')->nullable();
            $table->json('additional_data')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['provider_code', 'type']);
            $table->index(['provider_ref']);
            $table->index(['name']);
        });
    }

    /**
     * Відмінити міграції.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking_updates');
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('shipping_addresses');
        Schema::dropIfExists('shipping_rates');
        Schema::dropIfExists('shipping_zones');
        Schema::dropIfExists('shipping_methods');
        Schema::dropIfExists('shipping_providers');
    }
};
