<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\CustomerCredentialService;
use App\Services\RecaptchaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PortalAuthController extends Controller
{
    public function __construct(private CustomerCredentialService $credentials) {}

    public function showLogin(string $locale)
    {
        if (Auth::guard('customer')->check()) {
            return redirect(route('portal.home', ['locale' => $locale]));
        }

        return view('portal.auth.login', ['recaptchaSiteKey' => config('services.recaptcha.site_key')]);
    }

    public function login(Request $request, string $locale, RecaptchaService $recaptcha)
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string'],
            'g-recaptcha-response' => ['required'],
        ]);

        if (! $recaptcha->verify($data['g-recaptcha-response'], $request->ip())) {
            return back()->withErrors(['g-recaptcha-response' => 'Anti-robot verification failed. Please try again.']);
        }

        $candidate = Customer::where('username', $data['username'])->first();

        if ($candidate) {
            if ($candidate->isBlocked()) {
                $this->credentials->log($candidate, 'login_failed', ['reason' => 'blocked']);
                return back()->withErrors(['username' => 'This account is blocked. Please contact your account manager.']);
            }
            if ($candidate->isSuspended()) {
                $this->credentials->log($candidate, 'login_failed', ['reason' => 'suspended']);
                return back()->withErrors(['username' => 'This account is suspended.']);
            }
        }

        if (! Auth::guard('customer')->attempt(['username' => $data['username'], 'password' => $data['password']])) {
            if ($candidate) {
                $this->credentials->log($candidate, 'login_failed', ['reason' => 'invalid_password']);
            }
            return back()->withErrors(['username' => 'These credentials do not match our records.'])->withInput(['username' => $data['username']]);
        }

        $request->session()->regenerate();

        /** @var Customer $customer */
        $customer = Auth::guard('customer')->user();
        $customer->forceFill(['last_login_at' => now(), 'last_login_ip' => $request->ip()])->save();
        $this->credentials->log($customer, 'login');

        return redirect()->intended(route('portal.home', ['locale' => $locale]));
    }

    public function logout(Request $request, string $locale)
    {
        $customer = Auth::guard('customer')->user();
        if ($customer) {
            $this->credentials->log($customer, 'logout');
        }

        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(route('portal.login', ['locale' => $locale]));
    }

    public function showReset(string $locale)
    {
        return view('portal.auth.reset');
    }

    public function reset(Request $request, string $locale)
    {
        $data = $request->validate([
            'customer_code' => ['required', 'string'],
            'name' => ['required', 'string'],
            'phone' => ['required', 'string'],
        ]);

        $customer = Customer::where('customer_code', $data['customer_code'])->first();

        // Match against company name or any registered contact's name, and any phone on file
        $identityMatches = $customer
            && (str_contains(strtolower($customer->company_name), strtolower($data['name']))
                || $customer->contacts()->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($data['name']) . '%'])->exists())
            && (($customer->phone && trim($customer->phone) === trim($data['phone']))
                || $customer->contacts()->where('phone', $data['phone'])->exists());

        // Always show the same message, whether it matched or not — don't reveal which part failed
        if ($identityMatches) {
            $this->credentials->resetPassword($customer);
        }

        return back()->with('reset_submitted', true);
    }

    public function showChangePassword(string $locale)
    {
        return view('portal.auth.change-password');
    }

    public function changePassword(Request $request, string $locale)
    {
        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        /** @var Customer $customer */
        $customer = Auth::guard('customer')->user();
        $customer->password = Hash::make($data['password']);
        $customer->must_change_password = false;
        $customer->save();

        $this->credentials->log($customer, 'password_changed_by_customer');

        return redirect(route('portal.home', ['locale' => $locale]));
    }
}