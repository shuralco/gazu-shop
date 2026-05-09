<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CsrfDiagnostic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:csrf-diagnostic';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Diagnose CSRF 419 errors and session issues';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 CSRF Diagnostic Analysis');
        $this->newLine();

        // 1. Check session configuration
        $this->checkSessionConfig();

        // 2. Check CSRF middleware
        $this->checkCsrfMiddleware();

        // 3. Check storage permissions
        $this->checkStoragePermissions();

        // 4. Check recent server logs
        $this->checkServerLogs();

        // 5. Test CSRF token generation
        $this->testCsrfGeneration();

        $this->newLine();
        $this->info('✅ CSRF Diagnostic Complete');
    }

    private function checkSessionConfig(): void
    {
        $this->info('📋 Session Configuration:');

        $config = config('session');
        $this->table(
            ['Setting', 'Value'],
            [
                ['Driver', $config['driver']],
                ['Lifetime', $config['lifetime'].' minutes'],
                ['Encrypt', $config['encrypt'] ? 'Yes' : 'No'],
                ['Cookie Name', $config['cookie']],
                ['Same Site', $config['same_site']],
                ['Secure', $config['secure'] ? 'Yes' : 'No'],
                ['Path', $config['path']],
                ['Domain', $config['domain'] ?: 'null'],
            ]
        );
        $this->newLine();
    }

    private function checkCsrfMiddleware(): void
    {
        $this->info('🛡️ CSRF Middleware Status:');

        // Check if CSRF is in global middleware
        $middleware = app()->make(\Illuminate\Contracts\Http\Kernel::class)->getGlobalMiddleware();
        $csrfFound = false;

        foreach ($middleware as $mw) {
            if (str_contains($mw, 'VerifyCsrfToken') || str_contains($mw, 'csrf')) {
                $this->line("✅ Found: {$mw}");
                $csrfFound = true;
            }
        }

        if (! $csrfFound) {
            $this->warn('⚠️ CSRF middleware not found in global middleware');
        }

        $this->newLine();
    }

    private function checkStoragePermissions(): void
    {
        $this->info('📁 Storage Permissions:');

        $paths = [
            'storage/framework/sessions' => storage_path('framework/sessions'),
            'storage/app' => storage_path('app'),
            'storage/logs' => storage_path('logs'),
            'bootstrap/cache' => base_path('bootstrap/cache'),
        ];

        foreach ($paths as $name => $path) {
            if (is_dir($path)) {
                $perms = substr(sprintf('%o', fileperms($path)), -4);
                $writable = is_writable($path) ? '✅' : '❌';
                $this->line("{$writable} {$name}: {$perms}");
            } else {
                $this->warn("❌ Missing: {$name}");
            }
        }

        $this->newLine();
    }

    private function checkServerLogs(): void
    {
        $this->info('📜 Recent Server Activity:');

        $logPath = storage_path('logs/laravel.log');
        if (file_exists($logPath)) {
            $lines = file($logPath);
            $recentLines = array_slice($lines, -10);

            foreach ($recentLines as $line) {
                if (str_contains($line, '419') || str_contains($line, 'CSRF') || str_contains($line, 'TokenMismatch')) {
                    $this->warn(trim($line));
                }
            }
        } else {
            $this->warn('❌ Laravel log file not found');
        }

        $this->newLine();
    }

    private function testCsrfGeneration(): void
    {
        $this->info('🔑 CSRF Token Test:');

        try {
            $token = csrf_token();
            $this->line('✅ Token generated: '.substr($token, 0, 10).'...');

            // Test if session is working
            session(['test_key' => 'test_value']);
            $retrieved = session('test_key');

            if ($retrieved === 'test_value') {
                $this->line('✅ Session storage working');
            } else {
                $this->warn('❌ Session storage not working');
            }

        } catch (\Exception $e) {
            $this->error('❌ CSRF token generation failed: '.$e->getMessage());
        }

        $this->newLine();
    }
}
