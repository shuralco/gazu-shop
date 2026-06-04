<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-user admin navigation preferences (personal menu customization).
 * Shape: { "hidden": ["ProductResource", ...] }  (section keys = class basenames).
 * Purely cosmetic — hides items from THIS user's sidebar; does not affect access.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'nav_preferences')) {
            Schema::table('users', function (Blueprint $table) {
                $table->json('nav_preferences')->nullable()->after('access_preset_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'nav_preferences')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('nav_preferences');
            });
        }
    }
};
