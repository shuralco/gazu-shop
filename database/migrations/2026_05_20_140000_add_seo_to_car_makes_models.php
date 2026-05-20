<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SEO fields for car taxonomy so /zapchastyny/{make} and /{make}/{model} pages
 * get editable meta — same level as categories/brands.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['car_makes', 'car_models'] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            Schema::table($table, function (Blueprint $t) use ($table) {
                if (! Schema::hasColumn($table, 'meta_title')) {
                    $t->string('meta_title')->nullable();
                }
                if (! Schema::hasColumn($table, 'meta_description')) {
                    $t->string('meta_description', 320)->nullable();
                }
                if (! Schema::hasColumn($table, 'description')) {
                    $t->text('description')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        foreach (['car_makes', 'car_models'] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            Schema::table($table, function (Blueprint $t) use ($table) {
                foreach (['meta_title', 'meta_description', 'description'] as $col) {
                    if (Schema::hasColumn($table, $col)) {
                        $t->dropColumn($col);
                    }
                }
            });
        }
    }
};
