<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Viber-опції шаблону: кнопка з лінком (caption/action у TurboSMS API),
 * картинка, транзакційний прапорець, TTL. button_url підтримує ті самі
 * плейсхолдери {{order.*}} — напр. лінк трекінгу з {{order.ttn}}.
 *
 * Ідемпотентний seed: шаблону order.shipped додаємо кнопку «Відстежити»
 * лише якщо кнопка ще не задана (ручні правки не перетираються).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sms_templates', function (Blueprint $table) {
            if (! Schema::hasColumn('sms_templates', 'viber_button_text')) {
                $table->string('viber_button_text', 30)->nullable()->after('viber_text');
                $table->string('viber_button_url', 500)->nullable()->after('viber_button_text');
                $table->string('viber_image_url', 500)->nullable()->after('viber_button_url');
                $table->boolean('viber_transactional')->default(true)->after('viber_image_url');
                $table->unsignedInteger('viber_ttl')->nullable()->after('viber_transactional');
            }
        });

        // order.shipped: кнопка «Відстежити» → трекінг Нової Пошти за ТТН
        DB::table('sms_templates')
            ->where('key', 'order.shipped')
            ->whereNull('viber_button_text')
            ->update([
                'viber_button_text' => 'Відстежити 📦',
                'viber_button_url' => 'https://novaposhta.ua/tracking/?cargo_number={{order.ttn}}',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        Schema::table('sms_templates', function (Blueprint $table) {
            $table->dropColumn(['viber_button_text', 'viber_button_url', 'viber_image_url', 'viber_transactional', 'viber_ttl']);
        });
    }
};
