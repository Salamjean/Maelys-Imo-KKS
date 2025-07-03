<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AgenceMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    // AgenceMiddleware.php
    public function handle($request, Closure $next)
    {
        if (!Auth::guard('agence')->check()) {
            return redirect()->route('agence.login');
        }
        return $next($request);
    }
}
