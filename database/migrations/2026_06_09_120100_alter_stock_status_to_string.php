<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * products.stock_status / product_variants.stock_status були MySQL enum
 * ('in_stock','out_of_stock','preorder') — це блокувало кастомні ключі з
 * довідника StockStatus. Переводимо в varchar (значення зберігаються).
 */
return new class extends Migration
{
    private array $tables = ['products', 'product_variants'];

    public function up(): void
    {
        foreach ($this->tables as $t) {
            if (Schema::hasColumn($t, 'stock_status')) {
                DB::statement("ALTER TABLE `{$t}` MODIFY `stock_status` VARCHAR(64) NOT NULL DEFAULT 'in_stock'");
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $t) {
            if (Schema::hasColumn($t, 'stock_status')) {
                DB::statement("ALTER TABLE `{$t}` MODIFY `stock_status` ENUM('in_stock','out_of_stock','preorder') NOT NULL DEFAULT 'in_stock'");
            }
        }
    }
};
