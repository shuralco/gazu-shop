<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /**
     * Визначити, чи може користувач переглядати будь-які моделі.
     */
    public function viewAny(?User $user): bool
    {
        // Всі користувачі (навіть гості) можуть переглядати список продуктів
        return true;
    }

    /**
     * Визначити, чи може користувач переглядати модель.
     */
    public function view(?User $user, Product $product): bool
    {
        // Всі користувачі (навіть гості) можуть переглядати окремі продукти
        return true;
    }

    /**
     * Визначити, чи може користувач створювати моделі.
     */
    public function create(User $user): bool
    {
        // Тільки адміністратори можуть створювати продукти
        return $user->is_admin;
    }

    /**
     * Визначити, чи може користувач оновлювати модель.
     */
    public function update(User $user, Product $product): bool
    {
        // Тільки адміністратори можуть редагувати продукти
        return $user->is_admin;
    }

    /**
     * Визначити, чи може користувач видаляти модель.
     */
    public function delete(User $user, Product $product): bool
    {
        // Тільки адміністратори можуть видаляти продукти
        return $user->is_admin;
    }

    /**
     * Визначити, чи може користувач відновлювати модель.
     */
    public function restore(User $user, Product $product): bool
    {
        // Тільки адміністратори можуть відновлювати продукти
        return $user->is_admin;
    }

    /**
     * Визначити, чи може користувач остаточно видаляти модель.
     */
    public function forceDelete(User $user, Product $product): bool
    {
        // Тільки адміністратори можуть остаточно видаляти продукти
        return $user->is_admin;
    }
}
