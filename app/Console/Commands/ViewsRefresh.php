<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Безпечна заміна голому `view:clear`.
 *
 * Проблема: `view:clear` сам по собі видаляє ~394 скомпільовані Blade-шаблони,
 * і ПЕРШИЙ хіт кожного типу сторінки потім рекомпілює їх на льоту (~500ms
 * спайк, підсилений opcache.validate_timestamps=0). Будь-який код/кнопка, що
 * робить view:clear ТЕРМІНАЛЬНИМ кроком, лишає storefront холодним.
 *
 * Ця команда атомарно робить clear + одразу bulk-compile (view:cache), тож
 * холодного вікна не виникає. Усі точки коду (Filament-кнопки кешу, enable/
 * disable модуля, ShopSettings, ModuleInstaller) кличуть ЦЕ замість view:clear.
 */
class ViewsRefresh extends Command
{
    protected $signature = 'gazu:views:refresh';

    protected $description = 'Перекомпілювати Blade-view (view:clear → view:cache) без холодного вікна';

    public function handle(): int
    {
        $this->call('view:clear');
        $this->call('view:cache');

        return self::SUCCESS;
    }
}
