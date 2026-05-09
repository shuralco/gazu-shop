<?php

namespace App\Livewire\User;

use App\Services\LoyaltyService;
use Livewire\Component;

class AccountComponent extends Component
{
    public function render()
    {
        $user = auth()->user();
        $loyaltyService = app(LoyaltyService::class);

        return view('livewire.user.account-component', [
            'title' => 'Особистий кабінет',
            'user' => $user,
            'ordersCount' => $user->orders()->count(),
            'totalSpent' => $user->total_spent ?? 0,
            'recentOrders' => $user->orders()->with('orderProducts')->latest()->take(3)->get(),
            'tier' => $loyaltyService->getUserTier($user),
            'nextTier' => $loyaltyService->getNextTier($user),
            'tierProgress' => $loyaltyService->getProgressToNextTier($user),
            'wishlistCount' => $user->wishlistItems()->count(),
            'addressCount' => $user->addresses()->count(),
        ]);
    }
}
