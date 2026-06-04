<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Access-rights PRESETS (lightweight custom RBAC).
 *
 * A preset = a named bundle of per-section permissions stored as JSON:
 *   { "<SectionKey>": {"view":bool,"create":bool,"update":bool,"delete":bool}, ... }
 * SectionKey = the Resource/Page class basename (e.g. "ProductResource").
 *
 * Users get one preset via users.access_preset_id. users.is_admin = true is a
 * super-admin and bypasses presets (full access). Purely additive.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('access_presets', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->json('permissions')->nullable();   // SectionKey => {view,create,update,delete}
            $table->boolean('is_system')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        if (! Schema::hasColumn('users', 'access_preset_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('access_preset_id')->nullable()->after('is_admin')
                    ->constrained('access_presets')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'access_preset_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropConstrainedForeignId('access_preset_id');
            });
        }
        Schema::dropIfExists('access_presets');
    }
};
