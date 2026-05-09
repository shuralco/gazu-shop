<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\LoyaltyService;
use Illuminate\Console\Command;

class RecalculateLoyaltyTiers extends Command
{
    protected $signature = 'loyalty:recalculate-tiers';

    protected $description = 'Recalculate loyalty tiers for all users';

    public function handle(LoyaltyService $loyaltyService): int
    {
        $this->info('Recalculating loyalty tiers...');

        $count = 0;

        User::chunk(100, function ($users) use ($loyaltyService, &$count) {
            foreach ($users as $user) {
                $loyaltyService->recalculateTier($user);
                $count++;
            }
        });

        $this->info("Done. Recalculated tiers for {$count} users.");

        return self::SUCCESS;
    }
}
