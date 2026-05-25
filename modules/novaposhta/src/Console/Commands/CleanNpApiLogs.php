<?php

namespace App\Console\Commands;

use App\Models\ShippingApiLog;
use Illuminate\Console\Command;

class CleanNpApiLogs extends Command
{
    protected $signature = 'np:clean-api-logs
                            {--days=7 : Delete logs older than N days}
                            {--keep=2000 : Keep at most N most recent logs after age cleanup}';

    protected $description = 'Delete NP API logs older than --days and cap total to --keep';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $keep = (int) $this->option('keep');

        $deletedByAge = ShippingApiLog::where('created_at', '<', now()->subDays($days))->delete();
        $this->info("Removed {$deletedByAge} logs older than {$days} days.");

        $total = ShippingApiLog::count();
        if ($total > $keep) {
            $excess = $total - $keep;
            $idsToDelete = ShippingApiLog::orderBy('created_at', 'asc')
                ->limit($excess)
                ->pluck('id');
            ShippingApiLog::whereIn('id', $idsToDelete)->delete();
            $this->info("Removed {$excess} oldest logs to cap at {$keep}.");
        }

        $this->info('Remaining: '.ShippingApiLog::count());

        return self::SUCCESS;
    }
}
