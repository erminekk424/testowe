<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class OctaneMetricsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $start = microtime(true);
        $startMemory = memory_get_usage(true);

        $response = $next($request);

        $duration = microtime(true) - $start;
        $memoryUsed = memory_get_usage(true) - $startMemory;

        Log::info('Request metrics', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'duration_ms' => round($duration * 1000, 2),
            'memory_mb' => round($memoryUsed / 1024 / 1024, 2),
            'response_code' => $response->status(),
        ]);

        return $response;
    }
}
