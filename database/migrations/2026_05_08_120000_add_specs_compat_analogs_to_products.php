<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Поля для GAZU product page:
 *   specifications  — асоц. масив key=>value (Виробник, Висота, M20×1.5...)
 *   compatibility   — список об'єктів {make, model, years, engine}
 *   analogs         — список об'єктів {brand, oem, price, qty, rating} (free-form, без linked-product вимоги)
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'specifications')) {
                $table->json('specifications')->nullable()->after('content');
            }
            if (! Schema::hasColumn('products', 'compatibility')) {
                $table->json('compatibility')->nullable()->after('specifications');
            }
            if (! Schema::hasColumn('products', 'analogs')) {
                $table->json('analogs')->nullable()->after('compatibility');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            foreach (['specifications', 'compatibility', 'analogs'] as $col) {
                if (Schema::hasColumn('products', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
