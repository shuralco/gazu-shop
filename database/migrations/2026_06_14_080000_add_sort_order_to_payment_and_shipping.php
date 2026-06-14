<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Керування способами оплати/доставки на checkout «як в OpenCart»:
 * порядок (sort_order) для платіжних систем і провайдерів доставки +
 * опис провайдера (показується підзаголовком на checkout).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payment_gateway_settings') && ! Schema::hasColumn('payment_gateway_settings', 'sort_order')) {
            Schema::table('payment_gateway_settings', function (Blueprint $table) {
                $table->integer('sort_order')->default(0)->index();
            });
        }

        if (Schema::hasTable('shipping_providers')) {
            Schema::table('shipping_providers', function (Blueprint $table) {
                if (! Schema::hasColumn('shipping_providers', 'sort_order')) {
                    $table->integer('sort_order')->default(0)->index();
                }
                if (! Schema::hasColumn('shipping_providers', 'description')) {
                    $table->string('description', 255)->nullable();
                }
            });
        }

        $this->seedDefaults();
    }

    /**
     * Гарантуємо мінімальний набір способів оплати/доставки для checkout
     * (ідемпотентно). Без цього на проді (де в payment_gateway_settings лише
     * платіжний шлюз) зник би «накладений платіж»/«рахунок» при переході
     * checkout на БД як єдине джерело.
     */
    private function seedDefaults(): void
    {
        if (Schema::hasTable('payment_gateway_settings')) {
            $now = now();
            // «Картка онлайн» сідимо лише якщо ще немає онлайн-шлюзу — щоб не
            // дублювати з реальним LiqPay/WayForPay/Monobank.
            $onlineExists = DB::table('payment_gateway_settings')
                ->whereIn('code', ['liqpay', 'wayforpay', 'monobank', 'card'])
                ->exists();

            $methods = [];
            if (! $onlineExists) {
                $methods[] = ['code' => 'card', 'name' => 'Оплата картою онлайн', 'description' => 'Visa, Mastercard', 'sort_order' => 10];
            }
            $methods[] = ['code' => 'cod', 'name' => 'Накладений платіж', 'description' => 'Оплата при отриманні', 'sort_order' => 20];
            $methods[] = ['code' => 'invoice', 'name' => 'Рахунок для оплати', 'description' => 'Безготівковий розрахунок для бізнесу', 'sort_order' => 30];

            foreach ($methods as $m) {
                $exists = DB::table('payment_gateway_settings')->where('code', $m['code'])->exists();
                if (! $exists) {
                    DB::table('payment_gateway_settings')->insert(array_merge($m, [
                        'is_active' => true,
                        'fee_percentage' => 0,
                        'currency' => 'UAH',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]));
                }
            }
        }

        // Описи/порядок провайдерів доставки (оновлюємо лише якщо порожні).
        if (Schema::hasTable('shipping_providers') && Schema::hasColumn('shipping_providers', 'sort_order')) {
            $providerDefaults = [
                'novaposhta' => ['description' => 'Відділення / Поштомат / Курʼєр НП — 1-3 дні', 'sort_order' => 10],
                'ukrposhta'  => ['description' => 'Відділення / адреса · 3-5 днів, дешевше', 'sort_order' => 20],
                'pickup'     => ['description' => 'Самовивіз з магазину — безкоштовно', 'sort_order' => 30],
            ];
            foreach ($providerDefaults as $code => $vals) {
                $row = DB::table('shipping_providers')->where('code', $code)->first();
                if (! $row) {
                    continue;
                }
                $update = [];
                if (empty($row->description)) {
                    $update['description'] = $vals['description'];
                }
                if ((int) ($row->sort_order ?? 0) === 0) {
                    $update['sort_order'] = $vals['sort_order'];
                }
                if (! empty($update)) {
                    DB::table('shipping_providers')->where('id', $row->id)->update($update);
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('payment_gateway_settings') && Schema::hasColumn('payment_gateway_settings', 'sort_order')) {
            Schema::table('payment_gateway_settings', fn (Blueprint $t) => $t->dropColumn('sort_order'));
        }
        if (Schema::hasTable('shipping_providers')) {
            Schema::table('shipping_providers', function (Blueprint $table) {
                foreach (['sort_order', 'description'] as $col) {
                    if (Schema::hasColumn('shipping_providers', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
