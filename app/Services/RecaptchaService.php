<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RecaptchaService
{
    public function verify(?string $token, ?string $ip = null): bool
    {
        $secret = (string) config('services.recaptcha.secret');
        if ($secret === '' || !$token) return false;

        $res = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $secret,
            'response' => $token,
            'remoteip' => $ip,
        ]);

        if (!$res->ok()) return false;

        $json = $res->json();
        return (bool)($json['success'] ?? false);
    }
}