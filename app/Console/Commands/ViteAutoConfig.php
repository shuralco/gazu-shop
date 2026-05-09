<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ViteAutoConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vite:auto-config {--force-build : Force build assets even if dev server is available}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically configure Vite for current environment (dev/production/mobile)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔧 Auto-configuring Vite environment...');
        
        $hotFile = public_path('hot');
        $manifestFile = public_path('build/manifest.json');
        $forceBuild = $this->option('force-build');
        
        // Check current state
        $hasHotFile = File::exists($hotFile);
        $hasManifest = File::exists($manifestFile);
        
        $this->info("Environment: " . app()->environment());
        $this->info("Hot file exists: " . ($hasHotFile ? '✅' : '❌'));
        $this->info("Manifest exists: " . ($hasManifest ? '✅' : '❌'));
        
        if ($forceBuild || (!$hasHotFile && !$hasManifest)) {
            $this->warn('Building production assets...');
            $this->call('optimize:clear');
            
            $buildResult = shell_exec('cd ' . base_path() . ' && npm run build 2>&1');
            
            if (str_contains($buildResult, 'built in')) {
                $this->info('✅ Assets built successfully');
            } else {
                $this->error('❌ Build failed: ' . $buildResult);
                return self::FAILURE;
            }
        }
        
        // Test asset loading
        $this->info('Testing asset loading...');
        $assetHtml = \App\Helpers\ViteHelper::renderAssets();
        
        if (str_contains($assetHtml, 'build/assets/')) {
            $this->info('✅ Using production assets');
        } else {
            $this->info('🔧 Using development assets');
        }
        
        $this->info('🎉 Vite auto-configuration complete!');
        
        return self::SUCCESS;
    }
}
