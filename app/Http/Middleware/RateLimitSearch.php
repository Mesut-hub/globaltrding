<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitSearch
{
    public function handle(Request $request, Closure $next): Response
    {
        // key = IP + locale (prevents one user spamming)
        $key = 'search:' . $request->ip() . ':' . ($request->route('locale') ?? 'xx');

        // Allow 30 requests per minute per IP
        if (RateLimiter::tooManyAttempts($key, 30)) {
            return response()->json([
                'q' => (string) $request->query('q', ''),
                'results' => [],
                'error' => 'Too many requests. Please slow down.',
            ], 429);
        }

        RateLimiter::hit($key, 60);

        return $next($request);
    }
}