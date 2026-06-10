<?php

namespace App\Console\Commands;

use App\Models\DisplaySetting;
use App\Models\Order;
use Illuminate\Console\Command;

/**
 * Preflight перед здачею/деплоєм: ловить демо-маркери й незаповнені
 * реквізити, які НЕ мають потрапити в прод.
 *
 * Запуск: php artisan launch:check
 * Exit code 1 якщо є FAIL-пункти (зручно для CI/deploy-гейта).
 */
class LaunchCheck extends Command
{
    protected $signature = 'launch:check';

    protected $description = 'Перевірка готовності до проду: демо-тексти, реквізити, пошта, оплата, черга';

    /** Демо-дефолти з GazuVisualSettings::$defaults — якщо в БД null або збігається, значить ніхто не заповнив. */
    private const DEMO_CONTACTS = [
        'gazu_phone' => '0 800 75 10 24',
        'gazu_topbar_hours' => 'Пн-Нд 8:00–20:00',
    ];

    public function handle(): int
    {
        $fail = 0;
        $warn = 0;
        $rows = [];

        $add = function (string $status, string $check, string $detail) use (&$rows, &$fail, &$warn) {
            $rows[] = [$status, $check, $detail];
            if ($status === 'FAIL') {
                $fail++;
            }
            if ($status === 'WARN') {
                $warn++;
            }
        };

        // --- 1. Демо-замовлення -------------------------------------------------
        $demoOrders = Order::query()
            ->where('email', 'like', 'demo+%@gazu.demo')
            ->orWhere('email', 'like', 'maptest+%')
            ->count();
        $demoOrders
            ? $add('FAIL', 'Демо-замовлення', "{$demoOrders} шт — запустіть: php artisan db:scrub-demo --force")
            : $add('OK', 'Демо-замовлення', 'немає');

        // --- 2. Контакти/футер (демо-фолбеки) ------------------------------------
        foreach (self::DEMO_CONTACTS as $key => $demo) {
            $val = DisplaySetting::get($key);
            if ($val === null || $val === $demo) {
                $add('FAIL', "Реквізити: {$key}", 'демо-значення — заповніть: Налаштування → GAZU візуальні блоки');
            } else {
                $add('OK', "Реквізити: {$key}", (string) $val);
            }
        }

        $footerAbout = DisplaySetting::get('gazu_footer_about');
        $footerAbout
            ? $add('OK', 'Футер: about-текст', 'заповнено')
            : $add('WARN', 'Футер: about-текст', 'демо-фолбек (авто-текст) — за бажанням заповніть');

        $socialFilled = 0;
        foreach (['facebook', 'instagram', 'telegram', 'youtube'] as $net) {
            $v = DisplaySetting::get("gazu_social_{$net}");
            if ($v && $v !== '#') {
                $socialFilled++;
            }
        }
        $socialFilled
            ? $add('OK', 'Соцмережі', "{$socialFilled}/4 заповнено")
            : $add('WARN', 'Соцмережі', 'жодного посилання (демо "#") — у футері не показуються');

        // --- 3. Юр-сторінки ------------------------------------------------------
        $infoPages = class_exists(\App\Models\InfoPage::class) ? \App\Models\InfoPage::count() : 0;
        $infoPages >= 5
            ? $add('OK', 'Інфо/юр-сторінки', "{$infoPages} у БД (перевірте реальні реквізити в текстах!)")
            : $add('FAIL', 'Інфо/юр-сторінки', 'мало/немає — запустіть: php artisan db:seed --class=InfoPageSeeder');

        // --- 4. Пошта -------------------------------------------------------------
        $mailer = (string) config('mail.default');
        $mailHost = (string) config('mail.mailers.smtp.host');
        if (in_array($mailer, ['log', 'array', '']) || ($mailer === 'smtp' && in_array($mailHost, ['', '127.0.0.1', 'mailpit', 'smtp.your-provider.com']))) {
            $add('FAIL', 'Пошта (SMTP)', "MAIL_MAILER={$mailer}, host=".($mailHost ?: '(порожньо)').' — листи замовлень не підуть');
        } else {
            $add('OK', 'Пошта (SMTP)', "{$mailer} → {$mailHost}");
        }

        // --- 5. Оплата ------------------------------------------------------------
        DisplaySetting::get('gazu_payment_enabled', false)
            ? $add('OK', 'Онлайн-оплата', 'увімкнена (перевірте ключі шлюзу і SANDBOX=false)')
            : $add('WARN', 'Онлайн-оплата', 'вимкнена (gazu_payment_enabled=false) — лише накладений платіж');

        // --- 6. Середовище ---------------------------------------------------------
        config('app.debug')
            ? $add('FAIL', 'APP_DEBUG', 'true — на проді має бути false')
            : $add('OK', 'APP_DEBUG', 'false');

        app()->isProduction()
            ? $add('OK', 'APP_ENV', 'production')
            : $add('WARN', 'APP_ENV', config('app.env').' (для локалки нормально)');

        config('queue.default') === 'sync'
            ? $add('WARN', 'Черга', 'sync — листи/нотифікації блокують запит; на проді: redis + worker')
            : $add('OK', 'Черга', (string) config('queue.default'));

        // --- Вивід -----------------------------------------------------------------
        $this->table(['Статус', 'Перевірка', 'Деталі'], $rows);
        $this->newLine();

        if ($fail) {
            $this->error("✗ FAIL: {$fail} блокер(ів). WARN: {$warn}. Не готово до проду.");

            return self::FAILURE;
        }

        $warn
            ? $this->warn("✓ Блокерів немає. WARN: {$warn} — перегляньте перед запуском.")
            : $this->info('✓ Усі перевірки пройдено. Готово до проду.');

        return self::SUCCESS;
    }
}
