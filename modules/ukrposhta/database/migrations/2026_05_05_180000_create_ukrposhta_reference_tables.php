<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('up_regions', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary(); // REGION_ID
            $table->string('name_ua', 120)->index();
            $table->string('name_en', 120)->nullable();
            $table->timestamps();
        });

        Schema::create('up_cities', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary(); // CITY_ID
            $table->unsignedInteger('region_id')->index();
            $table->unsignedInteger('district_id')->nullable()->index();
            $table->string('name_ua', 200);
            $table->string('name_en', 200)->nullable();
            $table->string('district_ua', 200)->nullable();
            $table->string('city_type_ua', 30)->nullable();
            $table->unsignedInteger('population')->nullable();
            $table->string('postcode', 10)->nullable()->index();
            $table->timestamps();

            $table->index('name_ua');
        });

        Schema::create('up_post_offices', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('po_id')->unique();
            $table->unsignedInteger('city_id')->nullable()->index();
            $table->string('postcode', 10)->index();
            $table->string('city_ua', 200)->nullable();
            $table->string('district_ua', 200)->nullable();
            $table->string('region_ua', 120)->nullable();
            $table->string('type_acronym', 10)->nullable()->index(); // ПВ, ВПЗ, etc.
            $table->string('type_long', 100)->nullable();
            $table->string('address', 300)->nullable();
            $table->string('lock_code', 30)->nullable()->index(); // status: open/closed/etc
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('up_post_offices');
        Schema::dropIfExists('up_cities');
        Schema::dropIfExists('up_regions');
    }
};
