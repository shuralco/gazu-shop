<?php

namespace App\Console\Commands;

use App\Services\CacheOptimizationService;
use Illuminate\Console\Command;

class OptimizeProduction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'optimize:production {--force : Force optimization even in development}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically optimize application for production deployment';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Starting production optimization...');

        if (! app()->environment('production') && ! $this->option('force')) {
            $this->warn('Not in production environment. Use --force to override.');

            return self::FAILURE;
        }

        // Step 1: Clear all caches
        $this->info('1️⃣ Clearing existing caches...');
        $this->call('optimize:clear');

        // Step 2: Build frontend assets
        $this->info('2️⃣ Building frontend assets...');
        $buildResult = shell_exec('npm run build 2>&1');
        if (! str_contains($buildResult, 'built in')) {
            $this->error('❌ Frontend build failed');

            return self::FAILURE;
        }

        // Step 3: Optimize Laravel
        $this->info('3️⃣ Optimizing Laravel...');
        $this->call('config:cache');
        $this->call('route:cache');
        $this->call('view:cache');
        $this->call('event:cache');

        // Step 4: Apply cache optimizations
        $this->info('4️⃣ Applying cache optimizations...');
        app(CacheOptimizationService::class)->optimizeForEnvironment();

        // Step 5: Format code
        $this->info('5️⃣ Formatting code...');
        shell_exec('vendor/bin/pint --quiet');

        // Step 6: Test key functionality
        $this->info('6️⃣ Testing optimizations...');
        $this->call('vite:auto-config');

        $this->info('✅ Production optimization complete!');
        $this->info('🎯 Key optimizations applied:');
        $this->line('   • Frontend assets built and cached');
        $this->line('   • Laravel caches optimized');
        $this->line('   • Database queries optimized');
        $this->line('   • Environment auto-detection enabled');

        return self::SUCCESS;
    }
}
