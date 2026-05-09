<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Области (регіони)
        Schema::create('np_areas', function (Blueprint $table) {
            $table->id();
            $table->string('ref', 36)->unique();
            $table->string('description');
            $table->timestamps();
        });

        // Кеш міст
        Schema::create('np_cities', function (Blueprint $table) {
            $table->id();
            $table->string('ref', 36)->unique();
            $table->string('description');
            $table->string('description_ru')->nullable();
            $table->string('area_ref', 36)->nullable()->index();
            $table->string('area_description')->nullable();
            $table->string('settlement_type')->nullable();
            $table->boolean('is_branch')->default(false);
            $table->index('description');
            $table->timestamps();
        });

        // Кеш відділень/поштоматів
        Schema::create('np_warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('ref', 36)->unique();
            $table->string('site_key', 10)->nullable()->index();
            $table->string('description');
            $table->string('short_address')->nullable();
            $table->string('city_ref', 36)->index();
            $table->string('city_description')->nullable();
            $table->string('type_ref', 36)->nullable();
            $table->string('type_description')->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->integer('total_max_weight')->default(30);
            $table->string('max_dimensions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->index(['city_ref', 'type_ref']);
            $table->timestamps();
        });

        // Відправлення (ТТН)
        Schema::create('np_shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('ref', 36)->nullable()->index();
            $table->string('ttn', 20)->nullable()->index();
            $table->string('status')->default('new');
            $table->string('np_status')->nullable();
            $table->string('np_status_code')->nullable();

            // Відправник
            $table->string('sender_ref', 36)->nullable();
            $table->string('sender_city_ref', 36)->nullable();
            $table->string('sender_warehouse_ref', 36)->nullable();
            $table->string('sender_address')->nullable();

            // Отримувач
            $table->string('recipient_name')->nullable();
            $table->string('recipient_phone', 20)->nullable();
            $table->string('recipient_city_ref', 36)->nullable();
            $table->string('recipient_city_name')->nullable();
            $table->string('recipient_warehouse_ref', 36)->nullable();
            $table->string('recipient_warehouse_name')->nullable();
            $table->string('recipient_address')->nullable();

            // Параметри відправлення
            $table->string('service_type')->default('WarehouseWarehouse');
            $table->string('cargo_type')->default('Parcel');
            $table->decimal('weight', 8, 3)->default(0.5);
            $table->decimal('volume', 8, 4)->nullable();
            $table->integer('seats_amount')->default(1);
            $table->decimal('cost', 10, 2)->default(0);
            $table->decimal('shipping_cost', 10, 2)->default(0);

            // Оплата
            $table->string('payer_type')->default('Recipient');
            $table->string('payment_method')->default('Cash');
            $table->decimal('cod_amount', 10, 2)->default(0);
            $table->string('backward_delivery_type')->nullable();

            // Додатково
            $table->text('description')->nullable();
            $table->string('estimated_delivery_date')->nullable();
            $table->json('tracking_history')->nullable();
            $table->timestamp('last_tracked_at')->nullable();
            $table->text('print_url')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('np_shipments');
        Schema::dropIfExists('np_warehouses');
        Schema::dropIfExists('np_cities');
        Schema::dropIfExists('np_areas');
    }
};
