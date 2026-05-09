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
        Schema::table('products', function (Blueprint $table) {
            // Додаємо колонки з безпечними значеннями за замовчуванням
            if (! Schema::hasColumn('products', 'name')) {
                $table->string('name')->nullable()->after('title');
            }
            if (! Schema::hasColumn('products', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('is_new');
            }
            if (! Schema::hasColumn('products', 'quantity')) {
                $table->integer('quantity')->default(999)->after('price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['name', 'is_active', 'quantity']);
        });
    }
};
