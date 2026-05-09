<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    /**
     * Визначити, чи може користувач переглядати будь-які моделі.
     */
    public function viewAny(?User $user): bool
    {
        // Всі користувачі (навіть гості) можуть переглядати список категорій
        return true;
    }

    /**
     * Визначити, чи може користувач переглядати модель.
     */
    public function view(?User $user, Category $category): bool
    {
        // Всі користувачі (навіть гості) можуть переглядати окремі категорії
        return true;
    }

    /**
     * Визначити, чи може користувач створювати моделі.
     */
    public function create(User $user): bool
    {
        // Тільки адміністратори можуть створювати категорії
        return $user->is_admin;
    }

    /**
     * Визначити, чи може користувач оновлювати модель.
     */
    public function update(User $user, Category $category): bool
    {
        // Тільки адміністратори можуть редагувати категорії
        return $user->is_admin;
    }

    /**
     * Визначити, чи може користувач видаляти модель.
     */
    public function delete(User $user, Category $category): bool
    {
        // Тільки адміністратори можуть видаляти категорії
        return $user->is_admin;
    }

    /**
     * Визначити, чи може користувач відновлювати модель.
     */
    public function restore(User $user, Category $category): bool
    {
        // Тільки адміністратори можуть відновлювати категорії
        return $user->is_admin;
    }

    /**
     * Визначити, чи може користувач остаточно видаляти модель.
     */
    public function forceDelete(User $user, Category $category): bool
    {
        // Тільки адміністратори можуть остаточно видаляти категорії
        return $user->is_admin;
    }
}
