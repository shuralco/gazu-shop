<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seo_meta', function (Blueprint $table) {
            if (! Schema::hasColumn('seo_meta', 'sitemap_include')) {
                $table->boolean('sitemap_include')->default(true)->after('is_active')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('seo_meta', function (Blueprint $table) {
            if (Schema::hasColumn('seo_meta', 'sitemap_include')) {
                $table->dropIndex(['sitemap_include']);
                $table->dropColumn('sitemap_include');
            }
        });
    }
};
