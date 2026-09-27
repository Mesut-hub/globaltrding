<?php

namespace App\Http\Middleware;

use App\Support\FilamentPermissions;
use Closure;
use Illuminate\Http\Request;

class EnsureOpsAccess
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless(FilamentPermissions::allowed(auth()->user(), 'customer_registration'), 403);
        return $next($request);
    }
}