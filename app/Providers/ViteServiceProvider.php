<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class ViteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Auto-configure Vite based on environment
        $this->configureViteEnvironment();
    }
    
    private function configureViteEnvironment(): void
    {
        $hotFile = public_path('hot');
        $manifestFile = public_path('build/manifest.json');
        
        // If no hot file and no build manifest, auto-build for production
        if (!File::exists($hotFile) && !File::exists($manifestFile)) {
            if (app()->environment('production')) {
                $this->ensureBuildAssets();
            }
        }
        
        // Auto-clear config cache when environment changes
        if (app()->configurationIsCached()) {
            $this->clearConfigCache();
        }
    }
    
    private function ensureBuildAssets(): void
    {
        try {
            // Auto-build assets if missing in production
            if (!app()->runningInConsole()) {
                dispatch(function () {
                    Artisan::call('optimize:clear');
                    shell_exec('cd ' . base_path() . ' && npm run build > /dev/null 2>&1 &');
                })->delay(now()->addSeconds(1));
            }
        } catch (\Exception $e) {
            // Silent fail - don't break the app
            logger()->warning('ViteServiceProvider: Could not auto-build assets', [
                'error' => $e->getMessage()
            ]);
        }
    }
    
    private function clearConfigCache(): void
    {
        try {
            if (app()->runningInConsole()) {
                Artisan::call('config:clear');
            }
        } catch (\Exception $e) {
            // Silent fail
        }
    }
}