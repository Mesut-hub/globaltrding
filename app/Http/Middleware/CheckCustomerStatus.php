<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\CustomerAccountService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckCustomerStatus
{
    public function __construct(private CustomerAccountService $accountService) {}

    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = Auth::guard('product')->user();

        // Only act when a product-guard user is authenticated
        if ($user === null) {
            return $next($request);
        }

        $locale = app()->getLocale();

        // ── Check: permanently blocked ────────────────────────────────────
        if ($user->isBlocked()) {
            Auth::guard('product')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $this->accountService->logActivity($user, 'auto_logout_blocked', [
                'ip'     => $request->ip(),
                'reason' => 'Session terminated — account is blocked',
            ]);

            return redirect("/{$locale}/login")
                ->with('product_auth_error', 'blocked')
                ->with('blocked_reason', $user->blocked_reason);
        }

        // ── Check: suspended (may have auto-expired) ──────────────────────
        if (($user->status ?? User::STATUS_ACTIVE) === User::STATUS_SUSPENDED) {
            if ($user->suspended_until && $user->suspended_until->isPast()) {
                // Suspension expired — quietly restore and continue
                $this->accountService->expireSuspension($user);
                return $next($request);
            }

            // Still suspended
            Auth::guard('product')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $this->accountService->logActivity($user, 'auto_logout_suspended', [
                'ip'    => $request->ip(),
                'until' => $user->suspended_until?->toDateTimeString(),
            ]);

            return redirect("/{$locale}/login")
                ->with('product_auth_error', 'suspended')
                ->with('suspended_until', $user->suspended_until?->toDateTimeString())
                ->with('suspended_reason', $user->suspended_reason);
        }

        // ── Check: product access revoked (active user but no access) ─────
        if (! $user->has_product_access) {
            Auth::guard('product')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect("/{$locale}/login")
                ->with('product_auth_error', 'access_revoked');
        }

        return $next($request);
    }
}