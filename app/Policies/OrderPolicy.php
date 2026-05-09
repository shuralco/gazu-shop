<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Визначити, чи може користувач переглядати будь-які моделі.
     */
    public function viewAny(User $user): bool
    {
        // Тільки адміністратори можуть переглядати всі замовлення
        return $user->is_admin;
    }

    /**
     * Визначити, чи може користувач переглядати модель.
     */
    public function view(User $user, Order $order): bool
    {
        // Адміністратори можуть переглядати будь-які замовлення
        // Користувачі можуть переглядати тільки свої замовлення
        return $user->is_admin || $user->id === $order->user_id;
    }

    /**
     * Визначити, чи може користувач створювати моделі.
     */
    public function create(User $user): bool
    {
        // Всі авторизовані користувачі можуть створювати замовлення
        return true;
    }

    /**
     * Визначити, чи може користувач оновлювати модель.
     */
    public function update(User $user, Order $order): bool
    {
        // Адміністратори можуть редагувати будь-які замовлення
        // Користувачі можуть редагувати тільки свої замовлення (наприклад, статус доставки)
        return $user->is_admin || $user->id === $order->user_id;
    }

    /**
     * Визначити, чи може користувач видаляти модель.
     */
    public function delete(User $user, Order $order): bool
    {
        // Тільки адміністратори можуть видаляти замовлення
        return $user->is_admin;
    }

    /**
     * Визначити, чи може користувач відновлювати модель.
     */
    public function restore(User $user, Order $order): bool
    {
        // Тільки адміністратори можуть відновлювати замовлення
        return $user->is_admin;
    }

    /**
     * Визначити, чи може користувач остаточно видаляти модель.
     */
    public function forceDelete(User $user, Order $order): bool
    {
        // Тільки адміністратори можуть остаточно видаляти замовлення
        return $user->is_admin;
    }
}
