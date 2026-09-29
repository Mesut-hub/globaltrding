<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ __('portal.customer_portal') }} — {{ __('portal.brand') }}</title>
<link rel="icon" href="{{ asset('images/portal/favicon.ico') }}">
@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900">
<div class="flex min-h-screen">
  <aside class="w-60 flex-shrink-0 bg-[#0B1220] text-white flex flex-col">
    <div class="px-5 py-5 border-b border-white/10">
      <div class="px-5 py-5 border-b border-white/10 flex items-center gap-2.5">
      <img src="{{ asset('images/portal/logo.png') }}" alt="Global Trading" class="h-8 w-8">
      <div>
        <div class="font-extrabold text-sm">GLOBAL <span class="text-[#C99A3D]">TRADING</span></div>
        <div class="text-[10px] text-slate-400">{{ __('portal.slogan') }}</div>
      </div>
    </div>
    <nav class="p-3 flex flex-col gap-1 flex-1">
      <a href="{{ route('portal.home', app()->getLocale()) }}" class="px-3 py-2 rounded-lg text-sm {{ request()->routeIs('portal.home') ? 'bg-[#C99A3D]/10 border-l-2 border-[#C99A3D] text-white' : 'text-slate-300 hover:bg-white/5' }}">{{ __('portal.nav.home') }}</a>
      <a href="{{ route('portal.orders.index', app()->getLocale()) }}" class="px-3 py-2 rounded-lg text-sm {{ request()->routeIs('portal.orders.*') ? 'bg-[#C99A3D]/10 border-l-2 border-[#C99A3D] text-white' : 'text-slate-300 hover:bg-white/5' }}">{{ __('portal.nav.orders') }}</a>
      <a href="{{ route('portal.status', app()->getLocale()) }}" class="px-3 py-2 rounded-lg text-sm {{ request()->routeIs('portal.status') ? 'bg-[#C99A3D]/10 border-l-2 border-[#C99A3D] text-white' : 'text-slate-300 hover:bg-white/5' }}">{{ __('portal.nav.status') }}</a>
    </nav>
  </aside>

  <div class="flex-1 min-w-0">
    <div class="h-15 bg-white border-b border-slate-200 flex items-center justify-end gap-3 px-6 relative">
      <div class="flex items-center gap-1 text-xs">
        @foreach(config('locales.supported') as $loc)
          <a href="{{ url()->current() === route('portal.home', app()->getLocale()) ? route('portal.home', $loc) : str_replace('/'.app()->getLocale().'/', '/'.$loc.'/', url()->full()) }}"
             class="px-2 py-1 rounded {{ app()->getLocale() === $loc ? 'bg-slate-900 text-white' : 'text-slate-500 hover:bg-slate-100' }}">{{ strtoupper($loc) }}</a>
        @endforeach
      </div>
      <button type="button" id="companyMenuBtn" class="flex items-center gap-2 border border-slate-200 rounded-lg px-3 py-1.5 text-sm font-medium hover:bg-slate-50">
        {{ Auth::guard('customer')->user()->company_name }}
        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="m6 9 6 6 6-6"/></svg>
      </button>
      <div id="companyMenu" style="display:none;" class="absolute top-14 {{ app()->getLocale() === 'ar' ? 'left-6' : 'right-6' }} w-52 bg-white border border-slate-200 rounded-lg shadow-lg p-1">
        <a href="#" class="block px-3 py-2 rounded-md text-sm hover:bg-slate-50">{{ __('portal.nav.company_profile') }}</a>
        <hr class="my-1 border-slate-200">
        <form method="POST" action="{{ route('portal.logout', app()->getLocale()) }}">@csrf
          <button class="w-full text-left px-3 py-2 rounded-md text-sm text-red-600 hover:bg-red-50">{{ __('portal.nav.log_out') }}</button>
        </form>
      </div>
    </div>
    <main class="p-7">
      {{ $slot }}
    </main>
  </div>
</div>
<script>
  document.getElementById('companyMenuBtn').addEventListener('click', function (e) {
    e.stopPropagation();
    const menu = document.getElementById('companyMenu');
    menu.style.display = menu.style.display === 'none' ? '' : 'none';
  });
  document.addEventListener('click', function (e) {
    const menu = document.getElementById('companyMenu');
    if (menu.style.display !== 'none' && !e.target.closest('#companyMenu') && !e.target.closest('#companyMenuBtn')) {
      menu.style.display = 'none';
    }
  });
</script>
</body>
</html>