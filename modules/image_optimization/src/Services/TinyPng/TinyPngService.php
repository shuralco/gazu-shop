<?php

namespace App\Services\TinyPng;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TinyPngService
{
    private string $apiKey;
    private string $apiUrl = 'https://api.tinify.com/shrink';

    public function __construct()
    {
        $this->apiKey = config('tinypng.api_key');
    }

    public function isEnabled(): bool
    {
        return config('tinypng.enabled', true) && !empty($this->apiKey);
    }

    /**
     * Compress image file and return optimized content
     */
    public function compress(string $filePath): ?array
    {
        if (!$this->isEnabled()) {
            return null;
        }

        try {
            $content = file_get_contents($filePath);
            if (!$content) {
                return null;
            }

            $originalSize = strlen($content);

            // Upload to TinyPNG
            $response = Http::withBasicAuth('api', $this->apiKey)
                ->connectTimeout(10)
                ->timeout(60)
                ->withBody($content, 'application/octet-stream')
                ->post($this->apiUrl);

            if (!$response->successful()) {
                Log::error('TinyPNG compress failed', [
                    'status' => $response->status(),
                    'error' => $response->json('error') ?? $response->body(),
                ]);
                return null;
            }

            $result = $response->json();
            $outputUrl = $result['output']['url'] ?? null;
            $compressedSize = $result['output']['size'] ?? $originalSize;
            $ratio = $result['output']['ratio'] ?? 1;

            if (!$outputUrl) {
                return null;
            }

            // Download compressed image
            $options = $this->getResizeOptions();

            if (!empty($options) || config('tinypng.convert_to_webp')) {
                // Post-process: resize and/or convert
                $postData = [];

                if (!empty($options)) {
                    $postData['resize'] = $options;
                }

                if (config('tinypng.convert_to_webp')) {
                    $postData['convert'] = ['type' => ['image/webp']];
                }

                $downloadResponse = Http::withBasicAuth('api', $this->apiKey)
                    ->connectTimeout(10)
                    ->timeout(60)
                    ->post($outputUrl, $postData);

                if ($downloadResponse->successful()) {
                    $optimizedContent = $downloadResponse->body();
                    $compressedSize = strlen($optimizedContent);
                } else {
                    // Fallback: just download without resize/convert
                    $downloadResponse = Http::connectTimeout(10)->timeout(30)->get($outputUrl);
                    $optimizedContent = $downloadResponse->body();
                }
            } else {
                $downloadResponse = Http::connectTimeout(10)->timeout(30)->get($outputUrl);
                $optimizedContent = $downloadResponse->body();
            }

            $savings = $originalSize - $compressedSize;
            $savingsPercent = $originalSize > 0 ? round(($savings / $originalSize) * 100, 1) : 0;

            Log::info('TinyPNG optimized', [
                'file' => basename($filePath),
                'original' => $this->formatBytes($originalSize),
                'compressed' => $this->formatBytes($compressedSize),
                'savings' => "{$savingsPercent}%",
            ]);

            return [
                'content' => $optimizedContent,
                'original_size' => $originalSize,
                'compressed_size' => $compressedSize,
                'savings' => $savings,
                'savings_percent' => $savingsPercent,
                'is_webp' => config('tinypng.convert_to_webp'),
            ];
        } catch (\Throwable $e) {
            Log::error('TinyPNG error', ['error' => $e->getMessage(), 'file' => $filePath]);
            return null;
        }
    }

    /**
     * Compress and save to storage
     */
    public function compressAndSave(string $storagePath, string $disk = 'public'): ?array
    {
        $fullPath = Storage::disk($disk)->path($storagePath);

        if (!file_exists($fullPath)) {
            return null;
        }

        $result = $this->compress($fullPath);
        if (!$result || empty($result['content'])) {
            return null;
        }

        // Determine new path (change extension to .webp if converted)
        $newPath = $storagePath;
        if ($result['is_webp']) {
            $newPath = Str::beforeLast($storagePath, '.') . '.webp';
        }

        Storage::disk($disk)->put($newPath, $result['content']);

        return array_merge($result, ['path' => $newPath, 'original_path' => $storagePath]);
    }

    /**
     * Batch compress multiple files
     */
    public function batchCompress(array $storagePaths, string $disk = 'public'): array
    {
        $results = [];
        $totalSaved = 0;

        foreach ($storagePaths as $path) {
            $result = $this->compressAndSave($path, $disk);
            if ($result) {
                $results[] = $result;
                $totalSaved += $result['savings'];
            }
        }

        return [
            'processed' => count($results),
            'total_saved' => $totalSaved,
            'total_saved_formatted' => $this->formatBytes($totalSaved),
            'results' => $results,
        ];
    }

    /**
     * Get compression count for current month
     */
    public function getUsage(): ?int
    {
        try {
            $response = Http::withBasicAuth('api', $this->apiKey)
                ->connectTimeout(5)
                ->timeout(10)
                ->post($this->apiUrl, '');

            // TinyPNG returns compression count in header even on error
            return (int) $response->header('Compression-Count');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function getResizeOptions(): array
    {
        $maxWidth = config('tinypng.max_width', 1920);
        if ($maxWidth > 0) {
            return [
                'method' => 'scale',
                'width' => $maxWidth,
            ];
        }
        return [];
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }
}
