<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Таблиця купонів
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Код купону (SUMMER20, NEWUSER)
            $table->string('type')->default('percentage'); // percentage, fixed_amount, free_shipping
            $table->decimal('value', 8, 2); // Значення знижки (20 для 20% або 100 для 100 грн)
            $table->decimal('minimum_amount', 8, 2)->nullable(); // Мінімальна сума замовлення
            $table->decimal('maximum_discount', 8, 2)->nullable(); // Максимальна сума знижки
            $table->integer('usage_limit')->nullable(); // Максимальна кількість використань
            $table->integer('used_count')->default(0); // Кількість використань
            $table->integer('usage_limit_per_user')->nullable(); // Ліміт на користувача
            $table->boolean('is_active')->default(true);
            $table->datetime('valid_from');
            $table->datetime('valid_until');
            $table->text('description')->nullable();
            $table->timestamps();

            // Індекси для швидкого пошуку
            $table->index('code');
            $table->index(['is_active', 'valid_from', 'valid_until']);
        });

        // Таблиця використання купонів користувачами
        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('user_email')->nullable(); // Для гостьових замовлень
            $table->decimal('discount_amount', 8, 2); // Фактична сума знижки
            $table->timestamp('used_at');

            // Індекси
            $table->index(['coupon_id', 'user_id']);
            $table->index(['coupon_id', 'user_email']);
        });

        // Додати поле купону до замовлень
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('coupon_id')->nullable()->constrained()->onDelete('set null');
            $table->string('coupon_code')->nullable(); // Збережемо код для історії
            $table->decimal('discount_amount', 8, 2)->default(0); // Сума знижки
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['coupon_id']);
            $table->dropColumn(['coupon_id', 'coupon_code', 'discount_amount']);
        });

        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('coupons');
    }
};
