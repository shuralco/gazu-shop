<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Vehicle reference data for the car-selector widget (марка → модель → двигун).
     * Plus a product_compatibility pivot so we can answer "does this part fit
     * my car?" at the engine-code level.
     */
    public function up(): void
    {
        Schema::create('car_makes', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 60)->unique();
            $table->string('name', 80);
            $table->string('logo_path')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('car_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('make_id')->constrained('car_makes')->cascadeOnDelete();
            $table->string('slug', 80);
            $table->string('name', 120);
            $table->string('body_type', 40)->nullable(); // sedan/suv/crossover/hatchback
            $table->string('years_range', 20)->nullable(); // "2018-2024"
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['make_id', 'slug']);
            $table->index(['make_id', 'is_active', 'sort_order']);
        });

        Schema::create('car_engines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('model_id')->constrained('car_models')->cascadeOnDelete();
            $table->string('code', 40);                 // "1.5T", "2.0 TDI", "GW4G15B"
            $table->string('label', 60)->nullable();    // human-friendly name shown in dropdown
            $table->string('fuel_type', 20)->nullable(); // petrol/diesel/hybrid/electric
            $table->decimal('displacement', 4, 1)->nullable(); // 1.5, 2.0
            $table->unsignedInteger('hp')->nullable();
            $table->string('years_range', 20)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['model_id', 'code']);
            $table->index(['model_id', 'is_active', 'sort_order']);
        });

        // Product ↔ engine pivot. Many-to-many: a part fits N engines, an
        // engine has N compatible parts. Used by 4D compat-check.
        Schema::create('product_compatibility', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('engine_id')->constrained('car_engines')->cascadeOnDelete();
            $table->string('note', 200)->nullable(); // optional: "потрібен адаптер" / "до 2020"
            $table->timestamps();

            $table->unique(['product_id', 'engine_id']);
            $table->index('engine_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_compatibility');
        Schema::dropIfExists('car_engines');
        Schema::dropIfExists('car_models');
        Schema::dropIfExists('car_makes');
    }
};
