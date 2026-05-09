<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\LoyaltyService;
use Illuminate\Console\Command;

class AwardBirthdayBonuses extends Command
{
    protected $signature = 'loyalty:birthday-bonuses';

    protected $description = 'Award birthday bonus points';

    public function handle(LoyaltyService $loyaltyService): int
    {
        $this->info('Awarding birthday bonuses...');

        $awarded = 0;

        User::whereNotNull('birthdate')
            ->whereMonth('birthdate', now()->month)
            ->whereDay('birthdate', now()->day)
            ->chunk(100, function ($users) use ($loyaltyService, &$awarded) {
                foreach ($users as $user) {
                    $points = $loyaltyService->awardBirthdayBonus($user);
                    if ($points > 0) {
                        $awarded++;
                    }
                }
            });

        $this->info("Done. Awarded birthday bonuses to {$awarded} users.");

        return self::SUCCESS;
    }
}
