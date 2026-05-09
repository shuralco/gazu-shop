<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;

class ViteHelper
{
    /**
     * Smart Vite loading that automatically handles dev/production environments
     */
    public static function renderAssets(): string
    {
        if (!config('vite-auto.auto_detect', true)) {
            return '@vite([\'resources/css/app.css\', \'resources/js/app.js\'])';
        }
        
        $manifestPath = public_path('build/manifest.json');
        $hotFilePath = public_path('hot');
        
        // Force production assets if configured or accessing remotely
        $forceProduction = config('vite-auto.force_production_assets', false) || 
                          self::isRemoteAccess();
        
        // Check if Vite dev server is running and we're in local environment
        $isDevMode = File::exists($hotFilePath) && 
                    app()->environment('local') && 
                    !$forceProduction;
        
        if ($isDevMode) {
            // Development mode - use Vite dev server
            $hotUrl = trim(File::get($hotFilePath));
            return self::renderDevAssets($hotUrl);
        } else {
            // Production mode - use built assets
            if (File::exists($manifestPath)) {
                return self::renderProdAssets($manifestPath);
            } else {
                // Auto-build if missing
                self::autoBuildAssets();
                return self::renderFallbackAssets();
            }
        }
    }
    
    private static function renderDevAssets(string $hotUrl): string
    {
        return sprintf(
            '<script type="module" src="%s/@vite/client"></script>' . "\n" .
            '<script type="module" src="%s/resources/css/app.css"></script>' . "\n" .
            '<script type="module" src="%s/resources/js/app.js"></script>',
            $hotUrl, $hotUrl, $hotUrl
        );
    }
    
    private static function renderProdAssets(string $manifestPath): string
    {
        $manifest = json_decode(File::get($manifestPath), true);
        $output = '';
        
        // CSS
        if (isset($manifest['resources/css/app.css'])) {
            $cssFile = $manifest['resources/css/app.css']['file'];
            $output .= sprintf('<link rel="stylesheet" href="%s">' . "\n", asset("build/{$cssFile}"));
        }
        
        // JS
        if (isset($manifest['resources/js/app.js'])) {
            $jsFile = $manifest['resources/js/app.js']['file'];
            $output .= sprintf('<script type="module" src="%s"></script>', asset("build/{$jsFile}"));
        }
        
        return $output;
    }
    
    private static function renderFallbackAssets(): string
    {
        // Direct asset links as fallback
        return 
            '<link rel="stylesheet" href="' . asset('build/assets/app-Bxt1uP-i.css') . '">' . "\n" .
            '<script type="module" src="' . asset('build/assets/app-YRh28VZA.js') . '"></script>';
    }
    
    /**
     * Check if assets need to be built
     */
    public static function needsBuild(): bool
    {
        $manifestPath = public_path('build/manifest.json');
        $hotFilePath = public_path('hot');
        
        return !File::exists($hotFilePath) && !File::exists($manifestPath);
    }
    
    /**
     * Check if accessing remotely (mobile/IP access)
     */
    private static function isRemoteAccess(): bool
    {
        if (!request()) {
            return false;
        }
        
        $host = request()->getHost();
        return !in_array($host, ['localhost', '127.0.0.1', '::1']);
    }
    
    /**
     * Auto-build assets if needed
     */
    private static function autoBuildAssets(): void
    {
        try {
            if (!app()->runningInConsole()) {
                // Background build to avoid blocking request
                shell_exec('cd ' . base_path() . ' && npm run build > /dev/null 2>&1 &');
            }
        } catch (\Exception $e) {
            logger()->warning('ViteHelper: Could not auto-build assets', [
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get the correct APP_URL based on environment
     */
    public static function getAppUrl(): string
    {
        if (self::isRemoteAccess()) {
            return 'http://' . config('vite-auto.local_ip', '192.168.0.123') . ':' . config('vite-auto.ports.app', 8003);
        }
        
        return config('app.url', 'http://localhost:8003');
    }
}