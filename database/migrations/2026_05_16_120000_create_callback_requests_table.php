<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('callback_requests', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable();
            $table->string('phone', 32);
            $table->string('source', 32)->default('footer')->comment('footer, product_page, hero, etc.');
            $table->string('status', 20)->default('new')->comment('new, in_progress, done, spam');
            $table->text('notes')->nullable()->comment('manager internal notes');
            $table->string('referrer_url', 500)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('source');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('callback_requests');
    }
};
