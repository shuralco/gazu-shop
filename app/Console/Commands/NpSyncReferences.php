<?php

namespace App\Console\Commands;

use App\Services\Shipping\NovaPoshtaReferenceSync;
use Illuminate\Console\Command;

class NpSyncReferences extends Command
{
    protected $signature = 'np:sync-references
        {--areas : Sync regions/areas}
        {--cities : Sync cities}
        {--warehouses : Sync warehouses}
        {--all : Sync everything (default if no flags)}
        {--city= : Sync warehouses only for one CityRef}';

    protected $description = 'Sync Nova Poshta reference data (areas, cities, warehouses) into local DB';

    public function handle(NovaPoshtaReferenceSync $sync): int
    {
        $all = $this->option('all') || (!$this->option('areas') && !$this->option('cities') && !$this->option('warehouses') && !$this->option('city'));

        if ($all || $this->option('areas')) {
            $this->info('Syncing areas...');
            $count = $sync->syncAreas();
            $this->info("  → {$count} areas synced");
        }

        if ($all || $this->option('cities')) {
            $this->info('Syncing cities (this can take 30-60 seconds)...');
            $bar = $this->output->createProgressBar();
            $bar->setFormat(" %current% cities synced [%elapsed:6s%]");
            $bar->start();
            $count = $sync->syncCities(progress: function ($n) use ($bar) {
                $bar->setProgress($n);
            });
            $bar->finish();
            $this->newLine();
            $this->info("  → {$count} cities synced");
        }

        if ($this->option('city')) {
            $this->info("Syncing warehouses for city {$this->option('city')}...");
            $count = $sync->syncWarehouses($this->option('city'));
            $this->info("  → {$count} warehouses synced");
            return self::SUCCESS;
        }

        if ($all || $this->option('warehouses')) {
            $this->info('Syncing warehouses (this can take 1-2 minutes)...');
            $bar = $this->output->createProgressBar();
            $bar->setFormat(" %current% warehouses synced [%elapsed:6s%]");
            $bar->start();
            $count = $sync->syncWarehouses(progress: function ($n) use ($bar) {
                $bar->setProgress($n);
            });
            $bar->finish();
            $this->newLine();
            $this->info("  → {$count} warehouses synced");
        }

        $this->info('Done.');
        return self::SUCCESS;
    }
}
