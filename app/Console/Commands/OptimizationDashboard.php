<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class OptimizationDashboard extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'optimize:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show current optimization status and performance metrics';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('📊 SimpleShop Optimization Dashboard');
        $this->newLine();

        // Environment Status
        $this->showEnvironmentStatus();
        $this->newLine();

        // Asset Status
        $this->showAssetStatus();
        $this->newLine();

        // Cache Status
        $this->showCacheStatus();
        $this->newLine();

        // Performance Metrics
        $this->showPerformanceMetrics();
        $this->newLine();

        // Recommendations
        $this->showRecommendations();

        return self::SUCCESS;
    }

    private function showEnvironmentStatus(): void
    {
        $this->info('🌍 Environment Status');
        $this->table([
            'Setting', 'Value', 'Status',
        ], [
            ['Environment', app()->environment(), '✅'],
            ['Debug Mode', config('app.debug') ? 'ON' : 'OFF', config('app.debug') && app()->environment('production') ? '⚠️' : '✅'],
            ['Cache Driver', config('cache.default'), '✅'],
            ['Session Driver', config('session.driver'), '✅'],
            ['Auto Optimization', config('cache-optimization.auto_optimize', true) ? 'ON' : 'OFF', '✅'],
        ]);
    }

    private function showAssetStatus(): void
    {
        $this->info('📦 Asset Status');

        $hotFile = File::exists(public_path('hot'));
        $manifestFile = File::exists(public_path('build/manifest.json'));

        $this->table([
            'Asset Type', 'Status', 'Location',
        ], [
            ['Vite Dev Server', $hotFile ? '🟢 Running' : '⚪ Not Running', $hotFile ? 'http://127.0.0.1:5173' : '-'],
            ['Build Assets', $manifestFile ? '✅ Available' : '❌ Missing', $manifestFile ? '/build/assets/' : '-'],
            ['Auto Detection', config('vite-auto.auto_detect', true) ? '✅ Enabled' : '⚠️ Disabled', 'ViteHelper'],
        ]);
    }

    private function showCacheStatus(): void
    {
        $this->info('🗄️ Cache Status');

        $configCached = app()->configurationIsCached();
        $routesCached = app()->routesAreCached();

        $this->table([
            'Cache Type', 'Status', 'Size',
        ], [
            ['Configuration', $configCached ? '✅ Cached' : '⚪ Not Cached', $configCached ? 'Optimized' : '-'],
            ['Routes', $routesCached ? '✅ Cached' : '⚪ Not Cached', $routesCached ? 'Optimized' : '-'],
            ['Display Settings', Cache::has('display_settings_all') ? '✅ Cached' : '⚪ Not Cached', '1h TTL'],
            ['Mega Menu', Cache::has('mega_menu_structure') ? '✅ Cached' : '⚪ Not Cached', '1h TTL'],
        ]);
    }

    private function showPerformanceMetrics(): void
    {
        $this->info('⚡ Performance Metrics (Last Hour)');

        $metrics = Cache::get('performance_metrics_'.date('Y-m-d-H'), []);

        if (empty($metrics)) {
            $this->warn('No performance data available yet');

            return;
        }

        $avgResponseTime = round(array_sum(array_column($metrics, 'response_time')) / count($metrics), 2);
        $avgMemory = round(array_sum(array_column($metrics, 'memory_usage')) / count($metrics), 2);
        $avgQueries = round(array_sum(array_column($metrics, 'query_count')) / count($metrics), 1);
        $mobilePercent = round((count(array_filter($metrics, fn ($m) => $m['is_mobile'])) / count($metrics)) * 100, 1);

        $this->table([
            'Metric', 'Average', 'Status',
        ], [
            ['Response Time', $avgResponseTime.'ms', $avgResponseTime < 500 ? '✅ Good' : ($avgResponseTime < 1000 ? '⚠️ Fair' : '❌ Slow')],
            ['Memory Usage', $avgMemory.'MB', $avgMemory < 50 ? '✅ Good' : '⚠️ High'],
            ['SQL Queries', $avgQueries, $avgQueries < 10 ? '✅ Good' : '⚠️ High'],
            ['Mobile Traffic', $mobilePercent.'%', '📱 Info'],
            ['Total Requests', count($metrics), '📊 Info'],
        ]);
    }

    private function showRecommendations(): void
    {
        $this->info('💡 Optimization Recommendations');

        $recommendations = [];

        if (config('app.debug') && app()->environment('production')) {
            $recommendations[] = ['⚠️ HIGH', 'Disable debug mode in production', 'APP_DEBUG=false'];
        }

        if (! File::exists(public_path('build/manifest.json'))) {
            $recommendations[] = ['⚠️ MEDIUM', 'Build production assets', 'npm run build'];
        }

        if (! app()->configurationIsCached() && app()->environment('production')) {
            $recommendations[] = ['⚠️ MEDIUM', 'Cache Laravel configuration', 'php artisan optimize:production'];
        }

        if (empty($recommendations)) {
            $this->info('🎉 All optimizations are applied! Your app is running optimally.');
        } else {
            $this->table(['Priority', 'Recommendation', 'Action'], $recommendations);
        }
    }
}
