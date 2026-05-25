<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-installation module state.
     *
     * Acts as the persistent layer for the modular plugin system. Resolution
     * priority used by App\Support\ModuleManager:
     *   1. modules.{key}.enabled (this DB row)
     *   2. MODULE_{KEY} env var
     *   3. config('modules.{key}.enabled') fallback (default from manifest)
     *
     * `settings` stores per-module configuration overrides (e.g. loyalty_default_rate).
     * `installed_version` tracks which migration set has been applied so we know
     * when a module needs running newer migrations on engine upgrade.
     */
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->boolean('enabled')->default(true);
            $table->json('settings')->nullable();
            $table->string('installed_version')->nullable();
            $table->timestamp('enabled_at')->nullable();
            $table->timestamp('disabled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
