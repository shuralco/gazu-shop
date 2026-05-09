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
        Schema::table('display_settings', function (Blueprint $table) {
            $table->string('main_mega_menu_promo_image')->nullable();
            $table->text('main_mega_menu_promo_description')->nullable();
            $table->boolean('main_mega_menu_show_promo')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('display_settings', function (Blueprint $table) {
            $table->dropColumn(['main_mega_menu_promo_image', 'main_mega_menu_promo_description', 'main_mega_menu_show_promo']);
        });
    }
};
