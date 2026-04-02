<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocaleFromPath
{
    public function handle(Request $request, Closure $next)
    {
        $supported = config('locales.supported', ['en']);
        $default = config('locales.default', 'en');

        $segment = $request->segment(1);

        $locale = in_array($segment, $supported, true) ? $segment : $default;

        App::setLocale($locale);

        return $next($request);
    }
}