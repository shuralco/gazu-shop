<?php

namespace App\Console\Commands;

use App\Models\AccessPreset;
use App\Models\User;
use Illuminate\Console\Command;

/**
 * Діагностика й призначення RBAC-доступу в адмінці.
 *
 * Чому пункти меню приховані у не-супер-адміна — дві причини:
 *   1) пресет доступу (access_preset_id) не дає «view» на розділ;
 *   2) персональне приховування «Моє меню» (users.nav_preferences.hidden).
 * Ця команда показує обидва й дозволяє полагодити.
 *
 *   php artisan gazu:access --list                        # хто є, який пресет, що приховано
 *   php artisan gazu:access user@shop.ua                  # призначити «Адмін клієнта» (client_admin)
 *   php artisan gazu:access user@shop.ua --preset=admin_full
 *   php artisan gazu:access user@shop.ua --clear-hidden   # + скинути персональне приховування меню
 */
class AccessGrant extends Command
{
    protected $signature = 'gazu:access
        {email? : Email користувача, якому призначити пресет}
        {--preset=client_admin : Ключ пресету доступу}
        {--clear-hidden : Скинути персональне приховування пунктів (nav_preferences)}
        {--list : Показати не-супер-адмін користувачів і їхній доступ}';

    protected $description = 'Показати/призначити доступ до розділів адмінки (RBAC-пресети + Моє меню).';

    public function handle(): int
    {
        if ($this->option('list') || ! $this->argument('email')) {
            return $this->listUsers();
        }

        $email = trim((string) $this->argument('email'));
        $user = User::where('email', $email)->first();
        if (! $user) {
            $this->error("Користувача з email «{$email}» не знайдено.");

            return self::FAILURE;
        }

        if ($user->is_admin) {
            $this->warn("«{$email}» — супер-адмін (is_admin), він і так бачить усі розділи. Пресет не потрібен.");
        }

        $presetKey = (string) $this->option('preset');
        $preset = AccessPreset::where('key', $presetKey)->first();
        if (! $preset) {
            $this->error("Пресет «{$presetKey}» не знайдено. Наявні: ".AccessPreset::pluck('key')->implode(', '));

            return self::FAILURE;
        }

        $user->access_preset_id = $preset->id;

        if ($this->option('clear-hidden')) {
            $nav = $user->nav_preferences ?? [];
            $nav['hidden'] = [];
            $user->nav_preferences = $nav;
        }

        $user->save();

        $this->info("✓ «{$email}» → пресет «{$preset->name}» ({$preset->key}).");
        if ($this->option('clear-hidden')) {
            $this->line('  Персональне приховування меню скинуто.');
        }
        $this->line('  Клієнту треба перезайти в адмінку (вийти/зайти), щоб оновилось меню.');

        return self::SUCCESS;
    }

    private function listUsers(): int
    {
        $users = User::query()
            ->where('is_admin', false)
            ->with('accessPreset')
            ->orderBy('email')
            ->get(['id', 'name', 'email', 'access_preset_id', 'nav_preferences']);

        if ($users->isEmpty()) {
            $this->warn('Немає не-супер-адмін користувачів (усі — is_admin або їх нема).');

            return self::SUCCESS;
        }

        $rows = $users->map(function (User $u) {
            $hidden = $u->nav_preferences['hidden'] ?? [];

            return [
                $u->id,
                mb_strimwidth((string) $u->email, 0, 34, '…'),
                $u->accessPreset?->name ?? '— немає —',
                is_array($hidden) ? count($hidden) : 0,
            ];
        })->all();

        $this->table(['ID', 'Email', 'Пресет доступу', 'Приховано пунктів'], $rows);
        $this->line('Призначити повний бізнес-доступ: php artisan gazu:access <email> --preset=client_admin --clear-hidden');

        return self::SUCCESS;
    }
}
