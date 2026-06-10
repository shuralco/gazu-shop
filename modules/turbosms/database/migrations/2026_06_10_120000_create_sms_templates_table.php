<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Шаблони SMS/Viber-повідомлень (TurboSMS) — редагуються в адмінці.
 * Той самий патерн, що email_templates: {{var.path}} плейсхолдери.
 *
 * Seed дефолтів ідемпотентний (firstOrCreate-стиль): для нового клієнта
 * «встановив модуль → migrate → тексти на місці», а вже відредаговані
 * вручну шаблони повторна міграція НЕ перезаписує.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sms_templates')) {
            Schema::create('sms_templates', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();      // подія: order.created / order.shipped / ...
                $table->string('name');                // людська назва в адмінці
                $table->string('channel', 16)->default('hybrid'); // sms | viber | hybrid (viber→sms fallback)
                $table->text('text');                  // текст SMS (і Viber, якщо viber_text порожній)
                $table->text('viber_text')->nullable();// окремий текст для Viber (довший/з емодзі)
                $table->json('variables_help')->nullable(); // підказка змінних для адміна
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        $defaults = [
            [
                'key' => 'order.created',
                'name' => 'Замовлення прийнято',
                'channel' => 'hybrid',
                'text' => "GAZU: замовлення №{{order.id}} прийнято. Сума {{order.total}} грн. Менеджер зв'яжеться найближчим часом.",
                'viber_text' => "✅ GAZU: ваше замовлення №{{order.id}} прийнято!\nСума: {{order.total}} ₴\nМенеджер зв'яжеться з вами найближчим часом.",
                'variables_help' => ['order.id', 'order.total', 'order.customer_name'],
            ],
            [
                'key' => 'order.paid',
                'name' => 'Замовлення оплачено',
                'channel' => 'hybrid',
                'text' => 'GAZU: оплату по замовленню №{{order.id}} отримано. Готуємо до відправки.',
                'viber_text' => "💳 GAZU: оплату по замовленню №{{order.id}} отримано.\nГотуємо посилку до відправки — ТТН надішлемо окремо.",
                'variables_help' => ['order.id', 'order.total'],
            ],
            [
                'key' => 'order.shipped',
                'name' => 'Відправлено (ТТН)',
                'channel' => 'hybrid',
                'text' => 'GAZU: замовлення №{{order.id}} відправлено. ТТН {{order.ttn}}. Відстеження у застосунку перевізника.',
                'viber_text' => "📦 GAZU: замовлення №{{order.id}} в дорозі!\nТТН: {{order.ttn}}\nВідстежити можна у застосунку перевізника.",
                'variables_help' => ['order.id', 'order.ttn', 'order.carrier'],
            ],
            [
                'key' => 'order.status_changed',
                'name' => 'Зміна статусу замовлення',
                'channel' => 'sms',
                'text' => 'GAZU: статус замовлення №{{order.id}}: {{order.status_label}}.',
                'viber_text' => null,
                'variables_help' => ['order.id', 'order.status_label'],
            ],
        ];

        foreach ($defaults as $row) {
            $exists = DB::table('sms_templates')->where('key', $row['key'])->exists();
            if (! $exists) {
                $row['variables_help'] = json_encode($row['variables_help']);
                DB::table('sms_templates')->insert($row + [
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_templates');
    }
};
