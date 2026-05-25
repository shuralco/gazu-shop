<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('np_webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('ttn', 30)->nullable()->index();
            $table->string('status_code', 10)->nullable();
            $table->string('status', 200)->nullable();
            $table->json('payload');
            $table->boolean('signature_valid')->default(true);
            $table->boolean('processed')->default(false);
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index('processed');
            $table->index('signature_valid');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('np_webhook_logs');
    }
};
