<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Support\Tyn\TynVerifier;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * <x-tyn-widget form="callback" />: віджет усередині <form>; <x-tyn-widget script-only /> один раз у layout
 * (завантажувач сам знаходить контейнери, у тому числі додані пізніше через MutationObserver). Порожній, якщо вимкнено чи немає ключа. */
class TynWidget extends Component
{
    public string $siteKey;
    public string $base;
    public string $form;
    public bool $show;
    public bool $scriptOnly;

    public function __construct(string $form = 'other', bool $scriptOnly = false)
    {
        $this->siteKey = trim((string) config('tyn.site_key'));
        $this->base = TynVerifier::normalizeBase(config('tyn.base_url'));
        $this->form = in_array($form, ['login', 'register', 'callback', 'subscribe', 'contact', 'review', 'other'], true) ? $form : 'other';
        $this->scriptOnly = $scriptOnly;
        $this->show = (bool) config('tyn.enabled', true) && $this->siteKey !== '' && $this->base !== '' && (bool) config('tyn.secret');
    }

    public function render(): View
    {
        return view('components.tyn-widget');
    }
}
