<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Гараж користувача — список авто, зареєстрованих за акаунтом.
 * Кожен User може мати N авто; одне з них може бути is_primary
 * (за замовч. підставляється у фільтр «Ваш автомобіль»).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_cars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('make', 60);                           // Volkswagen
            $table->string('model', 80);                          // Passat B8
            $table->unsignedSmallInteger('year')->nullable();     // 2018
            $table->string('engine', 80)->nullable();             // 2.0 TDI · CKFC
            $table->string('body_type', 60)->nullable();          // Універсал
            $table->string('vin', 30)->nullable();                // 17-знач, але зберігаємо вільно
            $table->string('plate', 20)->nullable();              // AA1234BB
            $table->string('color', 40)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->json('meta')->nullable();                     // довільні поля (для майбутніх фіч)
            $table->timestamps();

            $table->index(['user_id', 'is_primary']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_cars');
    }
};
