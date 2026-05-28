<?php

namespace App\Console\Commands;

use App\Services\TinyPng\TinyPngService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class OptimizeImages extends Command
{
    protected $signature = 'images:optimize {--path=products : Directory in storage/app/public} {--limit=50 : Max images to process}';

    protected $description = 'Optimize images via TinyPNG API';

    public function handle(TinyPngService $service): int
    {
        if (!$service->isEnabled()) {
            $this->error('TinyPNG is not configured. Set TINYPNG_API_KEY in .env');
            return self::FAILURE;
        }

        $path = $this->option('path');
        $limit = (int) $this->option('limit');

        $this->info("Scanning storage/app/public/{$path}...");

        $files = collect(Storage::disk('public')->allFiles($path))
            ->filter(fn ($f) => preg_match('/\.(jpg|jpeg|png|gif)$/i', $f))
            ->take($limit);

        if ($files->isEmpty()) {
            $this->warn('No images found.');
            return self::SUCCESS;
        }

        $this->info("Found {$files->count()} images. Optimizing...");

        $bar = $this->output->createProgressBar($files->count());
        $totalSaved = 0;
        $processed = 0;

        foreach ($files as $file) {
            $result = $service->compressAndSave($file);
            if ($result) {
                $processed++;
                $totalSaved += $result['savings'];
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $formatted = $totalSaved >= 1048576
            ? round($totalSaved / 1048576, 2) . ' MB'
            : round($totalSaved / 1024, 1) . ' KB';

        $this->info("Done! Processed: {$processed}/{$files->count()}, Saved: {$formatted}");

        $usage = $service->getUsage();
        if ($usage !== null) {
            $this->info("TinyPNG API usage this month: {$usage}/500 compressions");
        }

        return self::SUCCESS;
    }
}
