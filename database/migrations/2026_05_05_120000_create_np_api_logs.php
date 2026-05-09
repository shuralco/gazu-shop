<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('np_api_logs', function (Blueprint $table) {
            $table->id();
            $table->string('endpoint_model', 100)->index();
            $table->string('endpoint_method', 100)->index();
            $table->boolean('success')->default(false)->index();
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->json('errors')->nullable();
            $table->json('warnings')->nullable();
            $table->string('caller', 200)->nullable()->comment('class:method that triggered the API call');
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('np_api_logs');
    }
};
