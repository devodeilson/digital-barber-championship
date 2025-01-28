<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    public function handle(Request $request, Closure $next, ...$permissions)
    {
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        foreach ($permissions as $permission) {
            if (!$request->user()->hasPermission($permission)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permission denied'
                ], 403);
            }
        }

        return $next($request);
    }
} 