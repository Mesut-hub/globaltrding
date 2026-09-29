<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ __('portal.change_password.title') }} — {{ __('portal.brand') }}</title>
@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50">
  <div class="max-w-sm mx-auto pt-24 px-6">
    <h1 class="text-xl font-bold mb-1">{{ __('portal.change_password.title') }}</h1>
    <p class="text-sm text-slate-500 mb-6">{{ __('portal.change_password.subtitle') }}</p>

    @if ($errors->any())
      <div class="mb-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg px-3 py-2">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('portal.password.change.post', app()->getLocale()) }}" class="space-y-4">
      @csrf
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1.5">{{ __('portal.change_password.new_password') }}</label>
        <input type="password" name="password" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm" required>
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1.5">{{ __('portal.change_password.confirm_password') }}</label>
        <input type="password" name="password_confirmation" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm" required>
      </div>
        <button class="w-full bg-[#C99A3D] text-[#241A05] font-bold py-2.5 rounded-lg text-sm">{{ __('portal.change_password.submit') }}</button>
    </form>
  </div>
</body>
</html>