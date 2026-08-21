<?php

namespace App\Support;

use App\Models\CustomerGroup;
use App\Models\User;

/**
 * Яка група клієнтів визначає ціну для відвідувача.
 *
 * Одне джерело правди для всіх грошових шляхів:
 *   - залогінений з активною групою → його група;
 *   - гість або користувач без (активної) групи → СТАНДАРТНА група (is_default).
 *
 * Саме тому ціна, вписана в «Гуртові ціни» для стандартної групи, є звичайною
 * роздрібною ціною сайту — її бачать усі незалогінені. Без цього явна ціна
 * діяла лише для залогінених, а гість отримував формулу націнки й міг бачити
 * зовсім іншу суму (на проді розбіжність сягала 16 тис. грн на товар).
 */
class PricingGroup
{
    /** Кеш стандартної групи на час запиту (гість → щоразу той самий SELECT). */
    private static ?CustomerGroup $defaultGroup = null;

    private static bool $loaded = false;

    /** Скидання per-request стану (Octane: воркер живе між запитами). */
    public static function flush(): void
    {
        self::$defaultGroup = null;
        self::$loaded = false;
    }

    /** Стандартна група — позначена is_default і активна. */
    public static function defaultGroup(): ?CustomerGroup
    {
        if (self::$loaded) {
            return self::$defaultGroup;
        }
        self::$loaded = true;

        if (! \Schema::hasTable('customer_groups')) {
            return self::$defaultGroup = null;
        }

        return self::$defaultGroup = CustomerGroup::query()
            ->when(\Schema::hasColumn('customer_groups', 'is_default'), fn ($q) => $q->where('is_default', true))
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();
    }

    /** Група, за якою рахуємо ціну цьому користувачеві (гість → стандартна). */
    public static function forUser(?User $user): ?CustomerGroup
    {
        if ($user && $user->customer_group_id) {
            $group = $user->customerGroup;
            if ($group && $group->is_active) {
                return $group;
            }
        }

        return self::defaultGroup();
    }

    /**
     * Групи, чиї «Гуртові ціни» треба підвантажити для цього відвідувача:
     * його власна + стандартна (роздрібна база). Для eager-load, щоб картки
     * не робили по SELECT кожна.
     *
     * @return array<int,int>
     */
    public static function relevantGroupIds(?int $userGroupId = null): array
    {
        return array_values(array_unique(array_filter([
            $userGroupId,
            self::defaultGroup()?->id,
        ])));
    }

    /** Чи ця група — стандартна (тобто її ціна і є «звичайна» для сайту). */
    public static function isDefault(?CustomerGroup $group): bool
    {
        $default = self::defaultGroup();

        return $group && $default && (int) $group->id === (int) $default->id;
    }
}
