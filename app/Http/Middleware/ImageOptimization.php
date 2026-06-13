<?php

namespace App\Http\Middleware;

use Closure;

class ImageOptimization
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // Add cache headers for images
        if ($request->getPathInfo() && preg_match('/\.(jpg|jpeg|png|gif|webp|svg)$/i', $request->getPathInfo())) {
            $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
        }

        return $response;
    }
}