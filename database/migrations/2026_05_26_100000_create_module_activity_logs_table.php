<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit trail of module-related actions: enable/disable/install/upgrade/
 * uninstall/settings_change. Tracks who did what + payload diff for rollback.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('module_key')->index();
            $table->string('action', 32)->index();
            $table->json('payload')->nullable();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_activity_logs');
    }
};
