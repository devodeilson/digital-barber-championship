<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Verifica se o usuário está logado e é admin
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', __t('messages.auth_required'));
        }

        if (!auth()->user()->is_admin) {
            return redirect()->route('home')->with('error', __t('messages.admin_required'));
        }

        return $next($request);
    }
}
