<?php

namespace Modules\PricingMarkup\Services;

use App\Models\CustomerGroup;
use App\Models\User;

/**
 * Націнка по групах клієнтів.
 *
 * Контракт:
 *   - ціна в картці товару = БАЗОВА (собівартість/закупка), покупець її не бачить;
 *   - клієнт бачить `базова + % націнки своєї групи`;
 *   - авторизований без групи або гість → «стандартна» група (is_default);
 *   - немає стандартної групи / модуль вимкнено → базова без змін (0 %).
 *
 * Націнка застосовується ДО решти логіки й стає «звичайною» ціною для цього
 * клієнта. Тому знижки та фіксовані гуртові ціни далі працюють як раніше —
 * вони відштовхуються від уже націненої ціни, а не від собівартості.
 */
class MarkupPricing
{
    /** Кеш стандартної групи на час запиту (гість → щоразу той самий SELECT). */
    private static ?CustomerGroup $defaultGroup = null;

    private static bool $defaultGroupLoaded = false;

    /** Скидання per-request стану (Octane: воркер живе між запитами). */
    public static function flush(): void
    {
        self::$defaultGroup = null;
        self::$defaultGroupLoaded = false;
    }

    /** Стандартна група — та, що позначена is_default (і активна). */
    public static function defaultGroup(): ?CustomerGroup
    {
        if (self::$defaultGroupLoaded) {
            return self::$defaultGroup;
        }
        self::$defaultGroupLoaded = true;

        if (! \Schema::hasTable('customer_groups') || ! \Schema::hasColumn('customer_groups', 'markup_percentage')) {
            return self::$defaultGroup = null;
        }

        return self::$defaultGroup = CustomerGroup::query()
            ->where('is_default', true)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();
    }

    /**
     * Група, за якою вважаємо ціну для цього користувача.
     * Гість або користувач без (активної) групи → стандартна.
     */
    public static function groupFor(?User $user): ?CustomerGroup
    {
        if ($user && $user->customer_group_id) {
            $group = $user->customerGroup;
            if ($group && $group->is_active) {
                return $group;
            }
        }

        return self::defaultGroup();
    }

    /** % націнки для користувача. 0 — якщо групи немає або модуль вимкнено. */
    public static function percentFor(?User $user): float
    {
        if (! self::enabled()) {
            return 0.0;
        }

        return (float) (self::groupFor($user)->markup_percentage ?? 0);
    }

    /**
     * Базова ціна → ціна для клієнта (з націнкою його групи).
     * Округлення до копійки; від'ємна націнка нижче -100 % не опускає ціну під нуль.
     */
    public static function apply(float $base, ?User $user): float
    {
        $percent = self::percentFor($user);
        if ($percent === 0.0 || $base <= 0) {
            return round($base, 2);
        }

        return round(max(0, $base * (1 + $percent / 100)), 2);
    }

    /** Модуль увімкнено? (DB-aware перевірка на момент розрахунку). */
    public static function enabled(): bool
    {
        try {
            return (bool) module('pricing_markup')->enabled();
        } catch (\Throwable) {
            return false;
        }
    }
}
