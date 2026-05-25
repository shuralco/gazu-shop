<?php

namespace App\Policies;

use App\Models\CustomerGroup;
use App\Models\User;

class CustomerGroupPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_admin;
    }

    public function view(User $user, CustomerGroup $customerGroup): bool
    {
        return $user->is_admin;
    }

    public function create(User $user): bool
    {
        return $user->is_admin;
    }

    public function update(User $user, CustomerGroup $customerGroup): bool
    {
        return $user->is_admin;
    }

    public function delete(User $user, CustomerGroup $customerGroup): bool
    {
        return $user->is_admin;
    }

    public function restore(User $user, CustomerGroup $customerGroup): bool
    {
        return $user->is_admin;
    }

    public function forceDelete(User $user, CustomerGroup $customerGroup): bool
    {
        return $user->is_admin;
    }
}
