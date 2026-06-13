<?php

namespace App\Http\Controllers;

use App\Models\CustomerActivityLog;
use App\Models\User;
use App\Services\CustomerAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductAuthController extends Controller
{
    public function __construct(private CustomerAccountService $accountService) {}

    // ── Show login form ───────────────────────────────────────────────────────

    public function show(Request $request, string $locale)
    {
        // Already authenticated product users shouldn't see the login page
        if (Auth::guard('product')->check()) {
            $user = Auth::guard('product')->user();
            if ($user?->canAccessProducts()) {
                return redirect("/{$locale}/products");
            }
        }

        return view('auth.login');
    }

    // ── Handle login ──────────────────────────────────────────────────────────

    public function login(Request $request, string $locale)
    {
        $data = $request->validate([
            'email'       => ['required', 'email', 'max:255'],
            'password'    => ['required', 'string'],
            'remember_me' => ['nullable', 'boolean'],
        ]);

        $remember = (bool) ($data['remember_me'] ?? false);

        // ── Pre-auth: look up the user and check account status BEFORE
        //    attempting authentication (avoids hashing on blocked accounts)
        /** @var User|null $candidate */
        $candidate = User::query()
            ->where('email', strtolower(trim($data['email'])))
            ->first();

        if ($candidate) {
            if ($candidate->isBlocked()) {
                $this->accountService->logActivity(
                    $candidate,
                    CustomerActivityLog::ACTION_LOGIN_FAILED,
                    ['reason' => 'Account is blocked', 'ip' => $request->ip()],
                    null,
                    $request->ip(),
                    $request->userAgent(),
                );

                return back()
                    ->withErrors(['email' => __('auth.account_blocked')])
                    ->withInput(['email' => $data['email']]);
            }

            if ($candidate->isSuspended()) {
                $this->accountService->logActivity(
                    $candidate,
                    CustomerActivityLog::ACTION_LOGIN_FAILED,
                    [
                        'reason' => 'Account is suspended',
                        'until'  => $candidate->suspended_until?->toDateTimeString(),
                        'ip'     => $request->ip(),
                    ],
                    null,
                    $request->ip(),
                    $request->userAgent(),
                );

                return back()
                    ->withErrors([
                        'email' => __('auth.account_suspended') .
                            ($candidate->suspended_until
                                ? ' ' . __('auth.suspended_until', [
                                    'date' => $candidate->suspended_until->format('d M Y H:i'),
                                ])
                                : ''),
                    ])
                    ->withInput(['email' => $data['email']]);
            }
        }

        // ── Attempt authentication ─────────────────────────────────────────
        $credentials = ['email' => $data['email'], 'password' => $data['password']];

        if (! Auth::guard('product')->attempt($credentials, $remember)) {
            // Log failed attempt (use candidate if found, otherwise skip logging to DB)
            if ($candidate) {
                $this->accountService->logActivity(
                    $candidate,
                    CustomerActivityLog::ACTION_LOGIN_FAILED,
                    ['reason' => 'Invalid password', 'ip' => $request->ip()],
                    null,
                    $request->ip(),
                    $request->userAgent(),
                );
            }

            return back()
                ->withErrors(['email' => __('auth.failed')])
                ->withInput(['email' => $data['email']]);
        }

        /** @var User $user */
        $user = Auth::guard('product')->user();

        // ── Post-auth: verify product access permission ────────────────────
        if (! $user->has_product_access) {
            Auth::guard('product')->logout();
            $request->session()->regenerateToken();

            return back()
                ->withErrors(['email' => __('auth.not_approved')])
                ->withInput(['email' => $data['email']]);
        }

        // ── Session hygiene ────────────────────────────────────────────────
        $request->session()->regenerate();

        // ── Track login ────────────────────────────────────────────────────
        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        $this->accountService->logActivity(
            $user,
            CustomerActivityLog::ACTION_LOGIN,
            ['ip' => $request->ip()],
            null,
            $request->ip(),
            $request->userAgent(),
        );

        return redirect()->intended("/{$locale}/products");
    }

    // ── Handle logout ─────────────────────────────────────────────────────────

    public function logout(Request $request, string $locale)
    {
        /** @var User|null $user */
        $user = Auth::guard('product')->user();

        if ($user) {
            $this->accountService->logActivity(
                $user,
                CustomerActivityLog::ACTION_LOGOUT,
                ['ip' => $request->ip()],
                null,
                $request->ip(),
                $request->userAgent(),
            );
        }

        Auth::guard('product')->logout();

        // Full session invalidation — security requirement
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect("/{$locale}/login")
            ->with('success', __('auth.logged_out'));
    }
}