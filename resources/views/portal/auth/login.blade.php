<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ __('portal.login.title') }} — {{ __('portal.brand') }}</title>
<link rel="icon" href="{{ asset('images/portal/favicon.ico') }}">
@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 overflow-hidden">
<div class="flex h-screen">
  <div class="flex-1 flex items-center justify-center p-10">
    <div class="w-full max-w-sm">

      <div id="loginForm">
        <div class="flex items-center gap-2 mb-6">
          <img src="{{ asset('images/portal/logo.png') }}" alt="Global Trading" class="h-9 w-9">
          <div>
            <div class="font-extrabold text-sm text-slate-900">GLOBAL <span class="text-[#C99A3D]">TRADING</span></div>
            <div class="text-[10px] text-slate-500">{{ __('portal.slogan') }}</div>
          </div>
        </div>
        <p class="text-xs font-bold text-[#7A5A1E] uppercase tracking-wide mb-2">{{ __('portal.login.kicker') }}</p>
        <h1 class="text-2xl font-bold mb-1">{{ __('portal.login.title') }}</h1>
        <p class="text-sm text-slate-500 mb-6">{{ __('portal.login.subtitle') }}</p>

        @if ($errors->any())
          <div class="mb-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg px-3 py-2">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('portal.login.post', app()->getLocale()) }}" class="space-y-4">
          @csrf
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">{{ __('portal.login.username') }}</label>
            <input name="username" value="{{ old('username') }}" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm" required>
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">{{ __('portal.login.password') }}</label>
            <input type="password" name="password" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm" required>
          </div>
          <div class="g-recaptcha" data-sitekey="{{ $recaptchaSiteKey }}"></div>
          <button class="w-full bg-[#C99A3D] hover:bg-[#B98A2E] text-[#241A05] font-bold py-2.5 rounded-lg text-sm">{{ __('portal.login.submit') }}</button>
        </form>
        <div class="text-center mt-4">
          <button type="button" id="showResetBtn" class="text-sm font-semibold text-[#7A5A1E] hover:underline">{{ __('portal.login.forgot_password') }}</button>
        </div>
      </div>

      <div id="resetForm" style="display:none;">
        <div class="flex items-center gap-2 mb-6">
          <img src="{{ asset('images/portal/logo.png') }}" alt="Global Trading" class="h-9 w-9">
          <div>
            <div class="font-extrabold text-sm text-slate-900">GLOBAL <span class="text-[#C99A3D]">TRADING</span></div>
            <div class="text-[10px] text-slate-500">{{ __('portal.slogan') }}</div>
          </div>
        </div>
        <p class="text-xs font-bold text-[#7A5A1E] uppercase tracking-wide mb-2">{{ __('portal.reset.kicker') }}</p>
        <h1 class="text-2xl font-bold mb-1">{{ __('portal.reset.title') }}</h1>
        <p class="text-sm text-slate-500 mb-6">{{ __('portal.reset.subtitle') }}</p>

        @if (session('reset_submitted'))
          <div class="mb-4 text-sm text-green-700 bg-green-50 border border-green-200 rounded-lg px-3 py-2">{{ __('portal.reset.submitted') }}</div>
        @endif

        <form method="POST" action="{{ route('portal.reset.post', app()->getLocale()) }}" class="space-y-4">
          @csrf
          <div><label class="block text-xs font-semibold text-slate-600 mb-1.5">{{ __('portal.reset.customer_id') }}</label><input name="customer_code" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm" required></div>
          <div><label class="block text-xs font-semibold text-slate-600 mb-1.5">{{ __('portal.reset.name') }}</label><input name="name" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm" required></div>
          <div><label class="block text-xs font-semibold text-slate-600 mb-1.5">{{ __('portal.reset.phone') }}</label><input name="phone" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm" required></div>
          <button class="w-full bg-[#C99A3D] hover:bg-[#B98A2E] text-[#241A05] font-bold py-2.5 rounded-lg text-sm">{{ __('portal.reset.submit') }}</button>
        </form>
        <div class="text-center mt-4">
          <button type="button" id="backToLoginBtn" class="text-sm font-semibold text-[#7A5A1E] hover:underline">{{ __('portal.reset.back_to_signin') }}</button>
        </div>
      </div>

    </div>
  </div>
  <div class="flex-1 bg-gradient-to-br from-[#0B1220] to-[#1B2740] hidden md:flex flex-col justify-center p-8 overflow-y-auto overflow-x-hidden" id="loginHeroPanel">
    @php($loginPage = \App\Models\Page::where('slug', 'portal-login')->where('is_published', true)->first())
    @if($loginPage)
      <div class="text-white w-full space-y-6" id="loginHeroWrap">
        @foreach($loginPage->blocks ?? [] as $block)
          @include('shared.blocks.render', ['block' => $block])
        @endforeach
      </div>
    @else
      <div class="border border-dashed border-white/25 rounded-2xl p-10 max-w-sm mx-auto text-center text-slate-300">
        <p class="inline-block text-[11px] font-bold uppercase tracking-wide text-[#C99A3D] bg-[#C99A3D]/15 px-2.5 py-1 rounded-full mb-3">{{ __('portal.side_panel.tag') }}</p>
        <h3 class="text-white text-lg font-bold mb-2">{{ __('portal.side_panel.title') }}</h3>
        <p class="text-sm">{{ __('portal.side_panel.fallback_body', ['slug' => 'portal-login']) }}</p>
      </div>
    @endif
  </div>
</div>
<style>
  /* Scoped to this page only. */
  #loginHeroWrap, #loginHeroWrap * { max-width: 100%; box-sizing: border-box; }
  #loginHeroWrap h1, #loginHeroWrap h2, #loginHeroWrap h3, #loginHeroWrap p {
    overflow-wrap: break-word;
    word-break: break-word;
  }
  #loginHeroWrap .gt-hero--screen,
  #loginHeroWrap .gt-hero--xl,
  #loginHeroWrap .gt-hero--lg {
    height: auto !important;
    aspect-ratio: 4 / 5 !important;
    min-height: 360px !important;
  }
  #loginHeroWrap .gt-hero__content {
    position: absolute !important;
    inset: auto 20px 20px 20px !important;
    top: auto !important;
    left: auto !important;
    transform: none !important;
    max-width: calc(100% - 40px) !important;
    width: auto !important;
  }
  @media (min-width: 1024px) {
    #loginHeroWrap .gt-hero--screen,
    #loginHeroWrap .gt-hero--xl,
    #loginHeroWrap .gt-hero--lg {
      aspect-ratio: 16 / 9 !important;
    }
  }
</style>
@if($recaptchaSiteKey)<script src="https://www.google.com/recaptcha/api.js" async defer></script>@endif
<script>
  document.getElementById('showResetBtn').addEventListener('click', function () {
    document.getElementById('loginForm').style.display = 'none';
    document.getElementById('resetForm').style.display = '';
  });
  document.getElementById('backToLoginBtn').addEventListener('click', function () {
    document.getElementById('resetForm').style.display = 'none';
    document.getElementById('loginForm').style.display = '';
  });
  @if(session('reset_submitted'))
    document.getElementById('loginForm').style.display = 'none';
    document.getElementById('resetForm').style.display = '';
  @endif
</script>
</body>
</html>