<?php

namespace App\Policies;

use App\Models\Filter;
use App\Models\User;

class FilterPolicy
{
    /**
     * Визначити, чи може користувач переглядати будь-які моделі.
     */
    public function viewAny(?User $user): bool
    {
        // Всі користувачі (навіть гості) можуть переглядати фільтри
        return true;
    }

    /**
     * Визначити, чи може користувач переглядати модель.
     */
    public function view(?User $user, Filter $filter): bool
    {
        // Всі користувачі (навіть гості) можуть переглядати окремі фільтри
        return true;
    }

    /**
     * Визначити, чи може користувач створювати моделі.
     */
    public function create(User $user): bool
    {
        // Тільки адміністратори можуть створювати фільтри
        return $user->is_admin;
    }

    /**
     * Визначити, чи може користувач оновлювати модель.
     */
    public function update(User $user, Filter $filter): bool
    {
        // Тільки адміністратори можуть редагувати фільтри
        return $user->is_admin;
    }

    /**
     * Визначити, чи може користувач видаляти модель.
     */
    public function delete(User $user, Filter $filter): bool
    {
        // Тільки адміністратори можуть видаляти фільтри
        return $user->is_admin;
    }

    /**
     * Визначити, чи може користувач відновлювати модель.
     */
    public function restore(User $user, Filter $filter): bool
    {
        // Тільки адміністратори можуть відновлювати фільтри
        return $user->is_admin;
    }

    /**
     * Визначити, чи може користувач остаточно видаляти модель.
     */
    public function forceDelete(User $user, Filter $filter): bool
    {
        // Тільки адміністратори можуть остаточно видаляти фільтри
        return $user->is_admin;
    }
}
