<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('np_api_logs') && ! Schema::hasTable('shipping_api_logs')) {
            Schema::rename('np_api_logs', 'shipping_api_logs');
        }

        if (Schema::hasTable('shipping_api_logs') && ! Schema::hasColumn('shipping_api_logs', 'provider')) {
            Schema::table('shipping_api_logs', function (Blueprint $table) {
                $table->string('provider', 30)->default('novaposhta')->after('id');
                $table->index('provider');
            });

            DB::table('shipping_api_logs')->whereNull('provider')->update(['provider' => 'novaposhta']);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('shipping_api_logs') && Schema::hasColumn('shipping_api_logs', 'provider')) {
            Schema::table('shipping_api_logs', function (Blueprint $table) {
                $table->dropIndex(['provider']);
                $table->dropColumn('provider');
            });
        }

        if (Schema::hasTable('shipping_api_logs') && ! Schema::hasTable('np_api_logs')) {
            Schema::rename('shipping_api_logs', 'np_api_logs');
        }
    }
};
