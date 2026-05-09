<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('np_warehouses', function (Blueprint $table) {
            if (! Schema::hasColumn('np_warehouses', 'number')) {
                $table->integer('number')->nullable()->after('description');
            }
            if (! Schema::hasColumn('np_warehouses', 'phone')) {
                $table->string('phone', 50)->nullable()->after('short_address');
            }
            if (! Schema::hasColumn('np_warehouses', 'place_max_weight')) {
                $table->integer('place_max_weight')->default(0)->after('total_max_weight');
            }
            if (! Schema::hasColumn('np_warehouses', 'sending_max_length')) {
                $table->integer('sending_max_length')->nullable()->after('max_dimensions');
                $table->integer('sending_max_width')->nullable();
                $table->integer('sending_max_height')->nullable();
                $table->integer('receiving_max_length')->nullable();
                $table->integer('receiving_max_width')->nullable();
                $table->integer('receiving_max_height')->nullable();
            }
            if (! Schema::hasColumn('np_warehouses', 'post_finance')) {
                $table->boolean('post_finance')->default(false);
                $table->boolean('bicycle_parking')->default(false);
                $table->boolean('payment_access')->default(false);
                $table->boolean('pos_terminal')->default(false);
                $table->boolean('international_shipping')->default(false);
                $table->tinyInteger('self_service_count')->default(0);
            }
            if (! Schema::hasColumn('np_warehouses', 'reception_schedule')) {
                $table->json('reception_schedule')->nullable();
                $table->json('delivery_schedule')->nullable();
                $table->json('schedule')->nullable();
            }
            if (! Schema::hasColumn('np_warehouses', 'warehouse_status')) {
                $table->string('warehouse_status', 30)->nullable();
                $table->date('warehouse_status_date')->nullable();
                $table->string('category_of_warehouse', 50)->nullable();
                $table->string('district_code', 30)->nullable();
                $table->string('region_city', 100)->nullable();
            }
        });

        Schema::table('np_cities', function (Blueprint $table) {
            if (! Schema::hasColumn('np_cities', 'city_id')) {
                $table->integer('city_id')->nullable()->after('id');
            }
            if (! Schema::hasColumn('np_cities', 'delivery_monday')) {
                $table->boolean('delivery_monday')->default(true);
                $table->boolean('delivery_tuesday')->default(true);
                $table->boolean('delivery_wednesday')->default(true);
                $table->boolean('delivery_thursday')->default(true);
                $table->boolean('delivery_friday')->default(true);
                $table->boolean('delivery_saturday')->default(true);
                $table->boolean('delivery_sunday')->default(false);
            }
            if (! Schema::hasColumn('np_cities', 'special_cash_check')) {
                $table->boolean('special_cash_check')->default(false);
                $table->string('settlement_type_description', 50)->nullable();
            }
            if (! Schema::hasColumn('np_cities', 'last_synced_at')) {
                $table->timestamp('last_synced_at')->nullable();
            }
        });

        Schema::table('np_areas', function (Blueprint $table) {
            if (! Schema::hasColumn('np_areas', 'areas_center_ref')) {
                $table->string('areas_center_ref', 36)->nullable()->after('description');
            }
            if (! Schema::hasColumn('np_areas', 'last_synced_at')) {
                $table->timestamp('last_synced_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('np_warehouses', function (Blueprint $table) {
            $table->dropColumn([
                'number', 'phone', 'place_max_weight',
                'sending_max_length', 'sending_max_width', 'sending_max_height',
                'receiving_max_length', 'receiving_max_width', 'receiving_max_height',
                'post_finance', 'bicycle_parking', 'payment_access', 'pos_terminal',
                'international_shipping', 'self_service_count',
                'reception_schedule', 'delivery_schedule', 'schedule',
                'warehouse_status', 'warehouse_status_date',
                'category_of_warehouse', 'district_code', 'region_city',
            ]);
        });

        Schema::table('np_cities', function (Blueprint $table) {
            $table->dropColumn([
                'city_id',
                'delivery_monday', 'delivery_tuesday', 'delivery_wednesday',
                'delivery_thursday', 'delivery_friday', 'delivery_saturday', 'delivery_sunday',
                'special_cash_check', 'settlement_type_description',
                'last_synced_at',
            ]);
        });

        Schema::table('np_areas', function (Blueprint $table) {
            $table->dropColumn(['areas_center_ref', 'last_synced_at']);
        });
    }
};
