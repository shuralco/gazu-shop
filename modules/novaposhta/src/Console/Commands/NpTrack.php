<?php

namespace App\Console\Commands;

use App\Services\Shipping\NovaPoshtaTracking;
use Illuminate\Console\Command;

class NpTrack extends Command
{
    protected $signature = 'np:track {--silent : Suppress output}';
    protected $description = 'Track all active Nova Poshta shipments and update statuses';

    public function handle(NovaPoshtaTracking $tracker): int
    {
        $stats = $tracker->trackAll();

        if (! $this->option('silent')) {
            $this->info("Total active: {$stats['total']}");
            $this->info("Updated: {$stats['updated']}");
            $this->info("Status changed: {$stats['status_changed']}");
        }

        return self::SUCCESS;
    }
}
