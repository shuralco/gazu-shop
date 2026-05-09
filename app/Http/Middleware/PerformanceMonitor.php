<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class PerformanceMonitor
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();
        $startQueries = DB::getQueryLog();

        // Enable query logging for this request
        DB::enableQueryLog();

        $response = $next($request);

        $endTime = microtime(true);
        $endMemory = memory_get_usage();
        $endQueries = DB::getQueryLog();

        // Calculate metrics
        $responseTime = round(($endTime - $startTime) * 1000, 2); // ms
        $memoryUsage = round(($endMemory - $startMemory) / 1024 / 1024, 2); // MB
        $queryCount = count($endQueries) - count($startQueries);

        // Log slow requests
        if ($responseTime > 1000) { // > 1 second
            logger()->warning('Slow request detected', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'response_time' => $responseTime.'ms',
                'memory_usage' => $memoryUsage.'MB',
                'query_count' => $queryCount,
                'user_agent' => $request->userAgent(),
            ]);
        }

        // Cache performance metrics for dashboard
        $this->cacheMetrics($request, $responseTime, $memoryUsage, $queryCount);

        // Add performance headers for development
        if (app()->environment('local')) {
            $response->headers->set('X-Response-Time', $responseTime.'ms');
            $response->headers->set('X-Memory-Usage', $memoryUsage.'MB');
            $response->headers->set('X-Query-Count', $queryCount);
        }

        return $response;
    }

    private function cacheMetrics(Request $request, float $responseTime, float $memoryUsage, int $queryCount): void
    {
        $key = 'performance_metrics_'.date('Y-m-d-H');

        $metrics = Cache::get($key, []);
        $metrics[] = [
            'timestamp' => now(),
            'url' => $request->path(),
            'method' => $request->method(),
            'response_time' => $responseTime,
            'memory_usage' => $memoryUsage,
            'query_count' => $queryCount,
            'is_mobile' => $this->isMobileRequest($request),
        ];

        // Keep only last 100 requests per hour
        if (count($metrics) > 100) {
            $metrics = array_slice($metrics, -100);
        }

        Cache::put($key, $metrics, 3600); // 1 hour
    }

    private function isMobileRequest(Request $request): bool
    {
        $userAgent = $request->userAgent();
        $mobilePatterns = ['Mobile', 'Android', 'iPhone', 'iPad'];

        foreach ($mobilePatterns as $pattern) {
            if (stripos($userAgent, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }
}
