<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthSession
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated via session
        if (!session()->has('authenticated') || !session()->get('authenticated')) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        return $next($request);
    }
}
