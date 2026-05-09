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
        Schema::table('filter_groups', function (Blueprint $table) {
            // Додаємо колонку з безпечним значенням за замовчуванням
            if (! Schema::hasColumn('filter_groups', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('title');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('filter_groups', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
