<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('display_settings', function (Blueprint $table) {
            $table->json('main_mega_menu_structure')->nullable()->after('value');
            $table->json('horizontal_mega_menu_structure')->nullable()->after('main_mega_menu_structure');
        });
    }

    public function down(): void
    {
        Schema::table('display_settings', function (Blueprint $table) {
            $table->dropColumn(['main_mega_menu_structure', 'horizontal_mega_menu_structure']);
        });
    }
};
