<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductAuthController extends Controller
{
    public function show(Request $request, string $locale)
    {
        return view('auth.login');
    }

    public function login(Request $request, string $locale)
    {
        $data = $request->validate([
            'email' => ['required','email'],
            'password' => ['required','string'],
        ]);

        if (!Auth::guard('product')->attempt(['email' => $data['email'], 'password' => $data['password']], true)) {
            return back()->withErrors(['email' => 'Invalid credentials.'])->withInput();
        }

        $u = Auth::guard('product')->user();
        if (!$u || !$u->has_product_access) {
            Auth::guard('product')->logout();
            //$request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors(['email' => 'Your account is not approved for product access yet.'])->withInput();
        }

        $request->session()->regenerate();

        return redirect("/{$locale}/products");
    }

    public function logout(Request $request, string $locale)
    {
        Auth::guard('product')->logout();
        //$request->session()->invalidate();
        //$request->session()->regenerateToken();

        return redirect("/{$locale}/products");
    }
}