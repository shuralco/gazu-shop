<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('np_scan_sheets', function (Blueprint $table) {
            $table->id();
            $table->string('ref', 36)->unique()->comment('NP ScanSheet Ref');
            $table->string('number', 30)->nullable()->comment('NP ScanSheet Number');
            $table->date('date');
            $table->integer('shipments_count')->default(0);
            $table->decimal('total_weight', 10, 3)->default(0);
            $table->decimal('total_cost', 12, 2)->default(0);
            $table->string('status', 30)->default('draft');
            $table->timestamp('printed_at')->nullable();
            $table->json('print_meta')->nullable();
            $table->timestamps();

            $table->index('date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('np_scan_sheets');
    }
};
