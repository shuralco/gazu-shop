<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_group_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_group_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 10, 2);
            $table->integer('min_quantity')->default(1);
            $table->timestamps();

            $table->unique(['product_id', 'customer_group_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_group_prices');
    }
};
