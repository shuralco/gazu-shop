<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add sort_order to filter_groups if not exists
        if (! Schema::hasColumn('filter_groups', 'sort_order')) {
            Schema::table('filter_groups', function (Blueprint $table) {
                $table->integer('sort_order')->default(0)->after('title');
            });
        }

        // Add sort_order to filters if not exists
        if (! Schema::hasColumn('filters', 'sort_order')) {
            Schema::table('filters', function (Blueprint $table) {
                $table->integer('sort_order')->default(0)->after('title');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('filter_groups', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });

        Schema::table('filters', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
