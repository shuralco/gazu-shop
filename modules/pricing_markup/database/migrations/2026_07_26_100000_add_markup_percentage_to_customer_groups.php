<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Модуль pricing_markup: % НАЦІНКИ на групі клієнтів.
 *
 * Стара колонка `discount_percentage` НЕ видаляється — вона лишається робочою
 * для знижкових сценаріїв (напр. акційна група) і дає шлях відкату. Націнка й
 * знижка застосовуються на різних кроках: спершу база + націнка групи (це стає
 * «звичайною» ціною для клієнта), потім уже знижки поверх.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('customer_groups')) {
            return;
        }

        if (! Schema::hasColumn('customer_groups', 'markup_percentage')) {
            Schema::table('customer_groups', function (Blueprint $table) {
                // signed: дозволяємо і відʼємну націнку (= знижка на рівні групи).
                $table->decimal('markup_percentage', 6, 2)->default(0)->after('discount_percentage');
            });
        }

        // Гарантія «рівно одна стандартна група»: якщо жодної не позначено —
        // беремо найстаршу активну, інакше вітрина для гостя лишиться без групи.
        $hasDefault = DB::table('customer_groups')->where('is_default', true)->exists();
        if (! $hasDefault) {
            $first = DB::table('customer_groups')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->value('id');
            if ($first) {
                DB::table('customer_groups')->where('id', $first)->update(['is_default' => true]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('customer_groups') && Schema::hasColumn('customer_groups', 'markup_percentage')) {
            Schema::table('customer_groups', function (Blueprint $table) {
                $table->dropColumn('markup_percentage');
            });
        }
    }
};
