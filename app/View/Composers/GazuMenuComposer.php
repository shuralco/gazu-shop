<?php

namespace App\View\Composers;

use App\Helpers\Cart\Cart;
use App\Models\DisplaySetting;
use App\Services\Gazu\MegaMenuBuilder;
use Illuminate\View\View;

/**
 * Шерить мега-дерево + список брендів між усіма GAZU views (header → mega-menu).
 * Кешує на 10 хвилин у пам'яті процесу, щоб не нагружати DB на кожен рендер.
 */
class GazuMenuComposer
{
    private static ?array $cachedTree = null;
    private static ?array $cachedBrands = null;

    public function __construct(private MegaMenuBuilder $builder) {}

    public function compose(View $view): void
    {
        if (self::$cachedTree === null) {
            self::$cachedTree = $this->builder->build();
        }
        if (self::$cachedBrands === null) {
            self::$cachedBrands = $this->builder->brands();
        }

        $view->with('megaTree', self::$cachedTree);
        $view->with('brands', self::$cachedBrands);

        // Live cart count — не кешуємо, має оновлюватись на кожен запит.
        $view->with('cartCount', Cart::getCartQuantityItems());

        // GAZU visual settings (з DisplaySetting або defaults).
        $view->with('gazuSettings', $this->loadVisualSettings());
    }

    private function loadVisualSettings(): array
    {
        $defaults = \App\Filament\Pages\GazuVisualSettings::$defaults ?? [];
        $out = [];
        foreach ($defaults as $k => $default) {
            $val = DisplaySetting::get($k);
            $out[$k] = ($val !== null && $val !== '') ? $val : $default;
        }
        return $out;
    }
}
