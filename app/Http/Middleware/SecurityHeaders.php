<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Proteção contra XSS e outros ataques
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        
        // Content Security Policy
        $response->headers->set('Content-Security-Policy', "
            default-src 'self';
            script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net;
            style-src 'self' 'unsafe-inline' https://fonts.googleapis.com;
            img-src 'self' data: https:;
            font-src 'self' https://fonts.gstatic.com;
            connect-src 'self';
            media-src 'self';
            object-src 'none';
            frame-src 'self';
            base-uri 'self';
            form-action 'self';
        ");

        return $response;
    }
} 