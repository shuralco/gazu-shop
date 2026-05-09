<?php

namespace App\Policies;

use App\Models\FilterGroup;
use App\Models\User;

class FilterGroupPolicy
{
    /**
     * Визначити, чи може користувач переглядати будь-які моделі.
     */
    public function viewAny(?User $user): bool
    {
        // Всі користувачі (навіть гості) можуть переглядати групи фільтрів
        return true;
    }

    /**
     * Визначити, чи може користувач переглядати модель.
     */
    public function view(?User $user, FilterGroup $filterGroup): bool
    {
        // Всі користувачі (навіть гості) можуть переглядати окремі групи фільтрів
        return true;
    }

    /**
     * Визначити, чи може користувач створювати моделі.
     */
    public function create(User $user): bool
    {
        // Тільки адміністратори можуть створювати групи фільтрів
        return $user->is_admin;
    }

    /**
     * Визначити, чи може користувач оновлювати модель.
     */
    public function update(User $user, FilterGroup $filterGroup): bool
    {
        // Тільки адміністратори можуть редагувати групи фільтрів
        return $user->is_admin;
    }

    /**
     * Визначити, чи може користувач видаляти модель.
     */
    public function delete(User $user, FilterGroup $filterGroup): bool
    {
        // Тільки адміністратори можуть видаляти групи фільтрів
        return $user->is_admin;
    }

    /**
     * Визначити, чи може користувач відновлювати модель.
     */
    public function restore(User $user, FilterGroup $filterGroup): bool
    {
        // Тільки адміністратори можуть відновлювати групи фільтрів
        return $user->is_admin;
    }

    /**
     * Визначити, чи може користувач остаточно видаляти модель.
     */
    public function forceDelete(User $user, FilterGroup $filterGroup): bool
    {
        // Тільки адміністратори можуть остаточно видаляти групи фільтрів
        return $user->is_admin;
    }
}
