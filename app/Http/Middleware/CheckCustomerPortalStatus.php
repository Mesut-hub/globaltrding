<?php

namespace App\Http\Middleware;

use App\Models\Customer;
use App\Services\CustomerCredentialService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckCustomerPortalStatus
{
    public function __construct(private CustomerCredentialService $credentials) {}

    public function handle(Request $request, Closure $next): Response
    {
        /** @var Customer|null $customer */
        $customer = Auth::guard('customer')->user();

        if ($customer === null) {
            return $next($request);
        }

        $locale = app()->getLocale();

        if ($customer->isBlocked() || ($customer->status === Customer::STATUS_SUSPENDED && $customer->isSuspended())) {
            Auth::guard('customer')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $this->credentials->log($customer, 'auto_logout_blocked_or_suspended');

            return redirect("/{$locale}/portal/login")
                ->with('portal_auth_error', $customer->isBlocked() ? 'blocked' : 'suspended');
        }

        // Force password change before anything else, except the change-password page itself
        if ($customer->must_change_password && ! $request->routeIs('portal.password.change*')) {
            return redirect(route('portal.password.change', ['locale' => $locale]));
        }

        return $next($request);
    }
}