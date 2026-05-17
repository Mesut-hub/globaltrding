@php
  $app = rtrim(config('app.url'), '/');
  $locale = config('locales.default', 'en');
  $resetUrl = $app . '/' . $locale . '/reset-password/' . urlencode($token) . '?email=' . urlencode($user->email);
@endphp

<p>Hello {{ e($user->name) }},</p>
<p>Your registration request has been approved.</p>
<p>Please set your password using the link below:</p>
<p><a href="{{ $resetUrl }}">{{ $resetUrl }}</a></p>
<p>After setting your password, you can login and access full product details.</p>
<p>Regards,<br>Global Trading</p>