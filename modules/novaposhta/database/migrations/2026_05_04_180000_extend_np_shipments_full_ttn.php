<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('np_shipments', function (Blueprint $table) {
            // Sender details (in addition to refs)
            if (! Schema::hasColumn('np_shipments', 'sender_contact_ref')) {
                $table->string('sender_contact_ref', 36)->nullable()->after('sender_warehouse_ref');
                $table->string('sender_contact_name', 200)->nullable()->after('sender_contact_ref');
                $table->string('sender_phone', 20)->nullable()->after('sender_contact_name');
            }

            // Recipient extended (juridical persons + courier address)
            if (! Schema::hasColumn('np_shipments', 'recipient_contact_name')) {
                $table->string('recipient_contact_name', 200)->nullable()->after('recipient_phone');
                $table->string('recipient_email', 200)->nullable();
                $table->string('recipient_edrpou', 20)->nullable();
                $table->string('recipient_company_name', 200)->nullable();
                $table->string('recipient_street', 255)->nullable();
                $table->string('recipient_house', 50)->nullable();
                $table->string('recipient_flat', 50)->nullable();
                $table->tinyInteger('recipient_floor')->nullable();
                $table->boolean('recipient_has_elevator')->default(false);
            }

            // Delivery preferences
            if (! Schema::hasColumn('np_shipments', 'preferred_delivery_date')) {
                $table->date('preferred_delivery_date')->nullable();
                $table->string('preferred_delivery_time_from', 5)->nullable(); // 09:00
                $table->string('preferred_delivery_time_to', 5)->nullable();   // 14:00
            }

            // Parcels (multi-place support)
            if (! Schema::hasColumn('np_shipments', 'parcels')) {
                $table->json('parcels')->nullable();
                $table->decimal('volume_weight', 8, 3)->nullable();
            }

            // Payment options
            if (! Schema::hasColumn('np_shipments', 'declared_cost')) {
                $table->decimal('declared_cost', 10, 2)->default(0);
                $table->boolean('payment_control')->default(false);
                $table->decimal('backward_delivery_amount', 10, 2)->default(0);
                $table->string('backward_delivery_payer', 30)->nullable();
            }

            // Shipping options
            if (! Schema::hasColumn('np_shipments', 'avia_delivery')) {
                $table->boolean('avia_delivery')->default(false);
                $table->string('packing_number', 50)->nullable();
                $table->text('additional_information')->nullable();
            }

            // Registry / printing
            if (! Schema::hasColumn('np_shipments', 'registry_ref')) {
                $table->string('registry_ref', 36)->nullable()->index();
                $table->timestamp('printed_at')->nullable();
                $table->date('actual_shipping_date')->nullable();
                $table->date('recipient_date')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('np_shipments', function (Blueprint $table) {
            $table->dropColumn([
                'sender_contact_ref', 'sender_contact_name', 'sender_phone',
                'recipient_contact_name', 'recipient_email', 'recipient_edrpou',
                'recipient_company_name', 'recipient_street', 'recipient_house',
                'recipient_flat', 'recipient_floor', 'recipient_has_elevator',
                'preferred_delivery_date', 'preferred_delivery_time_from', 'preferred_delivery_time_to',
                'parcels', 'volume_weight',
                'declared_cost', 'payment_control', 'backward_delivery_amount', 'backward_delivery_payer',
                'avia_delivery', 'packing_number', 'additional_information',
                'registry_ref', 'printed_at', 'actual_shipping_date', 'recipient_date',
            ]);
        });
    }
};
