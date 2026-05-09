<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Визначити, чи може користувач переглядати будь-які моделі.
     */
    public function viewAny(User $user): bool
    {
        // Тільки адміністратори можуть переглядати список всіх користувачів
        return $user->is_admin;
    }

    /**
     * Визначити, чи може користувач переглядати модель.
     */
    public function view(User $user, User $model): bool
    {
        // Адміністратори можуть переглядати будь-якого користувача
        // Користувачі можуть переглядати тільки свій профіль
        return $user->is_admin || $user->id === $model->id;
    }

    /**
     * Визначити, чи може користувач створювати моделі.
     */
    public function create(User $user): bool
    {
        // Тільки адміністратори можуть створювати користувачів
        return $user->is_admin;
    }

    /**
     * Визначити, чи може користувач оновлювати модель.
     */
    public function update(User $user, User $model): bool
    {
        // Адміністратори можуть редагувати будь-якого користувача
        // Користувачі можуть редагувати тільки свій профіль
        return $user->is_admin || $user->id === $model->id;
    }

    /**
     * Визначити, чи може користувач видаляти модель.
     */
    public function delete(User $user, User $model): bool
    {
        // Тільки адміністратори можуть видаляти користувачів
        // Але не можуть видаляти самих себе
        return $user->is_admin && $user->id !== $model->id;
    }

    /**
     * Визначити, чи може користувач відновлювати модель.
     */
    public function restore(User $user, User $model): bool
    {
        // Тільки адміністратори можуть відновлювати користувачів
        return $user->is_admin;
    }

    /**
     * Визначити, чи може користувач остаточно видаляти модель.
     */
    public function forceDelete(User $user, User $model): bool
    {
        // Тільки адміністратори можуть остаточно видаляти користувачів
        // Але не можуть видаляти самих себе
        return $user->is_admin && $user->id !== $model->id;
    }
}
