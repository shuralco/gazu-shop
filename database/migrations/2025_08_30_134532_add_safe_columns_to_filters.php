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
        Schema::table('filters', function (Blueprint $table) {
            // Додаємо колонки з безпечними значеннями за замовчуванням
            if (! Schema::hasColumn('filters', 'value')) {
                $table->string('value')->nullable()->after('title');
            }
            if (! Schema::hasColumn('filters', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('value');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('filters', function (Blueprint $table) {
            $table->dropColumn(['value', 'is_active']);
        });
    }
};
