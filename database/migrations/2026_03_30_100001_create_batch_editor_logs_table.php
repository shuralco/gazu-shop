<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batch_editor_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action_type', 50);
            $table->string('description');
            $table->json('filter_params')->nullable();
            $table->json('affected_ids');
            $table->json('changes_data')->nullable();
            $table->integer('affected_count')->default(0);
            $table->boolean('rolled_back')->default(false);
            $table->timestamp('created_at');
            $table->index('user_id');
            $table->index('action_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_editor_logs');
    }
};
