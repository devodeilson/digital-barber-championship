<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AccessLog
{
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        
        $response = $next($request);
        
        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000; // em milissegundos

        $log = [
            'timestamp' => now()->toIso8601String(),
            'ip' => $request->ip(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'user_id' => $request->user()?->id,
            'status' => $response->status(),
            'duration' => round($duration, 2) . 'ms',
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('referer'),
            'request_body' => $this->sanitizeData($request->all()),
            'response_size' => strlen($response->getContent()),
        ];

        Log::channel('access')->info('API Access Log', $log);

        return $response;
    }

    protected function sanitizeData($data)
    {
        $sensitiveFields = ['password', 'password_confirmation', 'credit_card'];
        
        return collect($data)->map(function ($value, $key) use ($sensitiveFields) {
            if (in_array($key, $sensitiveFields)) {
                return '********';
            }
            return $value;
        })->toArray();
    }
} 