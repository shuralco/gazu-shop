<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Own warehouses of the shop (NOT to be confused with shipping_warehouses,
 * which is a cache of carrier branches). Each merchant warehouse holds
 * its own NP/UP sender refs so TTN creation pulls origin from here, not
 * from the global DisplaySetting.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('merchant_warehouses', function (Blueprint $table) {
            $table->id();

            // Identity
            $table->string('code')->unique();
            $table->string('name');
            $table->enum('type', ['own', 'drop_ship', 'virtual'])->default('own');

            // Address
            $table->string('country', 2)->default('UA');
            $table->string('region')->nullable();
            $table->string('city')->nullable();
            $table->string('postcode')->nullable();
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Contacts
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->foreignId('manager_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('working_hours')->nullable();

            // Operations
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->boolean('pickup_supported')->default(false);
            $table->unsignedInteger('sort_order')->default(0);

            // Nova Poshta sender (per warehouse)
            $table->string('np_sender_ref')->nullable();
            $table->string('np_sender_city_ref')->nullable();
            $table->string('np_sender_warehouse_ref')->nullable();
            $table->string('np_contact_person_ref')->nullable();
            $table->string('np_sender_phone')->nullable();

            // UkrPoshta sender (per warehouse)
            $table->string('up_sender_uuid')->nullable();
            $table->string('up_sender_address_uuid')->nullable();
            $table->string('up_counterparty_token')->nullable();
            $table->string('up_ecom_bearer')->nullable();

            $table->timestamps();

            $table->index('is_active');
            $table->index(['is_default', 'is_active']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merchant_warehouses');
    }
};
