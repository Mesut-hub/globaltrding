<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Prevent MIME-type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Prevent clickjacking
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Enable XSS protection
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer policy (protect user privacy)
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions policy (formerly Feature-Policy)
        $response->headers->set('Permissions-Policy', implode(', ', [
            'camera=()',
            'microphone=()',
            'geolocation=()',
            'payment=()',
            'usb=()',
            'magnetometer=()',
            'gyroscope=()',
            'accelerometer=()',
        ]));

        // HSTS (HTTP Strict-Transport-Security) - ONLY in production
        if (app()->environment('production')) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        // Content Security Policy - STRONG
        $csp = $this->buildCSP($request);
        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }

    private function buildCSP(Request $request): string
    {
        $appUrl = rtrim((string) config('app.url'), '/');
        $scheme = parse_url($appUrl, PHP_URL_SCHEME) ?? 'https';
        $host = parse_url($appUrl, PHP_URL_HOST) ?? request()->getHost();

        return implode('; ', [
            // Default: restrict everything
            "default-src 'self' {$scheme}://{$host}",

            // Scripts: self + inline (if needed for critical CSS/JS)
            "script-src 'self' {$scheme}://{$host} 'unsafe-inline' https://cdn.jsdelivr.net 'unsafe-eval'",

            // Styles: self + inline
            "style-src 'self' {$scheme}://{$host} 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com",

            // Images: self + data + https
            "img-src 'self' {$scheme}://{$host} data: https:",

            // Fonts
            "font-src 'self' {$scheme}://{$host} https://fonts.gstatic.com data:",

            // Media
            "media-src 'self' {$scheme}://{$host} https:",

            // Forms
            "form-action 'self' {$scheme}://{$host}",

            // Frames
            "frame-ancestors 'none'",

            // Base URI
            "base-uri 'self'",

            // Upgrade insecure requests in production
            app()->environment('production') ? 'upgrade-insecure-requests' : '',
        ]);
    }
}