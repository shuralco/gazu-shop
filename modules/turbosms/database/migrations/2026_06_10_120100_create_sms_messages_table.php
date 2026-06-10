<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Журнал відправлених SMS/Viber (TurboSMS) — аудит, статуси доставки, дебаг.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sms_messages')) {
            return;
        }

        Schema::create('sms_messages', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20);
            $table->string('template_key')->nullable();   // null = ручна/тестова відправка
            $table->string('channel', 16);                 // sms | viber | hybrid
            $table->text('text');                          // фінальний відрендерений текст
            $table->string('message_id')->nullable();      // id у TurboSMS (для статусів)
            $table->string('status', 32)->default('queued'); // queued|sent|delivered|read|failed|rejected...
            $table->string('error')->nullable();           // текст помилки шлюзу
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->timestamps();

            $table->index('phone');
            $table->index('status');
            $table->index('template_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_messages');
    }
};
