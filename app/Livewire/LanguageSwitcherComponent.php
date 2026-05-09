<?php

namespace App\Livewire;

use Livewire\Component;

class LanguageSwitcherComponent extends Component
{
    public string $locale;

    public string $currentPath = '';

    public function mount(): void
    {
        $this->locale = app()->getLocale();
        $this->currentPath = request()->path();
    }

    public function switchLocale(string $locale): void
    {
        $available = config('app.available_locales', ['uk', 'en']);
        if (! in_array($locale, $available)) {
            return;
        }

        session()->put('locale', $locale);
        $this->locale = $locale;

        // Build new URL replacing locale prefix
        $path = $this->currentPath;

        // Remove current locale prefix if present
        foreach ($available as $loc) {
            if (str_starts_with($path, $loc . '/') || $path === $loc) {
                $path = substr($path, strlen($loc) + 1) ?: '';
                break;
            }
        }

        $newUrl = '/' . $locale . '/' . ltrim($path, '/');
        $this->redirect($newUrl);
    }

    public function render()
    {
        return view('livewire.language-switcher-component');
    }
}
