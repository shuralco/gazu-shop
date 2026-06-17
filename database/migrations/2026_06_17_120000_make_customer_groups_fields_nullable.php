<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Створення групи клієнтів падало (500): Filament через ConvertEmptyStringsToNull
 * вставляє явний NULL у необовʼязкові поля, що перебиває default(0) і ламає
 * NOT NULL. Робимо ці поля nullable — порожні значення стають NULL без помилки.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('customer_groups')) {
            return;
        }
        Schema::table('customer_groups', function (Blueprint $table) {
            $table->decimal('discount_percentage', 5, 2)->nullable()->default(0)->change();
            $table->decimal('min_order_amount', 10, 2)->nullable()->default(0)->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('customer_groups')) {
            return;
        }
        Schema::table('customer_groups', function (Blueprint $table) {
            $table->decimal('discount_percentage', 5, 2)->default(0)->change();
            $table->decimal('min_order_amount', 10, 2)->default(0)->change();
        });
    }
};
