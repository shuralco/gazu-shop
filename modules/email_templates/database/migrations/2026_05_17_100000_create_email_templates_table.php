<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key', 60)->unique()->comment('order.created, callback.received, etc.');
            $table->string('name', 120)->comment('Human-readable label у адмінці');
            $table->string('subject', 200);
            $table->text('body_html')->comment('HTML/Markdown body з {{variables}}');
            $table->string('from_email', 100)->nullable()->comment('override default sender');
            $table->string('from_name', 100)->nullable();
            $table->string('to_kind', 20)->default('customer')->comment('customer | admin | manager');
            $table->json('variables_help')->nullable()->comment('[{key: "name", desc: "Ім\'я клієнта"}]');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
