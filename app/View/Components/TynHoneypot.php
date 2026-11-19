<?php

declare(strict_types=1);

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * <x-tyn-honeypot />: поле-пастка website_url для ботів (людина його не бачить). Порожнє, якщо Тин вимкнено.
 */
class TynHoneypot extends Component
{
    public bool $show;

    public function __construct()
    {
        $this->show = (bool) config('tyn.enabled', true) && trim((string) config('tyn.site_key')) !== '' && (bool) config('tyn.secret');
    }

    public function render(): View
    {
        return view('components.tyn-honeypot');
    }
}
