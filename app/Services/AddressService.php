<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserAddress;

class AddressService
{
    /**
     * Створити нову адресу
     */
    public function create(User $user, array $data): UserAddress
    {
        if (! empty($data['is_default'])) {
            $this->clearDefaults($user);
        }

        // Якщо перша адреса — зробити її за замовчуванням
        if ($user->addresses()->count() === 0) {
            $data['is_default'] = true;
        }

        return $user->addresses()->create($data);
    }

    /**
     * Оновити адресу
     */
    public function update(UserAddress $address, array $data): UserAddress
    {
        if (! empty($data['is_default'])) {
            $this->clearDefaults($address->user);
        }

        $address->update($data);

        return $address->fresh();
    }

    /**
     * Видалити адресу
     */
    public function delete(UserAddress $address): void
    {
        $wasDefault = $address->is_default;
        $user = $address->user;
        $address->delete();

        // Якщо видалена адреса була за замовчуванням — призначити першу залишену
        if ($wasDefault) {
            $first = $user->addresses()->first();
            if ($first) {
                $first->update(['is_default' => true]);
            }
        }
    }

    /**
     * Встановити адресу за замовчуванням
     */
    public function setDefault(User $user, int $addressId): void
    {
        $this->clearDefaults($user);
        $user->addresses()->where('id', $addressId)->update(['is_default' => true]);
    }

    /**
     * Зняти прапорець "за замовчуванням" з усіх адрес
     */
    private function clearDefaults(User $user): void
    {
        $user->addresses()->update(['is_default' => false]);
    }
}
