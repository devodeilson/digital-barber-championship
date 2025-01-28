<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter as Cache;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RateLimiter
{
    protected $limiter;

    public function __construct(Cache $limiter)
    {
        $this->limiter = $limiter;
    }

    public function handle(Request $request, Closure $next, $maxAttempts = 60, $decayMinutes = 1)
    {
        $key = $this->resolveRequestSignature($request);

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many requests',
                'retry_after' => $this->limiter->availableIn($key)
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        $this->limiter->hit($key, $decayMinutes * 60);

        $response = $next($request);

        return $response->header('X-RateLimit-Limit', $maxAttempts)
            ->header('X-RateLimit-Remaining', $maxAttempts - $this->limiter->attempts($key))
            ->header('X-RateLimit-Reset', $this->limiter->availableIn($key));
    }

    protected function resolveRequestSignature($request)
    {
        return sha1(implode('|', [
            $request->method(),
            $request->root(),
            $request->path(),
            $request->ip(),
            $request->userAgent()
        ]));
    }
} 