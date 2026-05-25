<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('up_scan_sheets', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 50)->nullable()->unique()->comment('UkrPoshta registry UUID');
            $table->string('name', 200);
            $table->unsignedInteger('shipments_count')->default(0);
            $table->json('shipment_uuids')->nullable();
            $table->timestamp('printed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('up_scan_sheets');
    }
};
