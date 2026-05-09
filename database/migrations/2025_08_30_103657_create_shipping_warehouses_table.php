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
        Schema::create('shipping_warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->unique(); // ID з API (Ref)
            $table->string('name');
            $table->string('short_address');
            $table->string('type')->default('warehouse'); // warehouse, postomat
            $table->string('city_ref');
            $table->string('city_name');
            $table->string('provider_code')->default('novaposhta');
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->json('schedule')->nullable(); // графік роботи
            $table->json('additional_data')->nullable(); // додаткові дані з API
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['provider_code', 'city_ref']);
            $table->index(['type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_warehouses');
    }
};
