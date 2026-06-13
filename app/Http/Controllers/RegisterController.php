<?php

namespace App\Http\Controllers;

use App\Models\RegistrationRequest;
use App\Services\RecaptchaService;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    public function step1(Request $request, string $locale)
    {
        return view('auth.register-step1');
    }

    public function postStep1(Request $request, string $locale)
    {
        $data = $request->validate([
            'first_name' => ['required','string','max:100'],
            'last_name' => ['required','string','max:100'],
            'email' => ['required','email','max:255'],
            'occupation' => ['nullable','string','max:120'],
            'mobile_phone' => ['required','string','max:50'],
            'primary_product_interest' => ['nullable','string','max:255'],
            'preferred_language' => ['required','string','max:40'],
            'accepted_terms' => ['accepted'],
        ]);

        $request->session()->put('reg_step1', $data);

        return redirect("/{$locale}/register/step2");
    }

    public function step2(Request $request, string $locale)
    {
        if (!$request->session()->has('reg_step1')) {
            return redirect("/{$locale}/register");
        }

        return view('auth.register-step2', [
            'recaptchaSiteKey' => config('services.recaptcha.site_key'),
        ]);
    }

    public function postStep2(Request $request, string $locale, RecaptchaService $recaptcha)
    {
        $step1 = $request->session()->get('reg_step1');
        if (!is_array($step1)) return redirect("/{$locale}/register");

        $data2 = $request->validate([
            'company' => ['required','string','max:255'],
            'existing_customer' => ['required','in:yes,no'],
            'location' => ['required','string','max:120'],
            'city' => ['required','string','max:120'],
            'street_and_number' => ['required','string','max:255'],
            'zip_code' => ['required','string','max:30'],
            'industries_operate' => ['nullable','string','max:255'],
            'message' => ['nullable','string','max:2000'],
            'g-recaptcha-response' => ['required'],
        ]);

        $ok = $recaptcha->verify($data2['g-recaptcha-response'], $request->ip());
        if (!$ok) {
            return back()
                ->withErrors(['g-recaptcha-response' => 'Anti-robot verification failed. Please try again.'])
                ->withInput();
        }

        $rr = RegistrationRequest::create([
            ...$step1,
            'company' => $data2['company'],
            'existing_customer' => $data2['existing_customer'] === 'yes',
            'location' => $data2['location'],
            'city' => $data2['city'],
            'street_and_number' => $data2['street_and_number'],
            'zip_code' => $data2['zip_code'],
            'industries_operate' => $data2['industries_operate'] ?? null,
            'message' => $data2['message'] ?? null,
            'status' => 'submitted',
            'ip' => $request->ip(),
            'user_agent' => (string)$request->userAgent(),
        ]);

        $request->session()->forget('reg_step1');

        return redirect("/{$locale}/register/success");
    }

    public function success(Request $request, string $locale)
    {
        return view('auth.register-success');
    }
}