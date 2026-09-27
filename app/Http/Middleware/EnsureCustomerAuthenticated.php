<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureCustomerAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::guard('customer')->check()) {
            return redirect()->route('portal.login', ['locale' => $request->route('locale')]);
        }

        return $next($request);
    }
}