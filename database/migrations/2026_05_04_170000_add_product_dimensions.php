<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'length')) {
                $table->decimal('length', 8, 2)->nullable()->after('weight')->comment('Length in cm');
                $table->decimal('width', 8, 2)->nullable()->after('length')->comment('Width in cm');
                $table->decimal('height', 8, 2)->nullable()->after('width')->comment('Height in cm');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['length', 'width', 'height']);
        });
    }
};
