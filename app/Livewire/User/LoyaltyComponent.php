<?php

namespace App\Livewire\User;

use App\Models\LoyaltyTier;
use App\Services\LoyaltyService;
use Livewire\Component;
use Livewire\WithPagination;

class LoyaltyComponent extends Component
{
    use WithPagination;

    public function render()
    {
        $user = auth()->user();
        $loyaltyService = app(LoyaltyService::class);

        return view('livewire.user.loyalty-component', [
            'title' => 'Програма лояльності',
            'user' => $user,
            'tier' => $loyaltyService->getUserTier($user),
            'nextTier' => $loyaltyService->getNextTier($user),
            'progress' => $loyaltyService->getProgressToNextTier($user),
            'allTiers' => LoyaltyTier::active()->ordered()->get(),
            'transactions' => $user->loyaltyTransactions()->latest('created_at')->paginate(10),
            'redemptionRate' => (int) shopSetting('loyalty_redemption_rate', 100),
            'redemptionValue' => $loyaltyService->getRedemptionValue($user->loyalty_points),
        ]);
    }
}
